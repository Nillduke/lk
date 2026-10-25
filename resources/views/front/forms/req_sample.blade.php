@extends('layout.default')
@section('head_meta')
<meta name="robots" content="noindex, nofollow" />
@endsection
@section('content')
   <!-- **Breadcrumbs** -->
   <div class="container">
        <div class="row">
            <div class="col-12">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb site-breadcrumb mt-3">
                        <li class="breadcrumb-item"><a href="#" style="color:#000;"><i class="fas fa-home"></i></a></li>
                        <li class="breadcrumb-item"><a href="#"><strong>Request Free Sample</strong></a></li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <section class="requestSample-bg">
        <div class="container">
            <div class="row">
                <div class="col-12 col-sm-4 col-md-4 col-lg-4">
                    <div class="sample-about">
                        <h2 class="req-sample-about">About Report:</h2>
                        <h1 class="req-sample-title">
                            <a href="{{ route('rd_view',$report->slug) }}">{{ $report->title }}</a>
                        </h1>
                        <p class="req-sample-title mb-2"><strong>Industry: </strong> {{ $report->category_name }} </p>
                        <p class="req-sample-title mb-2"><strong>Pages: </strong> {{ $report->pages }} </p>
                        <p class="req-sample-title mb-2"><strong>Published Date: </strong> {{ site_date($report->pub_date, 'M Y') }} </p>
                    </div>
                </div>
                <div class="col-12 col-sm-8 col-md-8 col-lg-8">
                    <div class="req-sample-box">
                        <!-- <h5 class="text-center p-3"><strong>Request Sample</strong></h5> -->
                        @include('include.messages')
                        <p style="color: red; font-size:13px">* marked fields are mandatory.</p>
                        <form action="{{ route('request_sample_process',$report->id) }}" method="post">
@csrf
                        <input type="hidden" name="report_id" value="{{ $report->id }}">
                        <p style="display: none;"> <input type="hidden" name="pkjl" value="" /> </p>
                            <div class="row mb-3">
                                <div class="col">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="floatingName" placeholder="Full Name" name="full_name" id="full_name" value="{{ old('full_name') }}" required>
                                        <label for="floatingName">Full Name *</label>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="form-floating">
                                        <input type="email" class="form-control" id="floatingEmail" placeholder="Email Id" name="email_id" id="email_id" value="{{ old('email_id') }}" required>
                                        <label for="floatingEmail">Email Id *</label>
                                    </div>
                                </div>
                                
                            </div>
                            <div class="row mb-3">
                                <div class="col">
                                    <div class="form-floating">
                                        <input type="tel" class="form-control" id="floatingPhone" placeholder="Phone No." name="contact_no" id="contact_no" value="{{ old('contact_no') }}" required>
                                        <label for="floatingPhone">Phone No. *</label>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="form-floating">
                                        <select class="form-select" id="floatingCountry" aria-label="Country" name="country" id="country" required>
                                            <option value="" selected>Select Country *</option>
                                            @php 
                                            foreach ($countries as $country) { @endphp
                                                <option value="{{ $country->nicename }}" 
                                                @php if($country->nicename == old('country')){
                                                    echo 'selected';
                                                }@endphp>{{ $country->nicename }}</option>
                                            @php } @endphp
                                           
                                        </select>
                                        <label for="floatingCountry">Select Country</label>
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="floatingCompany" placeholder="Company" name="company" id="company" value="{{ old('company') }}" required>
                                        <label for="floatingCompany">Company</label>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="form-floating">
                                    <textarea class="form-control" placeholder="Your Special Request" id="floatingTextarea" style="height: 60px" name="message" id="message">{{ old('message') }}</textarea>
                                        <label for="floatingTextarea">Your Special Requirement</label>
                                    </div>
                                </div>
                            </div>
                            <!-- <div class="row mb-3">
                                <div class="col">
                                    <div class="form-floating">
                                        <textarea class="form-control" placeholder="Leave a comment here" id="floatingTextarea" style="height: 60px" name="message" id="message"></textarea>
                                        <label for="floatingTextarea">Your Special Request</label>
                                    </div>
                                </div>
                            </div> -->
                            <!-- <div class="row mb-3">
                            
                                <div class="col-6 col-sm-6 col-md-4 my-auto">
                                    <span class="secCode align-middle">4345</span>
                                    <i class="fas fa-sync refreshBtn align-middle"></i>
                                </div>
                                <div class="col-6 col-sm-6 col-md-5">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="floatingSecurityCode" placeholder="Security Code">
                                        <label for="floatingSecurityCode">Security Code</label>
                                    </div>
                                </div>
                            </div> -->
                            <div class="form-row">
                                <div class="col">
                                    <button class="btn btn-request-submit" name="Submit">Submit</button>
                                </div>
                            </div>
                        @if(config('site.recaptcha.enabled'))<div class="g-recaptcha my-3" data-sitekey="{{ config('site.recaptcha.site_key') }}"></div>@endif
</form>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
   
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
