<?php
namespace Tests\Feature;

use App\Services\ReportGraphics;
use App\Services\ReportContentGenerator;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ReportGraphicsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default'=>'sqlite','database.connections.sqlite.database'=>':memory:']); DB::purge('sqlite');
        Schema::create('lr_pages',function(Blueprint $t){$t->id();$t->string('slug');$t->integer('status');});
        Schema::create('reports',function(Blueprint $t){$t->id();$t->string('slug');$t->string('keyword');$t->string('title');$t->integer('status');});
        Schema::create('reports_meta',function(Blueprint $t){$t->id();$t->integer('report_id');$t->text('content');});
    }
    private function report(): object
    {
        return (object)['id'=>123,'slug'=>'example-market','keyword'=>'Example','title'=>'Example Market','content'=>'<p>Keep original research 2018-2028.</p><h2>Global Example Market Size, 2018-2028 (USD Million)</h2><p class="imglink"><img src="/frontend/images/market-size.svg" alt="Size"></p><p>Keep this exact position.</p><img src="https://www.eternityinsights.com/frontend/images/region-2.svg"><img src="/uploads/images/example-market.svg"><img src="/uploads/images/custom.svg"><img src="https://external.example/uploads/images/example-market.svg">'];
    }
    public function test_existing_three_images_keep_order_and_custom_content_is_preserved(): void
    {
        $g=app(ReportGraphics::class);$r=$this->report();$out=$g->content($r);
        $this->assertSame(3,substr_count($out,'/report-graphics/123/'));
        $this->assertStringContainsString('Keep original research 2018-2028.',$out);
        $this->assertStringContainsString('Market Size, 2022-2035',$out);
        $this->assertStringContainsString('src="/uploads/images/custom.svg"',$out);
        $this->assertStringContainsString('https://external.example/uploads/images/example-market.svg',$out);
        $this->assertLessThan(strpos($out,'region.svg'),strpos($out,'size.svg'));
        $this->assertLessThan(strpos($out,'taxonomy.svg'),strpos($out,'region.svg'));
        $this->assertSame(['size','region','taxonomy'],array_keys($g->sources($r)));
        $this->assertNull($g->kind('/uploads/images/../example-market.svg',$r));
        $this->assertNull($g->read('/../../.env'));
        $legacy=(object)['id'=>11240,'slug'=>'global-cold-forming-foil-market','keyword'=>'Cold Forming Foil'];
        $this->assertSame('taxonomy',$g->kind('https://eternityinsights.com/uploads/images/cold-forming-foil.svg',$legacy));
        $this->assertNull($g->kind('https://external.example/uploads/images/cold-forming-foil.svg',$legacy));
        $r->content=str_replace('2018-2028','2021-2035',$r->content);
        $this->assertStringContainsString('Market Size, 2022-2035',$g->content($r));
        config(['report_graphics.forecast_end'=>2036]);
        $this->assertNotSame($out,$g->content($r));
    }
    public function test_taxonomy_has_fourteen_years_keeps_segments_and_stamping_is_idempotent(): void
    {
        $g=app(ReportGraphics::class);
        $svg=view('template.rdsvg',['keyword'=>'Biobanking','types'=>'Cryogenic Storage, Media & Consumables','applications'=>'Drug Discovery'])->render();
        $out=$g->taxonomy($svg);$d=new \DOMDocument;$this->assertTrue($d->loadXML($out));
        $x=new \DOMXPath($d);$x->registerNamespace('s','http://www.w3.org/2000/svg');
        foreach(range(2022,2035) as $y) $this->assertSame(1,$x->query('//s:text[text()="'.$y.'"]')->length);
        $this->assertSame(1,$x->query('//s:text[text()="XX"]')->length);
        $this->assertSame($x->query('//s:text[text()="2025"]')->item(0)->getAttribute('x'),$x->query('//s:text[text()="XX"]')->item(0)->getAttribute('x'));
        $this->assertSame(14,$x->query('//*[@id="Graph-2"]//s:rect[@class="cls-8"]')->length);
        foreach(['Historical: 2022-2024','Base year: 2025','Forecast: 2026-2035','Cryogenic Storage','Drug Discovery','2022-2035'] as $label) $this->assertStringContainsString($label,$out);
        $this->assertStringNotContainsString('2018-2028',$out);
        $this->assertSame($g->stamp($out),$g->stamp($g->stamp($out)));
        $custom='<svg xmlns="http://www.w3.org/2000/svg"><text>Actual market analysis 2020</text></svg>';
        $this->assertSame($custom,$g->taxonomy($custom));
    }
    public function test_endpoint_serves_updated_images_with_cache_validation_and_rejects_inactive_reports(): void
    {
        $r=$this->report();DB::table('reports')->insert(['id'=>$r->id,'slug'=>$r->slug,'keyword'=>'Example & Research','title'=>$r->title,'status'=>1]);
        DB::table('reports_meta')->insert(['report_id'=>$r->id,'content'=>$r->content]);
        foreach(['size','region'] as $kind) {
            $response=$this->get('/report-graphics/123/'.$kind.'.svg')->assertOk()->assertHeader('Content-Type','image/svg+xml; charset=UTF-8');
            $d=new \DOMDocument;$this->assertTrue($d->loadXML($response->getContent()));
            if ($kind==='size') {
                $x=new \DOMXPath($d);$x->registerNamespace('s','http://www.w3.org/2000/svg');
                $this->assertSame(1,$x->query('//s:text[text()="XX"]')->length);
                $this->assertSame($x->query('//s:text[text()="2025"]')->item(0)->getAttribute('x'),$x->query('//s:text[text()="XX"]')->item(0)->getAttribute('x'));
            }
            foreach(['Historical: 2022-2024','Base year: 2025','Forecast: 2026-2035'] as $text) $response->assertSee($text,false);
            $this->withHeader('If-None-Match',$response->headers->get('ETag'))->get('/report-graphics/123/'.$kind.'.svg')->assertStatus(304);
            $this->flushHeaders();
        }
        $this->get('/report-graphics/123/other.svg')->assertNotFound();
        DB::table('reports')->where('id',123)->update(['status'=>0]);
        $this->get('/report-graphics/123/size.svg')->assertNotFound();
        $this->get('/report-graphics/999/size.svg')->assertNotFound();
    }
    public function test_new_import_generated_taxonomy_uses_the_same_period(): void
    {
        $generated=app(ReportContentGenerator::class)->image(987654321,'Test Market',['types'=>'A & B','applications'=>'C']);
        try {
            DB::table('reports')->insert(['id'=>987654321,'slug'=>'generated-test','keyword'=>'Test','title'=>'Test Market','status'=>1]);
            DB::table('reports_meta')->insert(['report_id'=>987654321,'content'=>'<img src="'.$generated['url'].'">']);
            $this->get('/report-graphics/987654321/taxonomy.svg')->assertOk()->assertSee('Forecast: 2026-2035',false);
            $svg=file_get_contents(public_path($generated['url']));
            $this->assertStringContainsString('2022-2035',$svg);
            $this->assertStringContainsString('Forecast: 2026-2035',$svg);
            $this->assertStringNotContainsString('2018-2028',$svg);
        } finally {
            if($generated['created']) {unlink($generated['created']);rmdir(dirname($generated['created']));}
        }
    }
    public function test_five_images_keep_distinct_sources_including_two_taxonomy_graphics(): void
    {
        $generator=app(ReportContentGenerator::class); $g=app(ReportGraphics::class);
        $first=$generator->image(987654322,'First saved graphic',['types'=>'First segment','applications'=>'First use']);
        $second=$generator->image(987654322,'Second saved graphic',['types'=>'Second segment','applications'=>'Second use']);
        try {
            $html='<p>Original introduction</p><img src="/frontend/images/market-size.svg"><img src="'.$first['url'].'"><p>Keep this paragraph here.</p><img src="/frontend/images/region-2.svg"><img src="'.$second['url'].'"><img src="/uploads/images/custom-photo.png">';
            $r=(object)['id'=>987654322,'slug'=>'five-image-test','keyword'=>'Five images','content'=>$html];
            DB::table('reports')->insert(['id'=>$r->id,'slug'=>$r->slug,'keyword'=>$r->keyword,'title'=>'Five images Market','status'=>1]);
            DB::table('reports_meta')->insert(['report_id'=>$r->id,'content'=>$html]);
            $out=$g->content($r);$this->assertSame(5,substr_count($out,'<img'));
            $this->assertStringContainsString('<p>Keep this paragraph here.</p>',$out);
            $this->assertStringContainsString('src="/uploads/images/custom-photo.png"',$out);
            preg_match_all('~src="([^\"]+)"~',$out,$matches);
            $this->assertNotSame($matches[1][1],$matches[1][3]);
            $this->get(html_entity_decode($matches[1][1]))->assertOk()->assertSee('First saved graphic',false)->assertDontSee('Second saved graphic',false);
            $this->get(html_entity_decode($matches[1][3]))->assertOk()->assertSee('Second saved graphic',false)->assertDontSee('First saved graphic',false);
            $this->get('/report-graphics/'.$r->id.'/taxonomy.svg?source='.str_repeat('0',20))->assertNotFound();
            $this->get('/report-graphics/'.$r->id.'/taxonomy.svg?source[]=invalid')->assertNotFound();
        } finally {
            foreach ([$first,$second] as $image) if ($image['created']) unlink($image['created']);
            $dir=public_path('uploads/images/reports/987654322');if(is_dir($dir) && count(scandir($dir))===2)rmdir($dir);
        }
    }

}
