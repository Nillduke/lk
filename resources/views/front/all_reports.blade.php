@extends('layout.default')
@section('content')
<div class="catalogue-page catalogue-all">
<section class="catalogue-hero"><div class="container"><nav aria-label="breadcrumb"><ol class="breadcrumb site-breadcrumb"><li class="breadcrumb-item"><a href="{{ url('/') }}" aria-label="Home"><i class="fas fa-home"></i></a></li><li class="breadcrumb-item active" aria-current="page">All Reports</li></ol></nav><div class="lat-rep-heading">
                        <h1>Latest Published Reports</h1>
                        <p class="catalogue-intro-text" id="catalogue-introduction">
                            Eternity Insights keeps a close tab on emerging industry trends, recent macroeconomic
                            assessments,
                            and changing business landscape. An array of our latest offerings comprising hundreds of report
                            on wide spectrum of sectors aim to take the clutter out of analytics. Our business intelligence
                            solutions try to cover trends that are recent as well as pertinent to help clients reach
                            business
                            decision faster, offering first-plunge advantage. Our carefully calibrated analysis tools and
                            cutting-edge technologies help researchers and consultants guide you through the labyrinth of
                            ever-changing business landscape. Our team relentlessly toil to update business intelligence to
                            reflect the most recent trends and policy overhauls around the world.
                        </p><button type="button" class="catalogue-intro-toggle" aria-expanded="false" aria-controls="catalogue-introduction">Read more <span aria-hidden="true">+</span></button>
                    </div>
@include('include.catalogue-search')
</div></section>
<section class="catalogue-section"><div class="container catalogue-layout">
<aside class="catalogue-sidebar" aria-label="Report filters"><div class="catalogue-sidebar-panel"><button type="button" class="catalogue-filter-toggle" aria-expanded="false" aria-controls="catalogue-filter-body">Filter reports <span aria-hidden="true">+</span></button><div class="catalogue-sidebar-body" id="catalogue-filter-body">@include('include.assistance')
                    <form action="" method="get" id="filterfrm">
@foreach(['q','per_page'] as $field) @if(request()->filled($field))<input type="hidden" name="{{ $field }}" value="{{ request($field) }}">@endif @endforeach
                    <aside>
                        <div class="latrep-sidebox">
                            <div class="key-sectors">
                                <h3>Key Sectors</h3>

                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="keySectors" id="allRports"
                                        value="all" {{ ($keySector == 'all' || $keySector == null) ? 'checked' : null }}>
                                    <label class="form-check-label" for="allRports">
                                        All Reports
                                    </label>
                                </div>
                                @php
                                foreach ($categories as $key => $cat) { @endphp
                                    <div class="form-check">
                                    <input class="form-check-input" type="radio" name="keySectors"
                                        id="{{ $cat->name.'_'.$cat->id }}" value="{{ $cat->id }}" 
                                        {{ ($cat->id == $keySector) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="{{ $cat->name.'_'.$cat->id }}">
                                        {{ $cat->name }}
                                    </label>
                                </div>
                                @php }@endphp                               
                                
                            </div>
                        </div>
                    </aside>

                    <aside>
                        <div class="latrep-sidebox">
                            <div class="key-regions">
                                <h3>Key Regions Covered</h3>

                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="keyRegionsCovered" id="global"
                                        value="all" {{ ($keyRegionsCovered == 'all' || $keyRegionsCovered == null) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="global">
                                        Global
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="keyRegionsCovered" id="africa"
                                        value="africa" {{ ($keyRegionsCovered == 'africa') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="africa">
                                        Africa
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="keyRegionsCovered" id="apac"
                                        value="apac" {{ ($keyRegionsCovered == 'apac') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="apac">
                                        APAC
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="keyRegionsCovered" id="europe"
                                        value="europe" {{ ($keyRegionsCovered == 'europe') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="europe">
                                        Europe
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="keyRegionsCovered"
                                        id="middleEast" value="Middle East" {{ ($keyRegionsCovered == 'Middle East') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="middleEast">
                                        Middle East
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="keyRegionsCovered"
                                        id="northAmerica" value="North America" {{ ($keyRegionsCovered == 'North America') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="northAmerica">
                                        North America
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="keyRegionsCovered"
                                        id="latinAmerica" value="Latin America" {{ ($keyRegionsCovered == 'Latin America') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="latinAmerica">
                                        Latin America
                                    </label>
                                </div>
                            </div>
                        </div>
                    </aside>
                @php /* @endphp
                    <aside>
                        <div class="latrep-sidebox">
                            <div class="select-price">
                                <h3>Select Price</h3>

                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="selectPrice" id="anyPrice"
                                        value="all" {{ ($selectPrice == 'all' || $selectPrice == null) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="anyPrice">
                                        Any Price
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="selectPrice" id="0-$2000"
                                        value="upto2000" {{ ($selectPrice == 'upto2000') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="0-$2000">
                                        0 - $2000
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="selectPrice" id="$2000-$4000"
                                        value="2000-4000" {{ ($selectPrice == '2000-4000') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="$2000-$4000">
                                        $2000 - $4000
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="selectPrice" id="$4000-Above"
                                        value="above" {{ ($selectPrice == 'above') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="$4000-Above">
                                        $4000 - Above
                                    </label>
                                </div>
                            </div>
                        </div>
                    </aside>

                    <aside>
                        <div class="latrep-sidebox">
                            <div class="select-period">
                                <h3>Select Period</h3>

                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="selectPeriod" id="allTheTime"
                                        value="all" {{ ($selectPeriod == 'all' || $selectPeriod == null) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="allTheTime">
                                        All The Time
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="selectPeriod" id="less1Month"
                                        value="less3months" {{ ($selectPeriod == 'less3months') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="less1Month">
                                        Less Than 3 Months
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="selectPeriod" id="less3Month"
                                        value="less6months" {{ ($selectPeriod == 'less6months') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="less3Month">
                                        Less Than 6 Months
                                    </label>
                                </div>                                
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="selectPeriod" id="less1Year"
                                        value="less1Year" {{ ($selectPeriod == 'less1Year') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="less1Year">
                                        Less Than 1 Year
                                    </label>
                                </div>
                            </div>
                        </div>
                    </aside>
                    @php */@endphp
                    </form></div></div></aside>
<main class="catalogue-results" aria-label="Report results">@include('include.catalogue-toolbar')
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
@section('footer_script')
<script>document.querySelectorAll('#filterfrm input[type=radio]').forEach(input=>input.addEventListener('change',()=>document.getElementById('filterfrm').submit()));</script>
@endsection
