<?php

namespace App\Http\Controllers;

use App\Services\EmailSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

class EmailSettingsController extends Controller
{
    private function authorizeSettings(Request $request): void
    {
        abort_unless($request->user()->canAccess('manage_access'), 403);
    }

    public function edit(Request $request, EmailSettings $settings)
    {
        $this->authorizeSettings($request);
        return view('admin.email-settings', ['settings' => $settings->values()]);
    }

    public function update(Request $request, EmailSettings $settings)
    {
        $this->authorizeSettings($request);
        $data = $request->validate([
            'mailer' => ['required', Rule::in(['smtp', 'hosting_relay', 'sendmail', 'log'])],
            'host' => ['nullable', 'required_if:mailer,smtp', 'string', 'max:253', 'regex:/\A[a-zA-Z0-9.\-:]+\z/'],
            'port' => ['nullable', 'required_if:mailer,smtp', 'integer', 'between:1,65535'],
            'security' => ['required', Rule::in(['tls', 'ssl', 'none'])],
            'username' => ['nullable', 'string', 'max:255', 'not_regex:/[\r\n]/'],
            'smtp_password' => ['nullable', 'string', 'max:1024', 'not_regex:/[\r\n]/'],
            'clear_password' => ['nullable', 'boolean'],
            'from_address' => ['required', 'email:rfc', 'max:254'],
            'from_name' => ['required', 'string', 'max:150', 'not_regex:/[\r\n]/'],
            'admin_address' => ['required', 'email:rfc', 'max:254'],
            'notifications' => ['nullable', 'boolean'],
        ]);
        if ($data['mailer'] === 'hosting_relay') {
            $domain = preg_replace('/^www\./i', '', (string) parse_url(config('app.url'), PHP_URL_HOST));
            if (strcasecmp(substr(strrchr($data['from_address'], '@'), 1), $domain) !== 0) {
                return back()->withErrors(['from_address' => 'The GoDaddy relay requires a sender email on your website domain.']);
            }
        }
        try {
            $settings->save($data);
            Mail::purge();
        } catch (\Throwable $e) {
            // Never expose environment contents or credentials in an error response.
            return back()->withErrors(['settings' => 'Email settings could not be saved. Check that the application can write its .env file.']);
        }
        return back()->with('success', 'Email settings saved. New emails will use these settings immediately.');
    }

    private function deliveryError(\Throwable $exception): string
    {
        $message = strtolower($exception->getMessage());
        if (str_contains($message, 'timed out') || str_contains($message, 'connection refused')) {
            return 'Cannot connect to the mail server. Your hosting may block this SMTP port. On GoDaddy cPanel, use the GoDaddy hosting relay option and authorize it in your domain SPF record.';
        }
        if (str_contains($message, 'authenticate') || str_contains($message, '535')) {
            return 'The mail server rejected the login. Check the SMTP username and app password.';
        }
        return 'Delivery failed. Check the connection, sender address and domain email authentication with your provider.';
    }

    public function connection(Request $request)
    {
        $this->authorizeSettings($request);
        if (!in_array(config('mail.default'), ['smtp', 'hosting_relay'], true)) {
            return back()->withErrors(['connection' => 'Save SMTP or GoDaddy relay as the delivery method before checking the connection.']);
        }
        try {
            $transport = Mail::mailer()->getSymfonyTransport();
            $transport->start();
            $transport->stop();
        } catch (\Throwable $exception) {
            \Illuminate\Support\Facades\Log::warning('Admin mail connection failed', ['mailer' => config('mail.default'), 'reason' => $this->deliveryError($exception)]);
            return back()->withErrors(['connection' => $this->deliveryError($exception)]);
        }
        return back()->with('success', 'Mail connection succeeded. This checks the saved server connection and login; no email was sent. Inbox delivery also requires valid domain SPF/DKIM settings.');
    }

    public function test(Request $request)
    {
        $this->authorizeSettings($request);
        $data = $request->validate(['test_recipient' => ['required', 'email:rfc', 'max:254']]);
        if (!in_array(config('mail.default'), ['smtp', 'hosting_relay', 'sendmail'], true)) {
            return back()->withErrors(['test_recipient' => 'Save SMTP, GoDaddy relay or server mail settings before sending a test email.']);
        }
        try {
            $sent = Mail::raw('This is a test email from your Eternity Insights admin panel. Your saved email settings were used to send it.', function ($message) use ($data) {
                $message->to($data['test_recipient'])->subject('Eternity Insights email settings test');
            });
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Admin test email failed', ['mailer' => config('mail.default'), 'reason' => $this->deliveryError($e)]);
            return back()->withErrors(['test_recipient' => $this->deliveryError($e)]);
        }
        \Illuminate\Support\Facades\Log::info('Admin test email accepted by transport', ['mailer' => config('mail.default'), 'message_id' => $sent?->getMessageId()]);
        return back()->with('success', 'The mail server accepted your test email. Check the recipient inbox and spam folder.');
    }
}
