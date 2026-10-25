<!doctype html>
<html amp lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,minimum-scale=1,initial-scale=1,maximum-scale=1,user-scalable=no">
<meta name="application-name" content="Market Research Reports">
<meta name="apple-mobile-web-app-title" content="Market Research Reports">
<meta name="theme-color" content="#353535">
	<title>{{ $title }}</title>
    <meta name="description" content="{{ $meta_description }}">
    <link rel="shortcut icon" href="{{ url('frontend/images/favicon.png') }}" />
<link rel="preconnect" href="https://cdn.ampproject.org">
<link rel="preload" as="script" href="https://cdn.ampproject.org/v0.js" fetchpriority="high">
<script async src="https://cdn.ampproject.org/v0.js"></script>
<script async custom-element="amp-social-share" src="https://cdn.ampproject.org/v0/amp-social-share-0.1.js"></script>
<script async custom-element="amp-sidebar" src="https://cdn.ampproject.org/v0/amp-sidebar-0.1.js"></script>
<script async custom-element="amp-accordion" src="https://cdn.ampproject.org/v0/amp-accordion-0.1.js"></script>

@if(config('site.analytics_id'))<script async custom-element="amp-analytics" src="https://cdn.ampproject.org/v0/amp-analytics-0.1.js"></script>@endif
<script async custom-element="amp-form" src="https://cdn.ampproject.org/v0/amp-form-0.1.js"></script>
<!--Import Page Related Scripts-->
<!--AMP HTML files require a canonical link pointing to the regular HTML. If no HTML version exists, it should point to itself.-->
<link rel="canonical" href="{{ url('/') }}">
<link rel="icon" href="/frontend/images/favicon.png" type="image/x-icon">
<link rel="shortcut icon" href="/frontend/images/favicon.png" />


