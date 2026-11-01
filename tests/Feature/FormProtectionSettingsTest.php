<?php
namespace Tests\Feature;

use App\Http\Middleware\AdminAccess;
use App\Models\User;
use App\Services\FormProtectionSettings;
use Illuminate\Support\Facades\Crypt;
use Mockery;
use Tests\TestCase;

class FormProtectionSettingsTest extends TestCase
{
    private string $path;
    protected function setUp(): void
    {
        parent::setUp();
        $dir=storage_path('framework/testing');if(!is_dir($dir))mkdir($dir,0700,true);
        $this->path=tempnam($dir,'forms-env-');file_put_contents($this->path,"APP_NAME=Test\nMAIL_HOST=keep.example\n");
        config(['site.turnstile.enabled'=>false,'site.turnstile.secret'=>'','site.turnstile.site_key'=>'public-test-key']);
        $this->app->instance(FormProtectionSettings::class,new FormProtectionSettings($this->path));$this->admin(true);
    }
    protected function tearDown(): void
    {
        if(isset($this->path)&&is_file($this->path))unlink($this->path);
        parent::tearDown();
    }
    private function admin(bool $allowed): void
    {
        $u=Mockery::mock(User::class)->makePartial();$u->id=1;$u->name='Admin';$u->shouldReceive('canAccess')->andReturn($allowed);
        $this->actingAs($u)->withoutMiddleware(AdminAccess::class);
    }
    public function test_secret_is_encrypted_never_echoed_and_configuration_updates_immediately(): void
    {
        $this->post('/authuser/settings/forms',['enabled'=>1,'site_key'=>'public-test-key','turnstile_secret'=>'private-test-secret'])->assertRedirect();
        $env=file_get_contents($this->path);$this->assertStringContainsString('MAIL_HOST=keep.example',$env);$this->assertStringNotContainsString('private-test-secret',$env);
        $values=\Dotenv\Dotenv::parse($env);$this->assertSame('private-test-secret',Crypt::decryptString($values['TURNSTILE_SECRET_ENCRYPTED']));
        $this->assertSame('', $values['TURNSTILE_SECRET_KEY']);$this->assertTrue(config('site.turnstile.enabled'));$this->assertSame('private-test-secret',config('site.turnstile.secret'));
        $this->get('/authuser/settings/forms')->assertOk()->assertDontSee('private-test-secret')->assertSee('Turnstile is enabled');
        $this->post('/authuser/settings/forms',['enabled'=>1,'site_key'=>'public-test-key','turnstile_secret'=>''])->assertRedirect();
        $this->assertSame('private-test-secret',config('site.turnstile.secret'));
        $this->post('/authuser/settings/forms',['enabled'=>1,'site_key'=>'different-public-key','turnstile_secret'=>''])->assertSessionHasErrors('turnstile_secret');
        config(['site.turnstile.secret'=>'stale-cached-value']);app(FormProtectionSettings::class)->apply();$this->assertSame('private-test-secret',config('site.turnstile.secret'));
    }
    public function test_cannot_enable_without_secret_and_invalid_secret_is_not_flashed(): void
    {
        $before=file_get_contents($this->path);
        $this->from('/authuser/settings/forms')->post('/authuser/settings/forms',['enabled'=>1,'site_key'=>'public-test-key','turnstile_secret'=>''])->assertSessionHasErrors('turnstile_secret');
        $this->assertSame($before,file_get_contents($this->path));
        $this->from('/authuser/settings/forms')->post('/authuser/settings/forms',['enabled'=>1,'site_key'=>'public-test-key','turnstile_secret'=>"private\ninvalid"])->assertSessionHasErrors('turnstile_secret')->assertSessionMissing('_old_input.turnstile_secret');
    }
    public function test_permissions_are_required_and_plain_env_configuration_is_supported(): void
    {
        $this->admin(false);$this->get('/authuser/settings/forms')->assertForbidden();
        $this->post('/authuser/settings/forms',['enabled'=>0,'site_key'=>'public-test-key'])->assertForbidden();
        file_put_contents($this->path,"TURNSTILE_ENABLED=true\nTURNSTILE_SITE_KEY=public-manual-key\nTURNSTILE_SECRET_KEY=manual-secret\n");
        app(FormProtectionSettings::class)->apply();$this->assertTrue(config('site.turnstile.enabled'));$this->assertSame('manual-secret',config('site.turnstile.secret'));
    }
}
