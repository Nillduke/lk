@extends('layout.default')
@section('head_meta')<meta name="robots" content="noindex,nofollow">@endsection
@section('content')<div class="container py-5"><h1>Continue to secure payment</h1><p>Order <strong>{{ $order->order_number }}</strong> | {{ $order->currency }} {{ number_format((float)$order->amt_tobe_paid,2) }}</p><p>You will be redirected to CCAvenue to complete your payment.</p><form method="post" action="{{ $action }}" id="ccavenue-payment"><input type="hidden" name="encRequest" value="{{ $encRequest }}"><input type="hidden" name="access_code" value="{{ $accessCode }}"><button type="submit" class="btn btn-primary">Continue to CCAvenue</button></form></div>@endsection
@section('footer_script')<script>document.getElementById('ccavenue-payment').submit();</script>@endsection
