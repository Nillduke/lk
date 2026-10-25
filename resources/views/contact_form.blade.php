@extends('layout.default')
@section('head_meta')
    
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
            <div class="col-12 col-sm-12 col-md-8 col-lg-8">
                <h2>Contact Us</h2>
                <div class="req-sample-box">                
                    <!-- <h5 class="text-center p-3"><strong>Request Sample</strong></h5> -->
                    @include('include.messages')
                    <form action="{{ route('contactUsProcess') }}" method="post">
@csrf
                        <div class="row mb-3">
                            <div class="col">
                                <div class="form-floating">
                                    <input type="email" class="form-control" id="floatingEmail" placeholder="Email Id" name="email_id" id="email" value="{{ old('email_id') }}">
                                    <label for="floatingEmail">Email Id</label>
                                </div>
                            </div>
                            <div class="col">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="floatingName" placeholder="Full Name" name="full_name" id="name" value="{{ old('full_name') }}">
                                    <label for="floatingName">Full Name</label>
                                </div>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col">
                                <div class="form-floating">
                                    <input type="tel" class="form-control" id="floatingPhone" placeholder="Phone No." name="contact_no" id="contact_no" value="{{ old('contact_no') }}">
                                    <label for="floatingPhone">Phone No.</label>
                                </div>
                            </div>
                            <div class="col">
                                <div class="form-floating">
                                    <select class="form-select" id="floatingCountry" aria-label="Country" name="country" id="country">
                                        <option value="" selected>Select Country</option>
                                        @php 
                                        foreach ($countries as $country) { @endphp
                                            <option value="{{ $country->nicename }}">{{ $country->nicename }}</option>
                                        @php } @endphp
                                        
                                    </select>
                                    <label for="floatingCountry">Select Country</label>
                                </div>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="floatingCompany" placeholder="Company" name="company" id="company" value="{{ old('company') }}">
                                    <label for="floatingCompany">Company</label>
                                </div>
                            </div>
                            <div class="col">
                                <div class="form-floating">
                                <textarea class="form-control" placeholder="Message" id="floatingTextarea" style="height: 60px" name="message" id="message">{{ old('message') }}</textarea>
                                    <label for="floatingTextarea">Message</label>
                                </div>
                            </div>
                        </div>
                        <!-- <div class="row mb-3">
                            <div class="col">
                                <div class="form-floating">
                                    <textarea class="form-control" placeholder="Leave a comment here" id="floatingTextarea" style="height: 60px" name="message" id="message"></textarea>
                                    <label for="floatingTextarea">Message</label>
                                </div>
                            </div>
                        </div> -->
                        <div class="row mb-3">
                        
                            <!-- <div class="col-6 col-sm-6 col-md-4 my-auto">
                                <span class="secCode align-middle">4345</span>
                                <i class="fas fa-sync refreshBtn align-middle"></i>
                            </div>
                            <div class="col-6 col-sm-6 col-md-5">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="floatingSecurityCode" placeholder="Security Code">
                                    <label for="floatingSecurityCode">Security Code</label>
                                </div>
                            </div> -->
                        </div>
                        <div class="form-row">
                            <div class="col">
                                <button class="btn btn-request-submit" name="Submit">Submit</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="col-12 col-sm-12 col-md-4 col-lg-4">
                @include('include.sidebar1')
            </div>
        </div>
    </div>
@endsection
