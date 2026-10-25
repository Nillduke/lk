<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class MigrationFlowTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        config(['site.mail_notifications' => false]);
    }

    private function admin(): User
    {
        return User::where('username', 'lk_admin')->firstOrFail();
    }

    public function test_primary_and_secondary_connections_use_the_single_environment_file(): void
    {
        $this->assertSame('ei_db', DB::connection()->getDatabaseName());
        $this->assertSame('reportdb', DB::connection('reportdb')->getDatabaseName());
        $this->assertNotEmpty(DB::connection('reportdb')->select('SELECT 1 AS connected'));
    }

    public function test_admin_routes_require_a_login_and_assigned_role(): void
    {
        $this->get('/authuser/dashboard')->assertRedirect('/auth/v/login');
        $user = User::create(['uuid' => (string) Str::uuid(), 'name' => 'Test unassigned user', 'username' => 'test_'.Str::random(8), 'email' => Str::random(12).'@example.test', 'password' => 'Test-password-123!', 'status' => 1, 'is_email_verified' => 1]);
        $this->actingAs($user)->get('/authuser/dashboard')->assertForbidden();
    }

    public function test_valid_enquiry_is_stored_and_invalid_email_is_rejected(): void
    {
        $id = DB::table('reports')->where('status', 1)->value('id');
        $email = Str::lower(Str::random(10)).'@example.test';
        $this->post('/request-a-sample/'.$id, ['full_name' => 'Laravel migration check', 'email_id' => $email, 'contact_no' => '+919876543210', 'company' => 'Test', 'country' => 'India', 'message' => 'Test enquiry'])->assertRedirect('/request-a-sample/thankyou');
        $this->assertDatabaseHas('reports_enquiries', ['email_id' => $email, 'report_id' => $id, 'type' => '1']);
        $this->postJson('/contactus', ['full_name' => 'Bad email', 'email_id' => 'invalid', 'contact_no' => '1234567890'])->assertUnprocessable();
    }

    public function test_checkout_uses_database_price_instead_of_client_amount(): void
    {
        $license = DB::table('reports_licenses as l')->join('reports as r', 'r.id', '=', 'l.report_id')->where('r.status', 1)->select('l.*')->first();
        $email = Str::lower(Str::random(10)).'@example.test';
        $this->post('/checkoutprocess', ['report_id' => $license->report_id, 'report_lic_id' => $license->id, 'license' => $license->lic_name.':0.01', 'amt_tobe_paid' => '0.01', 'name' => 'Pricing check', 'email' => $email, 'contact_no' => '1234567890', 'country' => 'India', 'payment_mode' => 'Wire Transfer'])->assertRedirect('/wt/success');
        $row = DB::table('checkouts')->where('email', $email)->first();
        $this->assertNotNull($row);
        $this->assertEquals($license->lic_price, $row->amt_tobe_paid);
        $this->assertSame('Pending', $row->payment_status);
    }

    public function test_admin_can_create_update_and_delete_report_with_metadata_and_licenses(): void
    {
        $this->actingAs($this->admin());
        $source = DB::table('reports')->first();
        $slug = 'migration-test-'.Str::lower(Str::random(10));
        $data = ['title' => 'Laravel integration test report', 'slug' => $slug, 'category_id' => $source->category_id, 'publisher_id' => $source->publisher_id, 'pages' => 123, 'pub_date' => '2026-10-24', 'region' => 'Global', 'status' => 1, 'content' => '<p>Test content</p>', 'toc' => 'Test TOC', 'tables_figures' => 'Test figures', 'licenses_json' => json_encode([['lic_name' => 'Single User License', 'lic_price' => 1234]])];
        $this->postJson('/authuser/manage/reports', $data)->assertOk()->assertJson(['status' => 1]);
        $id = DB::table('reports')->where('slug', $slug)->value('id');
        $this->assertNotNull($id);
        $this->assertDatabaseHas('reports_meta', ['report_id' => $id, 'toc' => 'Test TOC']);
        $this->assertDatabaseHas('reports_licenses', ['report_id' => $id, 'lic_price' => 1234]);
        DB::table('reports_licenses')->insert(['report_id'=>$id,'lic_name'=>'Multi User License','lic_price'=>3000]);
        $this->get('/all-reports?selectPrice=2000-4000')->assertOk()->assertDontSee('Laravel integration test report');
        $this->get('/all-reports?selectPrice=upto2000')->assertOk()->assertSee('Laravel integration test report');
        $data['title'] = 'Updated test report';
        $this->postJson('/authuser/manage/reports/'.$id, $data)->assertOk();
        $this->assertDatabaseHas('reports', ['id' => $id, 'title' => 'Updated test report']);
        $this->deleteJson('/authuser/manage/reports/'.$id)->assertOk();
        $this->assertDatabaseMissing('reports', ['id' => $id]);
        $this->assertDatabaseMissing('reports_meta', ['report_id' => $id]);
    }

    public function test_csv_import_creates_report_content_and_license(): void
    {
        $this->actingAs($this->admin());
        $source = DB::table('reports')->first();
        $slug = 'csv-test-'.Str::lower(Str::random(10));
        $csv = "title,slug,category_id,publisher_id,pages,pub_date,content,single_user_price\nCSV Test Report,$slug,$source->category_id,$source->publisher_id,100,2026-10-24,Imported content,999\n";
        $file = UploadedFile::fake()->createWithContent('reports.csv', $csv);
        $this->post('/authuser/reports/import', ['file' => $file])->assertSessionHasNoErrors()->assertRedirect();
        $id = DB::table('reports')->where('slug', $slug)->value('id');
        $this->assertNotNull($id);
        $this->assertDatabaseHas('reports_meta', ['report_id' => $id, 'content' => 'Imported content']);
        $this->assertDatabaseHas('reports_licenses', ['report_id' => $id, 'lic_price' => 999]);
    }

    public function test_password_reset_tokens_are_hashed_and_do_not_change_password_until_used(): void
    {
        $user = $this->admin();
        $old = $user->password;
        $this->post('/auth/v/forgot-password', ['email' => $user->email])->assertRedirect();
        $row = DB::table('lr_password_reset_tokens')->where('email', $user->email)->first();
        $this->assertNotNull($row);
        $this->assertStringStartsWith('$2y$', $row->token);
        $this->assertSame($old, $user->fresh()->password);
    }

    public function test_xlsx_import_reads_first_worksheet_and_default_category_and_publisher(): void
    {
        $this->actingAs($this->admin());
        $source = DB::table('reports')->first();
        $file = new UploadedFile(base_path('tests/Fixtures/reports.xlsx'), 'reports.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);
        $this->post('/authuser/reports/import', ['file' => $file, 'category_id' => $source->category_id, 'publisher_id' => $source->publisher_id])->assertSessionHasNoErrors()->assertRedirect();
        $id = DB::table('reports')->where('slug', 'xlsx-integration-test-fixture')->value('id');
        $this->assertNotNull($id);
        $this->assertDatabaseHas('reports_meta', ['report_id' => $id, 'content' => 'XLSX imported content']);
        $this->assertDatabaseHas('reports_licenses', ['report_id' => $id, 'lic_price' => 777]);
    }

    public function test_admin_metadata_bulk_status_and_user_api_are_authorized(): void
    {
        $this->actingAs($this->admin());
        $report = DB::table('reports')->first();
        $this->patchJson('/authuser/api/reports/meta/'.$report->id, ['toc' => 'Updated metadata test'])->assertOk();
        $this->assertDatabaseHas('reports_meta', ['report_id' => $report->id, 'toc' => 'Updated metadata test']);
        $this->postJson('/authuser/manage/reports/bulk', ['ids' => [$report->id], 'action' => 'status', 'status' => '0'])->assertOk();
        $this->assertDatabaseHas('reports', ['id' => $report->id, 'status' => 0]);
        $this->getJson('/authuser/api/users/'.$this->admin()->uuid)->assertOk()->assertJsonMissingPath('data.password')->assertJsonMissingPath('data.verification_code');
    }

    public function test_cms_content_is_served_by_laravel_without_editing_php_templates(): void
    {
        $this->actingAs($this->admin());
        $this->postJson('/authuser/manage/pages', ['slug' => 'faqs', 'title' => 'FAQs managed in Laravel', 'content' => '<p>CMS integration check</p>', 'status' => '1'])->assertOk();
        $this->get('/faqs')->assertOk()->assertSee('FAQs managed in Laravel')->assertSee('CMS integration check');
    }
}
