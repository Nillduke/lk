<?php
namespace Tests\Feature;
use App\Services\CcavenueGateway;
use App\Services\CcavenueSettings;
use App\Http\Middleware\AdminAccess;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Crypt;
use Tests\TestCase;
class CcavenuePaymentTest extends TestCase
{
 protected function setUp():void {
  parent::setUp();config(['database.default'=>'sqlite','database.connections.sqlite.database'=>':memory:','site.mail_notifications'=>false,'ccavenue.enabled'=>true,'ccavenue.mode'=>'test','ccavenue.merchant_id'=>'123456','ccavenue.access_code'=>'TESTACCESS','ccavenue.working_key'=>'TESTWORKINGKEY','ccavenue.api_access_code'=>'','ccavenue.api_working_key'=>'']);DB::purge('sqlite');Http::preventStrayRequests();
  $this->withoutMiddleware(\App\Http\Middleware\OptimizeDelivery::class);
  Schema::create('categories',fn(Blueprint $t)=>[$t->id(),$t->string('name'),$t->string('slug')]);
  Schema::create('country',fn(Blueprint $t)=>[$t->id(),$t->string('name'),$t->string('nicename'),$t->integer('phonecode')]);
  Schema::create('reports',fn(Blueprint $t)=>[$t->id(),$t->string('title'),$t->string('slug'),$t->integer('status')]);
  Schema::create('reports_licenses',fn(Blueprint $t)=>[$t->id(),$t->integer('report_id'),$t->string('lic_name'),$t->decimal('lic_price',10,2)]);
  Schema::create('checkouts',function(Blueprint $t){$t->id();$t->integer('checkout_id');$t->integer('report_id');$t->integer('report_license_id');foreach(['uuid','order_number','name','email','contact_no','country','license_type','currency','payment_by','payment_status','status'] as $f)$t->string($f);foreach(['company','address','message','payment_info'] as $f)$t->text($f)->nullable();$t->decimal('total_amount',10,2);$t->decimal('amt_tobe_paid',10,2);$t->boolean('is_active');$t->timestamps();});
  (require database_path('migrations/2026_11_01_000003_add_ccavenue_payments.php'))->up();
  DB::table('reports')->insert(['id'=>1,'title'=>'Biobanking Market Full Research Report','slug'=>'biobanking-market','status'=>1]);DB::table('reports_licenses')->insert(['id'=>7,'report_id'=>1,'lic_name'=>'Single User License','lic_price'=>3500]);
 }
 private function payload():array{return ['report_id'=>1,'report_lic_id'=>7,'name'=>'Buyer','email'=>'buyer@example.test','contact_no'=>'1234567890','country'=>'India','company'=>'Example','address'=>'Example Road','payment_mode'=>'CCAvenue','remember'=>'on','amt_tobe_paid'=>'1.00'];}
 private function attempt():object{$this->post('/checkoutprocess',$this->payload())->assertRedirect(route('ccavenue.redirect'));return DB::table('lr_payment_attempts')->first();}
 private function gatewayResponse(object $a,array $override=[]):string{return app(CcavenueGateway::class)->encrypt(http_build_query(array_replace(['order_id'=>$a->order_number,'amount'=>'3500.00','currency'=>'USD','merchant_param1'=>$a->nonce,'order_status'=>'Success','tracking_id'=>'123456789012'],$override)),'TESTWORKINGKEY');}
 private function api(object $a,array $override=[]):void {
  $data=array_replace(['order_no'=>$a->order_number,'order_amt'=>3500.00,'order_curr'=>'USD','reference_no'=>'123456789012','order_status'=>'Shipped','error_code'=>''],$override);
  Http::fake(['https://apitest.ccavenue.com/*'=>Http::response(http_build_query(['status'=>'0','enc_response'=>app(CcavenueGateway::class)->encrypt(json_encode($data),'TESTWORKINGKEY')]))]);
 }
 public function test_checkout_uses_saved_usd_price_and_encrypted_hosted_payload():void {
  $a=$this->attempt();$this->assertSame(350000,CcavenueGateway::money($a->amount));$this->assertStringNotContainsString('TESTWORKINGKEY',$a->credentials);$order=DB::table('checkouts')->first();$request=app(CcavenueGateway::class)->request($a,$order);parse_str(app(CcavenueGateway::class)->decrypt($request['encRequest'],'TESTWORKINGKEY'),$plain);
  $this->assertSame('3500.00',$plain['amount']);$this->assertSame('USD',$plain['currency']);$this->assertSame($a->nonce,$plain['merchant_param1']);$this->assertStringContainsString($a->id,$plain['redirect_url']);$this->assertStringStartsWith('https://test.ccavenue.com/',$request['action']);
  $this->get('/payments/ccavenue/redirect')->assertOk()->assertSee('encRequest')->assertDontSee('TESTWORKINGKEY');
 }
 public function test_verified_callback_without_session_marks_paid_once_and_never_downgrades():void {
  $a=$this->attempt();$this->flushSession();$this->api($a);$response=$this->post(route('ccavenue.response',$a->id),['encResp'=>$this->gatewayResponse($a)])->assertStatus(303);$response->assertRedirect();$this->get($response->headers->get('Location'))->assertOk()->assertSee('Payment received');
  $this->assertDatabaseHas('checkouts',['payment_status'=>'Success']);$this->assertDatabaseHas('lr_payment_attempts',['status'=>'Success','tracking_id'=>'123456789012']);
  $this->post(route('ccavenue.response',$a->id),['encResp'=>$this->gatewayResponse($a,['order_status'=>'Failure'])])->assertStatus(303);$this->assertDatabaseHas('checkouts',['payment_status'=>'Success']);Http::assertSentCount(1);
 }
 public function test_tampered_callback_fields_are_rejected_without_api_or_db_update():void {
  $a=$this->attempt();foreach([['amount'=>'1.00'],['currency'=>'INR'],['order_id'=>'OTHER'],['merchant_param1'=>'wrong'],['amount'=>'3500e0']] as $override)$this->post(route('ccavenue.response',$a->id),['encResp'=>$this->gatewayResponse($a,$override)])->assertStatus(422);
  $this->post(route('ccavenue.response',$a->id),['encResp'=>'bad'])->assertStatus(422);$this->assertDatabaseHas('checkouts',['payment_status'=>'Pending']);Http::assertNothingSent();
 }
 public function test_success_is_not_trusted_when_status_api_is_unavailable_or_mismatched():void {
  $a=$this->attempt();Http::fake(['*'=>Http::response('denied',503)]);$this->post(route('ccavenue.response',$a->id),['encResp'=>$this->gatewayResponse($a)])->assertStatus(303);$this->assertDatabaseHas('lr_payment_attempts',['status'=>'Verification pending']);$this->assertDatabaseHas('checkouts',['payment_status'=>'Pending']);
  foreach([['order_amt'=>1],['order_curr'=>'INR'],['order_no'=>'wrong'],['reference_no'=>'999999999999'],['order_fraud_status'=>'FR'],['order_status'=>'Awaited']] as $override){$this->api($a,$override);$this->post(route('ccavenue.response',$a->id),['encResp'=>$this->gatewayResponse($a)])->assertStatus(303);$this->assertDatabaseHas('checkouts',['payment_status'=>'Pending']);}
 }
 public function test_cancel_and_failure_do_not_claim_payment_and_plain_cancel_changes_nothing():void {
  $a=$this->attempt();$this->get(route('ccavenue.cancel',$a->id))->assertStatus(422);$this->assertDatabaseHas('checkouts',['payment_status'=>'Pending']);$this->post(route('ccavenue.cancel',$a->id),['encResp'=>$this->gatewayResponse($a,['order_status'=>'Aborted'])])->assertStatus(303);$this->assertDatabaseHas('checkouts',['payment_status'=>'Failed']);Http::assertNothingSent();
 }
 public function test_disabled_gateway_inactive_report_and_wrong_license_cannot_create_payment():void {
  config(['ccavenue.enabled'=>false]);$this->from('/checkout-form/1')->post('/checkoutprocess',$this->payload())->assertRedirect('/checkout-form/1')->assertSessionHasErrors('payment_mode');$this->assertDatabaseCount('checkouts',0);
  config(['ccavenue.enabled'=>true]);$p=$this->payload();$p['report_lic_id']=99;$this->post('/checkoutprocess',$p)->assertStatus(422);DB::table('reports')->update(['status'=>0]);$this->post('/checkoutprocess',$this->payload())->assertStatus(422);$this->assertDatabaseCount('lr_payment_attempts',0);
 }
 public function test_wire_transfer_still_works_and_terms_are_enforced():void {
  $p=$this->payload();unset($p['remember']);$this->postJson('/checkoutprocess',$p)->assertUnprocessable()->assertJsonValidationErrors('remember');$p['remember']='on';$p['payment_mode']='Wire Transfer';$this->post('/checkoutprocess',$p)->assertRedirect(route('wt_success'));$this->assertDatabaseHas('checkouts',['payment_by'=>'Wire Transfer','payment_status'=>'Pending']);$this->assertDatabaseCount('lr_payment_attempts',0);
 }
 private function admin(bool $allowed=true):void{$user=\Mockery::mock(User::class)->makePartial();$user->id=1;$user->name='Admin';$user->shouldReceive('canAccess')->andReturn($allowed);$this->actingAs($user)->withoutMiddleware(AdminAccess::class);}
 public function test_admin_settings_are_permission_protected_and_secrets_are_not_rendered_or_flashed():void {
  $this->admin(false);$this->get('/authuser/settings/payments')->assertForbidden();$this->post('/authuser/settings/payments',['enabled'=>1])->assertForbidden();$this->get('/authuser/payments/ccavenue')->assertForbidden();
  $this->admin();$this->get('/authuser/settings/payments')->assertOk()->assertSee('CCAvenue payment settings')->assertDontSee('TESTWORKINGKEY')->assertDontSee('TESTACCESS');
  $this->post('/authuser/settings/payments',['enabled'=>1,'mode'=>'test','merchant_id'=>'123456','working_key'=>'<secret>'])->assertSessionHasErrors('working_key')->assertSessionMissing('_old_input.working_key');
 }
 public function test_settings_encrypt_credentials_and_preserve_other_env_values_and_existing_secret():void {
  $path=tempnam(sys_get_temp_dir(),'cca');file_put_contents($path,"MAIL_HOST=unchanged.example\n");$s=new CcavenueSettings($path);
  try{$s->save(['enabled'=>1,'mode'=>'test','merchant_id'=>'123456','access_code'=>'PRIVATEACCESS','working_key'=>'PRIVATEKEY']);$contents=file_get_contents($path);$this->assertStringNotContainsString('PRIVATEKEY',$contents);$this->assertStringNotContainsString('PRIVATEACCESS',$contents);$this->assertStringContainsString('MAIL_HOST=unchanged.example',$contents);$this->assertTrue($s->ready());$s->save(['enabled'=>0,'mode'=>'test','merchant_id'=>'123456']);$this->assertSame('PRIVATEKEY',config('ccavenue.working_key'));$this->assertFalse($s->ready());}finally{unlink($path);}
 }
 public function test_admin_can_reconcile_lost_callback_and_unsigned_result_is_private():void {
  $a=$this->attempt();$this->get(route('ccavenue.result',$a->id))->assertForbidden();$this->admin();$this->api($a);$this->post(route('admin.ccavenue.recheck',$a->id))->assertRedirect();$this->assertDatabaseHas('checkouts',['payment_status'=>'Success']);$this->get('/authuser/payments/ccavenue')->assertOk()->assertSee('123456789012')->assertDontSee('TESTWORKINGKEY');
 }
 public function test_working_key_rotation_does_not_break_in_flight_callbacks():void {
  $a=$this->attempt();config(['ccavenue.enabled'=>false,'ccavenue.working_key'=>'NEWKEY']);$this->api($a);$this->post(route('ccavenue.response',$a->id),['encResp'=>$this->gatewayResponse($a)])->assertStatus(303);$this->assertDatabaseHas('checkouts',['payment_status'=>'Success']);
 }
 public function test_server_notification_updates_order_without_browser_session():void {
  $a=$this->attempt();$this->flushSession();$this->api($a);$this->post(route('ccavenue.notify'),['encResp'=>$this->gatewayResponse($a)])->assertOk()->assertSee('OK');$this->assertDatabaseHas('checkouts',['payment_status'=>'Success']);
 }

}
