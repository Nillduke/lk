@extends('layout.default')

@section('content')
    <!-- **Breadcrumbs** -->
    <div class="container">
        <div class="row">
            <div class="col-12">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb site-breadcrumb mt-3">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}" style="color:#000;"><i class="fas fa-home"></i></a></li>
                        <li class="breadcrumb-item active" aria-current="page">Refund Policy</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

<div class="container">
    <div class="row">
        <div class="col-md-8 col-12">
                <div class="cmscont">
                    <h1>Refund Policy</h1><hr>
                    <p>The client will make the payment to Eternity Insights as mentioned on the website. He/she is responsible for use, sales, and related taxes & customs.</p>
                    <p>Once made, the purchase decision cannot be undone. The client should get in touch with the company, in case of any doubt related to the transaction before deciding and clicking on the purchase button on the website.</p>
                    <p>
                        The company does not accept return of products after delivery owing to the nature and format of the data. However, under certain circumstances, the management can offer refunds solely at its discretion.
                        Considering the confidentiality and value of the data it provides in reports, therefore, cannot grant refunds after the report has been dispatched. However, it will provide customization of the report, if it contains missing or incomplete data.
                    </p>
                    <p>If you require Support or have a question regarding a purchase you have made or the scope and relevance of any report you have received, then contact us at: {{ config('site.sales_email') }}</p>
                </div>
        </div>
        <div class="col-md-4">
            @include('include.sidebar1')
        </div>
        </div>
    </div>
@endsection
