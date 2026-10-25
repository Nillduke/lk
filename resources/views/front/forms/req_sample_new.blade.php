@extends('layout.default')
@section('head_meta')<meta name="robots" content="noindex, nofollow">@endsection
@section('content')
<div class="enquiry-design"><div class="container enquiry-breadcrumb"><nav aria-label="breadcrumb">
                    <ol class="breadcrumb site-breadcrumb mt-3">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}" style="color:#000;"><i class="fas fa-home"></i></a></li>
                        <li class="breadcrumb-item"><a href="{{ route('industry_list', $report->category_slug) }}">{{ $report->category_name }}</a></li>
                        <li class="breadcrumb-item">{{ $report->keyword }} Market</li>
                        <li class="breadcrumb-item"><a href="#"><strong>Request Free Sample</strong></a></li>
                    </ol>
                </nav></div>
<section class="container enquiry-layout"><main class="enquiry-main"><div class="enquiry-report-header"><h1>Request Sample</h1>
                        <h2 class="report-title"><a href="{{ route('rd_view',$report->slug) }}">{{ $report->title }}</a></h2><div class="title-toggle-slot"><button type="button" class="enquiry-title-toggle" aria-expanded="false" hidden>Read full title <span aria-hidden="true">+</span></button></div>
                        <ul class="list-inline rd-info mt-3 mb-2">
                                <li class="list-inline-item rd-details-contents">
                                    No of Pages:  {{ $report->pages }}</li>
                                <li class="list-inline-item rd-details-contents">
                                    Published On: {{ site_date($report->pub_date, 'M Y') }}</li>
                                <li class="list-inline-item rd-details-contents">
                                    Format: PDF
                                </li>
                                <li class="list-inline-item rd-details-contents">
                                    Report ID : {{ $report->id }} </li>
                            </ul>
                    </div><div class="enquiry-form-panel"><form action="{{ route('request_sample_process',$report->id) }}" class="form-horizontal needs-validation enquiry-form" novalidate method="post" id="enqform1" enctype="multipart/form-data">
@csrf
                    <input type="hidden" name="report_id" value="{{ $report->id }}">
                        <p style="display: none;"> <input type="hidden" name="pkjl" value="" /> </p>
                        <p style="font-size: 12px; color:red;">* marked fields are mandatory</p>
                    <div class="row">
                        <div class="col-md-6 col-sm-6">
                            <div class="input-group">
                                <label class="enquiry-field-label" for="full_name">Full Name *</label>
                                <input type="text" class="form-control" placeholder="Full Name *" aria-label="Full Name" aria-describedby="basic-addon1" value="{{ old('full_name') }}" name="full_name" id="full_name" required>
                            </div> 
                        </div>
                        <div class="col-md-6 col-sm-6">        
                            <div class="input-group">
                                <label class="enquiry-field-label" for="email_id">Email Id *</label>
                                <input type="email" class="form-control" placeholder="Email Id *" aria-label="Email Id" aria-describedby="basic-addon1" value="{{ old('email_id') }}" name="email_id" id="email_id" required>
                            </div>
                        </div>					
                    </div>

                    <div class="row">
                        <div class="col-md-6 col-sm-6">
                            <div class="input-group mt-3">
                                <label class="enquiry-field-label" for="company">Company *</label>
                                <input type="text" class="form-control" placeholder="Company *" aria-label="Company" aria-describedby="basic-addon1" value="{{ old('company') }}" name="company" id="company" required>
                            </div>
                        </div>
                        <div class="col-md-6 col-sm-6">
                        
                            <div class="input-group mt-3">
                                <label class="enquiry-field-label" for="contact_no">Contact Number *</label>
                                <input type="text" class="form-control" placeholder="Contact Number *" aria-label="Contact Number" value="{{ old('contact_no') }}" name="contact_no" id="contact_no" required>
                            </div>
                        </div>					
                    </div>

                    <div class="row">
                        <div class="col-md-6 col-sm-6">
                            <div class="input-group mt-3">
                                <label class="enquiry-field-label" for="country">Select Country *</label>                
                                <select name="country" class="form-control" aria-label="Country" aria-describedby="basic-addon1" id="country" required>
                                    <option value="">Select Country *</option>
                                    @php foreach($countries as $country){@endphp

                                    <option value="{{ $country->nicename }}" @selected(old('country') == $country->nicename)>{{ $country->nicename }} (+{{ $country->phonecode }})</option>

                                    @php }@endphp

                                </select>
                            </div>           

                            </div>
                            <div class="col-md-6 col-sm-6">
                                <div class="input-group mt-3">
                                    <label class="enquiry-field-label" for="message">To receive personalized service, please share your research needs.</label>
                                    <textarea type="text" class="form-control" placeholder="To receive personalized service, please share your research needs." aria-label="Message" aria-describedby="basic-addon1" name="message" id="message" rows="3">{{ old('message') }}</textarea>
                                </div> 
                            </div>	
                        </div>
                        @php /*@endphp<div class="row">
                            <div class="col-md-6 col-sm-6 row">
                                <label for="question" class="col-md-12"> Security Question*</label>
                                <div class="input-group">
                                    <span class="input-group-text" id="basic-addon1"> <div class="sec-code">
                                            <span id="question"></span>
                                        </div></span>
                                    <input type="text" class="form-control" id="ans" placeholder="Enter Security Answer" name="ans"/>
                                </div>
                                <div id="valmessage" class="msgerr">Please verify.</div>            
                                    </div>   
                            </div>@php */@endphp
                            <div class="form-group" style="margin-top: 20px;">
                                <div class="row">
                                    <div class="col-12">
                                        
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12 col-sm-12 acc_consent"><label class="consent-control" for="accc">
                                    <input type="checkbox" name="acc" id="accc" required> &nbsp;&nbsp; Yes, I have read the <a href="{{ route('privacyPolicy') }}" target="_blank">Privacy Policy</a> This website is secure and your personal details are safe.
                                </label></div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 col-sm-6 btnsubmit">
                                        <button type="submit" class="contactus-btn" name="cont_submit2" id="cont_submit"> Submit <i class="fas fa-paper-plane"></i></button>
                                </div>
                            </div>
                @if(config('site.recaptcha.enabled'))<div class="g-recaptcha my-3" data-sitekey="{{ config('site.recaptcha.site_key') }}"></div>@endif
</form></div></main><aside class="enquiry-support" aria-label="Report inclusions and assistance">@include('include.enquiry-support')</aside></section></div>
@endsection
@section('footer_script')
<script>document.querySelectorAll('.enquiry-form').forEach(form=>form.addEventListener('submit',event=>{if(!form.checkValidity()){event.preventDefault();event.stopPropagation();form.reportValidity();}form.classList.add('was-validated');}));</script>
@endsection
