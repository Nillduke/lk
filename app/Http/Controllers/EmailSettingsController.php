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
            'mailer' => ['required', Rule::in(['smtp', 'sendmail', 'log'])],
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
        try {
            $settings->save($data);
            Mail::purge();
        } catch (\Throwable $e) {
            // Never expose environment contents or credentials in an error response.
            return back()->withErrors(['settings' => 'Email settings could not be saved. Check that the application can write its .env file.']);
        }
        return back()->with('success', 'Email settings saved. New emails will use these settings immediately.');
    }

    public function test(Request $request)
    {
        $this->authorizeSettings($request);
        $data = $request->validate(['test_recipient' => ['required', 'email:rfc', 'max:254']]);
        if (!in_array(config('mail.default'), ['smtp', 'sendmail'], true)) {
            return back()->withErrors(['test_recipient' => 'Save SMTP or server mail settings before sending a test email.']);
        }
        try {
            Mail::raw('This is a test email from your Eternity Insights admin panel. Your saved email settings were used to send it.', function ($message) use ($data) {
                $message->to($data['test_recipient'])->subject('Eternity Insights email settings test');
            });
        } catch (\Throwable $e) {
            return back()->withErrors(['test_recipient' => 'Delivery failed. Check the SMTP host, port, security, login and sender address with your email provider.']);
        }
        return back()->with('success', 'The mail server accepted your test email. Check the recipient inbox and spam folder.');
    }
}
