<?php
namespace Tests\Feature;
use Illuminate\Http\Request;
use Tests\TestCase;

class CatalogueCanonicalTest extends TestCase
{
    public function test_listing_canonicals_consolidate_page_one_and_tracking_but_keep_distinct_content(): void
    {
        $cases = [
            '/industry/software-hardware?page=1&utm_source=google' => '/industry/software-hardware',
            '/industry/healthcare?page=2&utm_source=google' => '/industry/healthcare?page=2',
            '/all-reports?page=1&per_page=12&keySectors=all&q=medical+devices&utm_source=test' => '/all-reports?q=medical%20devices',
            '/all-reports?per_page=50&page=3&selectPrice=above' => '/all-reports?page=3&per_page=50&selectPrice=above',
        ];
        foreach ($cases as $input => $expected) {
            $this->app->instance('request', Request::create('https://www.eternityinsights.com'.$input));
            $html = view('include.catalogue-meta')->render();
            $this->assertStringContainsString('href="'.e('https://www.eternityinsights.com'.$expected).'"', $html);
        }
    }
}
