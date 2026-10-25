<?php

namespace Tests\Feature;

use App\Http\Middleware\AdminAccess;
use App\Models\User;
use App\Services\EmailSettings;
use Dotenv\Dotenv;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;
use Mockery;
use Tests\TestCase;

class EmailSettingsTest extends TestCase
{
    private string $envFile;
    private EmailSettings $settings;

    protected function setUp(): void
    {
        parent::setUp();
        $this->envFile = tempnam(storage_path('framework'), 'smtp-test-');
        file_put_contents($this->envFile, "APP_NAME=Untouched\nDB_PASSWORD=untouched-secret\nMAIL_PASSWORD=legacy-secret\n");
        $this->settings = new EmailSettings($this->envFile);
        $this->app->instance(EmailSettings::class, $this->settings);
        $this->withoutMiddleware(AdminAccess::class);
    }

    protected function tearDown(): void
    {
        if (isset($this->envFile)) @unlink($this->envFile);
        parent::tearDown();
    }

    private function admin(bool $allowed = true): void
    {
        $user = Mockery::mock(User::class)->makePartial();
        $user->id = 123;
        $user->name = 'Settings administrator';
        $user->shouldReceive('canAccess')->andReturn($allowed);
        $this->actingAs($user);
    }

    private function data(): array
    {
        return ['mailer'=>'smtp', 'host'=>'smtp.example.com', 'port'=>587, 'security'=>'tls',
            'username'=>'sender@example.com', 'smtp_password'=>' secret $value "quoted" \\ ',
            'from_address'=>'sender@example.com', 'from_name'=>'Research $ team "name"',
            'admin_address'=>'sales@example.com', 'notifications'=>1];
    }

    public function test_settings_require_login_and_access_permission(): void
    {
        $this->get(route('admin.email.edit'))->assertRedirect(route('login'));
        $this->admin(false);
        $this->get(route('admin.email.edit'))->assertForbidden();
        $this->post(route('admin.email.update'), $this->data())->assertForbidden();
        $this->post(route('admin.email.test'), ['test_recipient'=>'test@example.com'])->assertForbidden();
        $this->post(route('admin.email.connection'))->assertForbidden();
    }

    public function test_page_has_masked_empty_password_and_test_form(): void
    {
        $this->admin();
        config(['mail.mailers.smtp.password'=>'never-render-this']);
        $this->get(route('admin.email.edit'))->assertOk()->assertSee('SMTP connection')->assertSee('Send a test email')->assertDontSee('never-render-this');
    }

    public function test_save_encrypts_password_preserves_other_connections_and_overrides_cached_config(): void
    {
        $this->admin();
        $data = $this->data();
        $this->post(route('admin.email.update'), $data)->assertSessionHasNoErrors()->assertSessionHas('success');
        $env = Dotenv::parse(file_get_contents($this->envFile));
        $this->assertSame('untouched-secret', $env['DB_PASSWORD']);
        $this->assertSame('', $env['MAIL_PASSWORD']);
        $this->assertSame($data['smtp_password'], Crypt::decryptString($env['MAIL_PASSWORD_ENCRYPTED']));
        $this->assertSame($data['from_name'], config('mail.from.name'));
        config(['mail.mailers.smtp.host'=>'stale-cache', 'mail.default'=>'log']);
        $this->settings->apply();
        $this->assertSame('smtp.example.com', config('mail.mailers.smtp.host'));
        $this->assertSame('smtp', config('mail.default'));
        $this->assertTrue(config('mail.mailers.smtp.require_tls'));
    }

    public function test_blank_password_preserves_legacy_password_and_clear_removes_it(): void
    {
        $data = $this->data(); $data['smtp_password'] = '';
        $this->settings->save($data);
        $this->assertSame('legacy-secret', config('mail.mailers.smtp.password'));
        $data['clear_password'] = 1; $data['security'] = 'none'; unset($data['notifications']);
        $this->settings->save($data);
        $this->assertNull(config('mail.mailers.smtp.password'));
        $this->assertFalse(config('site.mail_notifications'));
        $this->assertFalse(config('mail.mailers.smtp.auto_tls'));
        $data['security'] = 'ssl';
        $this->settings->save($data);
        $this->assertSame('smtps', config('mail.mailers.smtp.scheme'));
    }

