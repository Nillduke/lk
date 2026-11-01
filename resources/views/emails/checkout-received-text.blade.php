{{ $forAdmin ? 'Dear Admin,' : 'Hello '.$order->name.',' }}

Your checkout details have been recorded. Payment is pending. This is an acknowledgment, not a payment receipt. Report delivery follows payment confirmation.

Order number: {{ $order->order_number }}
Report name: {{ $report->title }}
License: {{ $order->license_type }}
Amount: {{ $order->currency }} {{ number_format((float)$order->amt_tobe_paid,2) }}
Payment method: {{ $order->payment_by }}
Payment status: Pending
Name: {{ $order->name }}
Email: {{ $order->email }}
@if($forAdmin)
Phone: {{ $order->contact_no }}
Company: {{ $order->company }}
Country: {{ $order->country }}
Address: {{ $order->address }}
@endif

Report: {{ route('rd_view',$report->slug) }}
For assistance: {{ config('site.admin_email') }}
Eternity Insights
