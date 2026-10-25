@extends('admin.layout')
@section('title','Dashboard')
@section('content')<h1 class="h3 mb-4">Dashboard</h1><div class="row">@foreach($counts as $label=>$count)<div class="col-md-4"><div class="panel"><p class="text-muted">{{ $label }}</p><strong class="display-6">{{ number_format($count) }}</strong></div></div>@endforeach</div><div class="panel"><h2 class="h5">Catalogue administration</h2><p>Manage reports, content, orders, enquiries, users and permissions from the navigation.</p></div>@endsection