<meta name="apple-mobile-web-app-capable" content="yes"/>
<meta name="apple-mobile-web-app-status-bar-style" content="black">
<link rel="preload" as="font" type="font/woff2" crossorigin href="/frontend/fonts/roboto-400-common.e2df779d91d1.woff2">
<style amp-custom>
*{
	margin:0;
	border:0;
	-webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
}
body{
	font-size:14px;
	line-height: 1.8;
	color: #353535;
	font-family: 'Roboto', sans-serif;
}
.clear{clear:both;}
.row {
    /*margin-right: -15px;
    margin-left: -15px;	*/
}
.row:after, .row:before{
    display: table;
    content: " ";
}
.row:after{clear:both;}
.col-4 , .col-12,.col-6, .col-8, .col-3, .col-9, .rpo-img{
	position: relative;
	min-height: 1px;
	padding-right: 15px;
	padding-left: 15px;
}
@@media (min-width: 992px){
.col-4 , .col-12, .col-6, .col-8, .col-3, .col-9{float:left;}
}
.col-3{width:25%;}
.col-4{width: 33.33333333%;}
.col-8{width: 66.66666667%;}
.col-9{width:75%;}
.col-6{width:50%;}
.col-12{width:100%}
#abe-preview {
    background: #fff;
    min-height: 100vh;
    width: 100vw;
    margin: 12px auto 0;
    max-width: 960px;
}
.logo{margin-bottom: 10px;}
.head-1{
    font-size: 26px;
    text-transform: capitalize;
    border-bottom: 1px solid #FF4949;
}
#sidebar {
    width: 250px;
    background-color: #D4F2FF;
}
.nav-bar{
	background-color: #FF4949 ;
	padding-top: 8px;
	padding-bottom: 8px;
	margin-left: -3px;
	margin-right: -3px;
	/*border-radius: 4px 4px 4px 4px;
	box-shadow: 0px 10px 4px -8px #272727;*/
}
.menu-btn{
    background-color: transparent;
    color: #fff;
	font-size:16px;
	font-family: 'Roboto', sans-serif;
}
.menu-btn i{margin-left:8px;}
.head-contact{
    padding: 0;
    list-style: none;
    text-align: right;
    padding: 19px 0;
}
.head-contact li{display:inline-block;padding: 10px 12px;}
.head-contact li:last-child{padding-right:0;}
.head-contact li i{color: #FF4949;font-size: 16px;}
.head-contact li a{color: #353535;text-decoration: none;}
/**** menu css start****/
.close-btn{text-align:right;}
.close-btn a{
    color: #FF4949;
    background-color: rgba(0,0,0,0.8);
    padding: 3px 4px;
    display: block;
    text-decoration: none;
}
.sidebar-menu {
    margin-left: 15px;
    margin-bottom: 0px;
}
.sidebar-menu h4 , .sidebar-item{
    background: none;
    border: none;
    color: #3a3a3a;
    line-height: 50px;
    font-size: 14px;
    font-weight: 400;
    text-transform: capitalize;
}
.sidebar-item{text-decoration:none;}
.sidebar-menu .fa-angle-down {
	width: 35px;
	height: 35px;
	position: absolute;
	right: 6px;
	top: 5px;
	color: #acacac;
	line-height: 35px;
	text-align: center;
	margin-right: 15px;
	font-size: 14px;
}
.sidebar-menu .nav-icon{
    color: #ffffff;
    background-color: #FF4949;
    padding: 7px;
    margin-right: 7px;
    border-radius: 19px;
}
.sidebar-menu ul {
	line-height: 0;
	padding: 0 0 15px 30px;
	list-style: none;
}
.sidebar-menu ul li{
	line-height:0;
}
.sidebar-menu ul li a {
	color: #6b757d;
	font-size: 14px;
	display: block;
	line-height: 30px;
	text-decoration: none;
}
.sidebar-menu ul li .fa-angle-right{margin-right: 15px;}
.sidebar-divider{
    font-family: 'Roboto', sans-serif;
    margin-bottom: 20px;
    font-size: 14px;
    padding-left: 20px;
    font-weight: 700;
    text-transform: uppercase;
    color: #495254;
    border-bottom: solid 1px rgba(0,0,0,0.1);
    border-top: solid 1px rgba(0,0,0,0.1);
    line-height: 40px;
    margin-bottom: 10px;
    background: #eee;
}
/**** menu css end****/
.banner-text{
	padding: 22px 0;
	text-align: center;
	font-size: 27px;
	color: #605858;
	text-shadow: 1px 1px 1px #bfbfbf;
}
.banner-text span{
    display: block;
    font-size: 22px;
    color: #464646;
}
.report-list{
    padding-left: 0;
    list-style: none;
}
.report-list li{display: block;padding: 24px 0px;border-bottom: 1px solid #eee;}
.report-list li:last-child{border:0;}
.report-list li amp-img{
	float:left;
    box-shadow: 2px 2px 3px #868686;
}
.rport-title a{
    font-weight: 700;
    color: rgba(0,0,0,0.8);
    transition: all .3s;
    font-size: 16px;
    text-decoration: none;
}
.btn-viewall{
	display: inline-block;
	float: right;
	background-color: #FF4949;
	color: #fff;
	font-weight: 700;
	text-transform: capitalize;
	text-decoration: none;
	padding: 4px 10px;
	border-radius: 4px;	
	transition:0.3s ease-in;
}
.btn-viewall:hover{
    background-color: #e49525;
    color: #fff;
}
.banner{
	background-color: #ffffff;
	border-bottom: 1px solid #ddd;
	margin-bottom: 15px;
	box-shadow: 0px 3px 4px #eee;
}
.search-form{
    display: table;
    margin: 0 auto 20px;
    width: 90%;
}
.search-form input{
    height: 35px;
    border: 1px solid #FF4949;
    width: 80%;
    padding: 0 10px;
}
.search-form button{
    height: 35px;
    width: 19%;
    background-color: #FF4949;
    color: #fff;
}
footer{
	background-color: #D4F2FF;
	color: rgba(0,0,0,0.8);
	margin-top: 35px;
}
.footer-title{
    color: #000;
    font-size: 14px;
    font-weight: 500;
    padding-bottom: 10px;
    border-bottom: 1px solid #18A0FB;
    text-transform: uppercase;
    margin-bottom: 20px;
}
.fot-loc{margin-bottom:10px;}
.fot-loc i{
	font-size: 44px;
	float: left;
	color: #FF4949;
	margin-right: 10px;
}
.footer-list{
	padding-left: 10px;
	list-style: none;
}
.footer-list li a{
    color: rgba(0,0,0,0.8);
    text-decoration: none;
    text-transform: capitalize;
	transition:0.3s ease-in;
}
.footer-list li a:hover{color:#FF4949;}
.footer-contact{margin-top: 5px;}
.footer-contact i{
	color: #FF4949;
	margin-right: 10px;
}
.footer-contact a{
    color: #fff;
    text-decoration: none;}
.copyright{
    background-color: #D4F2FF;
    color: rgba(0,0,0,0.9);
    font-weight: bold;
    text-align: center;
    font-size: 12px;
    border-top:1px solid #18A0FB;
}
.footer-contact-background{ background:#18A0FB; }
.footer-contact-border{ border-right: 1px solid #0077b5; }
.footer-contact-center{ text-align:center;}
.footer-contact-no{ margin-top:10px;margin-bottom:0px;color:#fff;margin-bottom: 10px;}
.footer-contact-no-inter{ color:#EFEFEF;font-size:15px;margin-bottom:10px;}
.footer-contact-no-span{ color:#EFEFEF;font-size:15px;}
@@media screen and (max-width:600px){
	.col-4 , .col-12, .col-8, .col-3, .col-9{width:100%;}
	.footer-title{margin-top:15px;}
	footer, .footer-list li{text-align:center;}
	.head-contact{display:none;}
	.banner-text{font-size: 19px;}
	.banner-text span{font-size:18px;}
	.report-list li amp-img{margin: 0 10px 0px 0;}
	.rport-detail{padding-left:0;}
}
@include('include.amp-design')
@include('include.amp-mobile-responsive')
@include('include.amp-performance')
</style>
<style amp-boilerplate>body{-webkit-animation:-amp-start 8s steps(1,end) 0s 1 normal both;-moz-animation:-amp-start 8s steps(1,end) 0s 1 normal both;-ms-animation:-amp-start 8s steps(1,end) 0s 1 normal both;animation:-amp-start 8s steps(1,end) 0s 1 normal both}@-webkit-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-moz-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-ms-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-o-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@@keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}</style><noscript><style amp-boilerplate>body{-webkit-animation:none;-moz-animation:none;-ms-animation:none;animation:none}</style></noscript>
</head>
<body>	
@if(config('site.analytics_id'))
<amp-analytics type="googleanalytics">
<script type="application/json">
{
  "vars": {
    "account": "{{ config('site.analytics_id') }}"
  },
  "triggers": {
    "trackPageview": {
      "on": "visible",
      "request": "pageview"
   }
  }
}
</script>
</amp-analytics>
@endif
<div id="abe-preview">
    @include('include.amp-header')
    <section class="nav-bar">
        <div class="row ">
        <div class="col-12">
        	<button class="menu-btn" on='tap:sidebar.open'>Menu<i class="fa fa-navicon"></i></button>
        </div>
        </div>
    </section>
    <section class="banner">
        <div class="row">
        	<div class="col-12">
            	<h1 class="banner-text">
                    End-less INSIGHTS for your business                 
                </h1>                
            </div>
			<div class="col-12">
				<form class="search-form" method="GET" action="{{ route('search', [], false) }}" target="_top">
@csrf
					<input type="search" placeholder="Search for market research report..." name="s">
					<button type="submit"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><circle cx="10.5" cy="10.5" r="6.5"/><path d="m16 16 5 5"/></svg></button>
				</form>
			</div>
        </div>
    </section>
<section>
<div class="row">
        <div class="col-12">
        	<h2 class="head-1">latest reports</h2>
        </div>
        <div class="col-12">
        <ul class="report-list">
        @php
            foreach ($reports as $key4 => $rep) {@endphp
            <li>
                <div class="rport-detail">
                <h3 class="rport-title">
                <a href="{{ route('rd_amp',$rep->slug) }}">{{ $rep->title }}</a>
                </h3>
                <p>{{ strip_tags($rep->description) }} ...</p>
                </div>
            </li> 
            @php } @endphp           
                  </ul>
        </div>
        <div class="col-12">
        	<a href="{{ route('all_reports') }}" class="btn-viewall">view all reports <i class="fa fa-arrow-right" aria-hidden="true"></i></a>
        </div>
</div>
<div class="row">
        <div class="col-12">
        	<h2 class="head-1">Industry News</h2>
        </div>
        <div class="col-12">
        <ul class="report-list">
        @php
            foreach ($indnews as $k => $news) {@endphp
            <li>
                <div class="rport-detail">
                <h3 class="rport-title">
                <a href="{{ route('news_details', $news->slug) }}">{{ $news->headline }}</a>
                </h3>
                <p>{!! substr(strip_tags($news->content), 0, 200) !!}  ...</p>
                </div>
            </li> 
            @php } @endphp           
                  </ul>
        </div>
        <div class="col-12">
        	<a href="{{ route('news_list') }}" class="btn-viewall">view all news <i class="fa fa-arrow-right" aria-hidden="true"></i></a>
        </div>
</div>
</section>
@include('include.amp-footer')
<div class="copyright">{{ date('Y') }} &copy; Copyright Eternity Insights</div>
</div><!--abe-preview main div-->
<amp-sidebar id="sidebar" layout="nodisplay" side="left">
		<div class="close-btn">
            <a href="#" on="tap:sidebar.toggle" class="ampstart-navbar-trigger items-start">
            Close <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m6 6 12 12M18 6 6 18" fill="none" stroke="currentColor" stroke-width="2"/></svg>
            </a>
			<div class="clear"></div>
		</div>
		<p class="sidebar-divider">Navigation</p>
		<div class="sidebar-menu">
			<a href="{{ route('home') }}" class="sidebar-item"><i class="nav-icon fa fa-home"></i>home</a>
		</div>
        <div class="sidebar-menu">
			<a href="{{ route('contactus') }}" class="sidebar-item"><i class="nav-icon fa fa-phone"></i>contact us</a>
		</div>
        <div class="sidebar-menu">
			<a href="{{ route('news_list') }}" class="sidebar-item"><i class="nav-icon fa fa-newspaper-o"></i>News</a>
        </div>        
        <div class="sidebar-menu">
			<a href="{{ route('all_reports') }}" class="sidebar-item"><i class="nav-icon fa fa-line-chart"></i>Reports Store</a>
		</div>
        <amp-accordion class="sidebar-menu">
            <section>
                <h4><i class="nav-icon fa fa-list-ul"></i>categories<i class="fa fa-angle-down"></i></h4>
                <ul>
                        <li><a href="{{ route('industry_list','healthcare') }}" ><i class="fa fa-angle-right"></i> Pharmaceuticals & Healthcare</a></li>
                        <li><a href="{{ route('industry_list','chemicals-materials') }}" ><i class="fa fa-angle-right"></i> Chemicals & Advanced Materials</a></li>
                        <li><a href="{{ route('industry_list','information-communication-technology') }}" ><i class="fa fa-angle-right"></i>Information & Communication Technology
                        </a></li>
                        <li><a href="{{ route('industry_list','consumer-goods') }}" ><i class="fa fa-angle-right"></i>Consumer Goods</a></li>
                        <li><a href="{{ route('industry_list','energy-power-utilities') }}" ><i class="fa fa-angle-right"></i> Energy, Power & Utilities</a></li>
                        <li><a href="{{ route('industry_list','agriculture') }}" ><i class="fa fa-angle-right"></i> Agriculture</a></li>

                        <li><a href="{{ route('industry_list','industry-services') }}" ><i class="fa fa-angle-right"></i>Industry Services</a></li>
                        <li><a href="{{ route('industry_list','business-and-financial-services') }}" > <i class="fa fa-angle-right"></i>Business & Financial Services</a></li>
                    
                    <li><a href="{{ route('industry_list','automotive-transportation') }}" > <i class="fa fa-angle-right"></i>Automotive & Transportation</a></li>
                        <li><a href="{{ route('industry_list','electronics-semiconductors') }}" > <i class="fa fa-angle-right"></i>Electronics & Semiconductors</a></li>
                        <li><a href="{{ route('industry_list','food-beverages') }}" ><i class="fa fa-angle-right"></i> Food & Beverages</a></li>
                        <li><a href="{{ route('industry_list','manufacturing-and-construction') }}" > <i class="fa fa-angle-right"></i>Manufacturing and Construction</a></li>
                        <li><a href="{{ route('industry_list','aerospace-defense') }}" ><i class="fa fa-angle-right"></i> Aerospace & Defense</a></li>
                        <li><a href="{{ route('industry_list','software-hardware') }}" ><i class="fa fa-angle-right"></i> Software & Hardware</a></li>
                        <li><a href="{{ route('industry_list','machinery-equipment') }}" ><i class="fa fa-angle-right"></i> Machinery & Equipment</a></li>
                </ul>
            </section>
        </amp-accordion>
        </amp-sidebar>
</body>
</html>
