@extends('layout.default')
@section('head_meta')
    
    <style>
        .enqfrm_box{
            background: #f9f9f9;
            padding: 20px 20px;
        }
        .form-control:focus{
            box-shadow: none !important; 
        }
        .btnsubmit{
            display: flex;
            justify-content: flex-end;
            align-items: center;
        }
        #question{
            font-size: 1.2rem;
        }
        .msgerr {
            color: red;
        }
        .msgok {
            color: green;
        }
        #enqform1 button[type="submit"] {
            background-color: #18b65f;
            border: 2px solid #18b65f;
            color: #ffffff;
            padding: 6px 28px;
            transition: 0.3s ease;
            display: block;
            font-size: 18px;
            /* box-shadow: 0 10px 10px -10px #000000; */
        }
        .help-block{
            color: red;
        }
        #enqform1 .form-control-feedback {
    right: 15px;
}
#enqform1 .selectContainer .form-control-feedback {
    right: 25px;
}
label[for="question"]{
    margin: 10px auto;
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
                        <li class="breadcrumb-item" aria-current="page">Contact Us</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="row">
            @include('include.messages')    
            <div class="col-12 col-sm-12 col-md-8 col-lg-8 cmscont">
                <h1>Contact Us</h1>
                <div class="enqfrm_box">                
                    <!-- <h5 class="text-center p-3"><strong>Request Sample</strong></h5> -->
                    @include('include.messages')
                    <form action="{{ route('contactUsProcess') }}" class="form-horizontal needs-validation" novalidate method="post" id="enqform1" enctype="multipart/form-data">
@csrf

                        <p style="display:none;">Leave This Empty: <input type="text" name="url2" /></p>  

                        <div class="row">
                            <div class="col-md-6 col-sm-6">
                                <div class="input-group">
                                    <label class="contact-field-label" for="full_name">Full Name</label>
                                    <input type="text" class="form-control" placeholder="Full Name" aria-label="Full Name" name="full_name" id="full_name" required>
                                    <div class="valid-feedback">
      Looks good!
    </div>
                                </div> 
                            </div>
                            <div class="col-md-6 col-sm-6">        
                                <div class="input-group">
                                    <label class="contact-field-label" for="email_id">Email Id</label>
                                    <input type="email" class="form-control" placeholder="Email Id" aria-label="Email Id" name="email_id" id="email_id" required>
                                </div>
                            </div>					
                        </div>

                        <div class="row">
                            <div class="col-md-6 col-sm-6">
                                <div class="input-group mt-3">
                                    <label class="contact-field-label" for="company">Company</label>
                                    <input type="text" class="form-control" placeholder="Company" aria-label="Company" name="company" id="company" required>
                                </div>
                            </div>
                            <div class="col-md-6 col-sm-6">
                            
                                <div class="input-group mt-3">
                                    <label class="contact-field-label" for="contact_no">Contact Number</label>
                                    <input type="text" class="form-control" placeholder="Contact Number" aria-label="Contact Number" name="contact_no" id="contact_no" required>
                                </div>
                            </div>					
                        </div>

                        <div class="row">
                            <div class="col-md-6 col-sm-6">
                                <div class="input-group mt-3">
                                    <label class="contact-field-label" for="country">Country</label>                
                                    <select name="country" class="form-control" aria-label="Country" id="country" required>
                                        <option value="">Select Country</option>
                                        @php foreach($countries as $country){@endphp

                                        <option value="{{ $country->nicename }}">{{ $country->nicename }} (+{{ $country->phonecode }})</option>

                                        @php }@endphp

                                    </select>
                                </div>           

                            </div>
                            <div class="col-md-6 col-sm-6">
                                <div class="input-group mt-3">
                                    <label class="contact-field-label" for="city">City</label>
                                    <input type="text" class="form-control" placeholder="City" aria-label="City" name="city" id="city" required>
                                </div>
                            </div>	
                        </div>

                        <div class="row">
                            <div class="col-md-12 col-sm-12">
                                <div class="input-group mt-3">
                                    <label class="contact-field-label" for="message">Message</label>
                                    <textarea type="text" class="form-control" placeholder="Message" aria-label="Message" name="message" id="message" required></textarea>
                                </div>   
                            </div>	
                        </div>

                        <div class="row">
                            <div class="col-md-6 col-sm-6 row">
                                <label for="question" class="col-md-12"> Security Question*</label>
                                <div class="col-md-4">
                                    <div class="sec-code">
                                        <span id="question"></span>
                                    </div>
                                </div>

                                <div class="col-md-8 col-sm-8">					
                                        <div class="cus-input">
                                            <!-- <label for="ans">Security Answer *:</label> -->
                                            <input type="text" class="form-control" id="ans" placeholder="Enter Security Answer" name="ans"/>
                                            <div id="valmessage" class="msgerr">Please verify.</div>
                                    </div>
                                </div>

                                </div>


                            <div class="col-md-6 col-sm-6 btnsubmit">
                                    <button type="submit" class="contactus-btn" name="cont_submit2" id="cont_submit"> Submit <i class="fas fa-paper-plane"></i></button>
                            </div>
                        </div>
                    @if(config('site.recaptcha.enabled'))<div class="g-recaptcha my-3" data-sitekey="{{ config('site.recaptcha.site_key') }}"></div>@endif
</form>
                </div>
            </div>
            <div class="col-12 col-sm-12 col-md-4 col-lg-4">
                @include('include.sidebar1')
            </div>
        </div>
    </div>
@endsection

@section('footer_script')
<script>document.querySelectorAll('.needs-validation').forEach(form=>form.addEventListener('submit',event=>{if(!form.checkValidity()){event.preventDefault();event.stopPropagation();form.reportValidity();}form.classList.add('was-validated');}));</script>
<script src="{{ asset('frontend/js/captcha.js') }}"></script>
@endsection
