<?php
namespace Tests\Feature;

use App\Http\Middleware\AdminAccess;
use App\Models\User;
use App\Services\ReportFaqs;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Tests\TestCase;

class ReportFaqsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();config(['database.default'=>'sqlite','database.connections.sqlite.database'=>':memory:']);DB::purge('sqlite');
        Schema::create('categories',function(Blueprint $t){$t->id();$t->string('name');$t->string('slug');});DB::table('categories')->insert(['id'=>1,'name'=>'Healthcare','slug'=>'healthcare']);
        Schema::create('publishers',function(Blueprint $t){$t->id();$t->string('name');});DB::table('publishers')->insert(['id'=>1,'name'=>'Eternity Insights']);
        Schema::create('country',function(Blueprint $t){$t->id();$t->string('nicename');$t->integer('phonecode');});
        Schema::create('reports',function(Blueprint $t){$t->id();$t->string('uuid')->nullable();$t->string('title',1500);$t->string('slug');$t->string('keyword')->nullable();$t->integer('category_id');$t->integer('publisher_id');$t->integer('status')->default(1);$t->integer('pages')->default(0);$t->date('pub_date');$t->string('region')->nullable();$t->text('key_info')->nullable();foreach(['description','meta_title','meta_description','meta_keywords'] as $c)$t->text($c)->nullable();$t->timestamps();});
        Schema::create('reports_meta',function(Blueprint $t){$t->id();$t->integer('report_id');$t->text('content');$t->text('toc')->nullable();$t->text('tables_figures')->nullable();$t->timestamps();});
        Schema::create('reports_licenses',function(Blueprint $t){$t->id();$t->integer('report_id');$t->string('lic_name');$t->integer('lic_price');});
        (require database_path('migrations/2026_11_01_000002_add_report_faq_overrides.php'))->up();
        $admin=Mockery::mock(User::class)->makePartial();$admin->id=1;$admin->shouldReceive('canAccess')->andReturnUsing(fn($permission)=>$permission!=='manage_access');$this->actingAs($admin)->withoutMiddleware(AdminAccess::class);
    }
    private function content(): string
    {
        return '<p>The global Biobanking Market was valued at USD 54.60 billion in 2025 and is projected to reach USD 88.78 billion by 2035, expanding at a CAGR of 4.98% during the forecast period.</p><p>The biobanking market is driven by growing adoption of precision medicine and increased research investment.</p><h3>Biobanking Market, By Product</h3><ul><li>Cryogenic Storage Systems</li><li>Media &amp; Consumables</li></ul><h3>Biobanking Market, By Application</h3><ul><li>Drug Discovery</li><li>Clinical Research</li></ul><p>North America is the largest market thanks to established infrastructure.</p><p>Asia Pacific is the fastest-growing region, supported by rising investments.</p><h3>List of Companies Profiled in the report:</h3><ul><li>Example, Inc.</li><li>Research &amp; Co.</li></ul>';
    }
    private function payload(): array
    {
        return ['title'=>'Biobanking Market 2026-2035','slug'=>'biobanking-test','keyword'=>'Biobanking','category_id'=>1,'publisher_id'=>1,'pub_date'=>'2026-11-01','status'=>1,'content'=>$this->content()];
    }
    private function create(): int {return $this->postJson('/authuser/manage/reports',$this->payload())->assertOk()->json('id');}
    public function test_five_questions_extract_supported_figures_segments_regions_and_companies(): void
    {
        $rows=app(ReportFaqs::class)->generate((object)['keyword'=>'Biobanking','content'=>$this->content()]);$this->assertCount(5,$rows);
        foreach(['54.60','88.78','2025','2035','4.98%'] as $value)$this->assertStringContainsString($value,$rows[0]['answer']);
        $this->assertStringContainsString('precision medicine',$rows[1]['answer']);$this->assertStringNotContainsString('cloud',$rows[1]['answer']);
        $this->assertStringContainsString('Product (Cryogenic Storage Systems, Media & Consumables)',$rows[2]['answer']);
        $this->assertStringContainsString('North America',$rows[3]['answer']);$this->assertStringContainsString('Asia Pacific',$rows[3]['answer']);
        $this->assertSame('Companies profiled in the report include Example, Inc.; Research & Co.',$rows[4]['answer']);
    }
    public function test_placeholders_company_valuations_and_unavailable_data_are_not_published_as_market_facts(): void
    {
        $r=(object)['keyword'=>'South America Pharmacy Information Systems','content'=>'<p>The market size in 2025 is USD XX.XX million. CAGR is XX.XX%.</p><p>Example Company was valued at USD 22 billion in 2024.</p><script>North America is the largest market and is valued at USD 500 billion.</script>','key_info'=>'{"types":"Software, Services","key_players":"Example Company"}'];
        $rows=app(ReportFaqs::class)->generate($r);$this->assertSame('',$rows[0]['answer']);$this->assertSame('',$rows[1]['answer']);$this->assertSame('',$rows[3]['answer']);
        $this->assertStringContainsString('South America Pharmacy Information Systems Market',$rows[0]['question']);
        $this->assertStringNotContainsString('global',$rows[2]['answer']);
    }
    public function test_public_page_replaces_old_faqs_and_schema_matches_visible_answers_on_standard_and_amp_pages(): void
    {
        $id=$this->create();
        foreach(['/report/biobanking-test','/report/amp/biobanking-test'] as $url){
            $response=$this->get($url)->assertOk()->assertSee('What is the size of the Biobanking Market')->assertSee('USD 54.60 billion')->assertDontSee('What would be the forecast period')->assertDontSee('2022 to 2030');
            preg_match_all('~<script type="application/ld\+json">(.*?)</script>~s',$response->getContent(),$m);$faq=array_values(array_filter(array_map(fn($j)=>json_decode($j,true),$m[1]),fn($j)=>($j['@type']??'')==='FAQPage'));
            $this->assertCount(1,$faq);$this->assertCount(5,$faq[0]['mainEntity']);$this->assertSame('4.98%',substr($faq[0]['mainEntity'][0]['acceptedAnswer']['text'],strpos($faq[0]['mainEntity'][0]['acceptedAnswer']['text'],'4.98%'),5));
        }
        $this->get('/authuser/manage/reports/'.$id.'/edit')->assertOk()->assertSee('Report FAQs')->assertSee('Extract new drafts')->assertSee('faq_mode',false);
        $this->get('/authuser/manage/reports/create')->assertOk()->assertSee('Report FAQs');
    }
    public function test_custom_answers_survive_report_content_updates_hidden_mode_and_reset_to_automatic(): void
    {
        $id=$this->create();$custom=['report_faqs_present'=>1,'faq_mode'=>'custom','report_faqs'=>[['question'=>'Custom question?','answer'=>'Custom answer.'],['question'=>'Unanswered draft?','answer'=>'']]];
        $this->postJson('/authuser/manage/reports/'.$id,$this->payload()+$custom)->assertOk();
        $this->get('/report/biobanking-test')->assertOk()->assertSee('Custom answer.')->assertDontSee('Unanswered draft?')->assertDontSee('What is the size');
        $this->postJson('/authuser/manage/reports/'.$id,array_replace($this->payload(),['content'=>'<p>Content was changed.</p>']))->assertOk();
        $this->get('/report/biobanking-test')->assertOk()->assertSee('Custom answer.');
        $custom['faq_mode']='hidden';$this->postJson('/authuser/manage/reports/'.$id,$this->payload()+$custom)->assertOk();
        $this->get('/report/biobanking-test')->assertOk()->assertDontSee('FAQPage')->assertDontSee('Custom answer.');
        $this->get('/authuser/manage/reports/'.$id.'/edit')->assertOk()->assertSee('Custom answer.');
        $custom['faq_mode']='auto';$this->postJson('/authuser/manage/reports/'.$id,$this->payload()+$custom)->assertOk();
        $this->assertDatabaseMissing('lr_report_faqs',['report_id'=>$id]);$this->get('/report/biobanking-test')->assertOk()->assertSee('What is the size');
    }
    public function test_validation_prevents_partial_saves_and_custom_text_cannot_inject_html_or_schema(): void
    {
        $id=$this->create();$bad=$this->payload()+['report_faqs_present'=>1,'faq_mode'=>'custom','report_faqs'=>[['question'=>'','answer'=>'Answer without question']]];
        $bad['title']='Must not save';$this->postJson('/authuser/manage/reports/'.$id,$bad)->assertUnprocessable()->assertJsonValidationErrors('report_faqs.0.question');$this->assertDatabaseMissing('reports',['title'=>'Must not save']);
        $bad['report_faqs']=[['question'=>'Question?','answer'=>'']];$this->postJson('/authuser/manage/reports/'.$id,$bad)->assertUnprocessable();
        $bad['report_faqs']=[['question'=>'Question?','answer'=>'</script><script>alert(1)</script>']];$this->postJson('/authuser/manage/reports/'.$id,$bad)->assertOk();
        $html=$this->get('/report/biobanking-test')->assertOk()->getContent();$this->assertStringNotContainsString('<script>alert(1)</script>',$html);$this->assertStringContainsString('&lt;script&gt;alert(1)&lt;/script&gt;',$html);$this->assertStringContainsString('\\u003C',$html);
    }
    public function test_extraction_uses_unsaved_content_without_writing_faqs_and_enforces_write_permission(): void
    {
        $this->postJson('/authuser/reports/faqs/extract',['keyword'=>'Biobanking','content'=>$this->content(),'report_segments'=>[]])->assertOk()->assertJsonCount(5,'rows')->assertJsonPath('rows.0.question','What is the size of the Biobanking Market, and how fast is it growing?');
        $this->assertSame(0,DB::table('lr_report_faqs')->count());
        $viewer=Mockery::mock(User::class)->makePartial();$viewer->id=2;$viewer->shouldReceive('canAccess')->andReturnFalse();$this->actingAs($viewer);
        $this->postJson('/authuser/reports/faqs/extract',['keyword'=>'Biobanking','content'=>$this->content()])->assertForbidden();
    }
    public function test_custom_report_images_keep_their_sources_when_style_is_the_first_attribute(): void
    {
        $payload=$this->payload();$images=[
            '<img style="width:800px;height:500px" src="/uploads/images/custom-test-size.png" alt="Market size">',
            "<img style='width:800px' src='/uploads/images/custom-test-competition.png' alt='Competition'>",
            '<img style="display:block" src="/uploads/images/custom-test-regions.png" alt="Regions">',
        ];
        $payload['content']='<p>Original report introduction.</p>'.implode('<p>Keep this paragraph between images.</p>',$images);
        $this->postJson('/authuser/manage/reports',$payload)->assertOk();
        $response=$this->get('/report/biobanking-test')->assertOk();
        foreach(['custom-test-size.png','custom-test-competition.png','custom-test-regions.png','Market size','Competition','Regions'] as $text)$response->assertSee($text);
        $response->assertSee('Original report introduction.')->assertDontSee('<img>',false);
        $this->assertDatabaseHas('reports_meta',['content'=>$payload['content']]);
    }

    public function test_healthcare_sections_and_coverage_provide_five_answers_without_inventing_missing_figures(): void
    {
        $html='<p>The report considers 2021-2024 historic period, 2025 as base year, and 2026-2035 as forecast period.</p><h2>Key Research Highlights: Market Drivers</h2><p>Increasing product approvals and growing research investment support demand across the market.</p><h2>Key Research Highlights: Trends</h2><p>Increasing research and development spending is a leading industry trend in this market.</p><h2>Key Research Highlights: Market Restraints</h2><p>High product costs and limited awareness restrict adoption in developing regions.</p><table><tr><td>The market size value in 2025</td><td>USD XX.XX Million</td></tr><tr><td>CAGR (2026-2035)</td><td>XX.XX %</td></tr><tr><td>Base year for estimation</td><td>2025</td></tr><tr><td>Forecast period</td><td>2026-2035</td></tr><tr><td>Regional scope</td><td>North America, Europe, Asia Pacific</td></tr></table><h2>Key Market Players</h2><ul><li>AbbVie, Inc.</li><li>Novartis</li><li>Merck</li><li>Bristol Myers Squibb</li><li>Johnson &amp; Johnson</li><li>Other Company</li></ul>';
        $r=(object)['keyword'=>'Antibody Therapeutics','content'=>$html,'key_info'=>'{"types":"Monoclonal, Bispecific"}'];
        $rows=app(ReportFaqs::class)->generate($r);$this->assertCount(5,array_filter($rows,fn($row)=>$row['answer']!==''));
        $this->assertStringContainsString('2026-2035',$rows[0]['answer']);$this->assertStringContainsString('2025 as the base year',$rows[0]['answer']);$this->assertStringContainsString('not provided',$rows[0]['answer']);$this->assertStringNotContainsString('XX',$rows[0]['answer']);
        foreach(['product approvals','research and development','High product costs'] as $fact)$this->assertStringContainsString($fact,$rows[1]['answer']);
        $this->assertStringContainsString('does not specify a leading region',$rows[3]['answer']);$this->assertStringNotContainsString('North America leads',$rows[3]['answer']);
        $this->assertSame('Companies profiled in the report include AbbVie, Inc.; Novartis; Merck; Bristol Myers Squibb; Johnson & Johnson.',$rows[4]['answer']);
    }

}
