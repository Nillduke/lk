<form action="{{ route('request_toc_process',$report->id) }}" class="form-horizontal needs-validation" novalidate method="post" id="enqform1" enctype="multipart/form-data">
@csrf
                    <input type="hidden" name="report_id" value="{{ $report->id }}">
                        <p style="display: none;"> <input type="hidden" name="pkjl" value="" /> </p>
                        <p style="font-size: 12px; color:red;">* marked fields are mandatory</p>
                    <div class="row">
                        <div class="col-md-6 col-sm-6">
                            <div class="input-group">
                                <span class="input-group-text" id="basic-addon1"><i class="fas fa-user"></i></span>
                                <input type="text" class="form-control" placeholder="Full Name *" aria-label="Full Name" aria-describedby="basic-addon1" name="full_name" id="full_name" required>
                            </div> 
                        </div>
                        <div class="col-md-6 col-sm-6">        
                            <div class="input-group">
                                <span class="input-group-text" id="basic-addon2"><i class="fas fa-envelope"></i></span>
                                <input type="email" class="form-control" placeholder="Email Id *" aria-label="Email Id" aria-describedby="basic-addon2" name="email_id" id="email_id" required>
                            </div>
                        </div>					
                    </div>

                    <div class="row">
                        <div class="col-md-6 col-sm-6">
                            <div class="input-group mt-3">
                                <span class="input-group-text" id="basic-addon3"><i class="fas fa-building"></i></span>
                                <input type="text" class="form-control" placeholder="Company *" aria-label="Company" aria-describedby="basic-addon3" name="company" id="company" required>
                            </div>
                        </div>
                        <div class="col-md-6 col-sm-6">
                        
                            <div class="input-group mt-3">
                                <span class="input-group-text" id="basic-addon4"><i class="fas fa-phone-alt"></i></span>
                                <input type="text" class="form-control" placeholder="Contact Number *" aria-label="Contact Number" name="contact_no" id="contact_no" required>
                            </div>
                        </div>					
                    </div>

                    <div class="row">
                        <div class="col-md-6 col-sm-6">
                            <div class="input-group mt-3">
                                <span class="input-group-text" id="basic-addon5"><i class="fas fa-map-marker-alt"></i></span>                
                                <select name="country" class="form-control" aria-label="Country" aria-describedby="basic-addon5" id="country" required>
                                    <option value="">Select Country *</option>
                                    @php foreach($countries as $country){@endphp

                                    <option value="{{ $country->nicename }}">{{ $country->nicename }} (+{{ $country->phonecode }})</option>

                                    @php }@endphp

                                </select>
                            </div>           

                            </div>
                            <div class="col-md-6 col-sm-6">
                                <div class="input-group mt-3">
                                    <span class="input-group-text" id="basic-addon6"><i class="fas fa-pencil-alt"></i></span>
                                    <textarea class="form-control" placeholder="Message" aria-label="Message" aria-describedby="basic-addon6" name="message" id="message" rows="1"></textarea>
                                </div> 
                            </div>	
                        </div>
                        <div class="form-group" style="margin-top: 20px;margin-bottom: 20px;">
                            <div class="row">
                                <div class="col-12">
                                    
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 col-sm-12 acc_consent"><label class="consent-control" for="accc">
                                <input type="checkbox" name="acc" id="accc" required> &nbsp;&nbsp; Yes, I have read the <a href="{{ route('termsConditions') }}" target="_blank">Privacy Policy</a> This website is secure and your personal details are safe.
                            </label></div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 col-sm-6 btnsubmit">
                                    <button type="submit" class="contactus-btn" name="cont_submit2" id="cont_submit"> Submit <i class="fas fa-paper-plane"></i></button>
                            </div>
                        </div>
                @if(config('site.recaptcha.enabled'))<div class="g-recaptcha my-3" data-sitekey="{{ config('site.recaptcha.site_key') }}"></div>@endif
</form>
