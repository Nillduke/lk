@extends('layout.default')
@section('content')
<div class="catalogue-page catalogue-industry">
<section class="catalogue-hero catalogue-category-hero" style="--category-image:url('/frontend/images/industry-banner/{{ $industry->slug }}.jpg')"><div class="container"><nav aria-label="breadcrumb"><ol class="breadcrumb site-breadcrumb"><li class="breadcrumb-item"><a href="{{ url('/') }}" aria-label="Home"><i class="fas fa-home"></i></a></li><li class="breadcrumb-item active" aria-current="page">{{ $industry->name }} Reports</li></ol></nav><h1>{{ $industry->name }} Reports</h1>@include('include.catalogue-search')
</div></section>
<section class="catalogue-section"><div class="container catalogue-layout">
<aside class="catalogue-sidebar" aria-label="Industry navigation and assistance"><div class="catalogue-sidebar-panel"><button type="button" class="catalogue-filter-toggle" aria-expanded="false" aria-controls="catalogue-filter-body">Browse categories <span aria-hidden="true">+</span></button><div class="catalogue-sidebar-body" id="catalogue-filter-body">@include('include.assistance')

<div class="catalogue-categories"><h3>Key Sectors</h3><nav aria-label="Report categories"><a href="{{ route('all_reports') }}">All Reports</a>@foreach($categories as $category)<a href="{{ route('industry_list', $category->slug) }}" @if($category->id == $industry->id) aria-current="page" @endif>{{ $category->name }}</a>@endforeach</nav></div>
@include('include.research-benefits')
</div></div></aside>
<main class="catalogue-results" aria-label="Industry report results">@include('include.catalogue-toolbar')
<div class="catalogue-report-grid">
@forelse($list as $report)
@include('include.catalogue-report')
@empty
<h3 class="catalogue-empty">No Reports found for applied filter.</h3>
@endforelse
</div>
<div class="catalogue-pagination catalogue-pagination-bottom">{!! $pager !!}</div></main>
</div></section></div>
@endsection
