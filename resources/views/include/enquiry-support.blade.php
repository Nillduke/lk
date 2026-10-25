                <div class="div-border">
                    <div class="license-type-box text-center mb-0" style="padding: 10px;">
                        <h4 class="box-title mb-0">Whats Included</h4>        
                    </div>
                    <div class="side-desc">
                        <ul>
                            <li><i class="fas fa-duotone fa-angle-right"></i> Market Dynamics</li>
                            <li><i class="fas fa-duotone fa-angle-right"></i> Competitive Analysis</li>
                            <li><i class="fas fa-duotone fa-angle-right"></i> Market Trends And Market Outlook</li>
                            <li><i class="fas fa-duotone fa-angle-right"></i> Market Share And Market Size</li>
                            <li><i class="fas fa-duotone fa-angle-right"></i> Opportunities And Customer Analysis</li>
                            <li><i class="fas fa-duotone fa-angle-right"></i> Product Pricing Research</li>
                        </ul>
                    </div>
                </div>
                <div class="div-border">
                    <div class="license-type-box text-center mb-0" style="padding: 10px;">
                        <h4 class="box-title mb-0">Need Assistance?</h4>        
                    </div>
                    <div class="need-assistance-desc">
                        We will be happy to help you find what you need. 
                        Please call us or write to us:<br> 
                        <a href="tel:+91-930-705-6852"> +91-930-705-6852 </a><br>
                        <a href="mailto:{{ config('site.sales_email') }}"> {{ config('site.sales_email') }} </a>
                    </div>
                </div>

<section class="enquiry-client-section"><h3>Our Clients</h3><div class="enquiry-client-grid">
@foreach(['Client-Honeywell.png'=>'Honeywell','Client-LG.png'=>'LG','Client-Philips.png'=>'Philips','Client-Evonik.png'=>'Evonik','intel.png'=>'Intel','Kodak.png'=>'Kodak'] as $image=>$client)
<div><img src="{{ asset('frontend/images/client-logo/'.$image) }}" alt="{{ $client }}" loading="lazy"></div>
@endforeach
</div></section>