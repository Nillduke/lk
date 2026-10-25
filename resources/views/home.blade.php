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
    "@@context" : "http://schema.org",
    "@@type" : "Organization",
    "name" : "Eternity Insights",
    "url" : "http://www.eternityinsights.com/",
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
<style>
    .nwstitle{
        font-size: 0.9rem;
    font-weight: 600;
    }

</style>

@endsection
@section('content')

        <!--** Banner **-->
<div id="demo" class="carousel slide mobile-banner" data-bs-ride="carousel" data-ride="carousel">

<!-- Indicators/dots -->
<div class="carousel-indicators">
    <button type="button" data-bs-target="#demo" data-bs-slide-to="0" class="active"></button>
    <button type="button" data-bs-target="#demo" data-bs-slide-to="1"></button>
    <button type="button" data-bs-target="#demo" data-bs-slide-to="2"></button>
</div>

<!-- The slideshow/carousel -->
<div class="carousel-inner">
    <div class="carousel-item active">
        <img src="{{ url('frontend/images/banner-2.png') }}" fetchpriority="high" loading="eager" alt="Eternity Banner 1" class="d-block zoom"
            style="width:100%">
        <div class="carousel-caption wow fadeInLeft">
            <h1 class="banner-title">End-less <span class="highlight">INSIGHTS</span> </h1>
            <h2 class="banner-title2">for your business</h2>
            <h6 class="banner-title3">We ensure our research reports showcase high level of quality standards and professionalism by providing accurate and realistic quantitative models supported by proper reasonings and justifications.</h6>
            <a class="btn banner-btn-bg text-uppercase" href="{{ route('aboutus') }}">Read More ></a>
        </div>
    </div>
    <div class="carousel-item">
        <img src="{{ url('frontend/images/banner-3.png') }}" loading="lazy" alt="Eternity Banner 2" class="d-block zoom"
            style="width:100%">
        <div class="carousel-caption wow fadeInLeft">
            <h1 class="banner-title">Valuable Market <span class="highlight">RESEARCH</span></h1>
            <h2 class="banner-title2">that never ends</h2>
            <h6 class="banner-title3">Eternity Insights offers tailormade end to end B2B business solutions to the companies located worldwide.</h6>
            <a class="btn banner-btn-bg text-uppercase" href="{{ route('aboutus') }}">Read More ></a>
        </div>
    </div>
    <div class="carousel-item">
        <img src="{{ url('frontend/images/banner-1.png') }}" loading="lazy" alt="Eternity Banner 3" class="d-block zoom"
            style="width:100%">
        <div class="carousel-caption wow fadeInLeft">
            <h1 class="banner-title">Clear Understanding of <span class="highlight">Research Objectives</span></h1>
            <h2 class="banner-title2">for your business</h2>
            <h6 class="banner-title3">Eternity Insights can help you make informed business decisions and thus assist you in taking the right path.</h6>
            <a class="btn banner-btn-bg text-uppercase" href="{{ route('aboutus') }}">Read More ></a>
        </div>
    </div>
</div>

<!-- Left and right controls/icons -->
<button class="carousel-control-prev" type="button" data-bs-target="#demo" data-bs-slide="prev">
    <span class="carousel-control-prev-icon"></span>
</button>
<button class="carousel-control-next" type="button" data-bs-target="#demo" data-bs-slide="next">
    <span class="carousel-control-next-icon"></span>
