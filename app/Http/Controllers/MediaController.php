<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class MediaController extends Controller
{
    public function show(Request $request, string $path)
    {
        abort_if(str_contains($path, '..') || str_contains($path, '\\') || str_contains($path, "\0") || str_contains($path, ':'), 404);
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $types = ['svg' => 'image/svg+xml', 'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'gif' => 'image/gif', 'webp' => 'image/webp', 'ico' => 'image/x-icon', 'pdf' => 'application/pdf', 'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'csv' => 'text/csv', 'txt' => 'text/plain', 'crtx' => 'application/octet-stream'];
        abort_unless(isset($types[$extension]), 404);
        $entry = 'uploads/'.ltrim($path, '/');
        // New admin uploads already exist locally and are not in the legacy ZIP.
        $local = realpath(public_path($entry));
        $uploadRoot = realpath(public_path('uploads'));
        if ($local && $uploadRoot && str_starts_with($local, $uploadRoot.DIRECTORY_SEPARATOR) && is_file($local)) {
            return response()->file($local, ['Content-Type' => $types[$extension], 'X-Content-Type-Options' => 'nosniff', 'Cache-Control' => 'public, max-age=86400']);
        }
        $archive = new \ZipArchive;
        abort_unless($archive->open(storage_path('app/legacy-media.zip'), \ZipArchive::RDONLY) === true, 503, 'The local media archive is unavailable.');
        try {
            $stat = $archive->statName($entry);
            abort_unless($stat && ! str_ends_with($stat['name'], '/'), 404);
            $target = public_path($entry);
            if (! is_file($target) || filesize($target) !== $stat['size']) {
                Cache::lock('original-media:'.sha1($entry), 30)->block(15, function () use ($archive, $entry, $target, $stat) {
                    clearstatcache(true, $target);
                    if (is_file($target) && filesize($target) === $stat['size']) {
                        return;
                    }
                    $directory = dirname($target);
                    if (! is_dir($directory)) {
                        mkdir($directory, 0755, true);
                    }
                    $temporary = tempnam($directory, 'media-');
                    try {
                        $source = $archive->getStream($entry);
                        abort_unless($source, 404);
                        $output = fopen($temporary, 'wb');
                        try {
                            stream_copy_to_stream($source, $output);
                        } finally {
                            fclose($source);
                            fclose($output);
                        }
                        abort_unless(filesize($temporary) === $stat['size'], 500, 'Media extraction failed.');
                        if (is_file($target)) {
                            unlink($target);
                        }
                        rename($temporary, $target);
                    } finally {
                        if (is_file($temporary)) {
                            unlink($temporary);
                        }
                    }
                });
            }
        } finally {
            $archive->close();
        }

        return response()->file($target, ['Content-Type' => $types[$extension], 'X-Content-Type-Options' => 'nosniff', 'Cache-Control' => 'public, max-age=86400']);
    }
}
