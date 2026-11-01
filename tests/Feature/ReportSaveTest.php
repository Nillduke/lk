<?php
namespace Tests\Feature;

use App\Http\Middleware\AdminAccess;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Tests\TestCase;

class ReportSaveTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default'=>'sqlite','database.connections.sqlite.database'=>':memory:']); DB::purge('sqlite');
        Schema::create('categories',fn(Blueprint $t)=>$t->id());
        Schema::create('publishers',fn(Blueprint $t)=>$t->id());
        DB::table('categories')->insert(['id'=>1]); DB::table('publishers')->insert(['id'=>1]);
        Schema::create('reports',function(Blueprint $t){
            $t->increments('id'); $t->string('uuid')->nullable();
            $t->unsignedInteger('category_id'); $t->unsignedInteger('publisher_id');
            $t->string('title',1500); $t->string('slug',150); $t->string('description',500)->nullable();
            $t->string('keyword',100)->nullable(); $t->integer('pages')->default(0);
            $t->string('region',50)->default('Global'); $t->mediumText('key_info')->nullable();
            $t->integer('status')->default(1); $t->date('pub_date');
            $t->string('meta_title',500)->nullable(); $t->string('meta_description',500)->nullable();
            $t->string('meta_keywords',500)->nullable(); $t->timestamps();
        });
        Schema::create('reports_meta',function(Blueprint $t){
            $t->increments('id'); $t->unsignedInteger('report_id'); $t->longText('content');
            $t->longText('toc')->nullable(); $t->longText('tables_figures')->nullable(); $t->timestamps();
        });
        Schema::create('reports_licenses',function(Blueprint $t){
            $t->increments('id'); $t->unsignedInteger('report_id'); $t->string('lic_name',50); $t->integer('lic_price');
        });
        Schema::create('checkouts',function(Blueprint $t){$t->id();$t->integer('report_license_id');});
        $admin=Mockery::mock(User::class)->makePartial();$admin->id=1;
        $admin->shouldReceive('canAccess')->andReturnTrue();$this->actingAs($admin)->withoutMiddleware(AdminAccess::class);
    }
    private function data():array
    {
        return ['title'=>'Save regression','slug'=>'save-regression','category_id'=>1,'publisher_id'=>1,
            'pub_date'=>'2026-11-01','status'=>0,'pages'=>100,'content'=>'<h2>Original</h2><p>Keep HTML exactly.</p>',
            'toc'=>'Contents','tables_figures'=>'','report_segments_present'=>1,
            'report_segments'=>[['label'=>'Product Type','value'=>'One, Two']],'report_licenses_present'=>1,
            'report_licenses'=>[['lic_name'=>'Single User License','lic_price'=>3500]]];
    }
    public function test_blank_optional_editor_fields_and_pages_save():void
    {
        $data=$this->data();foreach(['content','toc','tables_figures','pages'] as $field)$data[$field]=null;
        $id=$this->postJson('/authuser/manage/reports',$data)->assertOk()->json('id');
        $this->assertDatabaseHas('reports',['id'=>$id,'pages'=>0]);
        $this->assertDatabaseHas('reports_meta',['report_id'=>$id,'content'=>'','toc'=>'','tables_figures'=>'']);
        $this->assertDatabaseHas('reports_licenses',['report_id'=>$id,'lic_price'=>3500]);
    }
    public function test_long_segments_create_and_update_without_losing_content_or_license_ids():void
    {
        $data=$this->data();$data['report_segments'][0]['value']=str_repeat('Detailed product coverage, ',65);
        $id=$this->postJson('/authuser/manage/reports',$data)->assertOk()->json('id');
        $this->assertGreaterThan(1000,strlen(DB::table('reports')->where('id',$id)->value('key_info')));
        $licenseId=DB::table('reports_licenses')->where('report_id',$id)->value('id');
        $data['report_segments'][0]['value'].='Another option';
        $this->postJson('/authuser/manage/reports/'.$id,$data)->assertOk();
        $this->assertDatabaseHas('reports_meta',['report_id'=>$id,'content'=>$data['content']]);
        $this->assertDatabaseHas('reports_licenses',['id'=>$licenseId,'report_id'=>$id,'lic_price'=>3500]);
        $this->assertSame($data['report_segments'][0]['value'],json_decode(DB::table('reports')->where('id',$id)->value('key_info'),true)['product_type']);
    }
    public function test_metadata_patch_keeps_unsubmitted_content():void
    {
        $data=$this->data();$id=$this->postJson('/authuser/manage/reports',$data)->assertOk()->json('id');
        $this->patchJson('/authuser/api/reports/'.$id,['toc'=>'Changed contents'])->assertOk();
        $this->assertDatabaseHas('reports_meta',['report_id'=>$id,'content'=>$data['content'],'toc'=>'Changed contents']);
    }
    public function test_invalid_description_returns_field_error_without_writing_report():void
    {
        $data=$this->data();$data['description']=str_repeat('x',501);
        $this->postJson('/authuser/manage/reports',$data)->assertUnprocessable()->assertJsonValidationErrors('description');
        $this->assertDatabaseCount('reports',0);
    }
}