</button>
<div class="home-discovery">
<form action="{{ route('search') }}" method="get" class="home-discovery-form" role="search" data-report-search data-autocomplete-url="{{ route('search_auto') }}">
<label class="visually-hidden" for="home-report-search">Search by keyword, industry</label>
<input id="home-report-search" maxlength="120" type="search" name="s" placeholder="Search by keyword, industry" autocomplete="off">
<button type="submit" aria-label="Search reports"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><circle cx="10.8" cy="10.8" r="7.2"/><path d="m16 16 5 5"/></svg></button>
</form>
<div class="home-industry-shortcuts">
<a href="{{ route('industry_list','healthcare') }}">Pharmaceuticals &amp; Healthcare</a>
<a href="{{ route('industry_list','information-communication-technology') }}">Information &amp; Technology</a>
<a href="{{ route('industry_list','energy-power-utilities') }}">Energy, Power &amp; Utilities</a>
<a href="{{ route('industry_list','aerospace-defense') }}">Aerospace &amp; Defense</a>
<a href="{{ route('industry_list','chemicals-materials') }}">Chemicals &amp; Advanced Materials</a>
</div></div>
</div>

    <!--** About **-->
    <section class="index-about">
        <div class="container">
            <div class="row">
                <div class="col-md-6 abou-con wow fadeInLeft">
                    <div>
                        <h3 class="index-head font-26 org-bor wh-color "><span>ABOUT</span> US</h3>
                    </div>
                    <p class="wh-color font-16">Eternity Insights is a specialized research and consulting company, that offers syndicated reports, custom research, and deep-dive consulting solutions. We provide tailormade end-to-end B2B business solutions to companies worldwide. Our insights support the future growth strategies and roadmaps of companies across industries with multiple application areas, including, Pharmaceuticals, Healthcare, Food & Beverages, ICT, CPG, Chemicals and Advanced Materials, Automotive & Transportation, among others.</p>

                    <p class="wh-color font-16">We assist our clients in visualizing the entire marketplace, to identify and analyze their competition, build sustainable strategies to leverage their capabilities, and deliver on their commitments. Our reports are based on robust & comprehensive research approaches, that are validated by industry experts, which reinforces the high confidence & trust we have in our analysis, estimations, forecasts, and projections. This is crucial to strengthening client development, establishing trust, and assuring customer loyalty.</p>

                    <p class="wh-color font-16">In time, we will grow stronger while enhancing our research capabilities, increasing our learning & knowledge development, and expanding our databases. All driven by the trust of our partners and clients, to ensure and maintain Sustainable Delivery Solutions.</p>

                    <a class="btn btn-cta text-uppercase wow fadeInLeft" href="{{ route('aboutus') }}">Read More ></a>
                </div>

                <div class="col-md-6 wow fadeInRight">
                    <div class="text-center">
                        <h3 class="index-head" style="color:#ff8181">Why Choose Us?</h3>
                    </div>
                    <ul class="key-list">
                        <li>
                            <img src="{{ url('frontend/images/member_interaction.png') }}" width="80" height="80"
                                alt="Member Interaction" class="img-fluid" loading="lazy">
                            <h4>Syndicated Research</h4>
                            <p>Eternity Insights offers an extensive database of syndicated research reports, meticulously crafted by our dedicated and detail-oriented team of market analysts. These reports provide in-depth insights into market size, growth trends, drivers, restraints, opportunities, and forecast analysis. They are designed to support industry stakeholders in making informed business decisions.</p>
                        </li>
                        <li>
                            <img src="{{ url('frontend/images/custom-research3.png') }}" width="80" height="80" alt="custom Research"
                                class="img-fluid" loading="lazy">
                            <h4>Customized Research</h4>
                            <p>We understand that not every business faces the same challenges, and each industry leader has a unique vision for exploring niche opportunities. That is why our customized research solutions are tailored to address your specific business challenges, enabling you to design market and growth strategies that best suit your products and services.</p>
                        </li>
                        <li>
                            <img src="{{ url('frontend/images/qualitative.png') }}" width="80" height="80" alt="Market Monitoring"
                                class="img-fluid" loading="lazy">
                            <h4>Market Monitoring</h4>
                            <p>Eternity Insights believes that in today's fast-paced business environment, staying ahead of market trends is crucial. This is why our market monitoring service provides you with real-time insights and comprehensive analysis to keep you informed and competitive.</p>
                        </li>
                    </ul>
                </div>

            </div>
        </div>
    </section>


