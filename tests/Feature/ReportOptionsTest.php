<?php
namespace Tests\Feature;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class ReportOptionsTest extends TestCase
{
 use DatabaseTransactions;
 private function report(): object
 {
  $this->actingAs(User::where('username','lk_admin')->firstOrFail());
  $source=DB::table('reports')->first();
  $result=$this->postJson('/authuser/manage/reports',['title'=>'Professional options test','slug'=>'options-test-'.Str::lower(Str::random(12)),'category_id'=>$source->category_id,'publisher_id'=>$source->publisher_id,'pub_date'=>'2026-10-24','status'=>1,'content'=>'<p>Keep saved content.</p>','key_info'=>'{"software_type":"Type A, Type B","end-user":"Banking, Health","key_players":"Example, Inc., Another Company"}','licenses_json'=>'[{"lic_name":"Single User License","lic_price":3200},{"lic_name":"Corporate User License","lic_price":7200}]'])->assertOk();
  return DB::table('reports')->where('id',$result->json('id'))->first();
 }
 private function data(object $report): array
 {
  return ['title'=>$report->title,'category_id'=>$report->category_id,'publisher_id'=>$report->publisher_id,'pub_date'=>$report->pub_date,'report_segments_present'=>1,'report_licenses_present'=>1,'report_segments'=>app(\App\Services\ReportOptions::class)->rows($report),'report_licenses'=>DB::table('reports_licenses')->where('report_id',$report->id)->get()->map(fn($l)=>['id'=>$l->id,'lic_name'=>$l->lic_name,'lic_price'=>$l->lic_price])->all()];
 }
 public function test_report_forms_have_friendly_rows_without_json_editors(): void
 {
  $report=$this->report();$this->get('/authuser/manage/reports/'.$report->id.'/edit')->assertOk()->assertSee('Market segments &amp; key companies',false)->assertSee('Licenses &amp; pricing',false)->assertSee('Software Type')->assertSee('7200')->assertDontSee('name="key_info"',false)->assertDontSee('name="licenses_json"',false);
  $this->get('/authuser/manage/reports/create')->assertOk()->assertSee('Single User License')->assertSee('Add segment');
 }
 public function test_unchanged_options_keep_original_keys_values_and_license_ids(): void
 {
  $report=$this->report();$data=$this->data($report);$this->postJson('/authuser/manage/reports/'.$report->id,$data)->assertOk();
  $this->assertSame($report->key_info,DB::table('reports')->where('id',$report->id)->value('key_info'));
  foreach($data['report_licenses'] as $license)$this->assertDatabaseHas('reports_licenses',$license+['report_id'=>$report->id]);
  $this->assertDatabaseHas('reports_meta',['report_id'=>$report->id,'content'=>'<p>Keep saved content.</p>']);
 }
 public function test_add_edit_remove_segments_and_prices_round_trip_to_frontend(): void
 {
  $report=$this->report();$data=$this->data($report);
  $data['report_segments'][0]['value']="New type A\nNew type B";unset($data['report_segments'][1]);
  $data['report_segments'][]=['key'=>'','label'=>'Sales Channel','value'=>'Direct, Distributor'];
  $data['report_licenses'][0]['lic_price']=0;unset($data['report_licenses'][1]);$data['report_licenses'][]=['lic_name'=>'Enterprise License','lic_price'=>9900];
  $this->postJson('/authuser/manage/reports/'.$report->id,$data)->assertOk();$key=json_decode(DB::table('reports')->where('id',$report->id)->value('key_info'),true);
  $this->assertSame('New type A, New type B',$key['software_type']);$this->assertArrayNotHasKey('end-user',$key);$this->assertSame('Direct, Distributor',$key['sales_channel']);$this->assertSame('Example, Inc., Another Company',$key['key_players']);
  $this->assertDatabaseHas('reports_licenses',['id'=>$data['report_licenses'][0]['id'],'lic_price'=>0]);$this->assertDatabaseMissing('reports_licenses',['report_id'=>$report->id,'lic_name'=>'Corporate User License']);
  $this->get('/report/'.$report->slug)->assertOk()->assertSee('Enterprise License')->assertSee('9900');
 }
 public function test_invalid_options_return_friendly_errors_without_partial_writes(): void
 {
  $report=$this->report();$before=$this->data($report);
  $invalid=$before;$invalid['report_licenses'][0]['lic_price']='12.5';$this->postJson('/authuser/manage/reports/'.$report->id,$invalid)->assertUnprocessable()->assertJsonValidationErrors('report_licenses.0.lic_price');
  $invalid=$before;$invalid['report_segments'][]=['label'=>'Software Type','value'=>'Duplicate'];$this->postJson('/authuser/manage/reports/'.$report->id,$invalid)->assertUnprocessable();
  $invalid=$before;$invalid['report_licenses'][]=['lic_name'=>'Single User License','lic_price'=>100];$this->postJson('/authuser/manage/reports/'.$report->id,$invalid)->assertUnprocessable();
  $invalid=$before;$invalid['report_licenses'][0]['id']=2147483647;$this->postJson('/authuser/manage/reports/'.$report->id,$invalid)->assertUnprocessable();
  $this->assertDatabaseHas('reports',['id'=>$report->id,'key_info'=>$report->key_info]);$this->assertDatabaseHas('reports_licenses',['id'=>$before['report_licenses'][0]['id'],'lic_price'=>3200]);
 }
 public function test_all_rows_can_be_removed_explicitly_and_omitted_options_are_preserved(): void
 {
  $report=$this->report();$data=$this->data($report);unset($data['report_segments'],$data['report_licenses']);
  $this->postJson('/authuser/manage/reports/'.$report->id,$data)->assertOk();$this->assertDatabaseHas('reports',['id'=>$report->id,'key_info'=>'{}']);$this->assertSame(0,DB::table('reports_licenses')->where('report_id',$report->id)->count());
  $report=$this->report();$this->postJson('/authuser/manage/reports/'.$report->id,['title'=>$report->title,'category_id'=>$report->category_id,'publisher_id'=>$report->publisher_id,'pub_date'=>$report->pub_date])->assertOk();$this->assertDatabaseHas('reports',['id'=>$report->id,'key_info'=>$report->key_info]);$this->assertSame(2,DB::table('reports_licenses')->where('report_id',$report->id)->count());
 }
 public function test_licenses_used_in_orders_are_preserved_and_can_be_repriced(): void
 {
  config(['site.mail_notifications'=>false]);$report=$this->report();$data=$this->data($report);$license=$data['report_licenses'][0];
  $this->post('/checkoutprocess',['report_id'=>$report->id,'report_lic_id'=>$license['id'],'license'=>$license['lic_name'].':'.$license['lic_price'],'name'=>'Options test','email'=>'options-test@example.test','contact_no'=>'1234567890','country'=>'India','payment_mode'=>'Wire Transfer'])->assertRedirect('/wt/success');
  unset($data['report_licenses'][0]);$this->postJson('/authuser/manage/reports/'.$report->id,$data)->assertUnprocessable()->assertJsonValidationErrors('report_licenses');
  $data=$this->data($report);$data['report_licenses'][0]['lic_price']=4000;$this->postJson('/authuser/manage/reports/'.$report->id,$data)->assertOk();
  $this->assertDatabaseHas('reports_licenses',['id'=>$license['id'],'lic_price'=>4000]);$this->assertDatabaseHas('checkouts',['report_license_id'=>$license['id'],'amt_tobe_paid'=>3200]);
 }

}
