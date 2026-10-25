@extends('layout.default')

@section('content')
    <!-- **Breadcrumbs** -->
    <div class="container">
        <div class="row">
            <div class="col-12">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb site-breadcrumb mt-3">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}" style="color:#000;"><i class="fas fa-home"></i></a></li>
                        <li class="breadcrumb-item active" aria-current="page">HTML Sitemap</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

<div class="container">
    <div class="row">
        <div class="col-lg-9 col-md-8 col-12">
            <h2>HTML Sitemap</h2>
            <ul>
                @php 
                foreach ($industries as $industry) { @endphp
                    <li><a href="{{ route('industry_list',$industry->slug) }}">{{ $industry->name }}</a></li>
                @php } @endphp
                
            </ul>
        </div>
    </div>
</div>
@endsection
