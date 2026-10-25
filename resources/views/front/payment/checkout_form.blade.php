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
                        <li class="breadcrumb-item"><a href="#"><strong>Checkout</strong></a></li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <section class="requestSample-bg">
        <form action="{{ route('checkoutFormProcess') }}" method="post">
@csrf
            <div class="container">
                <div class="row">
                    <div class="col-12 col-sm-12 col-md-6 col-lg-6">
                        <div class="billing-box">
                            <div class="billing-heading">
                                <h2 class="req-sample-about mb-0">Report Name</h2>
                            </div>
                            <div class="billing-wraper">
                                <h1 class="billing-repName interior-compact">
                                    {{ $report->title }}
                                </h1><div class="title-toggle-slot"><button type="button" class="interior-expand-button" aria-expanded="false">Read full title +</button></div>
                            </div>
                            <div class="billing-heading">
                                <h2 class="req-sample-about mb-0">Shopping Cart</h2>
                            </div>
                            <div class="billing-wraper">
                                <div class="interior-table-wrap"><table class="table table-bordered billing-table">
                                    <thead>
                                        <tr>
                                            <th scope="col">Report Id</th>
                                            <th scope="col">License Type</th>
                                            <th scope="col">Price</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>{{ $report->id }}</td>
                                            <td>
                                                <select id="ddl_lice_type" class="form-select"
                                                    style="height: 36px;width: 85%;" name="license" 
                                                    {{ ($is_global) ? 'disabled=""' : '' }}>
                                                @php
                                                foreach ($licenses as $key => $value) { @endphp
                                                    
                                                    <option value="{{ $value->lic_name.':'.$value->lic_price }}" @php if($license_type == $value->lic_name){@endphp selected="" @php }@endphp >
                                                {{ $value->lic_name.' - '.$value->lic_price }}
                                            </option>

                                                @php } @endphp                                                
                                                </select>
                                                @php if($is_global){@endphp
                                                    <input type="hidden" name="license" value="{{ $checkout->license_type.':'.$checkout->amt_tobe_paid }}">
                                                @php } @endphp                                                
                                            </td>
                                            <td>$ <span class="lice_price">{{ $license_price }}</span> </td>
                                        </tr>
                                        <tr>
                                            <th scope="row">Total</th>
                                            <td colspan="2"> $ <span class="lice_price">{{ $license_price }}</span></td>
                                        </tr>
                                    </tbody>
                                </table></div>
                            </div>
                            <div class="billing-heading">
                                <h2 class="req-sample-about mb-0">Continue For Payment Method</h2>
                            </div>
                            <div class="billing-wraper">
                                <!-- <div class="row mb-3">
                                    <div class="col-12">
                                        <div class="payment-method text-center">
                                            <input type="radio" name="payment" class="form-radio" checked>
                                            <button class="btn btn-ccavenue">
                                                <img src="/frontend/images/cc_avenue.png" height="70" alt="eternity ccavenue" class="img-fluid">
                                            </button>
                                        </div>
                                    </div>
                                </div> -->
                                <div class="row mb-3">
                                    <div class="col-12">
                                        <div class="payment-method text-center">
                                            <input type="radio" name="payment_mode" class="form-radio" value="PayPal" @checked(config('site.paypal.enabled')) @disabled(!config('site.paypal.enabled'))>
                                            <button type="button" class="btn btn-paypal">
                                                <img src="/frontend/images/paypal.png" style="height:30px;" alt="eternity paypal" class="img-fluid">
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <div class="payment-method text-center">
                                            <input type="radio" name="payment_mode" class="form-radio" value="Wire Transfer" @checked(!config('site.paypal.enabled'))>
                                            <button type="button" class="btn btn-debcred">
                                                <img src="/frontend/images/credit-debit.png" style="height:30px;" alt="eternity credit-debit" class="img-fluid">
                                                Wire Transfer
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-12 col-md-6 col-lg-6">
                        <div class="req-sample-box">
                        @include('include.messages')
                            <h5 class="text-center p-3"><strong>Billing Details</strong></h5>
                            <!-- <form>
@csrf -->
                                <div class="row mb-3">
                                <div class="col">
                                        <label class="interior-field-label" for="billingName">Full Name</label><input type="text" class="form-control" id="billingName" placeholder="Full Name" name="name" value="{{ (($is_global ? true : false) ? $checkout->name : old('name')) }}" required>
                                    </div>
                                    <div class="col">
                                        <input type="hidden" name="report_id" value="{{ (($is_global ? true : false) ? $checkout->report_id : session('chk_report')) }}">
                                        <label class="interior-field-label" for="billingEmail">Email Id</label><input type="email" class="form-control" id="billingEmail" placeholder="Email Id" name="email" value="{{ (($is_global ? true : false) ? $checkout->email : old('email')) }}" required>
                                    </div>
                                   
                                </div>
                                <div class="row mb-3">
                                    <div class="col">
                                        <label class="interior-field-label" for="billingPhone">Phone No.</label><input type="tel" class="form-control" id="billingPhone" placeholder="Phone No." name="contact_no" value="{{ (($is_global ? true : false) ? $checkout->contact_no : old('contact_no')) }}" required>
                                    </div>
                                    <div class="col">
                                        <label class="interior-field-label" for="billingCompany">Company</label><input type="text" class="form-control" id="billingCompany" placeholder="Company" name="company" value="{{ (($is_global ? true : false) ? $checkout->company : old('company')) }}" required>
                                    </div>
                                </div>
                                                        
                                                            
                                <div class="row mb-3">
                                    <div class="col">
                                        <label class="interior-field-label" for="billingCountry">Country *</label><select class="form-select" id="billingCountry" aria-label="Country"  name="country" id="country" required>
                                        <option value="" selected>Select Country</option>
                                            @php 
                                            foreach ($countries as $country) { @endphp
                                                <option value="{{ $country->nicename }}" 
                                                    @php if((($is_global ? true : false) ? $checkout->country : old('country')) == $country->nicename){@endphp 
                                                        selected 
                                                        @php }@endphp>
                                                    {{ $country->nicename }}
                                                </option>
                                            @php } @endphp
                                        </select>
                                    </div>
                                    
                                </div>
                                <div class="row mb-3">
                                    <div class="col-12">
                                        <label class="interior-field-label" for="billingAddress">Address</label><textarea class="form-control" placeholder="Address" id="billingAddress" name="address"
                                            style="height: 60px">{{ (($is_global ? true : false) ? $checkout->address : old('address')) }}</textarea>
                                    </div>
                                </div>
                                <!-- <div class="row mb-3">
                                
                                </div> -->
                                <div class="row mb-3">
                                    <div class="col">
                                        <div class="form-check">
                                            <label class="form-check-label">
                                                <input class="form-check-input" type="checkbox" name="remember" required>
                                                <span class="rememberMe">
                                                    I have read and agree to the <a href="{{ route('termsConditions') }}" target="_blank">Terms &amp; Condition</a>
                                                </span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="col">
                                        <input type="hidden" id="amtotpay" name="amt_tobe_paid" value="{{ $license_price }}" />
                                        <button class="btn btn-request-submit" name="Submit">Submit</button>
                                    </div>
                                </div>
                            <!-- </form> -->
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </section>
@endsection
@section('footer_script')
<script>
    $(document).ready(function() { 
        //console.log('Test');
        $('#ddl_lice_type').on('change', function() {
            var pr = this.value.split(':')[1];
            $(".lice_price").html(pr);
            $("#amtotpay").val(pr);           
        });
    
    });
</script>
@endsection
