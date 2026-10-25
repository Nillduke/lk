@extends('layout.default')
@section('head_meta')
    
    <style>
        #g-recaptcha-response {
    display: block !important;
    position: absolute;
    margin: -78px 0 0 0 !important;
    width: 302px !important;
    height: 76px !important;
    z-index: -999999;
    opacity: 0;
}
.new-frmb {
    margin-top: 25px;
    margin-bottom: 25px;
    box-shadow: 0px 0px 12px #ddd;
    padding: 50px 15px 50px 0px;
    background-color: #deeaf1;
}
.new-absec {
    position: absolute;
    height: 100%;
    width: 50%;
    background-color: #1d4f94;
    left: -20%;
    background-size: cover;
    background-repeat: no-repeat;
    color: #fff;
}
.new-ccontainer {
    padding-left: 35%;
    padding-right: 5%;
    position: relative;
    display: inline-block;
    width: 100%;
}
.new-absec .body {
    background-color: #1a78a2d4;
    position: absolute;
    width: 100%;
    height: 100%;
    top: 0;
    left: 0;
    padding: 30px;
}
.new-absec h2 {
    font-weight: 700;
    font-size: 24px;
    margin: 0 0 20px;
}
.new-cform h2 span {
    font-size: 15px;
    display: block;
    margin-top: 11px;
    font-weight: 500;
}
.new-absec .body p {
    position: relative;
    padding-left: 50px;
    margin-bottom: 25px;
}
.new-absec h2 span {
    font-size: 14px;
    display: block;
    font-weight: 400;
    margin-top: 10px;
    line-height: 1.5;
}
.ei-nform {
    padding: 10px 15px;
    margin-bottom: 20px;
}
.new-cform h2 {
    font-size: 22px;
    font-weight: 700;
    margin: 0 0 20px;
}
.form-horizontal .form-group {
    margin-right: -15px;
    margin-left: -15px;
}
.has-feedback {
    position: relative;
}
.form-group {
    margin-bottom: 15px;
}
.new-cform .form-control {
    border: 1px solid #ddd;
    border-radius: 0;
    box-shadow: none;
    height: 40px;
}
.new-cform button[type="submit"] {
    background-color: #18b65f;
    border: 2px solid #18b65f;
    color: #ffffff;
    padding: 6px 28px;
    transition: 0.3s ease;
    display: block;
    font-size: 18px;
    /* box-shadow: 0 10px 10px -10px #000000; */
}
.new-absec .body p i {
    position: absolute;
    left: 0;
    top: 3px;
    height: 36px;
    width: 36px;
    line-height: 35px;
    text-align: center;
    font-size: 18px;
    color: #f7dd2f;
    border: 2px solid #fff;
}
.new-absec .body p strong {
    display: block;
    font-size: 16px;
    font-weight: 500;
}
.new-absec .body p span {
    display: block;
    font-size: 13px;
}
@@media (max-width: 768px){
.new-absec, .new-absec .body {
    position: relative;
    width: 100%;
    left: 0;
}
.new-frmb, .new-ccontainer {
    padding: 0;
}
}
@@media (max-width: 768px){
.new-cform {
    margin-top: 20px;
}
.ei-nform {
    padding-bottom: 20px;
    border-bottom: 1px solid #ddd;
}
.col-sm-12, .col-sm-8 {
    padding: 0 15px;
}
}
input, textarea, select, textarea::-webkit-input-placeholder, input::-webkit-input-placeholder {
    font-size: 12px !important;
}

    </style>
