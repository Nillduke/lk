@extends('layout.default')
@section('content')
<section class="interior-page-heading"><div class="container"><nav aria-label="breadcrumb"><ol class="breadcrumb site-breadcrumb"><li class="breadcrumb-item"><a href="{{ url('/') }}" aria-label="Home"><i class="fas fa-home"></i></a></li><li class="breadcrumb-item active">Industry News</li></ol></nav><h1>Industry News</h1></div></section>
<section class="container news-page-layout"><main><div class="interior-pagination">{!! $pager !!}</div><div class="news-editorial-grid">
@forelse($list as $news)
<article class="news-editorial-card"><a href="{{ route('news_details',$news->slug) }}" class="news-editorial-media"><img src="{{ news_thumbnail($news) }}" alt="{{ $news->headline }}" loading="{{ $loop->first ? 'eager' : 'lazy' }}" decoding="{{ $loop->first ? 'sync' : 'async' }}" @if($loop->first)fetchpriority="high"@endif onerror="this.onerror=null;this.parentElement.classList.add('media-fallback');this.src='/frontend/images/eternity_logo.png';"></a><div class="news-editorial-body"><span class="news-date">{{ site_date($news->created_at,'M Y') }}</span><h2><a href="{{ route('news_details',$news->slug) }}">{{ $news->headline }}</a></h2><p>{{ \Illuminate\Support\Str::limit(strip_tags($news->content),300) }}</p><span class="news-category">{{ $news->category_name }}</span><a class="news-read-link" href="{{ route('news_details',$news->slug) }}">View Details <span aria-hidden="true">&rarr;</span></a></div></article>
@empty <h3>No Industry News Exists.</h3> @endforelse
</div><div class="interior-pagination">{!! $pager !!}</div></main><aside>@include('include.sidebar1')</aside></section>
@endsection
