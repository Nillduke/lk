<?php
namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

class SitemapCatalogue
{
    public const LIMIT = 2500;
    public const KINDS = ['pages', 'categories', 'news', 'reports'];
    private ?array $pages = null;

    public function query(string $kind)
    {
        if ($kind === 'reports') {
            return DB::table('reports as r')
                ->where('r.status', 1)->whereNotNull('r.slug')->where('r.slug', '<>', '')
                ->groupBy('r.slug')->select('r.slug')->selectRaw('MIN(r.id) as sort_id')
                ->selectRaw("MAX(COALESCE(r.updated_at, r.pub_date)) as updated_at");
        }
        $table = $kind === 'news' ? 'news' : 'categories';
        $query = DB::table($table)->whereNotNull('slug')->where('slug', '<>', '');
        if ($kind === 'news') $query->where('status', 1)->whereNull('deleted_at');
        return $query->groupBy('slug')->select('slug')->selectRaw('MIN(id) as sort_id')->selectRaw('MAX(updated_at) as updated_at');
    }

    public function pageEntries(): array
    {
        if ($this->pages !== null) return $this->pages;
        $names = ['home','all_reports','news_list','aboutus','services','faqs','howToOrder','disclaimer','termsConditions','refundPolicy','privacyPolicy','contactus','html_sitemap'];
        $entries = [];
        foreach ($names as $name) $entries[route($name)] = ['loc' => route($name), 'updated_at' => null];
        foreach (DB::table('lr_pages')->where('status', 1)->orderBy('id')->get(['slug','updated_at']) as $page) {
            // Only include CMS slugs that the public router will actually display.
            if (!$page->slug || str_starts_with($page->slug, '/') || preg_match('~^(?:authuser|auth|uploads|storage|frontend|vendor)(?:/|$)~i', $page->slug)) continue;
            try {
                $matched = Route::getRoutes()->match(Request::create('/'.$page->slug));
                $allowed = $matched->getName() === 'cms.page' || in_array($matched->getName(), array_slice($names, 3), true);
                if (!$allowed) continue;
            } catch (\Throwable) { continue; }
            $location = url($page->slug);
            $entries[$location] = ['loc' => $location, 'updated_at' => $page->updated_at];
        }
        return $this->pages = array_values($entries);
    }

    public function count(string $kind): int
    {
        return $kind === 'pages' ? count($this->pageEntries()) : DB::query()->fromSub($this->query($kind), 'sitemap_entries')->count();
    }
    public function totalPages(string $kind): int { return (int)ceil($this->count($kind) / self::LIMIT); }
    public function entries(string $kind, int $page): array
    {
        if ($kind === 'pages') return array_slice($this->pageEntries(), ($page - 1) * self::LIMIT, self::LIMIT);
        $route = ['reports' => 'rd_view', 'news' => 'news_details', 'categories' => 'industry_list'][$kind];
        $rows = $this->query($kind)->orderBy('sort_id')->offset(($page - 1) * self::LIMIT)->limit(self::LIMIT)->get();
        // Read metadata dates only for this batch, not for the entire report catalogue.
        $metadata = $kind === 'reports' && $rows->isNotEmpty()
            ? DB::table('reports_meta')->whereIn('report_id', $rows->pluck('sort_id'))->groupBy('report_id')->select('report_id')->selectRaw('MAX(updated_at) as modified')->pluck('modified', 'report_id')
            : collect();
        return $rows->map(fn ($row) => ['loc' => route($route, $row->slug), 'updated_at' => max($row->updated_at ?? '', $metadata[$row->sort_id] ?? '')])->all();
    }
}
