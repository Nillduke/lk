<?php
namespace App\Http\Controllers;

use App\Services\SitemapCatalogue;
use Carbon\Carbon;

class SitemapController extends Controller
{
    private function xml(string $root, array $entries)
    {
        $writer = new \XMLWriter();
        $writer->openMemory(); $writer->startDocument('1.0', 'UTF-8');
        $writer->startElement($root); $writer->writeAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');
        foreach ($entries as $entry) {
            $writer->startElement($root === 'sitemapindex' ? 'sitemap' : 'url');
            $writer->writeElement('loc', $entry['loc']);
            if (!empty($entry['updated_at']) && !str_starts_with($entry['updated_at'], '0000-')) {
                try { $writer->writeElement('lastmod', Carbon::parse($entry['updated_at'])->toAtomString()); } catch (\Throwable) {}
            }
            $writer->endElement();
        }
        $writer->endElement(); $writer->endDocument();
        // Re-query on every request so imports, publishing and removals are reflected automatically.
        return response($writer->outputMemory(), 200, ['Content-Type' => 'application/xml; charset=UTF-8', 'Cache-Control' => 'no-cache, must-revalidate', 'X-Content-Type-Options' => 'nosniff']);
    }
    public function index(SitemapCatalogue $catalogue)
    {
        $entries = [];
        foreach (SitemapCatalogue::KINDS as $kind) {
            for ($page = 1, $total = $catalogue->totalPages($kind); $page <= $total; $page++) $entries[] = ['loc' => route('sitemap.part', [$kind, $page])];
        }
        return $this->xml('sitemapindex', $entries);
    }
    public function part(SitemapCatalogue $catalogue, string $kind, string $page)
    {
        $entries = $catalogue->entries($kind, (int)$page);
        abort_if(!$entries, 404);
        return $this->xml('urlset', $entries);
    }
    public function robots()
    {
        return response("User-agent: *\nDisallow:\n\nSitemap: ".route('sitemap.index')."\n", 200, ['Content-Type' => 'text/plain; charset=UTF-8', 'Cache-Control' => 'no-cache, must-revalidate']);
    }
}
