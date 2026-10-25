@extends('layout.default')
@section('content')
<div class="container py-5"><h1>{{ $title }}</h1><p>{{ $message }}</p>@if(session('order_number'))<p>Order: <strong>{{ session('order_number') }}</strong></p>@endif<a href="{{ route('home') }}">Return to website</a></div>
@endsection