    public function test_invalid_settings_do_not_write_or_flash_password(): void
    {
        $this->admin();
        $before = file_get_contents($this->envFile);
        $data = $this->data(); $data['port'] = 70000;
        $this->from(route('admin.email.edit'))->post(route('admin.email.update'), $data)->assertSessionHasErrors('port');
        $this->assertSame($before, file_get_contents($this->envFile));
        $this->assertNull(session('_old_input.smtp_password'));
    }

    public function test_test_email_uses_saved_delivery_even_if_notifications_are_off(): void
    {
        $this->admin(); Mail::fake();
        config(['mail.default'=>'smtp', 'site.mail_notifications'=>false]);
        Mail::shouldReceive('raw')->once()->withArgs(function ($body, $callback) {
            $message = Mockery::mock(\Illuminate\Mail\Message::class);
            $message->shouldReceive('to')->once()->with('test@example.com')->andReturnSelf();
            $message->shouldReceive('subject')->once()->with('Eternity Insights email settings test')->andReturnSelf();
            $callback($message);
            return str_contains($body, 'test email');
        });
        $this->post(route('admin.email.test'), ['test_recipient'=>'test@example.com'])->assertSessionHasNoErrors()->assertSessionHas('success');
    }

    public function test_transport_failure_shows_no_credentials(): void
    {
        $this->admin(); config(['mail.default'=>'smtp']);
        Mail::shouldReceive('raw')->once()->andThrow(new \RuntimeException('sensitive-password transport debug'));
        $this->post(route('admin.email.test'), ['test_recipient'=>'test@example.com'])->assertSessionHasErrors('test_recipient');
        $this->assertStringNotContainsString('sensitive-password', json_encode(session()->all()));
    }

    public function test_hosting_relay_preserves_credentials_but_never_uses_them_for_local_delivery(): void
    {
        $this->admin();
        $data = $this->data(); $data['mailer'] = 'hosting_relay';
        $domain = preg_replace('/^www\./', '', parse_url(config('app.url'), PHP_URL_HOST));
        $data['from_address'] = 'sales@'.$domain;
        $this->post(route('admin.email.update'), $data)->assertSessionHasNoErrors();
        $this->assertSame('hosting_relay', config('mail.default'));
        $this->assertNull(config('mail.mailers.hosting_relay.password'));
        $this->assertNull(config('mail.mailers.hosting_relay.username'));
        $this->assertSame('127.0.0.1', config('mail.mailers.hosting_relay.host'));
        $this->assertSame($data['smtp_password'], config('mail.mailers.smtp.password'));
    }

    public function test_hosting_relay_rejects_sender_on_an_unrelated_domain(): void
    {
        $this->admin(); $data = $this->data(); $data['mailer'] = 'hosting_relay';
        $this->post(route('admin.email.update'), $data)->assertSessionHasErrors('from_address');
    }

    public function test_connection_timeout_is_explained_without_exposing_transport_details(): void
    {
        $this->admin(); config(['mail.default'=>'smtp']);
        Mail::shouldReceive('mailer')->once()->andThrow(new \RuntimeException('Connection timed out: private-credential'));
        $this->post(route('admin.email.connection'))->assertSessionHasErrors('connection');
        $this->assertStringContainsString('hosting may block', session('errors')->first('connection'));
        $this->assertStringNotContainsString('private-credential', session('errors')->first('connection'));
    }

    public function test_connection_check_starts_and_stops_transport_without_sending(): void
    {
        $this->admin(); config(['mail.default'=>'hosting_relay']);
        $transport = Mockery::mock(\Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport::class);
        $transport->shouldReceive('start')->once();
        $transport->shouldReceive('stop')->once();
        $mailer = Mockery::mock(\Illuminate\Mail\Mailer::class);
        $mailer->shouldReceive('getSymfonyTransport')->once()->andReturn($transport);
        Mail::shouldReceive('mailer')->once()->andReturn($mailer);
        Mail::shouldReceive('raw')->never();
        $this->post(route('admin.email.connection'))->assertSessionHasNoErrors()->assertSessionHas('success');
    }

    public function test_log_transport_cannot_claim_test_delivery(): void
    {
        $this->admin(); config(['mail.default'=>'log']);
        Mail::shouldReceive('raw')->never();
        $this->post(route('admin.email.test'), ['test_recipient'=>'test@example.com'])->assertSessionHasErrors('test_recipient');
    }
}
