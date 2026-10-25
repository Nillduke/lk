<?php
namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class PerformanceDeliveryTest extends TestCase
{
    use DatabaseTransactions;

    public function test_html_is_compressed_only_when_the_client_accepts_gzip(): void
    {
        $plain = $this->get('/contactus')->assertOk();
        $this->assertFalse($plain->headers->has('Content-Encoding'));
        $compressed = $this->withHeader('Accept-Encoding', 'gzip')->get('/contactus')->assertOk()->assertHeader('Content-Encoding', 'gzip');
        $html = gzdecode($compressed->getContent());
        $this->assertStringContainsString('Contact', $html);
        $this->assertStringContainsString('</html>', $html);
        $this->assertLessThan(strlen($html) / 2, strlen($compressed->getContent()));
        $rejected = $this->withHeader('Accept-Encoding', 'gzip;q=0, identity')->get('/contactus')->assertOk();
        $this->assertFalse($rejected->headers->has('Content-Encoding'));
    }

    public function test_mobile_filters_and_checkout_titles_have_stable_server_rendered_markup(): void
    {
        $this->get('/industry/healthcare')->assertOk()
            ->assertSee('class="catalogue-filter-toggle" aria-expanded="false"', false)
            ->assertDontSee('<details class="catalogue-sidebar-panel" open>', false);
        $this->get('/checkout-form/96517')->assertOk()
            ->assertSee('billing-repName interior-compact', false)
            ->assertSee('class="interior-expand-button"', false)
            ->assertSee('class="interior-table-wrap"', false)
            ->assertSee('class="interior-field-label"', false);
    }

    public function test_search_pagination_exposes_more_results_without_duplicate_cards(): void
    {
        $first = $this->get('/searchfor?s=meta')->assertOk();
        $second = $this->get('/searchfor?s=meta&page=2')->assertOk();
        $first->assertViewHas('searchPagination', fn ($pages) => $pages->total() > 12 && $pages->perPage() === 12);
        $first->assertViewHas('list', fn ($rows) => count($rows) === 12);
        $firstIds = collect($first->viewData('list'))->pluck('id');
        $secondIds = collect($second->viewData('list'))->pluck('id');
        $this->assertEmpty($firstIds->intersect($secondIds));
        $second->assertSee('s=meta', false);
    }

    public function test_cookie_banner_is_absent_across_public_page_types(): void
    {
        foreach (['/', '/contactus', '/all-reports', '/industry/healthcare', '/industry-news', '/request-a-sample/96517', '/report/south-america-pharmacy-information-systems-market'] as $url) {
            $this->get($url)->assertOk()->assertDontSee('id="cookieNotice"', false)->assertDontSee('frontend/js/cc.js', false)->assertDontSee('We use cookies to enhance your experience');
        }
    }

    public function test_new_images_get_responsive_webp_derivatives_without_changing_the_upload(): void
    {
        $name = 'performance-test-'.bin2hex(random_bytes(6)).'.png';
        $path = public_path('frontend/'.$name);
        $image = imagecreatetruecolor(1280, 300);
        imagefill($image, 0, 0, imagecolorallocate($image, 30, 60, 120));
        imagepng($image, $path); imagedestroy($image);
        $checksum = hash_file('sha256', $path);
        $key = sha1('/frontend/'.$name.'|'.filemtime($path).'|'.filesize($path));
        try {
            $response = app(\App\Http\Middleware\OptimizeDelivery::class)->handle(
                \Illuminate\Http\Request::create('/contactus'),
                fn () => response('<html><script>const example=`<img src="/frontend/'.$name.'">`;</script><textarea><img src="/frontend/'.$name.'"></textarea><!-- <img src="/frontend/'.$name.'"> --><img src="/frontend/'.$name.'" alt="Original image"></html>')->header('Content-Type', 'text/html')
            );
            $html = $response->getContent();
            $this->assertStringContainsString('.webp', $html);
            $this->assertSame(3, substr_count($html, '/frontend/'.$name));
            $this->assertStringContainsString('<textarea><img src="/frontend/'.$name.'"></textarea>', $html);
            $this->assertStringContainsString('srcset=', $html);
            $this->assertStringContainsString('width="1280" height="300"', $html);
            $this->assertStringContainsString('alt="Original image"', $html);
            $this->assertSame($checksum, hash_file('sha256', $path));
            $this->assertSame(IMAGETYPE_WEBP, getimagesize(public_path('frontend/optimized/'.$key.'.webp'))[2]);
        } finally {
            foreach (['.json', '.webp', '-480.webp', '-960.webp'] as $suffix) {
                $file = public_path('frontend/optimized/'.$key.$suffix);
                if (is_file($file)) unlink($file);
            }
            unlink($path);
        }
    }

    public function test_amp_cleanup_preserves_text_and_removes_invalid_editor_attributes(): void
    {
        $html = amp_content('<p><span calibri="" style="font-family:Calibri">Market &amp; Research</span></p>');
        $this->assertStringContainsString('Market &amp; Research', $html);
        $this->assertStringContainsString('<span>', $html);
        $this->assertStringNotContainsString('calibri', $html);
        $this->assertStringNotContainsString('style=', $html);
    }
}
