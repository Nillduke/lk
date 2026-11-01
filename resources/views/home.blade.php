@extends('layout.default')
@section('head_meta')


<link rel="canonical" href="{{ url('/') }}" />
<link rel="amphtml" href="{{ url(route('home_amp')) }}" />
<meta name="msvalidate.01" content="28A3CA7354B2B1F914535B18315E31FC" />
<meta name="apple-mobile-web-app-capable" content="yes" />
<!-- twitter cards here -->
<meta name="author" content="Eternity Insights, https://www.eternityinsights.com" />
<meta name="copyright" content="Eternity Insights, https://www.eternityinsights.com, All right reserved." />
<meta name="classification" content="Custom Market Research" />
<meta name="distribution" content="Global" />
<meta name="language" content="EN" />
<meta name="coverage" content="worldwide">
<meta name="rating" content="general">
<meta name="document-classification" content="Market Research Services" />
<meta name="document-type" content="Public" />
<meta name="Page-Topic" content="Market Research" />
<script type="application/ld+json">
{ 
    "@@context" : "https://schema.org",
    "@@type" : "Organization",
    "name" : "Eternity Insights",
    "url" : "https://www.eternityinsights.com/",
    "logo": "{{ url("/") }}/frontend/images/eternity_logo.png",
    "sameAs" : [ "https://www.facebook.com/eternityinsight",
    "https://www.twitter.com/Eternity_MI",
    "https://www.linkedin.com/company/eternity-insights",
    "http://www.eternity-insights.blogspot.com/"
    ],
    "contactPoint": [{
        "@@type": "ContactPoint",
        "telephone": "+91 -930-705-6852",
        "contactType": "Customer Service"
    }]
}
</script>