<!--** Our Clients **-->
<div class="client-bg">
<div class="container">
    <div class="row">
        <div class="col-md-12 col-sm-12 col-12 mt-5">
            <div class="owl-carousel owl-theme client-logo">
                <div class="item">
                        <img src="{{ url('frontend/images/client-logo/djoglobal.png') }}" alt="djoglobal" width="150" height="77" loading="lazy">
                </div>
                <div class="item">
                    <img src="{{ url('frontend/images/client-logo/Arclin.png') }}" alt="Arclin" width="150" height="77" loading="lazy">
                </div>
                <div class="item">
                    <img src="{{ url('frontend/images/client-logo/photonetc.png') }}" alt="photonetc" width="150" height="77" loading="lazy">
                </div>
                <div class="item">
                    <img src="{{ url('frontend/images/client-logo/Client-Dropbox.png') }}" alt="Dropbox" width="160"
                        height="77" loading="lazy">
                </div>
                <div class="item">
                    <img src="{{ url('frontend/images/client-logo/Client-Evonik.png') }}" alt="Evonik" width="160"
                        height="77" loading="lazy">
                </div>
                <div class="item">
                    <img src="{{ url('frontend/images/client-logo/Client-Honeywell.png') }}" alt="Honeywell" width="160"
                        height="77" loading="lazy">
                </div>
                <div class="item">
                    <img src="{{ url('frontend/images/client-logo/Client-LG.png') }}" alt="LG" width="160" height="77" loading="lazy">
                </div>
                <div class="item">
                    <img src="{{ url('frontend/images/client-logo/Client-Philips.png') }}" alt="Honeywell" width="160"
                        height="77" loading="lazy">
                </div>

            </div>
        </div>
    </div>
