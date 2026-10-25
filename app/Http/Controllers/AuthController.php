<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        if ($request->isMethod('get')) {
            return view('auth.form', ['mode' => 'login', 'title' => 'Admin login']);
        }
        $data = $request->validate(['username' => 'required|string', 'password' => 'required|string']);
        $field = filter_var($data['username'], FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        if (! Auth::attempt([$field => $data['username'], 'password' => $data['password'], 'status' => 1])) {
            return back()->withErrors(['username' => 'The username or password is incorrect, or the account is inactive.'])->onlyInput('username');
        }
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    public function register(Request $request)
    {
        if ($request->isMethod('get')) {
            return view('auth.form', ['mode' => 'register', 'title' => 'Create an account']);
        }
        $data = $request->validate(['name' => 'required|string|max:100', 'username' => 'required|string|max:100|unique:users,username', 'email' => 'required|email|max:50|unique:users,email', 'password' => 'required|string|min:10|confirmed']);
        $data += ['uuid' => (string) Str::uuid(), 'status' => 0, 'is_email_verified' => 0];
        $user = User::create($data);
        $this->verification($user);

        return redirect()->route('login')->with('success', 'Account created. Check your email for the verification link.');
    }

    private function verification($user)
    {
        $token = Str::random(64);
        DB::table('users')->where('id', $user->id)->update(['verification_code' => hash('sha256', $token), 'verification_expiry_dt' => now()->addDay()]);
        SiteController::notify($user->email, 'Verify your email', route('emailverify', $token));
    }

    public function resend(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        $user = User::where('email', $request->email)->where('is_email_verified', 0)->first();
        if ($user) {
            $this->verification($user);
        }

return back()->with('success', 'If the account needs verification, an email has been sent.');
    }

    public function verify($token)
    {
        $user = User::where('verification_code', hash('sha256', $token))->where('verification_expiry_dt', '>', now())->first();
        abort_unless($user, 422, 'Verification link is invalid or expired.');
        $user->forceFill(['status' => 1, 'is_email_verified' => 1, 'verification_code' => null, 'verification_expiry_dt' => null])->save();

        return redirect()->route('login')->with('success', 'Email verified. You can sign in.');
    }

    public function forgot(Request $request)
    {
        if ($request->isMethod('get')) {
            return view('auth.form', ['mode' => 'forgot', 'title' => 'Reset password']);
        }
        $request->validate(['email' => 'required|email']);
        $user = User::where('email', $request->email)->first();
        if ($user) {
            $token = Str::random(64);
            DB::table('lr_password_reset_tokens')->updateOrInsert(['email' => $user->email], ['token' => Hash::make($token), 'created_at' => now()]);
            SiteController::notify($user->email, 'Reset your password', route('resetpassword', $token).'?email='.urlencode($user->email));
        }

        return back()->with('success', 'If the account exists, a password reset link has been sent.');
    }

    public function reset(Request $request, $token = null)
    {
        if ($request->isMethod('get')) {
            return view('auth.form', ['mode' => 'reset', 'title' => 'Choose a new password', 'token' => $token, 'email' => $request->email]);
        }
        $data = $request->validate(['email' => 'required|email', 'token' => 'required|string', 'password' => 'required|string|min:10|confirmed']);
        $row = DB::table('lr_password_reset_tokens')->where('email', $data['email'])->first();
        abort_unless($row && Carbon::parse($row->created_at)->gt(now()->subHour()) && Hash::check($data['token'], $row->token), 422, 'Reset link is invalid or expired.');
        DB::transaction(function () use ($data) {
            User::where('email', $data['email'])->update(['password' => Hash::make($data['password'])]);
            DB::table('lr_password_reset_tokens')->where('email', $data['email'])->delete();
        });

        return redirect()->route('login')->with('success', 'Password changed.');
    }

    public function google(Request $request)
    {
        abort_unless(config('site.google.client_id') && config('site.google.client_secret'), 503, 'Configure Google OAuth in .env first.');
        $state = Str::random(40);
        session(['google_state' => $state]);

        return redirect()->away('https://accounts.google.com/o/oauth2/v2/auth?'.http_build_query(['client_id' => config('site.google.client_id'), 'redirect_uri' => config('site.google.redirect'), 'response_type' => 'code', 'scope' => 'openid email profile', 'state' => $state]));
    }

    public function googleCallback(Request $request)
    {
        abort_unless($request->state && hash_equals((string) session()->pull('google_state'), (string) $request->state), 422, 'OAuth state mismatch');
        $res = Http::asForm()->timeout(20)->post('https://oauth2.googleapis.com/token', ['client_id' => config('site.google.client_id'), 'client_secret' => config('site.google.client_secret'), 'redirect_uri' => config('site.google.redirect'), 'grant_type' => 'authorization_code', 'code' => $request->code])->throw();
        $info = Http::withToken($res->json('access_token'))->timeout(20)->get('https://openidconnect.googleapis.com/v1/userinfo')->throw()->json();
        abort_unless($info['email_verified'] ?? false, 403);
        $user = User::firstOrCreate(['email' => $info['email']], ['uuid' => (string) Str::uuid(), 'name' => $info['name'] ?? $info['email'], 'username' => 'google_'.Str::random(12), 'password' => Str::random(64), 'is_email_verified' => 1, 'status' => 1]);
        abort_unless($user->status,403);
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }
}
