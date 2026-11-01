@php $faqMode=old('faq_mode',$faqEditor['mode']);$faqRows=old('report_faqs',$faqEditor['rows']); @endphp
<div class="panel" id="report-faq-editor" data-extract-url="{{ route('admin.faqs.extract',[],false) }}">
<input type="hidden" name="report_faqs_present" value="1">
<div class="d-flex flex-wrap justify-content-between gap-2"><div><h2 class="h5">Report FAQs</h2><p class="text-muted small">Use the five-question template with answers supported by this report. Blank answers stay off the website. Custom answers are preserved when report content changes.</p></div><div class="d-flex align-items-start gap-2"><button type="button" class="btn btn-outline-primary btn-sm" data-faq-extract>Extract new drafts</button><button type="button" class="btn btn-outline-secondary btn-sm" data-faq-add>+ Add FAQ</button></div></div>
<label for="faq-mode">FAQ display</label><select id="faq-mode" name="faq_mode" class="form-select" data-faq-mode><option value="auto" @selected($faqMode==='auto')>Automatic - extract supported answers from report content</option><option value="custom" @selected($faqMode==='custom')>Custom - publish my edited questions and answers</option><option value="hidden" @selected($faqMode==='hidden')>Hide FAQs on this report</option></select>
<p class="text-muted small">Editing a row switches to Custom. Extract new drafts replaces the rows below using the current content and segment fields. Click Save changes to publish. Market figures and years are taken from the report, not guessed.</p>
<p class="small" data-faq-feedback role="status" aria-live="polite"></p>
<div data-faq-rows>@foreach($faqRows as $faqIndex=>$faqRow)@include('admin.report-faq-row')@endforeach</div>
<template data-faq-template>@include('admin.report-faq-row',['faqIndex'=>0,'faqRow'=>['question'=>'','answer'=>'']])</template>
</div>
@push('scripts')<script src="/frontend/js/admin-report-faqs.js?v=20261101-1" defer></script>@endpush
