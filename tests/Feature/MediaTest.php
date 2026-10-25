<?php

namespace Tests\Feature;

use Tests\TestCase;

class MediaTest extends TestCase
{
    public function test_original_media_is_available_from_the_local_laravel_archive(): void
    {
        $zip = new \ZipArchive;
        $this->assertTrue($zip->open(storage_path('app/legacy-media.zip')) === true);
        $entry = null;
        for ($i = $zip->numFiles - 1; $i >= 0; $i--) {
            $name = $zip->getNameIndex($i);
            if (str_starts_with($name, 'uploads/images/') && str_ends_with($name, '.svg')) {
                $entry = $name;
                break;
            }
        }
        $this->assertNotNull($entry);
        $stat = $zip->statName($entry);
        $zip->close();
        $target = public_path($entry);
        $existed = is_file($target);
        try {
            $this->get('/'.$entry)->assertOk()->assertHeader('Content-Type', 'image/svg+xml');
            $this->assertFileExists($target);
            $this->assertSame($stat['size'], filesize($target));
        } finally {
            if (! $existed && is_file($target)) {
                unlink($target);
            }
        }
    }

    public function test_media_route_rejects_executable_files(): void
    {
        $this->get('/uploads/images/index.php')->assertNotFound();
    }
}
