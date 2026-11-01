<?php
namespace App\Services;

use Illuminate\Support\Facades\DB;

class ReportContentCleanup
{
    public const REPORT_FIELDS = ['description', 'meta_description'];
    public const META_FIELDS = ['content', 'toc', 'tables_figures'];
    public const MAX_FIELD_BYTES = 1000000;

    public function protectedSubject(string $title): bool
    {
        return (bool) preg_match('~covid|coronavirus|sars[- ]?cov|pandemic~iu', $title);
    }

    public function transform(string $value, string $field, array $options): array
    {
        $changes = [];
        $replace = function ($pattern, $replacement) use (&$value, &$changes, $field) {
            $value = preg_replace_callback($pattern, function ($m) use (&$changes, $replacement, $field) {
                $after = is_callable($replacement) ? $replacement($m) : $replacement;
                $changes[] = ['field' => $field, 'before' => $m[0], 'after' => $after];
                return $after;
            }, $value);
        };
        // Only self-contained generic coverage sentences; never remove analytical paragraphs.
        if ($options['sentences'] ?? false) {
            $replace('~\b(?:The|This) report (?:also )?(?:considers|covers|assesses|examines) the impact of COVID[-\x{2010}-\x{2015} ]?19 on (?:the )?(?:global )?[^<>\r\n.!?]{1,350}\bMarket\.~iu', '');
        }
        if ($field === 'toc' && ($options['toc'] ?? false)) {
            $replace('~(?<![\d.])(\d+(?:\.\d+)+)[ \t]+Impact of COVID[-\x{2010}-\x{2015} ]?19 Pandemic on Global [^<>\r\n]{1,350}? Market(?=\s*(?:<|$|\r|\n))~iu', fn($m) => $m[1].' Market Landscape and Industry Developments');
            $replace('~(?<![\d.])(\d+(?:\.\d+)+)[ \t]+Pre[- ]covid\s*(?:&(?:amp;)?|and)\s*Post[- ]Covid Market Analysis(?=\s*(?:<|$|\r|\n))~iu', fn($m) => $m[1].' Current Market Trends and Outlook');
        }
        return ['value' => $value, 'changes' => $changes];
    }

    public function load(int $id, bool $lock = false): ?array
    {
        $q = DB::table('reports')->where('id', $id);
        $report = ($lock ? $q->lockForUpdate() : $q)->first(array_merge(['id','title','slug','status','updated_at'], self::REPORT_FIELDS));
        if (!$report) return null;
        // Bound memory and network use on shared hosting before loading long editor content.
        $lengths = DB::table('reports_meta')->where('report_id', $id)->selectRaw('id, LENGTH(content) AS content_bytes, LENGTH(toc) AS toc_bytes, LENGTH(tables_figures) AS figures_bytes')->get();
        $reason = count($lengths) > 1 ? 'Multiple content records: review this report in the editor.' : null;
        foreach ($lengths as $length) {
            if (max($length->content_bytes ?? 0, $length->toc_bytes ?? 0, $length->figures_bytes ?? 0) > self::MAX_FIELD_BYTES) $reason = 'Large content: review this report individually in the editor.';
        }
        $meta = null;
        if (!$reason) {
            $q = DB::table('reports_meta')->where('report_id', $id)->orderBy('id');
            $meta = ($lock ? $q->lockForUpdate() : $q)->first(array_merge(['id','report_id','updated_at'], self::META_FIELDS));
        }
        return ['report' => $report, 'meta' => $meta, 'reason' => $reason];
    }

    public function plan(array $source, array $options): array
    {
        $r = $source['report']; $m = $source['meta'];
        $reason = $this->protectedSubject($r->title) ? 'COVID / pandemic is part of this report?s subject. Edit relevant content manually.' : $source['reason'];
        $updates = ['reports' => [], 'reports_meta' => []]; $changes = [];
        if (!$reason) {
            foreach (['reports' => [$r, self::REPORT_FIELDS], 'reports_meta' => [$m, self::META_FIELDS]] as $table => [$record, $fields]) {
                if (!$record) continue;
                foreach ($fields as $field) {
                    $old = $record->$field;
                    $result = $this->transform((string)$old, $field, $options);
                    if ($result['value'] !== (string)$old) {
                        $updates[$table][$field] = $result['value'];
                        array_push($changes, ...$result['changes']);
                    }
                }
            }
        }
        $reference = null;
        foreach (array_merge((array)$r, (array)$m) as $value) {
            $text = html_entity_decode(strip_tags((string)$value), ENT_QUOTES | ENT_HTML5, 'UTF-8');
            if (preg_match('~covid|coronavirus|pandemic|sars[- ]?cov~iu', $text, $match, PREG_OFFSET_CAPTURE)) {
                $reference = trim(mb_strcut($text, max(0, $match[0][1] - 160), 500, 'UTF-8'));
                break;
            }
        }
        return ['id' => $r->id, 'title' => $r->title, 'slug' => $r->slug, 'status' => $r->status,
            'reason' => $reason, 'reference' => $reference, 'changes' => $changes, 'updates' => $updates,
            'fingerprint' => hash('sha256', serialize([$r, $m])),
            'remaining' => $m ? (bool)preg_match('~covid|coronavirus|pandemic|sars[- ]?cov~iu', implode(' ', array_merge(array_values($updates['reports'] + (array)$r), array_values($updates['reports_meta'] + (array)$m)) )) : false];
    }

    public function batch(array $filters): array
    {
        $window = DB::table('reports')->where('id', '>', $filters['after'])->orderBy('id');
        if ($filters['status'] !== 'all') $window->where('status', $filters['status'] === 'active' ? 1 : 0);
        if ($filters['category_id']) $window->where('category_id', $filters['category_id']);
        if ($filters['search'] !== '') $window->where('title', 'like', '%'.$filters['search'].'%');
        $ids = $window->limit(200)->pluck('id');
        if ($ids->isEmpty()) return ['rows' => [], 'after' => $filters['after'], 'more' => false, 'scanned' => 0];
        $q = DB::table('reports')->whereIn('id', $ids)->where(function ($q) {
            foreach (array_merge(['title','meta_title','meta_keywords','keyword','key_info'],self::REPORT_FIELDS) as $field) {
                foreach (['covid','coronavirus','pandemic','sars-cov'] as $term) $q->orWhere($field,'like','%'.$term.'%');
            }
            $q->orWhereExists(function ($q) {
                $q->selectRaw('1')->from('reports_meta')->whereColumn('reports_meta.report_id','reports.id')->where(function ($q) {
                    foreach (self::META_FIELDS as $field) foreach (['covid','coronavirus','pandemic','sars-cov'] as $term) $q->orWhere($field,'like','%'.$term.'%');
                });
            });
        });
        $matches = $q->orderBy('id')->limit($filters['batch_size'] + 1)->pluck('id');
        $overflow = $matches->count() > $filters['batch_size'];
        $shown = $matches->take($filters['batch_size']);
        $rows = [];
        foreach ($shown as $id) {
            if ($source = $this->load($id)) $rows[] = $this->plan($source, $filters);
        }
        return ['rows' => $rows, 'after' => $overflow ? (int)$shown->last() : (int)$ids->last(), 'more' => $overflow || $ids->count() === 200, 'scanned' => $ids->count()];
    }
}
