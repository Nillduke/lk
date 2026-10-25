@php
$isInterior = !request()->routeIs('home', 'home_amp');
$pageKind = match (true) {
    request()->routeIs('all_reports','industry_list') => 'catalogue',
    request()->routeIs('aboutus','services') => 'company',
    request()->routeIs('faqs','howToOrder','disclaimer','termsConditions','refundPolicy','privacyPolicy') => 'document',
    request()->routeIs('html_sitemap') => 'sitemap',
    request()->routeIs('news_list') => 'news',
    request()->routeIs('news_details') => 'article',
    request()->routeIs('contactus') => 'contact',
    request()->routeIs('search') => 'search',
    str_starts_with(request()->path(), 'checkout') => 'checkout',
    str_starts_with(request()->path(), 'auth/') => 'account',
    request()->routeIs('unsubscribe') => 'unsubscribe',
    str_ends_with(request()->path(), '/thankyou') => 'result',
    default => 'general',
};
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    @php 
    if (isset($meta_description)) { @endphp
        <meta name="description" content="{{ $meta_description }}">    
    @php } 
    if (isset($meta_keywords)) { @endphp
        <meta name="keywords" content="{{ $meta_keywords }}">
    @php } @endphp
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script>window.dataLayer = window.dataLayer || []; function gtag(){dataLayer.push(arguments);}</script>
    @if(config('site.analytics_id'))
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ config('site.analytics_id') }}"></script>
    <script>gtag('js', new Date());gtag('config', @json(config('site.analytics_id')));</script>
    @endif
    
    <!-- Self-hosted typography and icons -->
    @yield('head_meta')
    @php $styleManifest = json_decode(file_get_contents(public_path('frontend/dist/manifest.json')), true); @endphp
    @php
        $styleUrl = $styleManifest[request()->routeIs('rd_view') ? 'report' : ($isInterior ? 'interior' : 'home')];
        $criticalKey = request()->routeIs('rd_view') ? 'report' : (request()->routeIs('news_list') ? 'news' : null);
        $criticalManifest = is_file(public_path('frontend/dist/critical-manifest.json')) ? json_decode(file_get_contents(public_path('frontend/dist/critical-manifest.json')), true) : [];
        $criticalStyle = $criticalKey ? ($criticalManifest[$criticalKey] ?? null) : null;
        $hasCriticalStyle = $criticalStyle && $criticalStyle['source'] === $styleUrl && is_file(public_path($criticalStyle['file']));
    @endphp
    @if($hasCriticalStyle)
    <style data-critical="{{ $criticalKey }}">{!! file_get_contents(public_path($criticalStyle['file'])) !!}</style>
    <link rel="preload" as="style" href="{{ $styleUrl }}" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="{{ $styleUrl }}"></noscript>
    @else
    <link rel="stylesheet" href="{{ $styleUrl }}">
    @endif

    <title>{{ $title ?? config('app.name') }}</title>
    <link rel="shortcut icon" href="/frontend/images/favicon.png" />

@if(config('site.recaptcha.enabled'))<script src="https://www.google.com/recaptcha/api.js" async defer></script>@endif
<link rel="preload" href="{{ asset('frontend/fonts/roboto-400-common.e2df779d91d1.woff2') }}" as="font" type="font/woff2" crossorigin>
@unless(request()->routeIs('rd_view'))
<link rel="preload" href="{{ asset('frontend/fonts/playfair-display-400-common.c5e10ffbbb10.woff2') }}" as="font" type="font/woff2" crossorigin>
@endunless
@if(request()->routeIs('rd_view'))
<script src="{{ asset('frontend/js/report-sticky-header.js') }}?v=20261024-1" defer></script>
@endif
<script src="{{ asset('frontend/js/report-search.js') }}?v=20261024-1" defer></script>
</head>
<body class="site-redesign {{ $isInterior ? 'interior-site interior-'.$pageKind : '' }} {{ request()->routeIs('home') ? 'page-home' : '' }} {{ request()->routeIs('rd_view') ? 'page-report' : '' }}">
@if($errors->any())<div class="container alert alert-danger mt-3">{{ $errors->first() }}</div>@endif
    @include('include.topmenu')
    @yield('sticky_report_header')
    @if($isInterior)<div class="interior-content">@endif
    @yield('content')
    @if($isInterior)</div>@endif
    @include('include.footer')
    <script src="{{ asset('frontend/js/site-design.js') }}?v=20261024-perf1" defer></script>
    @if($isInterior)<script src="{{ asset('frontend/js/interior-design.js') }}?v=20261024-perf1" defer></script>@endif
    <script src="{{ asset('frontend/js/mobile-responsive.js') }}?v=20261024-no-banner" defer></script>
    @yield('footer_script')
</body>
</html>