</div>
</div>



    <!--** Latest Report **-->
    <section class="trendingTabs latest-reports-section" id="latest-reports">
        <div class="container mt-4 mb-5">
            <div class="row">
                <div class="col-md-12 col-sm-12 col-12">
                    <h2 class="page-title mt-4">
                        <span>Latest Reports</span>
                    </h2>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 col-sm-12 col-12">
                    <div class="tabhomerep">
                        <button class="tablinks" onclick="openCity(event, 'Health_Care')" id="defaultOpen">Pharmaceuticals & Healthcare</button>
                        <button class="tablinks" onclick="openCity(event, 'IT')">Information &amp; Technology</button>
                        <button class="tablinks" onclick="openCity(event, 'Energy_Power')">Energy, Power & Utilities</button>

                        <button class="tablinks" onclick="openCity(event, 'Manufacturing_Construction')">Manufacturing and Construction</button>
                        <button class="tablinks" onclick="openCity(event, 'Aerospace_Defense')">Aerospace & Defense</button>
                        <button class="tablinks" onclick="openCity(event, 'Chemicals_Materials')">Chemicals & Advanced Materials</button>
                    </div>
                    <!-- Tab1 content -->
                    <div id="Health_Care" class="tabcontent">
                        <ul>
                            @php
                            foreach ($reports_one as $key1 => $rep1) {@endphp
                            <li class="latest-report-card">
                                <div class="report-card-mark" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M6 3h8l4 4v14H6zM14 3v5h5M9 12h6M9 16h6"/></svg></div>
                                <h4 class="latest-report-title">
                                    <a href="{{ route('rd_view',$rep1->slug) }}" class="tri_head">
                                        {{ $rep1->title }}
                                    </a>
                                </h4>
                                <div class="title-toggle-slot"><button class="report-card-expand" type="button" aria-expanded="false" hidden>Show full title <span aria-hidden="true">+</span></button></div>
                                <p>{{ substr(strip_tags($rep1->description), 0, 200) }}</p>
                                <p class="right-foot-p">Published Date: {{ site_date($rep1->pub_date, 'M Y') }} | <span class="amount">
                                        ${{ $rep1->lic_price }}</span> |
                                    <a href="{{ route('rd_view',$rep1->slug) }}" class="read-more"> Read More</a>
                                </p>
                            </li>
                           @php }@endphp
                        </ul>
                    </div>
                    <!-- Tab2 content -->
                    <div id="IT" class="tabcontent">
                        <ul>
                            @php
                            foreach ($reports_two as $key2 => $rep2) {@endphp
                            <li class="latest-report-card">
                                <div class="report-card-mark" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M6 3h8l4 4v14H6zM14 3v5h5M9 12h6M9 16h6"/></svg></div>
                                <h4 class="latest-report-title">
                                    <a href="{{ route('rd_view',$rep2->slug) }}" class="tri_head">
                                        {{ $rep2->title }}
                                    </a>
                                </h4>
                                <div class="title-toggle-slot"><button class="report-card-expand" type="button" aria-expanded="false" hidden>Show full title <span aria-hidden="true">+</span></button></div>
                                <p>{{ substr(strip_tags($rep2->description), 0, 200) }}</p>
                                <p class="right-foot-p">Published Date: {{ site_date($rep2->pub_date, 'M Y') }} | <span class="amount">
                                        ${{ $rep2->lic_price }}</span> |
                                    <a href="{{ route('rd_view',$rep2->slug) }}" class="read-more"> Read More</a>
                                </p>
                            </li>
                           @php }@endphp
                        </ul>
                    </div>
                    <!-- Tab3 content -->
                    <div id="Energy_Power" class="tabcontent">
                        <ul>
                            @php
                            foreach ($reports_three as $key3 => $rep3) {@endphp
                            <li class="latest-report-card">
                                <div class="report-card-mark" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M6 3h8l4 4v14H6zM14 3v5h5M9 12h6M9 16h6"/></svg></div>
                                <h4 class="latest-report-title">
                                    <a href="{{ route('rd_view',$rep3->slug) }}" class="tri_head">
                                        {{ $rep3->title }}
                                    </a>
                                </h4>
                                <div class="title-toggle-slot"><button class="report-card-expand" type="button" aria-expanded="false" hidden>Show full title <span aria-hidden="true">+</span></button></div>
                                <p>{{ substr(strip_tags($rep3->description), 0, 200) }}</p>
                                <p class="right-foot-p">Published Date: {{ site_date($rep3->pub_date, 'M Y') }} | <span class="amount">
                                        ${{ $rep3->lic_price }}</span> |
                                    <a href="{{ route('rd_view',$rep3->slug) }}" class="read-more"> Read More</a>
                                </p>
                            </li>
                           @php }@endphp
                        </ul>
                    </div>
                    <!-- Tab4 content -->
                    <div id="Manufacturing_Construction" class="tabcontent">
                        <ul>
                            @php
                            foreach ($reports_four as $key4 => $rep4) {@endphp
                            <li class="latest-report-card">
                                <div class="report-card-mark" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M6 3h8l4 4v14H6zM14 3v5h5M9 12h6M9 16h6"/></svg></div>
                                <h4 class="latest-report-title">
                                    <a href="{{ route('rd_view',$rep4->slug) }}" class="tri_head">
                                        {{ $rep4->title }}
                                    </a>
                                </h4>
                                <div class="title-toggle-slot"><button class="report-card-expand" type="button" aria-expanded="false" hidden>Show full title <span aria-hidden="true">+</span></button></div>
                                <p>{{ substr(strip_tags($rep4->description), 0, 200) }}</p>
                                <p class="right-foot-p">Published Date: {{ site_date($rep4->pub_date, 'M Y') }} | <span class="amount">
                                        ${{ $rep4->lic_price }}</span> |
                                    <a href="{{ route('rd_view',$rep4->slug) }}" class="read-more"> Read More</a>
                                </p>
                            </li>
                           @php }@endphp
                        </ul>
                    </div>
                    <!-- Tab5 content -->
                    <div id="Aerospace_Defense" class="tabcontent">
                        <ul>
                            @php
                            foreach ($reports_five as $key5 => $rep5) {@endphp
                            <li class="latest-report-card">
                                <div class="report-card-mark" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M6 3h8l4 4v14H6zM14 3v5h5M9 12h6M9 16h6"/></svg></div>
                                <h4 class="latest-report-title">
                                    <a href="{{ route('rd_view',$rep5->slug) }}" class="tri_head">
                                        {{ $rep5->title }}
                                    </a>
                                </h4>
                                <div class="title-toggle-slot"><button class="report-card-expand" type="button" aria-expanded="false" hidden>Show full title <span aria-hidden="true">+</span></button></div>
                                <p>{{ substr(strip_tags($rep5->description), 0, 200) }}</p>
                                <p class="right-foot-p">Published Date: {{ site_date($rep5->pub_date, 'M Y') }} | <span class="amount">
                                        ${{ $rep5->lic_price }}</span> |
                                    <a href="{{ route('rd_view',$rep5->slug) }}" class="read-more"> Read More</a>
                                </p>
                            </li>
                           @php }@endphp
                        </ul>
                    </div>
                     <!-- Tab6 content -->
                     <div id="Chemicals_Materials" class="tabcontent">
                        <ul>
                            @php
                            foreach ($reports_six as $key5 => $rep6) {@endphp
                            <li class="latest-report-card">
                                <div class="report-card-mark" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M6 3h8l4 4v14H6zM14 3v5h5M9 12h6M9 16h6"/></svg></div>
                                <h4 class="latest-report-title">
                                    <a href="{{ route('rd_view',$rep6->slug) }}" class="tri_head">
                                        {{ $rep6->title }}
                                    </a>
                                </h4>
                                <div class="title-toggle-slot"><button class="report-card-expand" type="button" aria-expanded="false" hidden>Show full title <span aria-hidden="true">+</span></button></div>
                                <p>{{ substr(strip_tags($rep6->description), 0, 200) }}</p>
                                <p class="right-foot-p">Published Date: {{ site_date($rep6->pub_date, 'M Y') }} | <span class="amount">
                                        ${{ $rep6->lic_price }}</span> |
                                    <a href="{{ route('rd_view',$rep6->slug) }}" class="read-more"> Read More</a>
                                </p>
                            </li>
                           @php }@endphp
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>
 <!--** Industry News **-->
 <div class="container-bg">
        <section class="industryNews">
            <div class="container dark-dot-bg mt-4 pt-5 pb-5">
                <div class="row">
                    <div class="col-md-12 col-sm-12 col-12">
                        <h2 class="page-title mt-4">
                            <span>Industry News</span>
                        </h2>
                    </div>
                </div>
            </div>
            <div class="container">
             <div class="grid">
                    <div class="row">
                    @php 
                    foreach ($indnews as $key => $news) {  @endphp 
                        <div class="col-md-3 col-sm-6 col-12 mb-5 news-card-column">
                            <article class="industry-news-card">
                                <a class="news-card-link" href="{{ route('news_details', $news->slug) }}">
                                    <div class="news-card-media">
                                        <img src="{{ news_thumbnail($news) }}" alt="News Image" loading="lazy" onerror="this.onerror=null;this.parentElement.classList.add('has-fallback');this.src='/frontend/images/eternity_logo.png';">
                                    </div>
                                    <div class="news-card-content">
                                        <span class="news-card-date">{{ site_date($news->created_at, 'M Y') }} </span>
                                        <h3>{{ $news->headline }}</h3>
                                        <p>{!! substr(strip_tags($news->content), 0, 80) !!} ...</p>
                                        <span class="news-card-arrow" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M5 19 19 5M5 5h14v14"/></svg></span>
                                    </div>
                                </a>
                            </article>
                        </div>
                        @php }@endphp
                    </div>
                </div>
                <div class="row">
                    <div class="text-center pb-5">
                        <a class="btn btn-cta2 text-uppercase" href="{{ route('news_list') }}">View All ></a>
                    </div>
                </div>
            </div>
        </section>
    </div>
        <!--** Client Testimonials **-->
        <section class="trendingTabs wow fadeInUp">
        <div class="container mt-4 mb-5">
            <div class="row mb-5">
                <div class="col-md-12 col-sm-12 col-12">
                    <h2 class="page-title mt-4">
                        <span>Client Testimonials</span>
                    </h2>
                </div>
                <div class="col-md-12 col-sm-12 col-12 text-center mt-5">
                    <blockquote class="addCurlys">
                        Clients value our experts for their knowledge and insights, but also recognize and respect
                        their limitations. We have worked with thousands of clients, companies, and experts to develop
                        the world's most robust compliance framework for primary research.
                    </blockquote>
                </div>
            </div>
            <div class="row mb-5">
                <div class="col-lg-6 col-md-6 col-sm-6 col-12">
                    <div class="row">
                        <div class="col-4 mb-3">
                            <div class="client-box">
                                <img src="{{ url('frontend/images/client-trust-01.png') }}" alt="client Industry" class="img-fluid" loading="lazy" />
                            </div>
                            <p class="client-per mb-0">100+</p>
                            <p class="client-text">Industry verticals served</p>
                        </div>
                        <div class="col-4 mb-3">
                            <div class="client-box">
                                <img src="{{ url('frontend/images/regions-11.png') }}" alt="client regions" class="img-fluid" loading="lazy"/>
                            </div>
                            <p class="client-per mb-0">60+</p>
                            <p class="client-text">Country specific reports available</p>
                        </div>
                        <div class="col-4 mb-3">
                            <div class="client-box">
                                <img src="{{ url('frontend/images/report-1.png') }}" alt="report-1.png" class="img-fluid" loading="lazy" />
                            </div>
                            <p class="client-per mb-0">10K+</p>
                            <p class="client-text">Premium reports database</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 col-md-6 col-sm-6 col-12">
                    <div id="demo1" class="carousel slide" data-bs-ride="carousel">

                        <!-- Indicators/dots -->
                        <div class="carousel-indicators testimonial-indicators">
                            <button type="button" data-bs-target="#demo1" data-bs-slide-to="0" class="active"></button>
                            <button type="button" data-bs-target="#demo1" data-bs-slide-to="1"></button>
                            <button type="button" data-bs-target="#demo1" data-bs-slide-to="2"></button>
                        </div>

                        <!-- The slideshow/carousel -->
                        <div class="carousel-inner">
                            <div class="carousel-item active">
                                <div class="testimonial-box">
                                    <p class="testimonial-desc mb-5">
                                    It was a wonderful experience working with Team Eternity Insights. We were highly satisfied with the report quality and professionalism. Thank you very much for extending support on customizations.
                                    </p>
                                    <h5 class="testimonials-user mb-3">
                                        Global Strategist, IOTG, Retail, Banking, Hospitality & Education
                                    </h5>
                                </div>
                            </div>
                            <div class="carousel-item">
                                <div class="testimonial-box">
                                <p class="testimonial-desc mb-5">
                                    We are grateful to have Eternity Insights as our research partners. The market data and forecasts were logical and realistic which supported our vision. Our sincere thanks to the entire team for stitching together this insightful report. 
                                    </p>
                                    <h5 class="testimonials-user mb-3">
                                        Fox C.P.M. | Indirect Procurement Manager
                                    </h5>
                                </div>
                            </div>
                            <div class="carousel-item">
                                <div class="testimonial-box">
                                <p class="testimonial-desc mb-5">
                                    Thank you Eternity Insights research team for meeting our expectations very well. The report was highly informative and we are hopeful these insights will shape our strategic directions
                                    </p>
                                    <h5 class="testimonials-user mb-3">
                                        Chief Commercial Officer
                                    </h5>
                                </div>
                            </div>
                            <div class="carousel-item">
                                <div class="testimonial-box">
                                <p class="testimonial-desc mb-5">
                                    It was a great experience working with Eternity Insights. They accurately grasped our requirement and served a tailored report in such a short timeline. We admire the after-sales service support provided to help us understand our queries and present data to our management 
                                    </p>
                                    <h5 class="testimonials-user mb-3">
                                        Chief Commercial Officer
                                    </h5>
                                </div>
                            </div>
                            <div class="carousel-item">
                                <div class="testimonial-box">
                                <p class="testimonial-desc mb-5">
                                    We are very happy we formed research agreement with Eternity Insights. The team is highly professional and punctual. We received timely updates on research work and the modifications required were patiently considered. We really appreciate their hard work and are very happy to extend long term business relations.
                                    </p>
                                    <h5 class="testimonials-user mb-3">
                                        Founder & CEO
                                    </h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
            