@endsection
@section('content')
@php
$homeCopy=require resource_path('data/home.php');
$homeGroups=[
 ['id'=>'all','label'=>'All industries','slug'=>null,'reports'=>array_slice($reports,0,6)],
 ['id'=>'healthcare','label'=>'Pharmaceuticals & Healthcare','slug'=>'healthcare','reports'=>$reports_one],
 ['id'=>'technology','label'=>'Information & Technology','slug'=>'information-communication-technology','reports'=>$reports_two],
 ['id'=>'energy','label'=>'Energy, Power & Utilities','slug'=>'energy-power-utilities','reports'=>$reports_three],
 ['id'=>'manufacturing','label'=>'Manufacturing and Construction','slug'=>'manufacturing-construction','reports'=>$reports_four],
 ['id'=>'aerospace','label'=>'Aerospace & Defense','slug'=>'aerospace-defense','reports'=>$reports_five],
 ['id'=>'chemicals','label'=>'Chemicals & Advanced Materials','slug'=>'chemicals-materials','reports'=>$reports_six],
];
$tabLabels=['All industries','Healthcare','Technology','Energy & Utilities','Manufacturing','Aerospace & Defense','Chemicals & Materials'];
foreach($homeGroups as $i=>&$group){$group['tab']=$tabLabels[$i];if($i>0 && !empty($group['reports']))$group['slug']=$group['reports'][0]->category_slug??$group['slug'];}
unset($group);
@endphp
<main class="ei-home">
<section class="ei-hero" aria-labelledby="home-heading">
<div class="container ei-hero-grid">
<div class="ei-hero-copy">
<p class="ei-eyebrow"><span></span>Market research &amp; consulting</p>
<h1 id="home-heading">End-less <em>INSIGHTS</em><span>for your business</span></h1>
<p class="ei-hero-intro">{{ $homeCopy['hero'][0] }}</p>
<form action="{{ route('search') }}" method="get" class="ei-home-search" role="search" data-report-search data-autocomplete-url="{{ route('search_auto') }}">
<label class="visually-hidden" for="home-report-search">Search by keyword, industry</label>
<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><circle cx="10.8" cy="10.8" r="7.2"/><path d="m16 16 5 5"/></svg>
<input id="home-report-search" maxlength="120" type="search" name="s" placeholder="Search by keyword, industry" autocomplete="off">
<button type="submit">Search <span aria-hidden="true">&rarr;</span></button>
</form>
<div class="ei-hero-links"><a href="{{ route('all_reports') }}">Explore reports <span aria-hidden="true">&rarr;</span></a><a href="{{ route('aboutus') }}">About Eternity Insights <span aria-hidden="true">&rarr;</span></a></div>
<div class="ei-industry-shortcuts" aria-label="Explore industries">
@foreach(array_slice($homeGroups,1,3) as $group)<a href="{{ route('industry_list',$group['slug']) }}">{{ $group['label'] }}</a>@endforeach
</div>
</div>
<div class="ei-hero-visual">
<img src="{{ asset('frontend/images/about-us-banner.png') }}" width="2701" height="728" fetchpriority="high" loading="eager" alt="Business district and modern office buildings" class="ei-hero-photo">
<div class="ei-visual-caption"><span class="ei-caption-line"></span><p>Valuable Market <strong>RESEARCH</strong><br>that never ends</p><span>{{ $homeCopy['hero'][1] }}</span></div>
<span class="ei-visual-corner" aria-hidden="true"></span>
</div>
</div>
</section>
<section class="ei-clients" aria-label="Our clients"><div class="container"><div class="ei-clients-heading"><span>Our clients</span><span class="ei-fine-line"></span><span>Eternity Insights</span></div><div class="ei-logo-track" tabindex="0" aria-label="Client logos. Scroll to see more.">
@include('include.new-client-logos',['clientLogoGrid'=>true])
@foreach(['djoglobal.png'=>'djoglobal','Arclin.png'=>'Arclin','photonetc.png'=>'photonetc','Client-Dropbox.png'=>'Dropbox','Client-Evonik.png'=>'Evonik','Client-Honeywell.png'=>'Honeywell','Client-LG.png'=>'LG','Client-Philips.png'=>'Philips'] as $file=>$name)<div><img src="{{ asset('frontend/images/client-logo/'.$file) }}" alt="{{ $name }}" width="150" height="77" loading="lazy" decoding="async"></div>@endforeach
</div></div></section>
<section class="ei-section ei-reports" id="latest-reports" aria-labelledby="latest-reports-heading">
<div class="container"><div class="ei-section-heading"><div><p class="ei-eyebrow">Research library</p><h2 id="latest-reports-heading">Latest Reports</h2></div><a class="ei-text-link" href="{{ route('all_reports') }}">View all reports <span aria-hidden="true">&rarr;</span></a></div>
<div class="ei-report-tabs" role="tablist" aria-label="Report industries">
@foreach($homeGroups as $group)<button type="button" role="tab" id="home-tab-{{ $group['id'] }}" aria-controls="home-panel-{{ $group['id'] }}" aria-selected="{{ $loop->first?'true':'false' }}" tabindex="{{ $loop->first?'0':'-1' }}" data-home-tab="{{ $group['id'] }}">{{ $group['tab'] }}</button>@endforeach
</div>
@foreach($homeGroups as $group)
<div class="ei-report-panel" role="tabpanel" id="home-panel-{{ $group['id'] }}" aria-labelledby="home-tab-{{ $group['id'] }}" data-home-panel="{{ $group['id'] }}" @if(!$loop->first)hidden @endif>
<div class="ei-report-grid">@forelse($group['reports'] as $homeReport)@include('include.home-report-card',['homeReport'=>$homeReport])@empty<p class="ei-empty">New reports will appear here when published. <a href="{{ route('all_reports') }}">Browse the report library</a>.</p>@endforelse</div>
@if($group['slug'])<a class="ei-industry-more" href="{{ route('industry_list',$group['slug']) }}">Explore {{ $group['label'] }} reports <span aria-hidden="true">&rarr;</span></a>@endif
</div>
@endforeach
</div></section>
<section class="ei-section ei-services" aria-labelledby="services-heading"><div class="container">
<div class="ei-section-heading"><div><p class="ei-eyebrow">Why Choose Us?</p><h2 id="services-heading">Valuable market research<br>that never ends</h2></div><p class="ei-heading-note">{{ $homeCopy['hero'][1] }}</p></div>
<div class="ei-service-grid">@foreach($homeCopy['services'] as $service)<article class="ei-service-card"><span class="ei-service-number">0{{ $loop->iteration }}</span><svg viewBox="0 0 32 32" fill="none" stroke="currentColor" stroke-width="1.3" aria-hidden="true">@if($loop->index===0)<path d="M6 4h15l5 5v19H6zM20 4v7h7M11 16h10M11 21h10"/>@elseif($loop->index===1)<circle cx="14" cy="14" r="9"/><path d="m21 21 7 7M10 14h8M14 10v8"/>@else<path d="M4 27h25M7 23V13h5v10M15 23V8h5v15M23 23V4h5v19"/>@endif</svg><h3>{{ $service['title'] }}</h3><p>{{ $service['text'] }}</p><a href="{{ route('services') }}" aria-label="Learn more about {{ $service['title'] }}">Read more <span aria-hidden="true">&rarr;</span></a></article>@endforeach</div>
</div></section>
<section class="ei-section ei-about" aria-labelledby="about-heading"><div class="container ei-about-grid">
<div class="ei-about-visual"><img src="{{ asset('frontend/images/banner1.jpg') }}" width="1400" height="400" loading="lazy" alt="Business professionals discussing research"><div><h3>Clear Understanding<br>of Research Objectives</h3><p>{{ $homeCopy['hero'][2] }}</p></div></div>
<div class="ei-about-copy"><p class="ei-eyebrow">About us</p><h2 id="about-heading">Eternity Insights</h2><p>{{ $homeCopy['about'][0] }}</p><details class="ei-about-details"><summary>More about Eternity Insights <span aria-hidden="true">+</span></summary>@foreach(array_slice($homeCopy['about'],1) as $paragraph)<p>{{ $paragraph }}</p>@endforeach</details><a class="ei-button" href="{{ route('aboutus') }}">Read more about us <span aria-hidden="true">&rarr;</span></a></div>
</div></section>
<section class="ei-section ei-news" aria-labelledby="news-heading"><div class="container"><div class="ei-section-heading"><div><p class="ei-eyebrow">Industry insights</p><h2 id="news-heading">Industry News</h2></div><a class="ei-text-link" href="{{ route('news_list') }}">View all news <span aria-hidden="true">&rarr;</span></a></div>
<div class="ei-news-grid">@forelse($indnews as $news)<article class="ei-news-card"><a class="ei-news-media" href="{{ route('news_details',$news->slug) }}" tabindex="-1" aria-hidden="true"><span class="ei-news-placeholder" aria-hidden="true"><svg viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="1.1"><path d="M10 6h21l8 8v29H10zM30 6v10h10M17 24h15M17 30h15M17 36h10"/></svg><span>Eternity Insights</span></span><img data-home-news-image src="{{ news_thumbnail($news) }}" alt="" width="640" height="400" loading="lazy" decoding="async"></a><div class="ei-news-copy"><time datetime="{{ site_date($news->created_at,'Y-m-d') }}">{{ site_date($news->created_at,'M Y') }}</time><h3><a href="{{ route('news_details',$news->slug) }}">{{ $news->headline }}</a></h3><p>{{ \Illuminate\Support\Str::limit(html_entity_decode(strip_tags($news->content??''),ENT_QUOTES|ENT_HTML5,'UTF-8'),140) }}</p><a class="ei-text-link" href="{{ route('news_details',$news->slug) }}">Read more <span aria-hidden="true">&rarr;</span></a></div></article>@empty<p>Industry news will appear here when published.</p>@endforelse</div>
</div></section>
<section class="ei-section ei-testimonials" aria-labelledby="testimonials-heading"><div class="container ei-testimonial-grid"><div class="ei-testimonial-intro"><p class="ei-eyebrow">Client Testimonials</p><h2 id="testimonials-heading">Our happy<br>customers</h2><p>{{ $homeCopy['trust'] }}</p><div class="ei-stat-grid"><div><strong>100+</strong><span>Industry verticals served</span></div><div><strong>60+</strong><span>Country specific reports available</span></div><div><strong>10K+</strong><span>Premium reports database</span></div></div></div>
<div class="ei-testimonial-box" data-home-testimonials><span class="ei-quote-mark" aria-hidden="true">&ldquo;</span><div class="ei-testimonial-quotes" aria-live="polite">@foreach($homeCopy['testimonials'] as $testimonial)<figure data-home-quote @if(!$loop->first)hidden @endif><blockquote>{{ $testimonial['quote'] }}</blockquote><figcaption>{{ $testimonial['author'] }}</figcaption></figure>@endforeach</div><div class="ei-testimonial-controls"><span data-home-quote-count>01 / {{ str_pad((string)count($homeCopy['testimonials']),2,'0',STR_PAD_LEFT) }}</span><div><button type="button" data-home-quote-prev aria-label="Previous testimonial">&larr;</button><button type="button" data-home-quote-next aria-label="Next testimonial">&rarr;</button></div></div></div>
</div></section>
<section class="ei-contact-strip"><div class="container"><div><p class="ei-eyebrow">Research &amp; consulting</p><h2>Clear understanding of<br>your research objectives.</h2></div><a class="ei-button" href="{{ route('contactus') }}">Contact us <span aria-hidden="true">&rarr;</span></a></div></section>
</main>
@endsection
@section('footer_script')
<script src="{{ asset('frontend/js/home-refresh.js') }}?v=20261101-1" defer></script>
@endsection
