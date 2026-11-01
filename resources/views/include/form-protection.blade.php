<div class="enquiry-form-protection">
    <div aria-hidden="true" style="position:absolute;left:-10000px;width:1px;height:1px;overflow:hidden;">
        <label>Leave this field empty<input type="text" name="website_url" tabindex="-1" autocomplete="off"></label>
    </div>
    <p class="small text-muted mt-3">Please do not include links or website addresses in your enquiry.</p>
    @if(config('site.turnstile.enabled'))
        <div class="cf-turnstile my-3" data-sitekey="{{ config('site.turnstile.site_key') }}" data-action="{{ $formAction }}" data-theme="light" data-size="flexible" data-refresh-expired="auto" style="min-height:65px;max-width:100%;"></div>
        @error('cf-turnstile-response')<p class="text-danger small" role="alert">{{ $message }}</p>@enderror
    @endif
    <p class="text-danger small enquiry-security-error" role="alert" tabindex="-1" hidden></p>
</div>
@once
@push('form_security_scripts')
<script src="{{ asset('frontend/js/enquiry-protection.js') }}?v=20261101-1" defer></script>
@if(config('site.turnstile.enabled'))<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" defer></script>@endif
@endpush
@endonce