@endsection
@section('footer_script')
<script src="{{ url('frontend/js/lightslider.js') }}" defer></script>

<script type="application/ld+json">{"@@context":"https://schema.org","@@type":"FAQPage","mainEntity":[{"@@type":"Question","name":"What is Eternity Insights?","acceptedAnswer":[{"@@type":"Answer","text":"Eternity Insights is a one stop destination for all industrial, commercial and profitmaking ventures seeking the best research of the market of all the sectors. The company prides itself in being efficient in fulfilling market research requirements of international and domestic clients."}]},{"@@type":"Question","name":"Why use Eternity Insights?","acceptedAnswer":[{"@@type":"Answer","text":"Eternity Insights offers accurate market analysis and research in the clientâ€™s sector of business. It has more than 18,000 research documents to support this claim. Experienced researchers and analysts with many years of work in their field of expertise work with us to provide the best analysis and reporting services in the industry."}]},{"@@type":"Question","name":"Who are our customers?","acceptedAnswer":[{"@@type":"Answer","text":"Eternity Insights has customers from almost every possible area of business activity. They include industrialists, entrepreneurs, organisations, executives and even students. Eternity Insights provides all of these with a detailed and accurate analysis of any specific market. We offer a comprehensive analysis of existing and potential future market trends for all business sectors. Among these are companies involved in banking, finance, insurance, technology and innovation, automobiles, medical, pharmaceutical, education, consulting, biotechnology, genetics and fast moving consumer goods (FMCGs)."}]},{"@@type":"Question","name":"How to reach our Customer Support System?","acceptedAnswer":[{"@@type":"Answer","text":"We take customer support and help services extremely seriously. We are easily contacted for help and support before, during and after the purchasing process. Contact us by telephone, email or by using the contact form provided on our website."}]},{"@@type":"Question","name":"How can I contribute to the publications of Eternity Insights?","acceptedAnswer":[{"@@type":"Answer","text":"That can be easily arranged. First, you must register as a publisher on the website. After the application has been approved the members of Eternity Insights will contact you."}]},{"@@type":"Question","name":"How to affiliate with Eternity Insights?","acceptedAnswer":[{"@@type":"Answer","text":"If you want to become an affiliate then simply contact us. You will then get a reply from us for further steps."}]},{"@@type":"Question","name":"What about the information collected about me?","acceptedAnswer":[{"@@type":"Answer","text":"We collect basic information about our users: name, email address, company, contact information and address. For more details on how we use your information check out our Terms & Conditions and Privacy pages."}]},{"@@type":"Question","name":"Is my private information protected?","acceptedAnswer":[{"@@type":"Answer","text":"The information you provide is protected according to our Privacy page. If at any point, the user feels disturbed by the newsletter, promotional offers or any other mails sent by Eternity Insights, then they can easily opt out of it by unsubscribing from the mailing list, a link for which is on each email we send you."}]}]}</script>

    <script>
        $(document).ready(function () {
            $('.carousel').carousel({
                pause: false
            });            
            var owl = $('.owl-carousel');
            owl.owlCarousel({
                items: 5,
                loop: true,
                margin: 30,
                autoplay: true,
                autoHeight:false,
                autoplayTimeout: 1300,
                autoplayHoverPause: true
            });
            // $("#search").focusout(function () {
            //     $("#autores").hide(1000);
            // });
        });
       if(window.screen.width > 768){
        function openCity(evt, cityName) {
            var i, tabcontent, tablinks;
            tabcontent = document.getElementsByClassName("tabcontent");
            for (i = 0; i < tabcontent.length; i++) {
                tabcontent[i].style.display = "none";
            }
            tablinks = document.getElementsByClassName("tablinks");
            for (i = 0; i < tablinks.length; i++) {
                tablinks[i].className = tablinks[i].className.replace(" active", "");
            }
            document.getElementById(cityName).style.display = "block";
            evt.currentTarget.className += " active";
        }

        // Get the element with id="defaultOpen" and click on it
        document.getElementById("defaultOpen").click();
       } 

    </script>
@endsection
