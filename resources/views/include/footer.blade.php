<!-- **Footer** -->
<footer class="footer-bg site-footer" data-shared-footer>
        <div class="container pb-5 pt-5">
            <div class="row">
                <div class="col-lg-9 col-md-9">
                    <div class="row">
                        <div class="col-sm-5 wow fadeInLeft">
                            <img src="{{ url('frontend/images/eternity-white-logo.svg') }}" alt="Eternity Insights Logo" width="250"
                                class="img-fluid" loading="lazy" />
                            <p class="footer-detail">Eternity Insights is a specialized research and consulting company, that offers syndicated reports, custom research, and deep-dive consulting solutions. We provide tailormade end-to-end B2B business solutions to companies worldwide. Our insights support the future growth strategies and roadmaps of companies across industries with multiple application areas, including, Pharmaceuticals, Healthcare, Food & Beverages, ICT, CPG, Chemicals and Advanced Materials, Automotive & Transportation, among others.</p>
                            <p>
                                <a href="https://www.trustpilot.com/review/eternityinsights.com" target="_blank"><img src="{{ url('frontend/images/trustpilot-icon.png') }}" alt="TrustPilot Reviews" style="width: 60%;" /></a>
                            </p>
                        </div>
                        <div class="col-sm-4 wow fadeInLeft">

                            <h4 class="footer-title text-uppercase">Popular Categories</h4>
                            <ul class="list-unstyled footer-links">
                                <li>
                                    <a rel="nofollow" href="{{ route('industry_list', 'healthcare') }}">Pharmaceuticals & Healthcare</a>
                                </li>
                                <li>
                                    <a rel="nofollow" href="{{ route('industry_list', 'consumer-goods') }}">Consumer Goods</a>
                                </li>
                                <li>
                                    <a rel="nofollow" href="{{ route('industry_list', 'automotive-transportation') }}">Automotive & Transportation</a>
                                </li>
                                <li>
                                    <a rel="nofollow" href="{{ route('industry_list', 'information-communication-technology') }}">Information & Communication Technology</a>
                                </li>
                                <li>
                                    <a rel="nofollow" href="{{ route('industry_list', 'electronics-semiconductors') }}">Electronics & Semiconductors</a>
                                </li>
                            </ul>

                        </div>
                        <div class="col-sm-3 wow fadeInRight">
                            <h4 class="footer-title text-uppercase">Quick Links</h4>
                            <ul class="list-unstyled footer-links">
                                <li><a href="{{ route('services') }}">Services</a></li>
                                <li><a href="{{ route('faqs') }}">FAQ</a></li>
                                <li><a rel="nofollow" href="{{ route('howToOrder') }}">How To Order</a></li>
                                <li><a rel="nofollow" href="{{ route('privacyPolicy') }}">Privacy Policy</a></li>
                                <li><a rel="nofollow" href="{{ route('refundPolicy') }}">Refund And Cancellation</a></li>
                                <li><a rel="nofollow" href="{{ route('disclaimer') }}">Disclaimer</a></li>
                                <li><a rel="nofollow" href="{{ route('termsConditions') }}">Terms &amp; Conditions</a></li>
                                <li><a href="{{ route('html_sitemap') }}">Sitemap</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-3 wow fadeInRight">
                    <h4 class="footer-title text-uppercase">Address</h4>
                    <p class="footer-add">
                        Baner, Pune - 411045,<br>
                        Maharashtra, India.
                    </p>
                    <ul class="list-unstyled footer-links-contact">
                        <li>
                            <img src="{{ url('frontend/images/phone-white.svg') }}" alt="phone" width="20" class="img-fluid" loading="lazy"/> &nbsp;
                            <a href="tel:+91-930-705-6852">+91-930-705-6852</a>
                        </li>
                        <li>
                            <img src="{{ url('frontend/images/mail.svg') }}" alt="mail" width="20" class="img-fluid" loading="lazy"/> &nbsp; <a
                                rel="nofollow" href="mailto:{{ config('site.sales_email') }}">{{ config('site.sales_email') }}</a>
                        </li>
                    </ul>

                    <h4 class="footer-title text-uppercase pt-3">Payment Modes</h4>
                    <ul class="list-unstyled footer-links footer-payment-bg">
                        <li>
                            <img src="{{ url('frontend/images/payment-options.png') }}" alt="Eternity Payment Option"
                                class="img-fluid" loading="lazy" >
                        </li>
                    </ul>
                    <span id="siteseal">
                        <script async src="https://seal.godaddy.com/getSeal?sealID=2eoCoyd11PhhKvFOyGN7GmL91Ok50l2kExHIyXdPPztW0evZKSV2JtQw4Eb7"></script>
                    </span>
                </div>
            </div>
        </div>
        <div class="container wow fadeInUp" style="border-top:1px solid #8abfee;">
            <div class="row">
                <div class="col-sm-5 pb-3 pt-3">
                    <span class="mb-0" style="font-size:12px;">Â© Copyright at Eternity Insights. Page rendered in {elapsed_time} seconds.</span>
                </div>
                <div class="col-sm-7 pb-3 pt-3">
                    <div class="social">
                        <h4 class="footer-title text-uppercase mt-4">Follow Us &nbsp;&nbsp;</h4>
                        <a href="https://www.linkedin.com/company/eternityinsights/" class="social-wrapper" style="padding:4px 0 0 8px">
                            <i class="fab fa-linkedin-in"></i>
                        </a> &nbsp;
                        <a href="https://www.facebook.com/eternityinsight" class="social-wrapper" style="padding:3px 9px;">
                            <i class="fab fa-facebook-f"></i>
                        </a> &nbsp;
                        <a href="https://twitter.com/Eternity_MI" class="social-wrapper" style="padding: 3px 6px;">
                            <i class="fab fa-twitter"></i>
                        </a> &nbsp;                        
                        <a href="https://eternity-insights.blogspot.com/" class="social-wrapper" style="padding: 3px 6px;">
                            <i class="fab fa-dribbble"></i>
                        </a> &nbsp;
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script> -->
    <script src="{{ url('frontend/js/jqueryV3.5.1.min.js') }}"></script>
    <script src="{{ url('frontend/js/bootstrapV5.1.3.min.js') }}"></script>

    <script>
            /*******Slide Menu***********/
    function openNav() {
           document.getElementById("mySidenav").style.width = "75%";
       }

       function closeNav() {
           document.getElementById("mySidenav").style.width = "0";
       }
</script>
    
    <script>
        

        function openCity(evt, cityName) {
            var i, tabcontent, tablinks;
            tabcontent = document.getElementsByClassName("tabcontent");
            for (i = 0; i < tabcontent.length; i++) {
                tabcontent[i].style.display = "none";
            }
            tablinks = document.getElementsByClassName("tablinks");
            for (i = 0; i < tablinks.length; i++) {
                tablinks[i].className = tablinks[i].className.replace(" active", "");
            }
            document.getElementById(cityName).style.display = "block";
            evt.currentTarget.className += " active";
        }
 
        $(document).ready(function () { 
            
        });
</script>
@unless(request()->routeIs('home'))

@endunless
