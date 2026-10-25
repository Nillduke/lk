@extends('layout.default')
<style>
    .font-weight-bold{
        font-weight: 600;
    }
</style>
@section('content')
    <!-- **Breadcrumbs** -->
    <div class="container">
        <div class="row">
            <div class="col-12">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb site-breadcrumb mt-3">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}" style="color:#000;"><i class="fas fa-home"></i></a></li>
                        <li class="breadcrumb-item active" aria-current="page">FAQs</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

<div class="container">
    <div class="row">
        <div class="col-lg-9 col-md-8 col-12">
           <h2 class="">FAQs</h2><hr>
           <ul>
            <li class="list-unstyled font-weight-bold">1) What is Eternity Insights?</li><br>
            Eternity Insights is a one stop destination for all industrial, commercial and profitmaking ventures seeking the best research of the market of all the sectors. The company prides itself in being efficient in fulfilling market research requirements of international and domestic clients.<br>
            <li class="list-unstyled font-weight-bold"><br>2) Why use Eternity Insights?</li><br>
            Eternity Insights offers accurate market analysis and research in the clientâ€™s sector of business. It has more than 18,000 research documents to support this claim. Experienced researchers and analysts with many years of work in their field of expertise work with us to provide the best analysis and reporting services in the industry.<br>
            <li class="list-unstyled font-weight-bold"><br>3) Who are our customers?</li><br>
            Eternity Insights has customers from almost every possible area of business activity. They include industrialists, entrepreneurs, organisations, executives and even students. Eternity Insights provides all of these with a detailed and accurate analysis of any specific market.

            We offer a comprehensive analysis of existing and potential future market trends for all business sectors. Among these are companies involved in banking, finance, insurance, technology and innovation, automobiles, medical, pharmaceutical, education, consulting, biotechnology, genetics and fast moving consumer goods (FMCGs).<br>
            <li class="list-unstyled font-weight-bold"><br>4) How to reach our Customer Support System?</li><br>

            We take customer support and help services extremely seriously. We are easily contacted for help and support before, during and after the purchasing process.

            Contact us by telephone, email or by using the contact form provided on our website.<br>
            <li class="list-unstyled font-weight-bold"><br>5) How can I contribute to the publications of Eternity Insights?</li><br>

            That can be easily arranged. First, you must register as a publisher on the website. After the application has been approved the members of Eternity Insights will contact you.
            <li class="list-unstyled font-weight-bold"><br>6) How to affiliate with Eternity Insights?</li><br>

            If you want to become an affiliate then simply contact us. You will then get a reply from us for further steps.
            <li class="list-unstyled font-weight-bold"><br>7) What about the information collected about me?</li><br>

            We collect basic information about our users: name, email address, company, contact information and address.

            For more details on how we use your information check out our Terms &amp; Conditions and Privacy pages.
            <li class="list-unstyled font-weight-bold"><br>8) Is my private information protected?</li><br>
            </ul>
            The information you provide is protected according to our Privacy page.

            If at any point, the user feels disturbed by the newsletter, promotional offers or any other mails sent by Eternity Insights, then they can easily opt out of it by unsubscribing from the mailing list, a link for which is on each email we send you.<br>

            </div>
            <div class="col-lg-3 col-md-4 col-12">
                @include('include.sidebar1')
            </div>
            </div>
        </div>
    @endsection
