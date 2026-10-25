<?php
namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminEditorTest extends TestCase
{
    use DatabaseTransactions;
    private array $uploads = [];
    protected function tearDown(): void
    {
        foreach ($this->uploads as $path) if (is_file(public_path($path))) unlink(public_path($path));
        parent::tearDown();
    }
    private function admin(): void { $this->actingAs(User::where('username', 'lk_admin')->firstOrFail()); }
    private function image(string $resource = 'reports'): string
    {
        $result = $this->postJson('/authuser/media/images', ['resource' => $resource, 'file' => UploadedFile::fake()->image('chart.png', 800, 450)])->assertOk()->assertJsonStructure(['location', 'url', 'file']);
        $path = $result->json('file'); $this->uploads[] = $path;
        $this->assertSame('/'.$path, $result->json('location'));
        $this->get('/'.$path)->assertOk()->assertHeader('Content-Type', 'image/png');
        return '/'.$path;
    }
    public function test_all_forms_expose_local_editors_only_for_html_content(): void
    {
        $this->admin();
        foreach (config('admin') as $resource => $def) {
            $response = $this->get('/authuser/manage/'.$resource.'/create')->assertOk();
            $response->assertSee('/vendor/tinymce/tinymce.min.js', false)->assertSee('multipart/form-data', false);
            if (in_array($resource, ['reports', 'news', 'pages'])) $response->assertSee('admin-rich-editor', false);
            else $response->assertDontSee('class="form-control admin-rich-editor"', false);
        }
        $id = DB::table('reports')->value('id');
        $this->get('/authuser/manage/reports/'.$id.'/edit')->assertOk()->assertSee('report_content')->assertSee('report_toc')->assertSee('report_tables_figures');
    }
    public function test_raster_image_types_upload_and_are_publicly_readable(): void
    {
        $this->admin();
        foreach (['png', 'jpg', 'gif', 'webp'] as $extension) {
            $result = $this->postJson('/authuser/media/images', ['resource' => 'reports', 'file' => UploadedFile::fake()->image('chart.'.$extension, 60, 40)])->assertOk();
            $this->uploads[] = $result->json('file');
            $this->get($result->json('url'))->assertOk();
        }
    }
    public function test_image_endpoint_rejects_documents_executables_and_oversized_images(): void
    {
        $this->admin();
        foreach (['payload.php', 'drawing.svg', 'document.pdf'] as $name) {
            $this->postJson('/authuser/media/images', ['resource' => 'reports', 'file' => UploadedFile::fake()->createWithContent($name, '<?php echo "invalid";')])->assertUnprocessable();
        }
        $this->postJson('/authuser/media/images', ['resource' => 'news', 'file' => UploadedFile::fake()->image('large.png')->size(10241)])->assertUnprocessable();
    }
    public function test_upload_permissions_follow_news_module_and_require_authentication(): void
    {
        $this->postJson('/authuser/media/images', ['file' => UploadedFile::fake()->image('chart.png')])->assertUnauthorized();
        $user = User::create(['uuid' => (string)Str::uuid(), 'name' => 'Editor test', 'username' => 'editor_'.Str::random(8), 'email' => Str::random(10).'@example.test', 'password' => 'Test-password-123!', 'status' => 1, 'is_email_verified' => 1]);
        $role = DB::table('rbac_roles')->insertGetId(['Title' => 'Test news editor', 'Description' => 'Temporary test role', 'Lft' => 900000, 'Rght' => 900001]);
        $permission = DB::table('rbac_permissions')->where('Title', 'manage_news')->value('ID');
        DB::table('rbac_userroles')->insert(['UserID' => $user->id, 'RoleID' => $role, 'AssignmentDate' => time()]);
        DB::table('rbac_rolepermissions')->insert(['RoleID' => $role, 'PermissionID' => $permission, 'AssignmentDate' => time()]);
        $this->actingAs($user); $this->image('news');
        $this->postJson('/authuser/media/images', ['resource' => 'reports', 'file' => UploadedFile::fake()->image('chart.png')])->assertForbidden();
    }
    public function test_report_editor_html_round_trips_into_frontend_tabs(): void
    {
        $this->admin(); $url = $this->image();
        $source = DB::table('reports')->first(); $slug = 'editor-test-'.Str::lower(Str::random(12));
        $html = '<h2>Editor chart</h2><p><strong>Formatted research</strong></p><img src="'.$url.'" alt="Uploaded chart"><table><tbody><tr><td>Test segment</td></tr></tbody></table>';
        $data = ['title' => 'Editor round trip test', 'slug' => $slug, 'category_id' => $source->category_id, 'publisher_id' => $source->publisher_id, 'pages' => 100, 'pub_date' => '2026-10-24', 'region' => 'Global', 'status' => 1, 'content' => $html, 'toc' => '<h2>Test contents</h2><ul><li>Test chapter</li></ul>', 'tables_figures' => '<h2>Test figures</h2><p>Uploaded figure</p>'];
        $saved = $this->postJson('/authuser/manage/reports', $data)->assertOk();
        $this->assertDatabaseHas('reports_meta', ['report_id' => $saved->json('id'), 'content' => $html]);
        $this->get('/report/'.$slug)->assertOk()->assertSee('Formatted research')->assertSee('Uploaded chart')->assertSee('Test segment')->assertSee('Test chapter')->assertSee('Uploaded figure')->assertSee('href="#tableFig"', false);
    }
    public function test_news_featured_upload_and_content_match_frontend(): void
    {
        $this->admin(); $url = $this->image('news');
        $category = DB::table('categories')->value('id'); $slug = 'editor-news-'.Str::lower(Str::random(12));
        $data = ['headline' => 'Editor upload test', 'slug' => $slug, 'category_id' => $category, 'content' => '<h2>News test</h2><p>News editor content</p><img src="'.$url.'" alt="News inline upload">', 'featured_img' => '', 'featured_img_file' => UploadedFile::fake()->image('featured.jpg', 600, 400), 'status' => 1];
        $saved = $this->postJson('/authuser/manage/news', $data)->assertOk();
        $row = DB::table('news')->where('id', $saved->json('id'))->first();
        $this->assertStringStartsWith('/uploads/images/', $row->featured_img); $this->uploads[] = ltrim($row->featured_img, '/');
        $this->get($row->featured_img)->assertOk()->assertHeader('Content-Type', 'image/jpeg');
        $this->get(route('news_details', $slug, false))->assertOk()->assertSee('News editor content')->assertSee('News inline upload');
        $this->get(route('news_list', [], false))->assertOk()->assertSee('Editor upload test');
        $this->postJson('/authuser/manage/news/'.$row->id, ['headline' => $row->headline, 'category_id' => $category, 'content' => $row->content, 'featured_img' => $row->featured_img])->assertOk();
        $this->assertDatabaseHas('news', ['id' => $row->id, 'featured_img' => $row->featured_img]);
    }
    public function test_cms_editor_content_is_published_only_for_active_pages(): void
    {
        $this->admin(); $url = $this->image('pages');
        $slug = 'cms-editor-test-'.Str::lower(Str::random(12));
        $result = $this->postJson('/authuser/manage/pages', ['slug' => $slug, 'title' => 'CMS editor test', 'content' => '<h2>CMS rich heading</h2><p>CMS rich body</p><img src="'.$url.'" alt="CMS uploaded image">', 'status' => 1])->assertOk();
        $this->get('/'.$slug)->assertOk()->assertSee('CMS rich body')->assertSee('CMS uploaded image');
        $this->postJson('/authuser/manage/pages/'.$result->json('id'), ['status' => 0])->assertOk();
        $this->get('/'.$slug)->assertNotFound();
        $this->get('/editor-missing-page-123')->assertNotFound();
    }

    public function test_general_media_upload_remains_usable_for_spreadsheets(): void
    {
        $this->admin();
        $result = $this->postJson('/authuser/files/upload', ['file' => UploadedFile::fake()->createWithContent('reports.csv', "title,pages\nTest,100\n")])->assertOk();
        $this->uploads[] = $result->json('file');
        $this->get($result->json('url'))->assertOk()->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }
}
