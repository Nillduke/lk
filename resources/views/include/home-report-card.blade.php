@php
$shortTitle=trim($homeReport->keyword??'');
if($shortTitle!=='' && !preg_match('/\bmarket$/i',$shortTitle))$shortTitle.=' Market';
$shortTitle=$shortTitle?:$homeReport->title;
@endphp
<article class="ei-report-card"><div class="ei-report-meta"><span>{{ $homeReport->category_name??'Market research' }}</span><time datetime="{{ site_date($homeReport->pub_date,'Y-m-d') }}">{{ site_date($homeReport->pub_date,'M Y') }}</time></div><h3><a href="{{ route('rd_view',$homeReport->slug) }}">{{ $shortTitle }}</a></h3><p class="ei-report-description">{{ html_entity_decode(strip_tags($homeReport->description??''),ENT_QUOTES|ENT_HTML5,'UTF-8') }}</p><details class="ei-report-coverage"><summary>Report coverage <span aria-hidden="true">+</span></summary><p>{{ $homeReport->title }}</p></details><div class="ei-report-footer"><span>@if($homeReport->lic_price!==null)From <strong>USD {{ number_format((float)$homeReport->lic_price) }}</strong>@else Research report @endif</span><a href="{{ route('rd_view',$homeReport->slug) }}" aria-label="View {{ $shortTitle }} report">View report <span aria-hidden="true">&rarr;</span></a></div></article>
