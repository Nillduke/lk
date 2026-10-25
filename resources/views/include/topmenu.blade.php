<header class="site-header" data-shared-header>
<!-- **Header** -->

    <div class="header bg">
        <div class="container p-2">
            <div class="row">
                <div class="col-2 col-md-5 d-none d-sm-block"></div>
                <div class="col-10 col-md-7 col-12">
                    <ul class="list-inline top-contact-info mb-0">
                        <li class="list-inline-item contact-details-contents">
                            <img src="/frontend/images/mail.svg" width="20" height="20" class="Mail" alt="Mail"> &nbsp; <a
                                href="mailto:{{ config('site.sales_email') }}"> {{ config('site.sales_email') }}</a>
                        </li>
                        <li class="list-inline-item contact-details-contents phone-bg">
                            <img src="/frontend/images/phone.svg" class="Phone" alt="Phone" width="20" height="20"><a
                                href="tel:+91-930-705-6852"> +91-930-705-6852</a>
                        </li>
                    </ul>
                </div>
            </div>
            <!--/row-->
        </div>
        <!--container-->
    </div>
    <!-- **Menu** -->
    <div class="nav-shadow sticky-top">
        <nav class="navbar navbar-expand-{{ $isInterior ? 'lg' : 'md' }} navbar-dark custom-nav p-0">
            <div class="container">
                <a class="navbar-brand" aria-label="Eternity Insights home" href="{{ route('home') }}">
                    <img src="/frontend/images/eternity_logo.png" class="custom-logo" alt="Eternity Logo">
                </a>
                <button class="navbar-toggler" aria-label="Toggle navigation" aria-expanded="false" type="button" data-bs-toggle="collapse" data-bs-target="#mySidenav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="mySidenav">
                    <ul class="navbar-nav ms-auto custom-menu">
                        <li class="nav-item">
                            <a class="nav-link text-uppercase" href="{{ route('home') }}">Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-uppercase" href="{{ route('all_reports') }}">Reports Store</a>
                        </li>                        
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle text-uppercase" href="#" id="navbarDropdown1" role="button"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                Industries
                            </a>
                            <ul class="dropdown-menu mega-menu scrollable-menu" aria-labelledby="navbarDropdown1">
                                <li class="widht42 border-red-right">
                                    <ul>
                                        <li class="nav-about" style="padding: 0 30px;color: #9b9b9b;">
                                            <h6>Latest Published Reports</h6>Eternity Insights keeps a close tab on emerging
                                            industry trends, recent macroeconomic assessments, and changing business
                                            landscape. <br /><br /> Our business intelligence solutions try to cover trends
                                            that are recent as well as pertinent to help clients reach business decision
                                            faster, offering first-plunge advantage.<br /><br /><a class="mega-menu-cta" href="{{ route('all_reports') }}"><span>Read More</span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path d="M4 12h15M13 5l7 7-7 7"/></svg></a>
                                        </li>
                                    </ul>
                                </li>
                                <li class="widht42 border-red-right">
                                    <ul>
                                    <li><a href="{{ route('industry_list','healthcare') }}" class="dropdown-item"> Pharmaceuticals & Healthcare</a></li>
                                        <li><a href="{{ route('industry_list','chemicals-materials') }}" class="dropdown-item"> Chemicals & Advanced Materials</a></li>
                                        <li><a href="{{ route('industry_list','information-communication-technology') }}" class="dropdown-item">Information & Communication Technology
                                        </a></li>
                                        <li><a href="{{ route('industry_list','consumer-goods') }}" class="dropdown-item">Consumer Goods</a></li>
                                        <li><a href="{{ route('industry_list','energy-power-utilities') }}" class="dropdown-item"> Energy, Power & Utilities</a></li>
                                        <li><a href="{{ route('industry_list','agriculture') }}" class="dropdown-item"> Agriculture</a></li>

                                        <li><a href="{{ route('industry_list','industry-services') }}" class="dropdown-item">Industry Services</a></li>
                                        <li><a href="{{ route('industry_list','business-and-financial-services') }}" class="dropdown-item"> Business & Financial Services</a></li>
                                    </ul>
                                </li>
                                <li class="widht42">
                                    <ul>
                                    <li><a href="{{ route('industry_list','automotive-transportation') }}" class="dropdown-item"> Automotive & Transportation</a></li>
                                        <li><a href="{{ route('industry_list','electronics-semiconductors') }}" class="dropdown-item"> Electronics & Semiconductors</a></li>
                                        <li><a href="{{ route('industry_list','food-beverages') }}" class="dropdown-item"> Food & Beverages</a></li>
                                        <li><a href="{{ route('industry_list','manufacturing-and-construction') }}" class="dropdown-item"> Manufacturing and Construction</a></li>
                                        <li><a href="{{ route('industry_list','aerospace-defense') }}" class="dropdown-item"> Aerospace & Defense</a></li>
                                        <li><a href="{{ route('industry_list','software-hardware') }}" class="dropdown-item"> Software & Hardware</a></li>
                                        <li><a href="{{ route('industry_list','machinery-equipment') }}" class="dropdown-item"> Machinery & Equipment</a></li>
                                    </ul>
                                </li>
                            </ul>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-uppercase" href="{{ route('aboutus') }}">About Us</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-uppercase" href="{{ route('contactus') }}">Contact Us</a>
                        </li>
                        <li class="hdr-srch">
                            <button id="header-search-toggle" type="button" aria-label="Search reports" aria-controls="searchDiv" aria-expanded="false"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="10.5" cy="10.5" r="6.5"/><path d="m16 16 5 5"/></svg></button>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </div>
    <div id="searchDiv" class="search-bg">
        <div class="container">
            <div class="row">
                <div class="col-md-8 offset-md-2">
                <form id="demo-21" action="{{ route('search') }}" method="get" role="search" data-report-search data-autocomplete-url="{{ route('search_auto') }}">
                    <label class="visually-hidden" for="search">Search reports by keyword or industry</label>
                    <div class="input-group">
                        <input type="search" class="form-control" placeholder="Search by keyword, industry" name="s" id="search" maxlength="120" autocomplete="off">
                        <button class="header-search-submit" type="submit" aria-label="Search reports"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="10.5" cy="10.5" r="6.5"/><path d="m16 16 5 5"/></svg></button>
                        <button type="button" id="bottle" aria-label="Close search"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m6 6 12 12M18 6 6 18"/></svg></button>
                    </div>
                </form>
                </div>
            </div>
        </div>
    </div>
        <!--** mobile view search **-->
        <div class="mobile-search-bg">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <form action="{{ route('search') }}" name="frm_search" method="get">
@csrf
                    <div class="input-group">
                        <input type="text" class="form-control" placeholder="Search"
                            style="height: 30px;border-radius: .25rem">
                    </div>
                </form>
             </div>
        </div>
    </div>
    </div>

</header>
