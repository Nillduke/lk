@if(!empty($reportFaqs))
<section class="faq-sec" id="report-faqs" style="margin: 3.2rem 0 6rem 0;">
<div class="container"><h2>Frequently Asked Questions on this Report</h2>
@if($ampFaqs??false)
@foreach($reportFaqs as $faq)<h3>{{ $faq['question'] }}</h3><p>{!! nl2br(e($faq['answer'])) !!}</p>@endforeach
@else
<div class="accordion accordion-flush" id="reportFaqAccordion">
@foreach($reportFaqs as $faqIndex=>$faq)
<div class="accordion-item"><h3 class="accordion-header" id="report-faq-heading-{{ $faqIndex }}"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#report-faq-answer-{{ $faqIndex }}" aria-expanded="false" aria-controls="report-faq-answer-{{ $faqIndex }}">{{ $faq['question'] }}</button></h3><div id="report-faq-answer-{{ $faqIndex }}" class="accordion-collapse collapse" aria-labelledby="report-faq-heading-{{ $faqIndex }}" data-bs-parent="#reportFaqAccordion"><div class="accordion-body">{!! nl2br(e($faq['answer'])) !!}</div></div></div>
@endforeach
</div>
@endif
</div></section>
<script type="application/ld+json">{!! app(\App\Services\ReportFaqs::class)->schema($reportFaqs) !!}</script>
@endif
