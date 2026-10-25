@extends('layout.default')
@section('head_meta')
<style>.auth{max-width:440px;margin:55px auto;padding:36px;background:white;border:1px solid #dce4f0;border-radius:6px;box-shadow:0 10px 40px #182b4810}.auth label{display:block;margin:12px 0 5px}.auth .btn-success{background:#142d82;border-color:#142d82}@media(max-width:600px){.auth{margin:25px 20px;padding:24px}}</style>
@endsection
@section('content')
<main class="auth"><a href="{{ route('home') }}"><img src="/frontend/images/eternity_logo.png" width="220" alt="Eternity Insights"></a><h1 class="h3 mt-4">{{ $title }}</h1>
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
<form method="post" action="{{ ['login'=>route('login'),'register'=>route('register.process'),'forgot'=>route('forgot-password'),'reset'=>route('update_pasword')][$mode] }}">@csrf
@if($mode==='register')<label>Name</label><input class="form-control" name="name" value="{{ old('name') }}" required>@endif
@if(in_array($mode,['login','register']))<label>{{ $mode==='login'?'Username or email':'Username' }}</label><input class="form-control" name="username" value="{{ old('username') }}" required>@endif
@if($mode!=='login')<label>Email</label><input class="form-control" type="email" name="email" value="{{ old('email',$email??'') }}" required>@endif
@if($mode!=='forgot')<label>Password</label><input class="form-control" name="password" type="password" required autocomplete="{{ $mode==='login'?'current-password':'new-password' }}">@endif
@if(in_array($mode,['register','reset']))<label>Confirm password</label><input class="form-control" name="password_confirmation" type="password" required>@endif
@if($mode==='reset')<input type="hidden" name="token" value="{{ $token }}">@endif
<button class="btn btn-success w-100 my-4">{{ $title }}</button></form>
@if($mode==='login')<p><a href="{{ route('forgot-password') }}">Forgot password?</a></p><p><a href="{{ route('register') }}">Create an account</a></p>@if(config('site.google.client_id'))<a href="{{ route('google.login') }}">Sign in with Google</a>@endif @else<a href="{{ route('login') }}">Back to login</a>@endif</main>
@endsection
