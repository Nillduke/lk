@extends('layout.default')
@section('head_meta')
<style>
    h1.tqh1{
        text-align:center;
        font-size:50px; 
        margin:20px auto;    
        font-weight: bold;
        text-transform: uppercase;
        color: #ff4949;
    }
    .font-weight-bold{
        font-weight: 600;
    }
</style>
<link href="/frontend/css/owl.carousel.min.css" rel="stylesheet">
<link href="/frontend/css/owl.theme.default.min.css" rel="stylesheet">

@endsection
@section('content')
    <!-- **Breadcrumbs** -->
    <div class="container">
        <div class="row">
            <div class="col-12">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb site-breadcrumb mt-3">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}" style="color:#000;"><i class="fas fa-home"></i></a></li>
                        <li class="breadcrumb-item active" aria-current="page">Thank You</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <section class="container">
        <div class="row">
            <div class="col-12">
                <div class="thankyou-wrapper text-center">
                    <i class="fas fa-clipboard-check fa-7x"></i>
                    <h1 class="text-uppercase">Thank You</h1>
                    <p>
                        We have received your request for @php if($enqfor == 'Search' || $enqfor == 'Contact Us'){
                            echo $enqfor.'.';
                        } else { @endphp
                        {{ session('enq_reportlink') }} research report. <br/>
                        
                        @php } @endphp
                        Our research specialist will contact you soon.
                    </p>
                </div>
            </div>

            <div class="col-12 mb-5">
                <div id="demo" class="carousel slide" data-bs-ride="carousel">
                    <div class="text-center">
                        <h4 class="text-uppercase" style="font-weight: 600;">Testimonials</h4>
                    </div>
                    <!-- Indicators/dots -->
                    <div class="carousel-indicators testimonial-indicators">
                        <button type="button" data-bs-target="#demo" data-bs-slide-to="0" class="active"></button>
                        <button type="button" data-bs-target="#demo" data-bs-slide-to="1"></button>
                        <button type="button" data-bs-target="#demo" data-bs-slide-to="2"></button>
                    </div>

                    <!-- The slideshow/carousel -->
                    <div class="carousel-inner thankyou-testi testimonial-box">
                        <div class="carousel-item active">
                            <p class="testimonial-desc mb-5">
                                I would like to express my gratitude for the excellent service received this
                                morning from the support team. They acknowledged my concern of adding me to the
                                secondary user's list immediately. Thank you for this excellent, speedy and
                                thoughtful service.
                            </p>
                            <h5 class="testimonials-user mb-3">
                                UK based Health care company
                            </h5>
                        </div>
                        <div class="carousel-item">
                            <p class="testimonial-desc mb-5">
                                I would like to express my gratitude for the excellent service received this
                                morning from the support team. They acknowledged my concern of adding me to the
                                secondary user's list immediately. Thank you for this excellent, speedy and
                                thoughtful service.
                            </p>
                            <h5 class="testimonials-user mb-3">
                                UK based Health care company
                            </h5>
                        </div>
                        <div class="carousel-item">
                            <p class="testimonial-desc mb-5">
                                I would like to express my gratitude for the excellent service received this
                                morning from the support team. They acknowledged my concern of adding me to the
                                secondary user's list immediately. Thank you for this excellent, speedy and
                                thoughtful service.
                            </p>
                            <h5 class="testimonials-user mb-3">
                                UK based Health care company
                            </h5>
                        </div>
                    </div>
                </div>

                <div class="social mt-5 text-center">
                    <a href="https://www.facebook.com/eternityinsight" class="social-wrapper" style="padding:3px 9px;">
                        <i class="fab fa-facebook-f" aria-hidden="true"></i>
                    </a> &nbsp;
                    <a href="https://twitter.com/Eternity_MI" class="social-wrapper" style="padding: 3px 6px;">
                        <i class="fab fa-twitter" aria-hidden="true"></i>
                    </a> &nbsp;
                    <a href="https://www.linkedin.com/company/eternityinsights/" class="social-wrapper" style="padding: 3px 6px;">
                        <i class="fab fa-linkedin"></i>
                    </a> &nbsp;
                    <a href="https://eternity-insights.blogspot.com/" class="social-wrapper" style="padding: 3px 6px;">
                        <i class="fab fa-dribbble" aria-hidden="true"></i>
                    </a> &nbsp;
                </div>
            </div>
        </div>

        <!-- **Our Clients** -->
        <div class="row mb-5">
            <div class="col-12 mb-4">
                <h4 class="reports-wrapper-title">Our Clients</h4>
            </div>
            <div class="col-12">
                <div class="owl-carousel owl-theme client-logo">
                    <div class="item">
                        <img src="/frontend/images/client-logo/Client-Honeywell.png" alt="Honeywell" class="img-fluid">
                    </div>
                    <div class="item">
                        <img src="/frontend/images/client-logo/Client-Dropbox.png" alt="Dropbox" class="img-fluid">
                    </div>
                    <div class="item">
                        <img src="/frontend/images/client-logo/Client-Evonik.png" alt="Evonik" class="img-fluid">
                    </div>
                    <div class="item">
                        <img src="/frontend/images/client-logo/Client-Philips.png" alt="Philips" class="img-fluid">
                    </div>
                    <div class="item">
                        <img src="/frontend/images/client-logo/Client-LG.png" alt="LG" class="img-fluid">
                    </div>
                    <div class="item">
                        <img src="/frontend/images/client-logo/Client-Dropbox.png" alt="Dropbox" class="img-fluid">
                    </div>
                    <div class="item">
                        <img src="/frontend/images/client-logo/Client-Honeywell.png" alt="Honeywell" class="img-fluid">
                    </div>
                    <div class="item">
                        <img src="/frontend/images/client-logo/Client-Evonik.png" alt="Evonik" class="img-fluid">
                    </div>
                    <div class="item">
                        <img src="/frontend/images/client-logo/Client-LG.png" alt="LG" class="img-fluid">
                    </div>
                    <div class="item">
                        <img src="/frontend/images/client-logo/Client-Philips.png" alt="Philips" class="img-fluid">
                    </div>
                </div>
            </div>
        </div>
    </section>


@endsection
@section('footer_script')

<script src="/frontend/js/owl.carousel.min.js"></script>
    <script>
        $(document).ready(function () {
            var owl = $('.owl-carousel');
            owl.owlCarousel({
                items: 5,
                loop: true,
                margin: 10,
                autoplay: true,
                autoplayTimeout: 1300,
                autoplayHoverPause: true
            });
        });
    </script>
@endsection
