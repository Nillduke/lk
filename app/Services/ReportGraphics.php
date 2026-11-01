<?php
namespace App\Services;

class ReportGraphics
{
    public function period(): array
    {
        $p = config('report_graphics');
        $p['years'] = range($p['historical_start'], $p['forecast_end']);
        $p['range'] = $p['historical_start'].'-'.$p['forecast_end'];
        $p['label'] = 'Historical: '.$p['historical_start'].'-'.$p['historical_end'].'   |   Base year: '.$p['base_year'].'   |   Forecast: '.$p['forecast_start'].'-'.$p['forecast_end'];
        return $p;
    }

    public function kind(string $source, object $report): ?string
    {
        $url = parse_url(html_entity_decode($source, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        if ($url === false || (isset($url['host']) && !in_array(strtolower($url['host']), ['eternityinsights.com','www.eternityinsights.com','localhost','127.0.0.1'], true))) return null;
        $path = '/'.ltrim($url['path'] ?? '', '/');
        if (str_contains($path, '..') || str_contains($path, '\\') || str_contains($path, "\0")) return null;
        if ($path === '/frontend/images/market-size.svg') return 'size';
        if ($path === '/frontend/images/region-2.svg') return 'region';
        $slug = $report->slug ?? '';
        $keyword = \Illuminate\Support\Str::slug($report->keyword ?? '');
        $names = array_unique(array_filter([$slug, $keyword, preg_replace('/^global-/', '', $slug), preg_replace('/-market$/', '', preg_replace('/^global-/', '', $slug)), preg_replace('/-market$/', '', $keyword)]));
        foreach ($names as $name) if ($path === '/uploads/images/'.$name.'.svg') return 'taxonomy';
        if (preg_match('~^/uploads/images/reports/'.(int)$report->id.'/[a-f0-9]{20}\.svg$~D', $path)) return 'taxonomy';
        return null;
    }

    public function entries(object $report): array
    {
        $entries = [];
        preg_match_all('~<img\b[^>]*\bsrc\s*=\s*([\'\"])(.*?)\1~is', $report->content ?? '', $matches);
        foreach ($matches[2] as $source) {
            if ($kind = $this->kind($source, $report)) {
                $entries[] = ['kind'=>$kind,'source'=>$source,'key'=>$this->sourceKey($source)];
            }
        }
        return $entries;
    }

    public function sourceKey(string $source): string
    {
        return substr(hash('sha256', html_entity_decode($source, ENT_QUOTES | ENT_HTML5, 'UTF-8')), 0, 20);
    }

    public function sources(object $report): array
    {
        $sources = [];
        foreach ($this->entries($report) as $entry) $sources[$entry['kind']] ??= $entry['source'];
        return $sources;
    }

    public function content(object $report): string
    {
        $version = substr(hash('sha256', json_encode(config('report_graphics')).'v3'.($report->content ?? '')), 0, 16);
        $html = preg_replace_callback('~<img\b[^>]*>~is', function ($m) use ($report, $version) {
            return preg_replace_callback('~\bsrc\s*=\s*([\'\"])(.*?)\1~is', function ($src) use ($report, $version) {
                $kind = $this->kind($src[2], $report);
                if (!$kind) return $src[0];
                return 'src='.$src[1].e(route('report_graphic', ['id'=>$report->id,'kind'=>$kind,'source'=>$this->sourceKey($src[2])], false).'&v='.$version).$src[1];
            }, $m[0], 1);
        }, $report->content ?? '');
        // Synchronize the standard graph heading; historical research prose stays intact.
        if (!isset($this->sources($report)['size'])) return $html;
        return preg_replace_callback('~<h2\b[^>]*>[^<]*\bMarket Size,\s*20\d{2}\s*[-\x{2013}]\s*20\d{2}[^<]*</h2>~iu', fn ($m) => preg_replace('/20\d{2}\s*[-\x{2013}]\s*20\d{2}/u', $this->period()['range'], $m[0]), $html);
    }

    public function read(string $source): ?string
    {
        $path = ltrim(parse_url(html_entity_decode($source, ENT_QUOTES | ENT_HTML5, 'UTF-8'), PHP_URL_PATH) ?? '', '/');
        if (!preg_match('~^(?:frontend/images/(?:region-2|market-size)\.svg|uploads/images/[a-zA-Z0-9_/-]+\.svg)$~D', $path) || str_contains($path, '..')) return null;
        $file = public_path($path);
        if (is_file($file) && filesize($file) <= 2*1024*1024) return file_get_contents($file);
        if (!str_starts_with($path, 'uploads/')) return null;
        $zip = new \ZipArchive;
        if ($zip->open(storage_path('app/legacy-media.zip'), \ZipArchive::RDONLY) !== true) return null;
        try {
            $stat = $zip->statName($path);
            return $stat && $stat['size'] <= 2*1024*1024 ? ($zip->getFromName($path) ?: null) : null;
        } finally { $zip->close(); }
    }

    public function render(string $kind, string $source, string $keyword): ?string
    {
        if ($kind === 'size') return view('template.graphics.market-size', ['period'=>$this->period(), 'keyword'=>$keyword])->render();
        $svg = $this->read($source);
        if ($svg === null) return null;
        return $kind === 'taxonomy' ? $this->taxonomy($svg) : $this->stamp($svg);
    }

    public function taxonomy(string $svg): string
    {
        // Restrict changes to our generated placeholder chart; custom analytical SVGs stay untouched.
        if (!str_contains($svg, 'Market Size, Forecast and Y-o-Y Growth') || !str_contains($svg, 'id="Graph-2"')) return $svg;
        $doc = $this->document($svg);
        if (!$doc) return $svg;
        $xpath = new \DOMXPath($doc); $xpath->registerNamespace('s', 'http://www.w3.org/2000/svg');
        $graph = $xpath->query('//*[@id="Graph-2"]')->item(0);
        if (!$graph) return $svg;
        // The old graphic uses XX samples. Retain those placeholders rather than invent market values.
        $fragment = $doc->createDocumentFragment();
        $p = $this->period(); $bars=''; $points=[];
        foreach ($p['years'] as $i=>$year) {
            $x = 210 + $i * (1580 / (count($p['years']) - 1));
            $height = 95 + ($i % 4) * 35;
            $bars .= '<rect class="cls-8" x="'.($x-25).'" y="'.(605-$height).'" width="50" height="'.$height.'"/>';
            $bars .= '<text class="cls-11" text-anchor="middle" x="'.$x.'" y="638">'.$year.'</text>';
            if ($year === $p['base_year']) $bars .= '<text class="cls-12" text-anchor="middle" x="'.$x.'" y="'.(605-$height-25).'">XX</text>';
            $points[] = $x.','.(605-$height-55);
        }
        $bars .= '<rect class="cls-9" x="150" y="605" width="1700" height="6"/><polyline class="cls-10" points="'.implode(' ', $points).'"/>';
        // Remove only the old chart axis, sample values and line beside the graph group.
        $remove=[];
        foreach ($xpath->query('//s:text') as $node) if (in_array(trim($node->textContent), array_map('strval',range(2018,2028)), true) || trim($node->textContent)==='XX') $remove[]=$node;
        foreach ($xpath->query('//s:polyline[@class="cls-10"]') as $node) $remove[]=$node;
        foreach ($remove as $node) $node->parentNode->removeChild($node);
        while ($graph->firstChild) $graph->removeChild($graph->firstChild);
        $fragment->appendXML('<g xmlns="http://www.w3.org/2000/svg">'.$bars.'</g>'); $graph->appendChild($fragment);
        foreach ($xpath->query('//s:text') as $node) {
            if (str_contains($node->textContent,'Market Size, Forecast and Y-o-Y Growth')) {
                $node->nodeValue = preg_replace('/\b20\d{2}\s*[-\x{2013}]\s*20\d{2}\b/u', $p['range'], $node->textContent);
            }
        }
        return $this->stamp($doc->saveXML($doc->documentElement));
    }

    private function document(string $svg): ?\DOMDocument
    {
        $doc = new \DOMDocument; $previous=libxml_use_internal_errors(true);
        try { return $doc->loadXML($svg, LIBXML_NONET) ? $doc : null; }
        finally { libxml_clear_errors(); libxml_use_internal_errors($previous); }
    }

    public function stamp(string $svg): string
    {
        $doc=$this->document($svg); if (!$doc) return $svg;
        $root=$doc->documentElement;
        foreach (iterator_to_array($doc->getElementsByTagName('g')) as $node) if ($node->getAttribute('id')==='report-period') $node->parentNode->removeChild($node);
        $box=preg_split('/[\s,]+/', trim($root->getAttribute('viewBox')));
        if (count($box)!==4 || !is_numeric($box[2]) || !is_numeric($box[3])) return $svg;
        $width=(float)$box[2]; $height=(float)$box[3];
        // Idempotent: re-stamping a generated file must not grow its canvas repeatedly.
        $original=$root->getAttribute('data-period-original-height');
        if (is_numeric($original)) $height=(float)$original;
        $band=$width*.052;
        $root->setAttribute('data-period-original-height',(string)$height);
        $root->setAttribute('viewBox',implode(' ',[$box[0],$box[1],$width,$height+$band]));
        $root->removeAttribute('height'); $root->removeAttribute('width');
        $group=$doc->createElementNS('http://www.w3.org/2000/svg','g'); $group->setAttribute('id','report-period');
        $rect=$doc->createElementNS('http://www.w3.org/2000/svg','rect');
        foreach (['x'=>0,'y'=>$height,'width'=>$width,'height'=>$band,'fill'=>'#eef3fa'] as $k=>$v) $rect->setAttribute($k,(string)$v);
        $text=$doc->createElementNS('http://www.w3.org/2000/svg','text');
        foreach (['x'=>$width/2,'y'=>$height+$band*.62,'text-anchor'=>'middle','font-family'=>'Arial, sans-serif','font-size'=>$width*.017,'fill'=>'#182e75'] as $k=>$v) $text->setAttribute($k,(string)$v);
        $text->appendChild($doc->createTextNode($this->period()['label']));
        $group->appendChild($rect);$group->appendChild($text);$root->appendChild($group);
        return $doc->saveXML($root);
    }
}
