@extends('layout.default')
@section('head_meta')
<style>
    .cmscont h5, .cmscont h6{
        font-weight: bold;
    }
</style>
@endsection
@section('content')
    <!-- **Breadcrumbs** -->
    <div class="container">
        <div class="row">
            <div class="col-12">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb site-breadcrumb mt-3">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}" style="color:#000;"><i class="fas fa-home"></i></a></li>
                        <li class="breadcrumb-item active" aria-current="page">How To Order</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

<div class="container">
    <div class="row">
        <div class="col-md-8 col-12">
            <div class="cmscont">
                <h1>How To Order</h1><hr>                
                <h5>Order and Delivery</h5>
                <p>If you are placing an order on a companyâ€™s behalf, you should be authorized to make the purchase decision. You can place your order using following channels:</p>

                <h6>Ordering by Email:</h6>
                <p>Send all your requirements and questions to {{ config('site.sales_email') }} Remember to include all your details including billing & delivery address along with the preferred mode of payment. Our executives will get in touch with you within 1 business day.</p>

                <h6>Ordering by Telephone:</h6>
                <p>Call us on Office Contact: +91 (930) 705-6852 and speak with our customer service representative to place an order.</p>

                <h6>Online Delivery:</h6>
                <p>Published report will be delivered to you by email within 24 to 48 hours of receipt of payment in the form of a PDF to your email address.</p>

                <h6>Customization:</h6>
                <p>ETERNITY INSIGHTS allows a certain level of customization in market reports tailored to your requirements. However, the time required to accommodate customization will be determined by our analysts based on anticipated complexity, time deadlines, and peculiarities of the customization requested.</p>

                <h6>Physical Delivery:</h6>
                <p>A printed copy of the published report shall be sent by courier to your address within 08 to 10 working days from the date of receipt of payment. In the case of hard copies, delivery charges will be charged depending on the location of the client ETERNITY INSIGHTS does not guarantee that the goods when received are in perfect condition and is not liable for any damage during transit of goods. It is at the publisher's discretion, if the replacement can be made with no extra cost.</p>

            <h5>Method of Payment</h5>
            <p>You can use the following methods of payment:</p>
            <ul>
                <li>Credit Card <br>
            â€“ We accept VISA, MasterCard, Maestro, Diners Club and American Express cards. On successful payment, an invoice will emailed to you.</li>
                <li>
            PayPal<br>
            â€“ National and international clients can choose PayPal to pay online.</li>
                <li>
            Wire Transfer<br>
            â€“ Once you have placed your order (online, post or fax) an invoice will be emailed to you for payment via wire transfer or company cheque.</li>
            </ul>             
        </div>
    </div>
    <div class="col-md-4">
        @include('include.sidebar1')
    </div>
    </div>
</div>


@endsection
