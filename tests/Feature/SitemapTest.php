<?php
namespace Tests\Feature;

use App\Services\SitemapCatalogue;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class SitemapTest extends TestCase
{
    use DatabaseTransactions;
    private function xml($response): \SimpleXMLElement
    {
        $response->assertOk()->assertHeader('Content-Type', 'application/xml; charset=UTF-8');
        return simplexml_load_string($response->getContent());
    }
    public function test_report_parts_split_at_2500_and_update_immediately_when_reports_change(): void
    {
        $prefix='sitemap-test-'.Str::lower(Str::random(10));$source=DB::table('reports')->first();$rows=[];
        for($i=1;$i<=2501;$i++)$rows[]=['uuid'=>(string)Str::uuid(),'title'=>'Sitemap fixture '.$i,'slug'=>$prefix.'-'.$i,'category_id'=>$source->category_id,'publisher_id'=>$source->publisher_id,'status'=>1,'pub_date'=>'2026-10-24','updated_at'=>'2026-10-24 10:00:00'];
        foreach(array_chunk($rows,500) as $batch)DB::table('reports')->insert($batch);
        $catalogue=new class($prefix) extends SitemapCatalogue {
            public function __construct(private string $prefix) {}
            public function query(string $kind){$q=parent::query($kind);return $kind==='reports'?$q->where('r.slug','like',$this->prefix.'%'):$q;}
        };
        $this->app->instance(SitemapCatalogue::class,$catalogue);
        $index=$this->xml($this->get('/sitemap.xml'));$this->assertSame('sitemapindex',$index->getName());$this->assertStringContainsString('/sitemap-reports-2.xml',$index->asXML());
        $first=$this->xml($this->get('/sitemap-reports-1.xml'));$second=$this->xml($this->get('/sitemap-reports-2.xml'));
        $this->assertCount(2500,$first->url);$this->assertCount(1,$second->url);
        $urls=[];foreach([$first,$second] as $part)foreach($part->url as $entry)$urls[]=(string)$entry->loc;
        $this->assertCount(2501,array_unique($urls));$this->assertStringEndsWith($prefix.'-2501',(string)$second->url[0]->loc);
        DB::table('reports')->where('slug',$prefix.'-2501')->update(['status'=>0]);
        $this->get('/sitemap.xml')->assertOk()->assertDontSee('/sitemap-reports-2.xml',false);$this->get('/sitemap-reports-2.xml')->assertNotFound();
        DB::table('reports')->where('slug',$prefix.'-2501')->update(['status'=>1,'slug'=>$prefix.'-new-name']);
        $this->get('/sitemap.xml')->assertOk()->assertSee('/sitemap-reports-2.xml',false);
        $this->get('/sitemap-reports-2.xml')->assertOk()->assertSee($prefix.'-new-name')->assertDontSee($prefix.'-2501');
        $this->get('/sitemap-reports-0.xml')->assertNotFound();$this->get('/sitemap-reports-9999999.xml')->assertNotFound();$this->get('/sitemap-private-1.xml')->assertNotFound();
    }
    public function test_public_pages_and_news_are_included_but_private_inactive_and_deleted_entries_are_not(): void
    {
        $prefix='sitemap-public-'.Str::lower(Str::random(10));
        DB::table('lr_pages')->insert([['slug'=>$prefix,'title'=>'Public page','content'=>'Content','status'=>1],['slug'=>$prefix.'-draft','title'=>'Draft','content'=>'Content','status'=>0],['slug'=>'authuser/'.$prefix,'title'=>'Private path','content'=>'Content','status'=>1]]);
        $this->get('/sitemap-pages-1.xml')->assertOk()->assertSee($prefix)->assertDontSee($prefix.'-draft')->assertDontSee('authuser/');
        $source=(array)DB::table('news')->first();unset($source['id']);$source['slug']=$prefix.'-news';$source['headline']='Sitemap fixture';$source['status']=1;$source['deleted_at']=null;DB::table('news')->insert($source);
        $this->get('/sitemap-news-1.xml')->assertOk()->assertSee($prefix.'-news');
        DB::table('news')->where('slug',$source['slug'])->update(['deleted_at'=>now()]);$this->get('/sitemap-news-1.xml')->assertOk()->assertDontSee($prefix.'-news');
        $this->get('/robots.txt')->assertOk()->assertSee('Sitemap:')->assertSee('/sitemap.xml');
    }
    public function test_xml_is_valid_and_urls_are_unique_with_real_database_last_modified_dates(): void
    {
        $source=DB::table('reports')->where('status',1)->whereNotNull('slug')->first();
        DB::table('reports')->where('id',$source->id)->update(['updated_at'=>'2020-01-01']);
        DB::table('reports_meta')->updateOrInsert(['report_id'=>$source->id],['updated_at'=>'2026-10-24 12:00:00']);
        $xml=$this->xml($this->get('/sitemap-reports-1.xml'));$this->assertLessThanOrEqual(2500,count($xml->url));
        $locs=[];foreach($xml->url as $entry){$locs[]=(string)$entry->loc;if(str_ends_with((string)$entry->loc,'/report/'.$source->slug))$this->assertStringStartsWith('2026-10-24',(string)$entry->lastmod);}
        $this->assertSame(count($locs),count(array_unique($locs)));
        $this->xml($this->get('/sitemap-categories-1.xml'));
    }
}
