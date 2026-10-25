@php
    $stickyTitle = trim($keyword ?? $report->keyword ?? $report->title);
    if (!preg_match('/\bmarket$/i', $stickyTitle)) $stickyTitle .= ' Market';
    $stickyUpdated = !empty($report->updated_at) && site_date($report->updated_at, 'd M Y');
    $stickyDate = $stickyUpdated ? $report->updated_at : $report->pub_date;
@endphp
<section class="report-sticky-header" id="report-sticky-header" aria-label="Report quick actions" hidden>
    <div class="report-sticky-inner">
        <a class="report-sticky-logo" href="{{ route('home') }}" aria-label="Eternity Insights home">
            <img src="{{ asset('frontend/images/eternity_logo.png') }}" alt="Eternity Insights" width="170" height="60">
        </a>
        <div class="report-sticky-details">
            <a class="report-sticky-title" href="#report-page-title" title="{{ $stickyTitle }}">{{ $stickyTitle }}</a>
            <div class="report-sticky-meta">
                @if($stickyDate)<span>{{ $stickyUpdated ? 'Updated' : 'Published' }}: {{ site_date($stickyDate, 'd M Y') }}</span>@endif
                <span>Report ID: {{ $report->id }}</span>
            </div>
        </div>
        <div class="report-sticky-support">
            <span>Reach out for immediate support</span>
            <a href="mailto:{{ config('site.sales_email') }}"><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 6 9 7 9-7"/></svg>{{ config('site.sales_email') }}</a>
        </div>
        <div class="report-sticky-actions">
            <a class="report-sticky-cta report-sticky-primary" href="{{ route('request_sample', $report->id) }}"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3v12m-5-5 5 5 5-5M4 15v5h16v-5"/></svg><span>Request Sample</span></a>
            <a class="report-sticky-cta report-sticky-secondary" href="{{ route('enquiry_buying', $report->id) }}"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M21 11a8 8 0 0 1-8 8H7l-4 3V11a9 9 0 0 1 18 0Z"/><path d="M8 10h8M8 14h5"/></svg><span>Inquire Before Buying</span></a>
        </div>
    </div>
</section>
