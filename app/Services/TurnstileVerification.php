<?php
namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class TurnstileVerification
{
    public function verify(Request $request, string $action): void
    {
        if (!config('site.turnstile.enabled')) return;
        $data = $request->validate(['cf-turnstile-response'=>'required|string|max:2048'], ['cf-turnstile-response.required'=>'Please complete the security verification.']);
        $secret = (string)config('site.turnstile.secret');
        if ($secret === '') throw ValidationException::withMessages(['cf-turnstile-response'=>'Security verification is temporarily unavailable. Please try again later.']);
        try {
            $response = Http::asForm()->connectTimeout(5)->timeout(10)->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
                'secret'=>$secret,'response'=>$data['cf-turnstile-response'],
            ]);
            $result = $response->json();
        } catch (\Throwable $exception) {
            // Never log the token, secret, request body or visitor details.
            Log::warning('Turnstile verification unavailable');
            throw ValidationException::withMessages(['cf-turnstile-response'=>'Security verification could not be reached. Please refresh and try again.']);
        }
        $host = strtolower((string)parse_url(config('app.url'), PHP_URL_HOST));
        $bare = preg_replace('/^www\./', '', $host);
        $allowed = array_unique([$host, $bare, 'www.'.$bare]);
        if (!$response->successful() || !is_array($result) || ($result['success'] ?? false) !== true
            || !in_array(strtolower((string)($result['hostname'] ?? '')), $allowed, true)
            || ($result['action'] ?? '') !== $action) {
            throw ValidationException::withMessages(['cf-turnstile-response'=>'Security verification failed or expired. Please refresh and try again.']);
        }
    }
}
