<?php
namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ReportContentGenerator
{
    public function keyword(string $title): string
    {
        $keyword = preg_split('/\s+market\b|\s+share,/i', $title, 2)[0];
        return trim(preg_replace('/^global\s+/i', '', $keyword));
    }

    public function image(int $id, string $keyword, array $segments): array
    {
        $svg = view('template.rdsvg', ['keyword' => $keyword, 'types' => $segments['types'] ?? '', 'applications' => $segments['applications'] ?? ''])->render();
        // Content-addressed names never overwrite another report's original image.
        $path = 'uploads/images/reports/'.$id.'/'.substr(hash('sha256', $svg), 0, 20).'.svg';
        $target = public_path($path);
        $created = !is_file($target);
        if ($created) {
            if (!is_dir(dirname($target)) && !mkdir(dirname($target), 0755, true) && !is_dir(dirname($target))) throw new \RuntimeException('Could not create report image directory.');
            if (file_put_contents($target, $svg, LOCK_EX) === false) throw new \RuntimeException('Could not write report image.');
        }
        return ['url' => '/'.$path, 'created' => $created ? $target : null];
    }

    public function content(int $id, int $category, string $keyword, array $segments, string $image, bool $includeImages): string
    {
        $html = view($category === 15 ? 'template.reports.content_template_healthcare' : 'template.reports.content_template_all', [
            'keyword' => $keyword, 'types_str' => $segments['types'] ?? '', 'applications_str' => $segments['applications'] ?? '',
            'key_players_str' => $segments['key_players'] ?? '', 'products_str' => '', 'image_src' => $image,
            'request_sample_link' => route('request_sample', $id, false),
        ])->render();
        if (!$includeImages) $html = preg_replace('~<p\b[^>]*class="imglink"[^>]*>\s*<img\b[^>]*>\s*</p>~i', '', $html);
        return $html;
    }

    public function toc(string $keyword, array $segments): string
    {
        $lists = [];
        foreach (['types', 'applications', 'key_players'] as $field) {
            // The Blade template escapes each segment before rendering it.
            $lists[$field] = array_map(fn ($item) => trim($item), array_filter(explode(',', $segments[$field] ?? ''), fn ($item) => trim($item) !== ''));
        }
        return view('template.reports.toc_template', $lists + ['keyword' => $keyword])->render();
    }

    public function imageUrl(string $url): string
    {
        $url = trim($url);
        if (preg_match('~[\x00-\x20\\\\]~', $url) || str_contains($url, '..')) $this->invalidImage();
        $parts = parse_url($url);
        if ($parts === false) $this->invalidImage();
        if (isset($parts['scheme'])) {
            if (!in_array(strtolower($parts['scheme']), ['http', 'https']) || empty($parts['host']) || isset($parts['user']) || isset($parts['pass'])) $this->invalidImage();
            // Reuse the local archive for the legacy host's upload paths.
            if (in_array(strtolower($parts['host']), ['eternityinsights.com', 'www.eternityinsights.com']) && str_starts_with($parts['path'] ?? '', '/uploads/')) return $this->imageUrl($parts['path']);
            return $url;
        }
        if (isset($parts['host']) || !preg_match('~^/?(?:uploads|frontend|storage)/[^?#]+\.(?:png|jpe?g|gif|webp|svg|avif)(?:[?#].*)?$~i', $url)) $this->invalidImage();
        $path = '/'.ltrim($parts['path'], '/');
        if (!is_file(public_path($path))) {
            $zip = new \ZipArchive;
            $found = $zip->open(storage_path('app/legacy-media.zip'), \ZipArchive::RDONLY) === true;
            $exists = $found && $zip->locateName(ltrim($path, '/')) !== false;
            if ($found) $zip->close();
            if (!$exists) throw ValidationException::withMessages(['image_url' => 'The local image does not exist. Upload it first, then use its returned path.']);
        }
        return '/'.ltrim($url, '/');
    }

    private function invalidImage(): never
    {
        throw ValidationException::withMessages(['image_url' => 'Use an HTTP(S) image URL or a local uploads, storage or frontend image path.']);
    }

    public function appendImage(string $content, string $url, string $keyword): string
    {
        // Re-importing the same image must not duplicate it.
        if (preg_match_all('~<img\b[^>]*\bsrc\s*=\s*([\'"])(.*?)\1~is', $content, $matches)) {
            foreach ($matches[2] as $source) if (html_entity_decode($source, ENT_QUOTES | ENT_HTML5, 'UTF-8') === $url) return $content;
        }
        $image = '<p class="imglink" data-report-import-image="1"><img src="'.e($url).'" alt="'.e($keyword).' Market"></p>';
        if (preg_match('~<p\b[^>]*data-report-import-image="1"[^>]*>.*?</p>~is', $content)) return preg_replace_callback('~<p\b[^>]*data-report-import-image="1"[^>]*>.*?</p>~is', fn () => $image, $content, 1);
        return $content."\n".$image;
    }
}
