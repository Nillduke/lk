@extends('admin.layout')
@section('title','Form protection')
@section('content')
<h1 class="h3 mb-3">Form protection</h1>
<div class="panel"><h2 class="h5">Enquiry spam filters</h2><p class="mb-0">Link blocking, phone-number validation and hidden spam-field checks are always active on Contact Us, sample, discount, customization, buying enquiry, table-of-contents and search enquiry forms. Contact Us also requires a message of at least 10 characters.</p></div>
<div class="panel">
<h2 class="h5">Cloudflare Turnstile</h2>
@if($settings['enabled']&&$settings['has_secret'])<div class="alert alert-success">Turnstile is enabled on enquiry forms.</div>
@else<div class="alert alert-warning">Turnstile is not active. Enter the matching secret key and enable verification below. The public site key alone cannot verify submissions.</div>@endif
<form action="{{ route('admin.forms.update') }}" method="post">@csrf
<div class="row"><div class="col-lg-8">
<label for="site_key">Site key</label><input id="site_key" class="form-control" name="site_key" value="{{ old('site_key',$settings['site_key']) }}" maxlength="255" required autocomplete="off">
<label for="turnstile_secret">Secret key</label><input id="turnstile_secret" type="password" class="form-control" name="turnstile_secret" maxlength="255" autocomplete="new-password" placeholder="{{ $settings['has_secret']?'Saved securely - leave blank to keep it':'Paste the matching secret key from Cloudflare' }}">
<p class="small text-muted">Find this in Cloudflare Dashboard &gt; Turnstile &gt; your widget &gt; Settings. Use the secret for the site key above and allow eternityinsights.com and www.eternityinsights.com in the widget's hostname settings. The saved secret is encrypted in the existing .env file and is never displayed.</p>
<input type="hidden" name="enabled" value="0"><label class="d-block mb-3"><input type="checkbox" name="enabled" value="1" @checked(old('enabled',$settings['enabled']))> Enable Turnstile verification on enquiry forms</label>
<button class="btn btn-primary">Save form protection</button>
</div></div></form>
</div>
@endsection
