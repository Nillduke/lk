<?php
namespace Tests\Feature;

use App\Http\Middleware\OptimizeDelivery;
use App\Rules\NoEnquiryLinks;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class EnquiryProtectionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();config(['database.default'=>'sqlite','database.connections.sqlite.database'=>':memory:','site.turnstile.enabled'=>false,'site.mail_notifications'=>false,'app.url'=>'https://www.eternityinsights.com']);DB::purge('sqlite');
        $this->withoutMiddleware([OptimizeDelivery::class,\Illuminate\Routing\Middleware\ThrottleRequests::class]);Mail::fake();Http::preventStrayRequests();
        Schema::create('categories',function(Blueprint $t){$t->id();$t->string('name');$t->string('slug');});
        DB::table('categories')->insert(['id'=>1,'name'=>'Healthcare','slug'=>'healthcare']);
        Schema::create('country',function(Blueprint $t){$t->id();$t->string('nicename');$t->integer('phonecode');});DB::table('country')->insert(['nicename'=>'India','phonecode'=>91]);
        Schema::create('lr_pages',function(Blueprint $t){$t->id();$t->string('slug');$t->integer('status');});
        Schema::create('reports',function(Blueprint $t){$t->id();$t->string('title');$t->string('slug');$t->string('keyword');$t->integer('category_id');$t->integer('status');$t->integer('pages');$t->date('pub_date');});
        DB::table('reports')->insert(['id'=>1,'title'=>'Example Market','slug'=>'example-market','keyword'=>'Example','category_id'=>1,'status'=>1,'pages'=>100,'pub_date'=>'2026-10-01']);
        Schema::create('reports_licenses',function(Blueprint $t){$t->id();$t->integer('report_id');$t->integer('lic_price');});
        Schema::create('reports_enquiries',function(Blueprint $t){$t->id();$t->integer('report_id')->nullable();$t->string('uuid');$t->string('type');$t->string('status');foreach(['full_name','email_id','contact_no','company','role','country','message'] as $f)$t->text($f)->nullable();$t->timestamps();});
    }
    private function lead(): array { return ['full_name'=>'Alex Smith','email_id'=>'alex+research@example.com','contact_no'=>'+44 (0)20 7946 0958 ext 123','company'=>'Example Group','country'=>'India','message'=>'Please send information about your research services.','website_url'=>'']; }
    public static function linkPayloads(): array
    {
        return [
            ['message','See https://spam.example/offer'],['full_name','www.spam.example'],['contact_no','https://spam.example'],['email_id','https://spam.example'],
            ['message','Please visit spam.example for this offer'],['message','<a href="/offer">click</a>'],['message','[website](/offer)'],
            ['message','Visit https&#58;//spam.example'],['message','Visit %68%74%74%70%73%3A%2F%2Fspam.example'],['message','Visit spam[.]example'],
            ['message',"Visit \u{FF48}\u{FF54}\u{FF54}\u{FF50}\u{FF53}\u{FF1A}\u{FF0F}\u{FF0F}spam\u{FF0E}example"],
            ['message',"Visit h\u{200B}ttps://spam.example"],['company','spam.example'],['message','http://192.0.2.1/offer'],
        ];
    }
    #[DataProvider('linkPayloads')]
    public function test_links_are_rejected_before_storage_or_email(string $field,string $value): void
    {
        $data=$this->lead();$data[$field]=$value;
        $this->postJson('/contactus',$data)->assertUnprocessable()->assertJsonValidationErrors($field);
        $this->assertDatabaseCount('reports_enquiries',0);Mail::assertNothingSent();Http::assertNothingSent();
    }
    public function test_legitimate_email_phone_and_message_still_work(): void
    {
        $this->post('/contactus',$this->lead())->assertRedirect('/contactus/thankyou');
        $this->assertDatabaseHas('reports_enquiries',['email_id'=>'alex+research@example.com','type'=>'5']);Mail::assertNothingSent();
        $rule=new NoEnquiryLinks;
        foreach(['Data: annual market revenue','Dr. Alex Smith','A & B Ltd.','Please provide figures, e.g. annual revenue.'] as $text)$this->assertFalse($rule->containsLink($text));
    }
    public function test_contact_requires_meaningful_message_and_rejects_invalid_phone_and_honeypot(): void
    {
        foreach(['','TEST'] as $text){$data=$this->lead();$data['message']=$text;$this->postJson('/contactus',$data)->assertJsonValidationErrors('message');}
        $data=$this->lead();$data['contact_no']='RTTT';$this->postJson('/contactus',$data)->assertJsonValidationErrors('contact_no');
        foreach(['website_url','url2','pkjl'] as $trap){$data=$this->lead();$data[$trap]='bot';$this->postJson('/contactus',$data)->assertJsonValidationErrors('form');}
        $data=$this->lead();$data['full_name']=['bad'];$this->postJson('/contactus',$data)->assertUnprocessable();
        $this->assertDatabaseCount('reports_enquiries',0);Mail::assertNothingSent();
    }
    public function test_every_report_enquiry_endpoint_enforces_link_policy(): void
    {
        foreach(['request-a-sample','ask-for-discount','enquiry-before-buying','request-for-customization','request-toc'] as $type){
            $data=$this->lead();$data['message']='Visit https://spam.example';$this->postJson('/'.$type.'/1',$data)->assertUnprocessable()->assertJsonValidationErrors('message');
        }
        $data=$this->lead();$data['message']='Visit https://spam.example';$this->postJson('/searchfor',$data)->assertUnprocessable()->assertJsonValidationErrors('message');
        $this->assertDatabaseCount('reports_enquiries',0);Mail::assertNothingSent();
    }
    public function test_missing_failed_or_mismatched_turnstile_tokens_never_save_leads(): void
    {
        config(['site.turnstile.enabled'=>true,'site.turnstile.secret'=>'test-secret']);
        $this->postJson('/contactus',$this->lead())->assertUnprocessable()->assertJsonValidationErrors('cf-turnstile-response');Http::assertNothingSent();
        $data=$this->lead();$data['cf-turnstile-response']='test-token';
        foreach([['success'=>false],['success'=>true,'hostname'=>'attacker.example','action'=>'contactus'],['success'=>true,'hostname'=>'www.eternityinsights.com','action'=>'request-a-sample']] as $result){
            Http::fake(['challenges.cloudflare.com/*'=>Http::response($result)]);
            $this->postJson('/contactus',$data)->assertUnprocessable()->assertJsonValidationErrors('cf-turnstile-response');
        }
        $this->assertDatabaseCount('reports_enquiries',0);Mail::assertNothingSent();
    }
    public function test_valid_turnstile_is_verified_server_side_before_save_and_replay_fails(): void
    {
        config(['site.turnstile.enabled'=>true,'site.turnstile.secret'=>'test-secret']);
        Http::fake(['challenges.cloudflare.com/*'=>Http::sequence()->push(['success'=>true,'hostname'=>'www.eternityinsights.com','action'=>'contactus'])->push(['success'=>false,'error-codes'=>['timeout-or-duplicate']])]);
        $data=$this->lead();$data['cf-turnstile-response']='test-token';
        $this->post('/contactus',$data)->assertRedirect('/contactus/thankyou');
        Http::assertSent(fn($r)=>$r->url()==='https://challenges.cloudflare.com/turnstile/v0/siteverify'&&$r['secret']==='test-secret'&&$r['response']==='test-token');
        $this->postJson('/contactus',$data)->assertUnprocessable();$this->assertDatabaseCount('reports_enquiries',1);
    }
    public function test_turnstile_network_failures_fail_closed(): void
    {
        config(['site.turnstile.enabled'=>true,'site.turnstile.secret'=>'test-secret']);
        Http::fake(fn()=>throw new \Illuminate\Http\Client\ConnectionException('Network unavailable'));
        $data=$this->lead();$data['cf-turnstile-response']='test-token';$this->postJson('/contactus',$data)->assertUnprocessable();
        $this->assertDatabaseCount('reports_enquiries',0);Mail::assertNothingSent();
    }
    public function test_turnstile_widget_and_shared_script_are_present_on_active_form_pages(): void
    {
        config(['site.turnstile.enabled'=>true,'site.turnstile.site_key'=>'public-test-key']);
        foreach(['/contactus'=>'contactus','/request-a-sample/1'=>'request-a-sample','/ask-for-discount/1'=>'ask-for-discount','/enquiry-before-buying/1'=>'enquiry-before-buying','/request-for-customization/1'=>'request-for-customization'] as $url=>$action){
            $response=$this->get($url)->assertOk()->assertSee('data-enquiry-form',false)->assertSee('data-action="'.$action.'"',false)->assertSee('https://challenges.cloudflare.com/turnstile/v0/api.js',false)->assertDontSee('www.google.com/recaptcha');
            $this->assertSame(1,substr_count($response->getContent(),'https://challenges.cloudflare.com/turnstile/v0/api.js'));
            $response->assertSee('enquiry-protection.js',false);
        }
    }
}
