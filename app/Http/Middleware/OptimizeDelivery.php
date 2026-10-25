<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OptimizeDelivery
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        if ($response instanceof BinaryFileResponse || $response instanceof StreamedResponse) return $response;
        $original = $response instanceof \Illuminate\Http\Response ? $response->original : null;
        $type = $response->headers->get('Content-Type', '');
        if (str_contains($type, 'text/html') && !$request->is('admin/*', 'authuser/*') && !$request->routeIs('admin.*', 'dashboard') && !$request->routeIs('home_amp', 'rd_amp')) {
            $html = $response->getContent();
            if (!str_contains($html, '<html amp')) {
                // Preserve editor data, structured scripts, and comments byte for byte.
                $pattern = <<<'HTML_IMAGES'
~<!--[\s\S]*?-->|<(script|style|textarea|title|iframe|xmp)\b(?:[^>"']+|"[^"]*"|'[^']*')*>[\s\S]*?</\1\s*>|<img\b(?:[^>"']+|"[^"]*"|'[^']*')*>~i
HTML_IMAGES;
                $html = preg_replace_callback($pattern, fn ($m) => strncasecmp($m[0], '<img', 4) === 0 ? $this->image($m[0]) : $m[0], $html);
                $response->setContent($html);
            }
        }
        if (preg_match('~(?:^|,)\s*gzip(?:\s*;\s*q=([0-9.]+))?\s*(?:,|$)~i', $request->header('Accept-Encoding', ''), $encoding) && (!isset($encoding[1]) || (float)$encoding[1] > 0) && !$response->headers->has('Content-Encoding') && preg_match('~text/|application/(json|javascript|xml)~', $type) && strlen($response->getContent()) > 1024 && !$request->isMethod('HEAD') && !in_array($response->getStatusCode(), [204,304])) {
            $response->setContent(gzencode($response->getContent(), 6));
            $response->headers->set('Content-Encoding', 'gzip');
            $response->headers->remove('Content-Length');
            $response->setVary('Accept-Encoding', false);
        }
        if ($response instanceof \Illuminate\Http\Response) $response->original = $original;
        return $response;
    }

    private function image(string $tag): string
    {
        if (!preg_match('~\bsrc\s*=\s*([\'"])(.*?)\1~is', $tag, $match)) return $tag;
        $src = html_entity_decode($match[2], ENT_QUOTES | ENT_HTML5);
        $bytes = null; $file = null;
        if (preg_match('~^data:image/(?:png|jpeg|jpg|webp);base64,(.+)$~is', $src, $data)) {
            $key = sha1($src); $bytes = base64_decode($data[1], true);
        } else {
            $url = parse_url($src);
            if (isset($url['host']) && !in_array($url['host'], [request()->getHost(), 'localhost', '127.0.0.1', 'www.eternityinsights.com', 'eternityinsights.com'])) return $this->lazy($tag);
            $path = rawurldecode($url['path'] ?? '');
            if (str_contains($path, '/optimized/') || preg_match('~(?:^|/)(?:eternity_logo|logo)\.png$~i', $path)) return $tag;
            $file = realpath(public_path(ltrim($path, '/')));
            if (!$file && str_starts_with($path, '/uploads/') && !str_contains($path, '..') && !str_contains($path, '\\') && preg_match('~\.(png|jpe?g|webp|svg)$~i', $path)) {
                $zip = new \ZipArchive;
                if ($zip->open(storage_path('app/legacy-media.zip'), \ZipArchive::RDONLY) === true) {
                    $entry = ltrim($path, '/'); $original = $zip->getFromName($entry); $zip->close();
                    if ($original !== false && strlen($original) <= 20*1024*1024) {
                        $target = public_path($entry); if (!is_dir(dirname($target))) mkdir(dirname($target), 0755, true);
                        file_put_contents($target, $original, LOCK_EX); $file = realpath($target);
                    }
                }
            }
            if ($file && str_starts_with($file, public_path().DIRECTORY_SEPARATOR) && strtolower(pathinfo($file, PATHINFO_EXTENSION)) === 'svg') {
                $local = '/'.str_replace('%2F', '/', rawurlencode(ltrim($path, '/')));
                $tag = str_replace($match[0], 'src="'.$local.'"', $tag);
                return $this->lazy($tag);
            }
            if (!$file || !str_starts_with($file, public_path().DIRECTORY_SEPARATOR) || !preg_match('~\.(png|jpe?g|webp)$~i', $file)) return $this->lazy($tag);
            $key = sha1($path.'|'.filemtime($file).'|'.filesize($file));
        }
        $dir = public_path('frontend/optimized');
        $metaFile = $dir.'/'.$key.'.json';
        if (is_file($metaFile)) {
            $meta = json_decode(file_get_contents($metaFile), true);
        } else {
            $bytes ??= file_get_contents($file);
            if (!$bytes || strlen($bytes) > 20*1024*1024) return $tag;
            $size = @getimagesizefromstring($bytes);
            if (!$size || $size[0]*$size[1] > 30000000) return $tag;
            $image = @imagecreatefromstring($bytes);
            if (!$image) return $tag;
            if (!is_dir($dir)) mkdir($dir, 0755, true);
            $width = min(1920, $size[0]); $height = max(1, (int)round($size[1]*$width/$size[0]));
            if ($width !== $size[0]) { $resized = $this->resize($image, $width, $height); imagedestroy($image); if (!$resized) return $this->lazy($tag); $image = $resized; }
            imagepalettetotruecolor($image); imagealphablending($image, true); imagesavealpha($image, true);
            $target = $dir.'/'.$key.'.webp';
            $temp = tempnam($dir, 'opt-'); $written = @imagewebp($image, $temp, 82); imagedestroy($image);
            if (!$written || !filesize($temp) || !rename($temp, $target)) { if (is_file($temp)) unlink($temp); return $this->lazy($tag); }
            $meta = ['width'=>$width, 'height'=>$height, 'url'=>'/frontend/optimized/'.$key.'.webp'];
            file_put_contents($metaFile, json_encode($meta), LOCK_EX);
        }
        if (!$meta) return $tag;
        if ($meta['width'] > 480 && !isset($meta['srcset'])) {
            $base = public_path(ltrim($meta['url'], '/'));
            $image = @imagecreatefromwebp($base); $sources = [];
            if ($image) {
                foreach ([480, 960] as $width) {
                    if ($width >= $meta['width']) continue;
                    $height = max(1, (int)round($meta['height']*$width/$meta['width']));
                    $resized = $this->resize($image, $width, $height);
                    if (!$resized) continue;
                    imagealphablending($resized, true); imagesavealpha($resized, true);
                    $small = str_replace('.webp', '-'.$width.'.webp', $base);
                    $temp = tempnam($dir, 'opt-'); $written = @imagewebp($resized, $temp, 82); imagedestroy($resized);
                    if (!$written || !filesize($temp) || !rename($temp, $small)) { if (is_file($temp)) unlink($temp); continue; }
                    $sources[] = str_replace('.webp', '-'.$width.'.webp', $meta['url']).' '.$width.'w';
                }
                imagedestroy($image);
                $sources[] = $meta['url'].' '.$meta['width'].'w'; $meta['srcset'] = implode(', ', $sources);
                file_put_contents($metaFile, json_encode($meta), LOCK_EX);
            }
        }
        if (isset($meta['srcset']) && !preg_match('~\bsrcset\s*=~i', $tag)) {
            $sizes = (request()->routeIs('news_list') || request()->routeIs('home') && !str_contains($tag, 'fetchpriority=')) ? '(max-width:767px) calc(100vw - 40px), (max-width:991px) 45vw, 360px' : '(max-width:767px) 100vw, 960px';
            if (request()->routeIs('home') && str_contains($tag, 'fetchpriority=')) $sizes = '(max-width:767px) 100vw, 100vw';
            $tag = substr($tag, 0, -1).' srcset="'.$meta['srcset'].'" sizes="'.$sizes.'">';
        }
        $tag = str_replace($match[0], 'src="'.$meta['url'].'"', $tag);
        if (!preg_match('~\bwidth\s*=~i', $tag) && !preg_match('~\bheight\s*=~i', $tag)) $tag = substr($tag,0,-1).' width="'.$meta['width'].'" height="'.$meta['height'].'">';
        if (!preg_match('~\bdecoding\s*=~i', $tag)) $tag = substr($tag,0,-1).' decoding="async">';
        return $this->lazy($tag);
    }

    private function resize(\GdImage $image, int $width, int $height): \GdImage|false
    {
        $resized = @imagescale($image, $width, $height, IMG_BICUBIC_FIXED);
        if ($resized) return $resized;
        // Some shared-hosting GD builds cannot use the bicubic scaler.
        $resized = @imagecreatetruecolor($width, $height);
        if (!$resized) return false;
        imagealphablending($resized, false); imagesavealpha($resized, true);
        imagefill($resized, 0, 0, imagecolorallocatealpha($resized, 0, 0, 0, 127));
        if (!@imagecopyresampled($resized, $image, 0, 0, 0, 0, $width, $height, imagesx($image), imagesy($image))) { imagedestroy($resized); return false; }
        return $resized;
    }

    private function lazy(string $tag): string
    {
        if (!preg_match('~\b(?:loading|fetchpriority)\s*=~i', $tag) && !preg_match('~logo|brand|favicon~i', $tag)) $tag = substr($tag, 0, -1).' loading="lazy">';
        return $tag;
    }
}
