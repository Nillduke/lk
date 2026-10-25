<!doctype html>
<html amp lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,minimum-scale=1,initial-scale=1,maximum-scale=1,user-scalable=no">
<meta name="application-name" content="eternity insights">
<meta name="apple-mobile-web-app-title" content="Market Research Reports">
<meta name="theme-color" content="#353535">
<title>{{ $title }}</title>
<meta name="description" content="{{ $meta_description }}">
<link rel="shortcut icon" href="/frontend/images/favicon.png" />
<link rel="preconnect" href="https://cdn.ampproject.org">
<link rel="preload" as="script" href="https://cdn.ampproject.org/v0.js" fetchpriority="high">
<script async src="https://cdn.ampproject.org/v0.js"></script>
<script async custom-element="amp-social-share" src="https://cdn.ampproject.org/v0/amp-social-share-0.1.js"></script>
<script async custom-element="amp-sidebar" src="https://cdn.ampproject.org/v0/amp-sidebar-0.1.js"></script>
<script async custom-element="amp-accordion" src="https://cdn.ampproject.org/v0/amp-accordion-0.1.js"></script>

<script async custom-element="amp-selector" src="https://cdn.ampproject.org/v0/amp-selector-0.1.js"></script>
<script async custom-element="amp-form" src="https://cdn.ampproject.org/v0/amp-form-0.1.js"></script>
@if(config('site.analytics_id'))<script async custom-element="amp-analytics" src="https://cdn.ampproject.org/v0/amp-analytics-0.1.js"></script>@endif
<!--Import Page Related Scripts-->
<!--AMP HTML files require a canonical link pointing to the regular HTML. If no HTML version exists, it should point to itself.-->
<link rel="canonical" href="{{ url(route('rd_view', $report->slug)) }}">
<link rel="icon" href="/frontend/images/favicon.png" type="image/x-icon">
<link rel="shortcut icon" href="/frontend/images/favicon.png" />

<!--<link rel="stylesheet" href="rd.css">-->

