<?php
namespace Tests\Feature;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;
class HomepageRedesignTest extends TestCase
{
 public function test_homepage_uses_published_data_and_preserves_report_coverage_and_search(): void
 {
  config(['database.default'=>'sqlite','database.connections.sqlite.database'=>':memory:']); DB::purge('sqlite');
  $this->withoutMiddleware(\App\Http\Middleware\OptimizeDelivery::class);
  Schema::create('categories',function(Blueprint $t){$t->id();$t->string('name');$t->string('slug');});
  Schema::create('reports',function(Blueprint $t){$t->id();$t->string('title');$t->string('keyword');$t->string('slug');$t->integer('category_id');$t->integer('status');$t->date('pub_date');});
  Schema::create('reports_meta',function(Blueprint $t){$t->id();$t->integer('report_id');$t->text('content');});
  Schema::create('reports_licenses',function(Blueprint $t){$t->id();$t->integer('report_id');$t->integer('lic_price');});
  Schema::create('news',function(Blueprint $t){$t->id();$t->string('headline');$t->string('slug');$t->text('content');$t->string('featured_img')->nullable();$t->integer('status');$t->timestamp('deleted_at')->nullable();$t->timestamp('created_at');});
  foreach([15,19,21,25,26,12] as $id){
   DB::table('categories')->insert(['id'=>$id,'name'=>'Industry '.$id,'slug'=>'industry-'.$id]);
   DB::table('reports')->insert(['id'=>$id,'title'=>'Research '.$id.' Market Size, Share and Full Segmentation Analysis','keyword'=>'Research '.$id,'slug'=>'research-'.$id,'category_id'=>$id,'status'=>1,'pub_date'=>'2026-11-01']);
   DB::table('reports_meta')->insert(['report_id'=>$id,'content'=>'<p>Published research description.</p>']);
   foreach([4800,3500] as $price)DB::table('reports_licenses')->insert(['report_id'=>$id,'lic_price'=>$price]);
  }
  DB::table('reports')->insert(['title'=>'PRIVATE DRAFT','keyword'=>'PRIVATE DRAFT','slug'=>'private-draft','category_id'=>15,'status'=>0,'pub_date'=>'2026-11-02']);
  foreach([[1,'Published news',null],[2,'Deleted news','2026-11-01']] as [$id,$headline,$deleted])DB::table('news')->insert(['id'=>$id,'headline'=>$headline,'slug'=>'news-'.$id,'content'=>'Current industry developments.','status'=>1,'deleted_at'=>$deleted,'created_at'=>'2026-11-01']);
  $response=$this->get('/')->assertOk()->assertSee('Latest Reports')->assertSee('Published news')->assertDontSee('Deleted news')->assertDontSee('PRIVATE DRAFT')->assertSee('USD 3,500')->assertSee('Full Segmentation Analysis')->assertSee('data-report-search',false)->assertSee('data-home-testimonials',false);
  $html=$response->getContent();
  $this->assertSame(1,preg_match_all('/<h1\b/',$html));
  $this->assertSame(7,preg_match_all('/role="tab"/',$html));
  $this->assertSame(7,preg_match_all('/role="tabpanel"/',$html));
  foreach([15,19,21,25,26,12] as $id)$response->assertSee('/report/research-'.$id,false)->assertSee('/industry/industry-'.$id,false);
 }
}
