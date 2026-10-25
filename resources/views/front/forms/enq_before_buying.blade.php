@extends('layout.default')
@section('head_meta')<meta name="robots" content="noindex, nofollow">@endsection
@section('content')
<div class="enquiry-design"><div class="container enquiry-breadcrumb"><nav aria-label="breadcrumb">
                    <ol class="breadcrumb site-breadcrumb mt-3">
                        <li class="breadcrumb-item"><a href="#" style="color:#000;"><i class="fas fa-home"></i></a></li>
                        <li class="breadcrumb-item"><a href="#"><strong>Enquiry Before Buying</strong></a></li>
                    </ol>
                </nav></div>
<section class="container enquiry-layout"><main class="enquiry-main"><div class="enquiry-report-header"><h2 class="enquiry-page-heading">{{ $form_for }}</h2>
                        <h2 class="req-sample-about">About Report:</h2>
                        <h1 class="report-title">
                            <a href="{{ route('rd_view',$report->slug) }}">{{ $report->title }}</a>
                        </h1><div class="title-toggle-slot"><button type="button" class="enquiry-title-toggle" aria-expanded="false" hidden>Read full title <span aria-hidden="true">+</span></button></div>
                        <p class="req-sample-title mb-2"><strong>Industry: </strong> {{ $report->category_name }} </p>
                        <p class="req-sample-title mb-2"><strong>Pages: </strong> {{ $report->pages }} </p>
                        <p class="req-sample-title mb-2"><strong>Published Date: </strong> {{ site_date($report->pub_date, 'M Y') }} </p>
                        <p class="req-sample-title mb-2"><strong>Price: </strong> $ {{ $report->lic_price }} </p>
                    </div><div class="enquiry-form-panel"><form action="{{ route('enquiry_buying_process',$report->id) }}" method="post" class="enquiry-form">
@csrf
                        <p style="display: none;"> <input type="hidden" name="pkjl" value="" /> </p>    
                        <div class="row mb-3">
                                <div class="col">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="floatingName" placeholder="Full Name" name="full_name" id="full_name" value="{{ old('full_name') }}" required>
                                        <label for="floatingName">Full Name</label>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="form-floating">
                                        <input type="email" class="form-control" id="floatingEmail" placeholder="Email Id" name="email_id" id="email_id" value="{{ old('email_id') }}" required>
                                        <label for="floatingEmail">Email Id</label>
                                    </div>
                                </div>
                               
                            </div>
                            <div class="row mb-3">
                                <div class="col">
                                    <div class="form-floating">
                                        <input type="tel" class="form-control" id="floatingPhone" placeholder="Phone No." name="contact_no" id="contact_no" value="{{ old('full_name') }}" required>
                                        <label for="floatingPhone">Phone No.</label>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="form-floating">
                                        <select class="form-select" id="floatingCountry" aria-label="Country" name="country" id="country" required>
                                            <option value="" selected>Select Country</option>
                                            @php 
                                            foreach ($countries as $country) { @endphp
                                                <option value="{{ $country->nicename }}" 
                                                @php if($country->nicename == old('country')){
                                                    echo 'selected';
                                                }@endphp>
                                                {{ $country->nicename }}
                                            </option>
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
                                    <textarea class="form-control" placeholder="Leave a comment here" id="floatingTextarea" style="height: 60px" name="message" id="message">{{ old('message') }}</textarea>
                                        <label for="floatingTextarea">Your Special Request</label>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group" style="margin-top: 20px;margin-bottom: 20px;">
                                <div class="row">
                                    <div class="col-12">
                                        
                                    </div>
                                </div>
                            </div>
                           
                            <div class="form-row">
                                <div class="col">
                                    <button class="btn btn-request-submit" name="Submit">Submit</button>
                                </div>
                            </div>
                        @if(config('site.recaptcha.enabled'))<div class="g-recaptcha my-3" data-sitekey="{{ config('site.recaptcha.site_key') }}"></div>@endif
</form></div></main><aside class="enquiry-support" aria-label="Report inclusions and assistance">@include('include.enquiry-support')</aside></section></div>
@endsection
@section('footer_script')
<script>document.querySelectorAll('.enquiry-form').forEach(form=>form.addEventListener('submit',event=>{if(!form.checkValidity()){event.preventDefault();event.stopPropagation();form.reportValidity();}form.classList.add('was-validated');}));</script>
@endsection
