@extends('layout.default')
@section('head_meta')
<style>
    h1.ph1{
    font-size: 1.4rem;
    font-weight: 600;
    color: #0276a7;
    /* line-height: 15px; */
}
.nmeta{
    margin: 15px auto;
    padding-bottom: 5px;
    border-bottom: 1px solid #c7c7c7;
}
.cont{
    font-size: 0.95rem;
}
#sb1 .div-border:first-of-type{
    margin-top: 0 !important;
}
</style>
@endsection
@section('content')
        <!-- **Breadcrumbs** -->
        <div class="container">
        <div class="row">
            <div class="col-12">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb site-breadcrumb mt-3">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}" style="color:#000;"><i class="fas fa-home"></i></a></li>
                        <li class="breadcrumb-item active" aria-current="page">Industry News</li>
                        <li class="breadcrumb-item active" aria-current="page">{{ $news->headline }}</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <section class="mt-3 mb-3">
        <div class="container">
            <div class="row">
                <div class="col-12 col-md-9 col-lg-9" >
                    <h1 class="ph1">{{ $news->headline }}</h1>
                    <p class="nmeta">Published on: {{ site_date($news->created_at, 'd M Y') }}</p>
                    <div class="cont">
                        {!! $news->content !!}
                    </div>
                </div>
                <div class="col-12 col-md-3 col-lg-3" id="sb1">
                        @include('include.sidebar1')
                </div>
            </div>
        </div>
    </section>
@endsection