@endsection
@section('content')
<!-- **Breadcrumbs** -->
<div class="container">
        <div class="row">
            <div class="col-12">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb site-breadcrumb mt-3">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}" style="color:#000;"><i class="fas fa-home"></i></a></li>
                        <li class="breadcrumb-item"><a href="#"><strong>Search</strong></a></li>
                        <li class="breadcrumb-item active" aria-current="page">Search Results for "{{ $s }}"</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
    @php //if ($s && count($list) > 0) {@endphp       
    
    <div class="container">
        <div class="row">
            <div class="col-12">
                <h2>{{ count($list) > 0 ? 'Search' : 'No results for keyword, try another!' }}</h2>
                <div class="search-results">
                    @php 
                    if(count($list) > 0){
                        foreach ($list as $key => $report) { @endphp
                        <div class="latRepbox">
                        <div class="d-flex">
                            <div class="flex-shrink-0">
                                <img src="/frontend/images/report-thumbnail/report_thumbnail_4.png" class="img-fluid"
                                    alt="eternity latest report" width="75">
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h4 class="similar-rep-title interior-compact">
                                    <a href="{{ route('rd_view',$report->slug) }}">{{ $report->title }}</a>
                                </h4><div class="title-toggle-slot"><button type="button" class="interior-expand-button" aria-expanded="false">Read full title +</button></div>
                                <ul class="list-inline rd-info latrep-info mt-2 mb-3">
                                    <li class="list-inline-item latrep-contents">{{ site_date($report->pub_date, 'M Y') }}</li>
                                    <li class="list-inline-item latrep-contents">{{ $report->category_name }}</li>
                                    <li class="list-inline-item latrep-contents">{{ $report->pages }} PAGES</li>
                                    <li class="list-inline-item latrep-contents">$ {{ $report->lic_price }}</li>
                                </ul>
                                <a href="{{ route('rd_view',$report->slug) }}" class="btn btn-latrepView">View Report &amp; ToC</a>
                                <a href="{{ route('request_sample',$report->id) }}" class="btn btn-latrepView">Download Free Sample</a>
                            </div>
                            </div>
                        </div>
                        @php }
                        } else {@endphp                            
                        @php }@endphp
                </div>
                
            </div>
            
        </div>
    </div>
   @if(isset($searchPagination))<div class="container catalogue-pagination">{{ $searchPagination->links() }}</div>@endif
   @php //}@endphp  
   @php if ($s && count($list) == 0) {@endphp                        
    <!-- FORM -->
    <div class="container">
        <div class="row">
            <div class="col-12 col-md-8 offset-md-3 new-frmb">
                <div class="new-ccontainer">
                @php if(session()->has('error_message')){
                    echo session()->get('error_message');
                    session()->forget('error_message');
                    @endphp
                @php } @endphp
                <div class="new-absec">
                    <div class="body">
                        <h2>CONTACT US</h2>					
                        <p><i class="fa fa-phone"></i> <strong>Phone</strong><span>+91 -930-705-6852</span></p>
                        <p><i class="fa fa-envelope"></i> <strong>Mail Us</strong><span>{{ config('site.sales_email') }}</span></p>
                        <p><i class="fa fa-map-marker"></i><span>Office No.A-3/308, <br>3rd Floor Premnagar Complex, <br>Mumbai-MH, 400605, India.</span></p>
                    </div>
                </div>
            <form action="{{ route('search_form_process') }}" method="post" class="form-horizontal ei-nform new-cform" id="sampleForm">
@csrf
                <input type="hidden" name="s" value="{{ $s }}">
                @php $ferrors = session()->get('error_messages');@endphp
                    <h2>REQUEST A CALL BACK 
                    <span>Our customer support team will be happy to answer your needs</span></h2>
                <!--<p>Fill in the following details and we shall get in touch with you.</p>-->
                    <div class="form-group">
                    <div class="row">
                        <div class="col-12 col-md-6">
                            <label class="interior-field-label" for="name">Enter Your Name *</label><input type="text" class="form-control" id="name" placeholder="Enter Your Name" name="full_name" required="" value="{{ old('full_name') }}">
                            @php 
                            if(isset($ferrors['full_name'])){@endphp
                                <small class="text-danger">{{ $ferrors['full_name'] }}</small>  
                            @php } @endphp
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="interior-field-label" for="email_id">Enter Your Email *</label><input type="text" class="form-control" id="email_id" placeholder="Enter Your Email" name="email_id" required="" value="{{ old('email_id') }}">
                            @php 
                            if(isset($ferrors['email_id'])){@endphp
                                <small class="text-danger">{{ $ferrors['email_id'] }}</small>  
                            @php } @endphp
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="row">
                    <div class="col-12 col-md-6">
                            <label class="interior-field-label" for="contact_no">Enter Contact Number *</label><input type="text" class="form-control" id="contact_no" placeholder="Enter Contact Number" name="contact_no" required="" value="{{ old('contact_no') }}" >
                            @php 
                            if(isset($ferrors['contact_no'])){@endphp
                                <small class="text-danger">{{ $ferrors['contact_no'] }}</small>  
                            @php } @endphp
                        </div>
                            <div class="col-12 col-md-6">                            
                                <select name="country" id="country" class="form-control">
                                        <option value="" selected>Select Country</option>
                                        @php 
                                        foreach ($countries as $country) { @endphp
                                            <option value="{{ $country->nicename }}" 
                                            @php if($country->nicename == old('country')){
                                                echo 'selected';
                                            }@endphp>{{ $country->nicename }}</option>
                                        @php } @endphp
                                </select>
                                @php 
                                if(isset($ferrors['country'])){@endphp
                                    <small class="text-danger">{{ $ferrors['country'] }}</small>  
                                @php } @endphp
                            </div>
                        
                    </div>
                </div>
                
                <div class="form-group">
                    <div class="row">
                    <div class="col-12 col-md-6">
                            <label class="interior-field-label" for="company">Enter Company *</label><input type="text" class="form-control" id="company" placeholder="Enter Company" name="company" required="" value="{{ old('full_name') }}">
                            
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="interior-field-label" for="message">Enter Message</label><textarea class="form-control" rowa="2" id="message" name="message" placeholder="Enter Message">{{ old('message') }}</textarea>
                            @php 
                            if(isset($ferrors['message'])){@endphp
                                <small class="text-danger">{{ $ferrors['message'] }}</small>  
                            @php } @endphp
                        </div>
                    </div>
                    
                </div>
                <div class="form-group">
                    <div class="row">
                        <div class="col-12">
                            
                        </div>
                    </div>
                </div>
                <div class="col-sm-12 row">	
                <button type="submit">Submit <i class="fa fa-paper-plane" aria-hidden="true"></i></button>
                </div>
	        @if(config('site.recaptcha.enabled'))<div class="g-recaptcha my-3" data-sitekey="{{ config('site.recaptcha.site_key') }}"></div>@endif
</form>
	</div>
	</div>
	</div>
	</div>
    @php }@endphp  
    @php session()->forget('error_messages');@endphp
@endsection
@section('footer_script')
<script>
    window.onload = function() {
    var $recaptcha = document.querySelector('#g-recaptcha-response');

    if($recaptcha) {
        $recaptcha.setAttribute("required", "required");
    }
};
</script>

@endsection
