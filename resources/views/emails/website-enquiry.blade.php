<!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>New website enquiry</title></head>
<body style="margin:0;padding:0;background:#f5f3f1;color:#282828;font-family:Arial,Helvetica,sans-serif;">
<div style="display:none;max-height:0;overflow:hidden;opacity:0;">New {{ \App\Mail\WebsiteEnquiry::label($lead['type']) }} from {{ $lead['full_name'] }}.</div>
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f5f3f1;"><tr><td align="center" style="padding:28px 12px;">
<table role="presentation" width="720" cellspacing="0" cellpadding="0" style="width:100%;max-width:720px;background:#ffffff;border:1px solid #dedbd7;border-top:4px solid #c14d28;">
<tr><td style="padding:23px 26px;background:#ffffff;"><a href="{{ route('home') }}"><img src="{{ isset($message) ? $message->embed(public_path('frontend/images/eternity_logo.png')) : asset('frontend/images/eternity_logo.png') }}" width="218" alt="Eternity Insights" style="display:block;width:218px;max-width:100%;height:auto;border:0;"></a></td></tr>
<tr><td style="padding:24px 26px;background:#a94327;color:#ffffff;"><p style="margin:0 0 16px;font-size:14px;line-height:21px;">Dear Admin,</p><p style="margin:0 0 5px;font-size:14px;line-height:21px;">You have a new website lead</p><h1 style="margin:0;font-size:24px;line-height:32px;font-weight:700;">{{ \App\Mail\WebsiteEnquiry::label($lead['type']) }}</h1></td></tr>
<tr><td style="padding:0 26px 24px;">
<table width="100%" cellspacing="0" cellpadding="0" style="width:100%;border-collapse:collapse;font-size:14px;line-height:22px;table-layout:fixed;">
@php
$rows = [
    'Report Name' => $report->title ?? 'General website enquiry',
    'Contact Person' => $lead['full_name'], 'Email ID' => $lead['email_id'],
    'Site' => 'EN', 'Country' => $lead['country'] ?? null, 'Phone No' => $lead['contact_no'],
    'Message' => $lead['message'] ?? null, 'Company Name' => $lead['company'] ?? null,
    'Job Role' => $lead['role'] ?? null, 'Client IP' => $clientIp,
    'Received' => \Illuminate\Support\Carbon::parse($lead['created_at'])->timezone(config('app.timezone'))->format('d M Y, h:i A').' ('.config('app.timezone').')',
];
@endphp
@foreach($rows as $label => $value)
<tr><th scope="row" align="left" valign="top" width="34%" style="width:34%;padding:13px 12px 13px 0;border-bottom:1px solid #e4e0dc;font-weight:400;color:#665f59;overflow-wrap:anywhere;">{{ $label }}</th><td valign="top" style="padding:13px 0 13px 16px;border-bottom:1px solid #e4e0dc;color:#282828;overflow-wrap:anywhere;word-break:break-word;">@if($label==='Email ID')<a href="mailto:{{ $value }}" style="color:#a94327;text-decoration:underline;">{{ $value }}</a>@elseif($label==='Phone No')<a href="tel:{{ preg_replace('/[^+0-9]/','',$value) }}" style="color:#a94327;text-decoration:underline;">{{ $value }}</a>@elseif($label==='Report Name' && $report)<a href="{{ route('rd_view',$report->slug) }}" style="color:#282828;font-weight:700;text-decoration:none;">{{ $value }}</a>@else{!! nl2br(e(filled($value) ? $value : 'Not provided')) !!}@endif</td></tr>
@endforeach
</table>
<p style="margin:24px 0 0;"><a href="{{ $enquiryId ? route('admin.edit',['enquiries',$enquiryId]) : route('admin.index','enquiries') }}" style="display:inline-block;background:#292929;color:#ffffff;padding:12px 20px;text-decoration:none;font-size:14px;line-height:20px;font-weight:700;border-radius:4px;">View enquiry in admin</a></p>
<p style="margin:18px 0 0;font-size:13px;line-height:20px;color:#746b64;">Reply to this email to contact {{ $lead['full_name'] }} directly.</p></td></tr>
<tr><td style="padding:18px 26px;background:#faf8f6;border-top:1px solid #e4e0dc;font-size:12px;line-height:19px;color:#746b64;">Eternity Insights &bull; Website lead notification<br><a href="{{ route('home') }}" style="color:#a94327;text-decoration:none;">{{ parse_url(config('app.url'), PHP_URL_HOST) }}</a></td></tr>
</table></td></tr></table></body></html>
