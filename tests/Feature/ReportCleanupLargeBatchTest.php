<?php
namespace Tests\Feature;

use App\Http\Middleware\AdminAccess;
use App\Models\User;
use App\Services\ReportCleanupBatchJob;
use App\Services\ReportContentCleanup;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Tests\TestCase;

class ReportCleanupLargeBatchTest extends TestCase
{
    private string $directory;
    private string $base = '/authuser/reports/content-cleanup';
    private function filters(): array
    {
        return ['after'=>0,'batch_size'=>5000,'status'=>'active','category_id'=>null,'search'=>'','sentences'=>1,'toc'=>1];
    }
    protected function setUp(): void
    {
        parent::setUp();
        $this->directory=storage_path('framework/testing/cleanup-'.bin2hex(random_bytes(6)));
        config(['database.default'=>'sqlite','database.connections.sqlite.database'=>':memory:','session.driver'=>'array','reports.cleanup_job_path'=>$this->directory]);DB::purge('sqlite');
        Schema::create('categories',function(Blueprint $t){$t->id();$t->string('name');});DB::table('categories')->insert(['id'=>1,'name'=>'Automotive']);
        Schema::create('reports',function(Blueprint $t){
            $t->id();$t->string('title');$t->string('slug');$t->integer('status')->default(1);$t->integer('category_id')->default(1);
            foreach(['description','meta_description','meta_title','meta_keywords','keyword','key_info'] as $f)$t->text($f)->nullable();$t->timestamps();
        });
        Schema::create('reports_meta',function(Blueprint $t){
            $t->id();$t->integer('report_id');foreach(['content','toc','tables_figures'] as $f)$t->longText($f)->nullable();$t->timestamps();
        });
        $admin=Mockery::mock(User::class)->makePartial();$admin->id=1;$admin->name='Admin';$admin->shouldReceive('canAccess')->andReturnTrue();
        $this->actingAs($admin)->withoutMiddleware(AdminAccess::class);
    }
    protected function tearDown(): void
    {
        if(isset($this->directory)&&is_dir($this->directory)){
            $this->assertStringStartsWith(storage_path('framework/testing/cleanup-'),$this->directory);
            foreach(glob($this->directory.'/*')?:[] as $file)if(is_file($file))unlink($file);
            rmdir($this->directory);
        }
        parent::tearDown();
    }
    private function seedReports(int $count): void
    {
        $sentence='The report also considers the impact of COVID-19 on the global Safety Market.';
        for($start=1;$start<=$count;$start+=200){
            $reports=[];$meta=[];
            for($id=$start;$id<=min($count,$start+199);$id++){
                $reports[]=['id'=>$id,'title'=>'Safety Market '.$id,'slug'=>'safety-'.$id,'description'=>$sentence,'updated_at'=>'2026-10-01 00:00:00'];
                $meta[]=['id'=>$id,'report_id'=>$id,'content'=>'<p>Keep analysis. '.$sentence.'</p><p>COVID-19 affected supply in 2020.</p>',
                    'toc'=>'<p>3.2 Impact of COVID-19 Pandemic on Global Safety Market</p><p>3.2.2 Pre-covid & Post-Covid Market Analysis</p>','tables_figures'=>'Keep figures.','updated_at'=>'2026-10-01 00:00:00'];
            }
            DB::table('reports')->insert($reports);DB::table('reports_meta')->insert($meta);
        }
    }
    private function ready(ReportCleanupBatchJob $jobs,string $id): array
    {
        for($n=0;$n<200;$n++){$state=$jobs->scan($id,1);if($state['phase']==='ready')return $state;}
        $this->fail('Preview did not finish in the expected bounded steps.');
    }
    public function test_five_thousand_report_preview_selection_and_apply_complete_without_large_form_posts(): void
    {
        $this->seedReports(5001);
        $this->get($this->base)->assertOk()->assertSee('value="5000" selected',false);
        $response=$this->post($this->base.'/preview',$this->filters())->assertRedirect();
        $id=basename(parse_url($response->headers->get('Location'),PHP_URL_PATH));
        $jobs=app(ReportCleanupBatchJob::class);$state=$this->ready($jobs,$id);
        $this->assertSame(5000,$state['matched']);$this->assertSame(5000,$state['eligible']);$this->assertSame(5000,$state['after']);$this->assertTrue($state['more']);
        $this->assertCount(50,$jobs->page($id,1,1)['rows']);$this->assertCount(50,$jobs->page($id,1,100)['rows']);
        $this->assertDatabaseCount('reports',5001);$this->assertDatabaseHas('reports',['id'=>1,'description'=>'The report also considers the impact of COVID-19 on the global Safety Market.']);
        $this->get($this->base.'/batch/'.$id)->assertOk()->assertSee('Select all 5,000 eligible reports in this batch')->assertSee('Page 1 of 100');
        $this->postJson($this->base.'/batch/'.$id.'/select',['mode'=>'all'])->assertOk()->assertJsonPath('selected',5000);
        $this->postJson($this->base.'/batch/'.$id.'/begin')->assertOk()->assertJsonPath('phase','applying');
        $this->postJson($this->base.'/batch/'.$id.'/apply')->assertOk()->assertJsonPath('processed',25);
        for($n=0;$n<210;$n++){$state=$jobs->applyStep($id,1);if($state['phase']==='complete')break;}
        $this->assertSame('complete',$state['phase']);$this->assertSame(5000,$state['updated']);$this->assertSame(0,$state['skipped']);
        $this->assertSame(5000,DB::table('reports')->where('description','')->count());
        $this->assertDatabaseHas('reports',['id'=>5001,'description'=>'The report also considers the impact of COVID-19 on the global Safety Market.']);
        $this->assertStringContainsString('COVID-19 affected supply in 2020.',DB::table('reports_meta')->where('id',1)->value('content'));
        $this->assertSame(5000,$jobs->applyStep($id,1)['updated']);
        $next=$jobs->start(array_replace($this->filters(),['after'=>5000]),1);$this->assertSame(1,$this->ready($jobs,$next)['matched']);
    }
    public function test_page_selections_persist_and_cannot_select_other_pages_or_protected_reports(): void
    {
        $this->seedReports(60);DB::table('reports')->where('id',1)->update(['title'=>'COVID-19 Diagnostics Market']);
        $jobs=app(ReportCleanupBatchJob::class);$id=$jobs->start($this->filters(),1);$this->ready($jobs,$id);
        $jobs->select($id,1,'page',1,[2,3]);$jobs->select($id,1,'page',2,[51]);
        $this->assertSame(3,$jobs->page($id,1)['progress']['selected']);$this->assertTrue($jobs->page($id,1,2)['rows'][0]['selected']);
        $this->postJson($this->base.'/batch/'.$id.'/select',['mode'=>'page','page'=>1,'ids'=>[51]])->assertUnprocessable();
        $this->postJson($this->base.'/batch/'.$id.'/select',['mode'=>'page','page'=>1,'ids'=>[1]])->assertUnprocessable();
        $this->assertSame(59,$jobs->select($id,1,'all')['selected']);
        $this->assertSame(0,$jobs->select($id,1,'none')['selected']);
        $this->postJson($this->base.'/batch/'.$id.'/begin')->assertUnprocessable();
        $foreign=$jobs->start($this->filters(),2);$this->get($this->base.'/batch/'.$foreign)->assertForbidden();
        $this->postJson($this->base.'/batch/'.$foreign.'/scan')->assertForbidden();
    }
    public function test_retry_after_commit_is_idempotent_and_concurrent_editor_changes_are_preserved(): void
    {
        $this->seedReports(3);$jobs=app(ReportCleanupBatchJob::class);$cleanup=app(ReportContentCleanup::class);
        $id=$jobs->start($this->filters(),1);$this->ready($jobs,$id);$jobs->select($id,1,'all');$jobs->beginApply($id,1);
        // Simulate a completed commit whose HTTP progress response never reached the client.
        $source=$cleanup->load(1);$plan=$cleanup->plan($source,$this->filters());
        foreach($plan['updates'] as $table=>$updates)if($updates)DB::table($table)->where('id',1)->update($updates+['updated_at'=>now()]);
        DB::table('reports_meta')->where('id',2)->update(['content'=>'New editorial content.']);
        $state=$jobs->applyStep($id,1);$this->assertSame('complete',$state['phase']);
        $this->assertSame(1,$state['already_current']);$this->assertSame(1,$state['skipped']);$this->assertSame(1,$state['updated']);
        $this->assertDatabaseHas('reports_meta',['id'=>2,'content'=>'New editorial content.']);
        $this->assertSame(3,$jobs->applyStep($id,1)['processed']);
    }
}
