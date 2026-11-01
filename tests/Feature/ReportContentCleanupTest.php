<?php
namespace Tests\Feature;

use App\Http\Middleware\AdminAccess;
use App\Models\User;
use App\Services\ReportContentCleanup;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Tests\TestCase;

class ReportContentCleanupTest extends TestCase
{
    private string $sentence = 'The report also considers the impact of COVID-19 on the global Automotive Active Safety Market.';
    private string $path = '/authuser/reports/content-cleanup';

    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default'=>'sqlite','database.connections.sqlite.database'=>':memory:','session.driver'=>'array']);
        DB::purge('sqlite');
        Schema::create('categories',function(Blueprint $t){$t->id();$t->string('name');});
        DB::table('categories')->insert(['id'=>1,'name'=>'Automotive']);
        Schema::create('reports',function(Blueprint $t){
            $t->id();$t->string('title');$t->string('slug');$t->integer('status')->default(1);$t->integer('category_id')->default(1);
            foreach(['description','meta_description','meta_title','meta_keywords','keyword','key_info'] as $f)$t->text($f)->nullable();
            $t->timestamps();
        });
        Schema::create('reports_meta',function(Blueprint $t){
            $t->id();$t->integer('report_id');foreach(['content','toc','tables_figures'] as $f)$t->longText($f)->nullable();$t->timestamps();
        });
        $this->admin(true);
    }
    private function admin(bool $allowed): void
    {
        $admin=Mockery::mock(User::class)->makePartial();$admin->id=1;$admin->name='Admin';
        $admin->shouldReceive('canAccess')->andReturn($allowed);
        $this->actingAs($admin)->withoutMiddleware(AdminAccess::class);
    }
    private function seedReport(int $id, string $title='Automotive Active Safety Market'): void
    {
        DB::table('reports')->insert(['id'=>$id,'title'=>$title,'slug'=>'report-'.$id,'description'=>$this->sentence,'updated_at'=>'2026-10-01 12:00:00']);
        DB::table('reports_meta')->insert(['report_id'=>$id,'content'=>'<p>Keep <strong>market analysis</strong>. '.$this->sentence.'</p><p>COVID-19 disrupted supply chains in 2020.</p>',
            'toc'=>'<p class="l2">3.2 Impact of COVID-19 Pandemic on Global Automotive Active Safety Market</p><p class="l3">3.2.1 Overview of Changing Landscape<br/>3.2.2 Pre-covid &amp; Post-Covid Market Analysis</p>',
            'tables_figures'=>'Figures stay intact.','updated_at'=>'2026-10-01 12:00:00']);
    }
    private function preview(array $overrides=[])
    {
        return $this->post($this->path.'/preview',array_merge(['after'=>0,'batch_size'=>10,'status'=>'active','category_id'=>null,'search'=>'','sentences'=>1,'toc'=>1],$overrides));
    }
    public function test_preview_is_read_only_and_shows_exact_proposed_changes(): void
    {
        $this->seedReport(1);$before=DB::table('reports_meta')->first();
        $this->get($this->path)->assertOk();
        $this->preview()->assertOk()->assertSee('Market Landscape and Industry Developments')->assertSee('Sentence removed.');
        $this->assertSame($before->content,DB::table('reports_meta')->first()->content);
        $this->assertSame($before->toc,DB::table('reports_meta')->first()->toc);
        $this->assertDatabaseHas('reports',['id'=>1,'description'=>$this->sentence]);
    }
    public function test_apply_only_selected_reports_preserves_historical_text_markup_and_numbering(): void
    {
        $this->seedReport(1);$this->seedReport(2);$this->preview()->assertOk();$token=session('report_cleanup_preview.token');
        $this->post($this->path.'/apply',['token'=>$token,'ids'=>[1]])->assertRedirect();
        $m=DB::table('reports_meta')->where('report_id',1)->first();
        $this->assertStringNotContainsString($this->sentence,$m->content);
        $this->assertStringContainsString('<strong>market analysis</strong>',$m->content);
        $this->assertStringContainsString('COVID-19 disrupted supply chains in 2020.',$m->content);
        $this->assertStringContainsString('3.2 Market Landscape and Industry Developments',$m->toc);
        $this->assertStringContainsString('3.2.1 Overview of Changing Landscape<br/>3.2.2 Current Market Trends and Outlook',$m->toc);
        $this->assertSame('Figures stay intact.',$m->tables_figures);
        $this->assertDatabaseHas('reports',['id'=>2,'description'=>$this->sentence]);
        $this->post($this->path.'/apply',['token'=>$token,'ids'=>[1]])->assertStatus(419);
    }
    public function test_subject_reports_are_protected_and_arbitrary_ids_rejected(): void
    {
        $this->seedReport(1,'COVID-19 Diagnostics Market');$this->seedReport(2);$this->preview()->assertOk()->assertSee('part of this report');
        $token=session('report_cleanup_preview.token');
        $this->post($this->path.'/apply',['token'=>$token,'ids'=>[1]])->assertUnprocessable();
        $this->post($this->path.'/apply',['token'=>$token,'ids'=>[999]])->assertUnprocessable();
        $this->assertDatabaseHas('reports',['id'=>1,'description'=>$this->sentence]);
    }
    public function test_concurrent_editor_changes_are_not_overwritten(): void
    {
        $this->seedReport(1);$this->preview()->assertOk();$token=session('report_cleanup_preview.token');
        DB::table('reports_meta')->where('report_id',1)->update(['content'=>'Editor changed this content.']);
        $this->post($this->path.'/apply',['token'=>$token,'ids'=>[1]])->assertRedirect()->assertSessionHas('success',fn($v)=>str_contains($v,'0 selected reports updated. 1'));
        $this->assertDatabaseHas('reports_meta',['report_id'=>1,'content'=>'Editor changed this content.']);
        $this->assertDatabaseHas('reports',['id'=>1,'description'=>$this->sentence]);
    }
    public function test_batch_size_options_cursor_and_permissions_are_enforced(): void
    {
        for($id=1;$id<=12;$id++)$this->seedReport($id);
        $this->preview(['batch_size'=>51])->assertSessionHasErrors('batch_size');
        $this->preview()->assertOk();$preview=session('report_cleanup_preview');$this->assertCount(10,$preview['rows']);$this->assertSame(10,$preview['after']);
        $this->preview(['after'=>10])->assertOk();$this->assertCount(2,session('report_cleanup_preview.rows'));
        $this->admin(false);$this->get($this->path)->assertForbidden();$this->preview()->assertForbidden();
    }
    public function test_rules_are_optional_and_do_not_delete_analytical_sentences_or_break_html(): void
    {
        $service=app(ReportContentCleanup::class);
        $html='<p>'.$this->sentence.'</p><p>COVID-19 increased demand by 12% in 2020.</p>';
        $this->assertSame($html,$service->transform($html,'content',['sentences'=>0,'toc'=>1])['value']);
        $changed=$service->transform($html,'content',['sentences'=>1,'toc'=>1])['value'];
        $this->assertSame('<p></p><p>COVID-19 increased demand by 12% in 2020.</p>',$changed);
        $this->assertSame('<p>The report also considers <b>the impact of COVID-19</b> on the global Market.</p>',$service->transform('<p>The report also considers <b>the impact of COVID-19</b> on the global Market.</p>','content',['sentences'=>1])['value']);
    }
    public function test_large_content_and_expired_previews_cannot_be_applied(): void
    {
        $this->seedReport(1);DB::table('reports_meta')->where('report_id',1)->update(['content'=>str_repeat('x',ReportContentCleanup::MAX_FIELD_BYTES+1).' COVID-19']);
        $this->preview()->assertOk()->assertSee('Large content');$this->assertFalse(session('report_cleanup_preview.rows.1.eligible'));
        $token=session('report_cleanup_preview.token');session()->put('report_cleanup_preview.expires',now()->subMinute()->timestamp);
        $this->post($this->path.'/apply',['token'=>$token,'ids'=>[1]])->assertStatus(419);
    }
}
