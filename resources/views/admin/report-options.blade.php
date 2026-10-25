@php
$options = app(\App\Services\ReportOptions::class);
$segmentRows = old('report_segments_present') ? old('report_segments', []) : $options->rows($record);
$licenseRows = old('report_licenses_present') ? old('report_licenses', []) : ($record ? $licenses->map(fn($l)=>['id'=>$l->id,'lic_name'=>$l->lic_name,'lic_price'=>$l->lic_price])->all() : [['lic_name'=>'Single User License','lic_price'=>3500],['lic_name'=>'Multi User License','lic_price'=>4800],['lic_name'=>'Corporate User License','lic_price'=>7200]]);
@endphp
<section class="panel report-options-panel" aria-labelledby="segment-heading">
<div class="report-options-heading"><div><h2 class="h5" id="segment-heading">Market segments &amp; key companies</h2><p class="text-muted">Organize the products, customers, applications and companies covered in this report.</p></div><button class="btn btn-outline-primary" type="button" data-add-report-row="segment">+ Add segment</button></div>
<input type="hidden" name="report_segments_present" value="1">
<div data-report-rows="segment">
@foreach($segmentRows as $index=>$segment)
@include('admin.report-segment-row',['rowIndex'=>$index,'segment'=>$segment])
@endforeach
</div>
<p class="report-options-empty text-muted" data-empty-rows="segment" @if(count($segmentRows))hidden @endif>No segments added yet. Add a segment to get started.</p>
<p class="small text-muted mb-0">Enter a segment name, such as Product Type or End User, then list its options. Use commas or put each option on a new line. Changes here update the report details; saved report content stays as you wrote it.</p>
<datalist id="report-segment-names"><option value="Product Type"><option value="Application"><option value="End User"><option value="Technology"><option value="Region"><option value="Key Players"></datalist>
</section>
<section class="panel report-options-panel" aria-labelledby="license-heading">
<div class="report-options-heading"><div><h2 class="h5" id="license-heading">Licenses &amp; pricing</h2><p class="text-muted">Set the purchase options customers see on the report page.</p></div><button class="btn btn-outline-primary" type="button" data-add-report-row="license">+ Add license</button></div>
<input type="hidden" name="report_licenses_present" value="1">
<div data-report-rows="license">
@foreach($licenseRows as $index=>$license)
@include('admin.report-license-row',['rowIndex'=>$index,'license'=>$license])
@endforeach
</div>
<p class="report-options-empty text-muted" data-empty-rows="license" @if(count($licenseRows))hidden @endif>No purchase options added. Use Add license to enter a name and price.</p>
<p class="small text-muted mb-0">Prices are in USD. Enter whole numbers without a currency symbol. Remove an unused row before saving.</p>
<datalist id="report-license-names"><option value="Single User License"><option value="Multi User License"><option value="Corporate User License"></datalist>
</section>
<template id="report-segment-template">@include('admin.report-segment-row',['rowIndex'=>'__INDEX__','segment'=>['key'=>'','label'=>'','value'=>'']])</template>
<template id="report-license-template">@include('admin.report-license-row',['rowIndex'=>'__INDEX__','license'=>['id'=>'','lic_name'=>'','lic_price'=>'']])</template>
