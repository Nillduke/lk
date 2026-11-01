<!doctype html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head>
<body style="margin:0;background:#f3f6fb;color:#233653;font:15px Arial,sans-serif"><table role="presentation" width="100%" cellspacing="0" cellpadding="0"><tr><td style="padding:24px 12px"><table role="presentation" width="640" cellspacing="0" cellpadding="0" style="max-width:640px;width:100%;margin:auto;background:#fff;border:1px solid #dce4ef">
<tr><td style="padding:24px;background:#152d60"><img src="{{ url('frontend/images/eternity-white-logo.svg') }}" alt="Eternity Insights" width="200" style="max-width:100%"><h1 style="font-size:22px;margin:20px 0 0;color:#fff">{{ $forAdmin ? 'New checkout received' : 'Thank you for your order' }}</h1></td></tr>
<tr><td style="padding:24px"><p>{{ $forAdmin ? 'Dear Admin,' : 'Hello '.$order->name.',' }}</p><p>{{ $forAdmin ? 'A customer has submitted the following checkout.' : 'We have recorded your order details.' }} <strong>Payment is pending.</strong> This message is an order acknowledgment, not a payment receipt. Report delivery will follow payment confirmation.</p>
<table width="100%" cellspacing="0" cellpadding="10" style="border-collapse:collapse;font-size:14px">
@foreach(['Order number'=>$order->order_number,'Report name'=>$report->title,'License'=>$order->license_type,'Amount'=>$order->currency.' '.number_format((float)$order->amt_tobe_paid,2),'Payment method'=>$order->payment_by,'Payment status'=>'Pending','Name'=>$order->name,'Email'=>$order->email] as $label=>$value)
<tr><th align="left" style="border:1px solid #dce4ef;background:#f6f8fc;width:32%;vertical-align:top">{{ $label }}</th><td style="border:1px solid #dce4ef;overflow-wrap:anywhere">{{ $value }}</td></tr>
@endforeach
@if($forAdmin)
@foreach(['Phone'=>$order->contact_no,'Company'=>$order->company,'Country'=>$order->country,'Address'=>$order->address] as $label=>$value)
<tr><th align="left" style="border:1px solid #dce4ef;background:#f6f8fc;vertical-align:top">{{ $label }}</th><td style="border:1px solid #dce4ef">{{ $value ?: '-' }}</td></tr>
@endforeach
@endif
</table><p><a href="{{ route('rd_view',$report->slug) }}" style="color:#17377b">View report</a></p>
<p>For assistance, contact <a href="mailto:{{ config('site.admin_email') }}" style="color:#17377b">{{ config('site.admin_email') }}</a> and quote your order number.</p>
</td></tr><tr><td style="padding:18px 24px;border-top:1px solid #dce4ef;color:#64748b;font-size:12px">&copy; {{ date('Y') }} Eternity Insights. All rights reserved.</td></tr>
</table></td></tr></table></body></html>
