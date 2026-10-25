<?php
namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ReportRowImporter
{
    public function __construct(private ReportContentGenerator $generator) {}

    public function import(array $row, array $options): string
    {
        $filled = fn ($value) => $value !== null && (!is_string($value) || trim($value) !== '');
        $row = array_filter($row, $filled);
        foreach ($row as $key => &$value) if (!in_array($key, ['content', 'toc', 'tables_figures'])) $value = trim((string)$value);
        unset($value);
        $id = $row['id'] ?? null;
        // Legacy files sometimes used a report ID in the slug column.
        if (!$id && isset($row['slug']) && ctype_digit($row['slug'])) { $id = $row['slug']; unset($row['slug']); }
        if ($id && !ctype_digit((string)$id)) throw ValidationException::withMessages(['id' => 'Report ID must be an integer.']);
        $slug = $row['slug'] ?? Str::limit(Str::slug($row['title'] ?? ''), 150, '');
        $existing = $id ? DB::table('reports')->where('id', $id)->first() : ($slug !== '' ? DB::table('reports')->where('slug', $slug)->first() : null);
        if ($id && !$existing) throw ValidationException::withMessages(['id' => 'Report ID was not found.']);
        if ($existing && !$options['update_existing']) return 'skipped';
        if (!$existing && !isset($row['title'])) throw ValidationException::withMessages(['title' => 'A title is required for a new report.']);

        foreach (['category_id' => 'categories', 'publisher_id' => 'publishers'] as $key => $table) {
            if (!isset($row[$key]) && !$existing && !empty($options[$key])) $row[$key] = $options[$key];
            if (isset($row[$key]) && !ctype_digit((string)$row[$key])) {
                $row[$key] = DB::table($table)->where('name', $row[$key])->value('id');
                if (!$row[$key]) throw ValidationException::withMessages([$key => 'The '.$table.' name was not found.']);
            }
        }
        if (isset($row['pub_date'])) {
            try {
                $row['pub_date'] = is_numeric($row['pub_date']) ? Carbon::create(1899, 12, 30)->addDays((int)$row['pub_date'])->toDateString() : Carbon::parse($row['pub_date'])->toDateString();
            } catch (\Throwable) { throw ValidationException::withMessages(['pub_date' => 'Invalid publication date.']); }
        }
        $data = array_intersect_key($row, array_flip(['title','slug','category_id','publisher_id','keyword','pages','pub_date','region','status','meta_title','meta_description','meta_keywords']));
        if (!$existing) $data += ['slug' => $slug, 'keyword' => $this->generator->keyword($row['title']), 'pages' => 0, 'region' => 'Global', 'status' => 1, 'pub_date' => now()->toDateString()];
        $effective = array_merge($existing ? (array)$existing : [], $data);
        $rules = ['title' => 'required|string|max:1500', 'slug' => ['required','string','max:150',Rule::unique('reports','slug')->ignore($existing->id ?? null)], 'category_id' => 'required|exists:categories,id', 'publisher_id' => 'required|exists:publishers,id', 'keyword' => 'nullable|string|max:100', 'pub_date' => 'required|date', 'pages' => 'nullable|integer|min:0|max:2147483647', 'region' => 'nullable|string|max:50', 'status' => 'nullable|in:0,1', 'meta_title' => 'nullable|string|max:500', 'meta_description' => 'nullable|string|max:500', 'meta_keywords' => 'nullable|string|max:500', 'single_user_price' => 'nullable|integer|min:0|max:2147483647', 'multi_user_price' => 'nullable|integer|min:0|max:2147483647', 'corporate_price' => 'nullable|integer|min:0|max:2147483647', 'include_images' => 'nullable|in:0,1', 'regenerate_content' => 'nullable|in:0,1', 'image_url' => 'nullable|string|max:2000'];
        Validator::make($effective + $row, $rules)->validate();
        $includeImages = isset($row['include_images']) ? $row['include_images'] === '1' : $options['include_images'];
        if (!$includeImages && isset($row['image_url'])) throw ValidationException::withMessages(['image_url' => 'Remove image_url or enable include_images.']);
        $imageUrl = isset($row['image_url']) ? $this->generator->imageUrl($row['image_url']) : null;
        $segments = $existing ? (json_decode($existing->key_info ?? '', true) ?: []) : [];
        $segments = array_merge($segments, array_intersect_key($row, array_flip(['types', 'applications', 'key_players'])));
        if (!$existing || array_intersect(['types','applications','key_players'], array_keys($row))) $data['key_info'] = json_encode((object)$segments, JSON_UNESCAPED_UNICODE);
        $regenerate = isset($row['regenerate_content']) ? $row['regenerate_content'] === '1' : $options['regenerate_content'];
        $createdImage = null;
        try {
            return DB::transaction(function () use ($row, $existing, $data, $effective, $segments, $regenerate, $includeImages, $imageUrl, &$createdImage) {
                $data['updated_at'] = now();
                if ($existing) {
                    $id = $existing->id;
                    DB::table('reports')->where('id', $id)->update($data);
                } else {
                    $id = DB::table('reports')->insertGetId($data + ['uuid' => (string)Str::uuid(), 'created_at' => now()]);
                }
                $previous = DB::table('reports_meta')->where('report_id', $id)->first();
                $meta = array_intersect_key($row, array_flip(['content', 'toc', 'tables_figures']));
                $keyword = $effective['keyword'] ?: $this->generator->keyword($effective['title']);
                $generateContent = !isset($meta['content']) && (!$existing || $regenerate);
                if ($generateContent) {
                    $image = $imageUrl ?? '';
                    if ($includeImages && !$imageUrl) {
                        $generated = $this->generator->image($id, $keyword, $segments);
                        $image = $generated['url']; $createdImage = $generated['created'];
                    }
                    $meta['content'] = $this->generator->content($id, (int)$effective['category_id'], $keyword, $segments, $image, $includeImages);
                } elseif ($imageUrl) {
                    $meta['content'] = $this->generator->appendImage($meta['content'] ?? $previous->content ?? '', $imageUrl, $keyword);
                }
                if (!isset($meta['toc']) && (!$existing || $regenerate)) $meta['toc'] = $this->generator->toc($keyword, $segments);
                if ($meta) {
                    if ($previous) DB::table('reports_meta')->where('report_id', $id)->update($meta + ['updated_at' => now()]);
                    else DB::table('reports_meta')->insert($meta + ['report_id' => $id, 'created_at' => now(), 'updated_at' => now()]);
                }
                foreach (['single_user_price' => ['Single User License'], 'multi_user_price' => ['Multi User License'], 'corporate_price' => ['Corporate License', 'Corporate User License']] as $key => $names) {
                    if (!isset($row[$key])) continue;
                    $license = DB::table('reports_licenses')->where('report_id', $id)->whereIn('lic_name', $names)->orderBy('id')->first();
                    if ($license) DB::table('reports_licenses')->where('id', $license->id)->update(['lic_price' => (int)$row[$key]]);
                    else DB::table('reports_licenses')->insert(['report_id' => $id, 'lic_name' => $names[0], 'lic_price' => (int)$row[$key]]);
                }
                return $existing ? 'updated' : 'created';
            });
        } catch (\Throwable $e) {
            if ($createdImage && is_file($createdImage)) unlink($createdImage);
            throw $e;
        }
    }
}