<meta name="apple-mobile-web-app-capable" content="yes"/>
<meta name="apple-mobile-web-app-status-bar-style" content="black">
<link rel="preload" as="font" type="font/woff2" crossorigin href="/frontend/fonts/roboto-400-common.e2df779d91d1.woff2">
<style amp-custom>
/* @charset "utf-8";
CSS Document */
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
.col-4 , .col-6, .col-12, .col-8, .col-3, .col-9, .rpo-img{
	position: relative;
	min-height: 1px;
	padding-right: 15px;
	padding-left: 15px;
}
.sample-download-cont{display: none;}
@@media (min-width: 992px){
.col-4 , .col-6, .col-12, .col-8, .col-3, .col-9{float:left;}
}
.col-3{width:25%;}
.col-4{width: 33.33333333%;}
.col-6{width:50%;}
.col-8{width: 66.66666667%;}
.col-9{width:75%;}
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
	color: #FF4949;
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
.rport-detail{padding-left: 90px;}
.rport-title a{
    font-weight: 700;
    color: rgba(0,0,0,0.8);
    transition: all .3s;
    font-size: 16px;
    text-decoration: none;
}
.rport-title a:hover{color:#FF4949;}
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
/* rd page css */
.rd-details-container{
	margin-top: 20px;
	margin-bottom: 30px;
	background-color: #f7f7f7;
	border-bottom: 1px solid #ddd;
	padding-top: 10px;
	padding-bottom: 10px;
	display: inline-block;
}
.rd-img{
    float: left;
    margin-top: 9px;
    box-shadow: 2px 2px 3px #868686;
}
.rd-details{padding-left: 135px;}
.rd-title{
	font-size: 20px;
    color: #0276A7;
    font-weight: 600;
    text-align: justify;
    line-height: 1.6;
}
.ampTabContainer {
	display: flex;
	flex-wrap: wrap;
	margin-bottom: 30px;
}
.tabButton[selected] {
	color: #fff;
    background-color: #FF4949;
    border-radius: 5px;
    font-weight: 600;
    transition: all .3s;
}
.tabButton {
	list-style: none;
	flex-grow: 1;
	text-align: center;
	cursor: pointer;
    border: 1px solid #FF4949;
    font-weight: 600;
    color: #000000;
    margin: 0px 20px 0 0;
    text-align: center;
    text-transform: uppercase;
    box-shadow: 0px 5px 5px -3px #999;
    padding: 5px;
}
amp-selector [option][selected]{outline:0;}
.tabContent {
	display: none;
	width: 100%;
	order: 1; /* must be greater than the order of the tab buttons to flex to the next line */
	margin-top:15px;
}
.tabButton[selected]+.tabContent {
	display: block;
}
.detail-list{
    padding: 0;
    list-style: none;
    display: inline-block;
    width: 100%;
}
.detail-list li{
    display: inline-block;
    margin-right: 16px;
    padding: 5px 0;
    width: 100%;
    border-bottom: 1px solid #ddd;
	text-align: right;
}
.detail-list li strong{
    display: inline-block;
    color: #686868;
    margin-right: 6px;
    float: left;
}

.btn-wrapper{text-align: center;}
.btn-sample{
    background-color: #04AFA5;
    padding: 5px 20px;
    color: #fff;
    border: 2px solid #04AFA5;
    border-radius: 5px;
    font-size: 14px;
    font-weight: 600;
    margin-bottom: 10px;
    box-shadow: 0px 1px 5px -1px #5b5b5b;
    text-decoration: none;
}
.btn-sample:hover {
    color: #04AFA5;
    background: #fff;
}
.btn-discount{
	background-color: #ff1c07;
	color: #fff;
	/* font-weight: 700; */
	padding: 4px 12px;
	text-decoration: none;
	display: inline-block;
	border-bottom: 2px solid #a21407;
}
.btn-sample i,.btn-discount i{margin-right:10px;}
.purch-box{
	width: 40%;
	margin: 0 auto;
	margin-bottom: 30px;
    background-color: #0276A7;
    padding:10px 0 0;
}
.purch-head{
    color: #fff;
    font-size: 18px;
    text-align: center;
    text-transform: uppercase;
    border-radius: 5px 5px 0 0;
	padding: 2px 10
}
.purch-body{}
.purch-option{
    padding:2px 10px;
}
input[type=radio]{
	display:none;
}
.purch-lable{
	display: block;
	cursor: pointer;
	position: relative;
	padding-left: 28px;
	padding-top: 4px;
	padding-bottom: 4px;
	border-bottom: 1px solid #4b5f75;
	font-size: 15px;
	color: #ffffff;
	text-transform: capitalize;
}
.purch-lable span{float:right;}
.purch-lable:before{
    content: "";
    display: block;
    width: 15px;
    height: 15px;
    margin-right: 20px;
    position: absolute;
    left: 0;
    bottom: 8px;
    background-color: #f2a325;
    border: 2px solid #bf801c;
}
input[type=radio]:checked + label:before {
	content: "\2713";
	color: #fff;
	font-size: 22px;
	text-align: center;
	line-height: 10px;
	font-weight: bold;
}
.btn-buynow{
    background-color: #FF4949;
    color: #fff;
    display: inherit;
    width: 100%;
    font-size: 16px;
    font-weight: 600;
    text-transform: uppercase;
    padding: 8px 10px;
	border-bottom: 3px solid #F01717;
}
.btn-buynow i, .btn-invoice i{margin-right: 10px;}
.btn-invoice{
	background-color: #214568;
	color: #fff;
	display: inherit;
	width: 100%;
	font-size: 16px;
	font-weight: 600;
	text-transform: uppercase;
	padding: 12px 10px 20px 10px;
	position: relative;
	border-radius: 0 23px;
    overflow: hidden;
}
.btn-invoice span{
    background-color: #fc8018;
    font-size: 10px;
    position: absolute;
    bottom: 0;
    width: 100%;
    left: 0;
}
.or-text{
	font-size: 16px;
    font-weight: 500;
    text-align: center;
    margin: 7px 0;
}
/* rd page css end*/
@@media screen and (max-width:600px){
	.col-4 , .col-6, .col-12, .col-8, .col-3, .col-9{width:100%;}
	.footer-title{margin-top:15px;}
	footer, .footer-list li{text-align:center;}
	.head-contact{}
	.banner-text{font-size: 19px;}
	.banner-text span{font-size:18px;}
	.report-list li amp-img{margin: 0 10px 0px 0;}
	.rport-detail{padding-left:0;}
	.head-contact li{padding:3px 0;}
	.rd-img{margin-right:10px;}
	.rd-details {padding-left: 0;}
	.rd-title{font-size: 18px;}
	.purch-box{width:100%;}
	.footer-contact{width:50%;float:left;}
	.fot-loc i{margin-bottom:0;width:100%;}
}
.sec-cures {
	background: #0276A7;
	text-align: center;
	color: #fff;
	padding: 10px 15px;
	margin-bottom: 20px;
	display: block;
	margin: 0 auto;
	width: 90%;
}
.sec-cures h3 {
    margin: 0;
    margin-bottom: 15px;
    font-size: 22px;
    font-weight: 700;
    color: #2b3f6c;
    padding: 7px 0;
    background-color: #fff;
}
.sec-cures a {
	display: inline-block;
	background-color: #fff;
	padding: 8px 16px;
	font-weight: 500;
	background: linear-gradient(to top left, #ec0b0b 0%, #FF4949 100%) repeat scroll 0 0;
	color: #ffffff;
	box-shadow: 0px 0px 4px #a1a2a2;
	border: 1px solid #f3b90f;
	border-radius: 2px;
	text-decoration: none;
	margin-top: 10px;
}
#reportSummary{font-size: 14px;}
#reportSummary h2{color: #0276a7;font-size: 15px;font-weight: 600;}
#reportSummary h3{color: #0276a7;font-size: 14px;font-weight: 600;}
#reportSummary h6{font-size: 15px;font-weight:700;}
#reportSummary table {margin: 20px 0;width: 95%;} 
#reportSummary table tbody, td, tfoot, th, thead, tr{border:1px solid #dee2e6;padding: 5px 0px 5px 10px;text-align: left;
vertical-align: middle;}
#reportSummary table tr:first-child td {background: #046893;border: 1px solid #006389;color: #fff;}
#reportSummary table tr:nth-child(2n+2) td {background: #f0f8ff;}
#reportSummary > .imglink{display: flex; flex-direction: column; align-items: center;}
#reportSummary > .imglink > img {width: 90%;border: 1px solid #d9edf7;margin: 25px auto;}
.sample-download{background-color: #0276A7;position: relative;padding: 20px 0; margin:50px 0;}
.sample-download img{position: absolute;bottom: 10px;}
.sample-download h3{color:#fff;font-size: 1.5rem;font-weight: 600;}
.btn-sample-dwn{color: #fff;border:2px solid #fff; border-radius: 5px;text-transform: uppercase;font-size: 0.9rem;
font-weight: 600;}
.btn-sample-dwn:hover{box-shadow: 0 0 8px 2px #062633;color:#fff;}
.sample-download-cont { background-color: #0276A7; position: relative; padding: 11px 0; margin: auto auto 15px auto;  width: 90%;}
.sample-download-cont p { color: #fff;font-size: 0.9rem;font-weight: 600; margin: 0;}
.btn-sample-dwn-cont {    color: #fff;    border: 2px solid #fff;    border-radius: 5px; text-transform: uppercase;    font-size: 0.65rem;    font-weight: 600;}
.btn-sample-dwn-cont:hover{box-shadow: 0 0 8px 2px #4cafd7;color:#fff;}
.my-auto{ display: flex; justify-content: center; }
@include('include.amp-design')
@include('include.amp-interior-design')
@include('include.amp-mobile-responsive')
@include('include.amp-performance')
</style>
<style amp-boilerplate>body{-webkit-animation:-amp-start 8s steps(1,end) 0s 1 normal both;-moz-animation:-amp-start 8s steps(1,end) 0s 1 normal both;-ms-animation:-amp-start 8s steps(1,end) 0s 1 normal both;animation:-amp-start 8s steps(1,end) 0s 1 normal both}@-webkit-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-moz-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-ms-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-o-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@@keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}</style><noscript><style amp-boilerplate>body{-webkit-animation:none;-moz-animation:none;-ms-animation:none;animation:none}</style></noscript>
<script async custom-element="amp-bind" src="https://cdn.ampproject.org/v0/amp-bind-0.1.js"></script>
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
    <section class="banner">
        <div class="row">
			<div class="col-12">
				<form class="search-form" method="GET" action="{{ route('search', [], false) }}" target="_top">
@csrf
					<input type="search" placeholder="Search for market research report..." name="s">
					<button type="submit"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><circle cx="10.5" cy="10.5" r="6.5"/><path d="m16 16 5 5"/></svg></button>
				</form>
			</div>
        </div>
    </section>
    <section class="nav-bar">
        <div class="row">
            <div class="col-12">
                <button class="menu-btn" on='tap:sidebar.open'>Menu<i class="fa fa-navicon"></i></button>
            </div>
        </div>
    </section>
    <section>
        <div class="row">
            <div class="col-12 rd-details-container">
                <amp-img src="/frontend/images/cover-1.png" alt="{{ $report->title }}" width="106" height="144" class="rd-img">
                </amp-img>
                <div class="rd-details">
                    <h1 class="rd-title" [class]="reportTitleExpanded ? 'rd-title is-expanded' : 'rd-title'">{{ $report->title }}</h1><button class="amp-title-toggle" type="button" on="tap:AMP.setState({reportTitleExpanded: !reportTitleExpanded})" [text]="reportTitleExpanded ? 'Show less' : 'Read full title'">Read full title</button>
                    <div>
                        <ul class="detail-list">
                            <li><strong>Category:</strong>{{ $report->category_name }}</li>
                            <li><strong>Published on:</strong>{{ site_date($report->pub_date, 'M Y') }}</li>
                            <li><strong>Pages:</strong>{{ $report->pages }}</li>
                            <li><strong>Formats:</strong>
                                <i class="fa fa-file-pdf-o" title="PDF Format"></i>
                                <i class="fa fa-file-excel-o" title="Excel Format"></i>
                                <i class="fa fa-dot-circle-o" title="CD Format"></i>
                            </li>
                            <li><strong>Report Code:</strong>{{ $report->id }}</li>
                        </ul>
                    </div>
                    <div class="btn-wrapper">
                        <a href="{{ route('request_sample', $report->id) }}" class="btn-sample"><i class="fa fa-download" aria-hidden="true"></i>Request PDF Sample</a>
                        <a href="{{ route('request_customization', $report->id) }}" class="btn-sample"><i class="fa fa-edit" aria-hidden="true"></i>Ask for Customization</a>
                        <a href="{{ route('enquiry_buying', $report->id) }}" class="btn-sample"><i class="fa fa-question-circle" aria-hidden="true"></i>Inquire Before Buying</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
<section>
<div class="col-12">
                <amp-selector role="tablist" layout="container" class="ampTabContainer">
                	<div role="tab" class="tabButton" selected option="a">Report Overview</div>
                	<div role="tabpanel"  class="tabContent" id="reportSummary">
                        <h3>Report Summary</h3>
                        @php /*$result = preg_replace('/(<img[^>]+>(?:<\/img>)?)/i', '$1</amp-img>', $report->content);
echo str_replace('<img', '<amp-img', $result);*/@endphp
@php 
$html = preg_replace('#<section class="sample-download-cont">(.*?)</section>#', '', $report->content);
$html = preg_replace('/<td (width|height)="\d*">/', '<td style="width: 46%;">', $html);
echo  amp_content(strip_tags($html,'<div><span><p><strong><ul><li><table><td><tr><thead><tbody><h2><h4><h3><h5><section><a>'));@endphp
                    </div>
                    <div role="tab" class="tabButton" option="b">Table Of Contents</div>
                    <div role="tabpanel" class="tabContent">
                        <h3>Table Of Contents</h3>
                          {!! amp_content($report->toc) !!}
                    </div>                                        
                </amp-selector>
            </div>
</section>
<div class="clear"></div>
<div class="row">
<!-- purchase option-->
    <div class="col-12">
        <div class="purch-box">
            <p class="purch-head">Choose License Type</p>
            <form action="{{ route('addPurchaseAmp', [], false) }}" method="GET" target="_top" >
@csrf
            <input type="hidden" name="report_id" value="{{ $report->id }}"/>            
                <div class="purch-body">
                @php 
                        foreach ($licenses as $key => $lic) {@endphp
                        <div class="purch-option">
                            <input type="radio" name="license" @php if($key == 0){@endphpchecked @php }@endphp id="option0{{ $key }}" value="{{ $lic->id.':'.$lic->lic_name.':'.$lic->lic_price }}">
                            <label class="purch-lable" for="option0{{ $key }}">{{ $lic->lic_name }} : <span>${{ $lic->lic_price }}</span></label>
                            
                        </div>
                           
                        @php } @endphp                    
                    <button type="submit" name="" value="" class="btn-buynow"><i class="fa fa-credit-card"></i>buy now</button>
                </div>
            </form>
        </div>
    </div>
    <div class="clear"></div>
<!-- purchase option end-->

</div>
      
   <div class="clear"></div>
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
