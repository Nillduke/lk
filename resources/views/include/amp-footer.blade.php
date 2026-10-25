<footer class="amp-site-footer" data-shared-footer>
    <div class="row footer-contact-background" >
        <div class="col-6 footer-contact-border" >
            <div class="footer-contact-center"><h4 class="footer-contact-no"><span class="fa fa-phone"> &nbsp;&nbsp;</span>+91-930-705-6852</h4></div>
        </div>
        <div class="col-6">
            <div class="footer-contact-center"><h4 class="footer-contact-no"><span class="fa fa-envelope"> &nbsp;&nbsp;</span>{{ config('site.sales_email') }}</h4></div>
        </div>
        </div>
    <div class="row">
        <div class="col-4">
            <p class="footer-title">Reach us at</p>
            <p class="fot-loc">
                <i class="fa fa-map-marker " aria-hidden="true"></i>
                Office No.A-3/308, 3rd Floor Premnagar Complex, Mumbai-MH, 400605, India.<br>
            </p>
        </div>
        <div class="col-4">
            <p class="footer-title">Explore</p>
            <ul class="footer-list">
            <li><a href="{{ route('services') }}">Services</a></li>
            <li><a href="{{ route('faqs') }}">FAQ</a></li>
            <li><a rel="nofollow" href="{{ route('howToOrder') }}">How To Order</a></li>
            <li><a rel="nofollow" href="{{ route('refundPolicy') }}">Refund And Cancellation</a></li>
            <li><a href="{{ route('disclaimer') }}">Disclaimer</a></li>
            <li><a rel="nofollow" href="{{ route('termsConditions') }}">Terms &amp; Conditions</a></li>
            <li><a href="{{ route('html_sitemap') }}">Sitemap</a></li>
            </ul>
        </div>
        <div class="col-4">
            <p class="footer-title">stay connected</p>
            <p class="footer-contact">
                <amp-social-share type="facebook" data-param-app_id="133186467213781"></amp-social-share>
                <amp-social-share type="linkedin"></amp-social-share>
                <amp-social-share type="gplus"></amp-social-share> 
                <amp-social-share type="twitter"></amp-social-share>
            </p>
        </div>
    </div>
</footer>