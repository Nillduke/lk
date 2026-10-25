@extends('layout.default')

@section('content')
    <!-- **Breadcrumbs** -->
    <div class="container">
        <div class="row">
            <div class="col-12">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb site-breadcrumb mt-3">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}" style="color:#000;"><i class="fas fa-home"></i></a></li>
                        <li class="breadcrumb-item active" aria-current="page">Disclaimer</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

<div class="container">
    <div class="row">
        <div class="col-lg-9 col-md-8 col-12">
            <h2 class="">Disclaimer</h2><hr>
            Eternity Insights does not offer any warranties or representations of any kind regarding the functioning of this site or any information, content, materials or products published thereon. Users using this website do so completely at their own risk, irrespective of the nature of any consequences for accessing and/or using it.<br><br>

            Eternity Insights does not warrant that any emails or other electronic information delivered from this website shall be free of shall be free of any viruses or other damaging elements. Eternity Insights cannot be held liable for any damage that arises out of the usage of this website, irrespective of the nature of that damage.<br><br>

            The reports acquired from Eternity Insights by our clients are intended only for their personal or internal use. These reports cannot be divulged, published or disclosed to any third party. Additionally, reports and other information provided by this website, cannot be resold, republished or lent in whole or in part to any other party without the expressed written permission of Eternity Insights.<br><br>

            All statements provided in the reports submitted by Eternity Insights to the users necessarily reflect the views of the analysts working on them. They do not reflect the opinions, views or formal position of corporate Eternity Insights in any way. The information provided and statements mentioned are not guaranteed.<br><br>

            The analyses provided to an client or reader are based upon empirical data based on the state of the markets at a given point of time and they can alter according to market changes with no responsibility for the effects of such changes allocated to this company.<br><br>

            Data is subject to fluctuation. Eternity Insights assumes no liability of any short term or long term decisions made by our clients on the basis of the information provided in our market reports.
            <br><br>
            We do not guarantee, or offer warranty against, the authenticity, accuracy and completeness of any report, information used or any such representations made over the website.
            <br><br>
            This Disclaimer can be changed at any time according to factors involved in the introduction or provision of new services or products by Eternity Insights.<br>
            </div>
            <div class="col-lg-3 col-md-4 col-12">
                @include('include.sidebar1')
            </div>
        </div>
    </div>
@endsection
