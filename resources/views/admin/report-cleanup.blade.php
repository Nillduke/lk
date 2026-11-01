@extends('admin.layout')
@section('title','Report content cleanup')
@push('styles')
<style>.cleanup-select{flex:0 0 18px;width:18px;height:18px}.cleanup-report-title{min-width:0}.cleanup-title{overflow-wrap:anywhere}#cleanup-select-all{width:18px;height:18px;vertical-align:middle;margin-right:6px}.cleanup-change{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin:12px 0}.cleanup-change>div{min-width:0;border:1px solid #dce4ed;border-radius:6px;padding:14px;background:#f8fafc;overflow-wrap:anywhere}.cleanup-change p{margin:8px 0 0;white-space:pre-wrap}.cleanup-title{font-size:17px;line-height:1.6}.cleanup-actions{display:flex;gap:12px;flex-wrap:wrap;align-items:center}@@media(max-width:800px){.cleanup-change{grid-template-columns:1fr}}</style>
@endpush
@section('content')
<h1 class="h3">Report content cleanup</h1>
<p class="text-muted">Review outdated COVID-19 wording in batches of up to 5,000 reports. Only the reports you select will be updated.</p>
<div class="panel">
<h2 class="h5">Find reports to review</h2>
<form method="post" action="{{ route('admin.cleanup.preview') }}">@csrf
<div class="row">
<div class="col-md-3"><label for="batch_size">Reports per batch</label><select id="batch_size" name="batch_size" class="form-select">@foreach([10,25,50,100,500,1000,5000] as $size)<option value="{{ $size }}" @selected(($filters['batch_size']??5000)==$size)>{{ number_format($size) }} reports</option>@endforeach</select></div>
<div class="col-md-3"><label for="cleanup_status">Report status</label><select id="cleanup_status" name="status" class="form-select">@foreach(['active'=>'Published reports','inactive'=>'Draft / inactive reports','all'=>'All reports'] as $value=>$label)<option value="{{ $value }}" @selected(($filters['status']??'active')===$value)>{{ $label }}</option>@endforeach</select></div>
<div class="col-md-6"><label for="cleanup_category">Industry</label><select id="cleanup_category" name="category_id" class="form-select"><option value="">All industries</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected(($filters['category_id']??null)==$category->id)>{{ $category->name }}</option>@endforeach</select></div>
<div class="col-12"><label for="cleanup_search">Report name (optional)</label><input id="cleanup_search" name="search" class="form-control" maxlength="150" value="{{ $filters['search']??'' }}" placeholder="For example: Automotive Active Safety"></div>
</div>
<label class="d-block"><input type="hidden" name="sentences" value="0"><input type="checkbox" name="sentences" value="1" @checked($filters['sentences']??true)> Remove generic COVID-19 coverage sentences</label>
<p class="small text-muted">For example: &quot;The report also considers the impact of COVID-19 on the global [name] Market.&quot; Historical analysis is left for manual review.</p>
<label class="d-block"><input type="hidden" name="toc" value="0"><input type="checkbox" name="toc" value="1" @checked($filters['toc']??true)> Update old COVID-specific table-of-contents headings</label>
<p class="small text-muted">Replace the standard COVID impact and pre/post-COVID headings with &quot;Market Landscape and Industry Developments&quot; and &quot;Current Market Trends and Outlook.&quot; Keep section numbering.</p>
<div class="cleanup-actions mt-3">
@if(($filters['after']??0)>0)<button class="btn btn-primary" name="after" value="{{ $filters['after'] }}">Preview next batch</button>@endif
<button class="btn {{ ($filters['after']??0)>0?'btn-outline-primary':'btn-primary' }}" name="after" value="0">{{ ($filters['after']??0)>0?'Preview from beginning':'Preview reports' }}</button>
<a class="btn btn-light" href="{{ route('admin.cleanup.index') }}">Reset filters</a>
</div>
<p class="small text-muted mt-3 mb-0">Preview does not save changes. Reports about COVID-19 or pandemics are protected. Other COVID references must be reviewed in the report editor.</p>
</form></div>
@if($batch)
<div class="panel"><h2 class="h5 mb-2">Batch preview: {{ count($batch['rows']) }} matching reports</h2><p class="mb-0">Check each change below, select the reports you want to update, then choose Apply. Unselected reports remain unchanged and are skipped for this pass. Use Preview from beginning to revisit them.</p><p class="small text-muted mt-2 mb-0">Each preview searches a limited range to keep requests small. {{ $batch['more']?'Continue with the next batch to review later reports.':'This search has reached the end.' }}</p></div>
<form method="post" action="{{ route('admin.cleanup.apply') }}" id="cleanup-apply">@csrf<input type="hidden" name="token" value="{{ $token }}">
@if(count($batch['rows']))<div class="cleanup-actions mb-3"><label class="mb-0"><input type="checkbox" id="cleanup-select-all"> Select all reports with proposed changes</label><span class="small text-muted" id="cleanup-selected-count">0 selected</span></div>@endif
@forelse($batch['rows'] as $row)
<article class="panel">
<div class="d-flex gap-3 align-items-start">
@if(!$row['reason'] && count($row['changes']))<input class="cleanup-select mt-2" type="checkbox" name="ids[]" value="{{ $row['id'] }}" aria-label="Select report {{ $row['id'] }}">@endif
<div class="cleanup-report-title"><h3 class="cleanup-title mb-1">{{ $row['title'] }}</h3><div class="small text-muted">Report #{{ $row['id'] }} | {{ (int)$row['status']===1?'Published':'Draft / inactive' }}</div></div>
</div>
<div class="cleanup-actions my-3"><a target="_blank" rel="noopener" href="{{ route('admin.edit',['reports',$row['id']]) }}">Open report editor</a>@if((int)$row['status']===1)<a target="_blank" rel="noopener" href="{{ route('rd_view',$row['slug']) }}">View website report</a>@endif</div>
@if($row['reason'])<p class="mb-0 text-muted">{{ $row['reason'] }}</p>
@elseif(!count($row['changes']))<p class="mb-0">COVID-related text found, but it does not match the selected cleanup rules. Please review it in the report editor.</p>
@else
<p class="small">{{ count($row['changes']) }} proposed {{ count($row['changes'])===1?'change':'changes' }}. All other content is kept.</p>
@foreach($row['changes'] as $change)
<div class="small fw-bold">{{ ['content'=>'Report description','toc'=>'Table of contents','tables_figures'=>'Tables & figures','description'=>'Short description','meta_description'=>'SEO description'][$change['field']] }}</div>
<div class="cleanup-change"><div><strong>Before</strong><p>{{ html_entity_decode(strip_tags($change['before']),ENT_QUOTES|ENT_HTML5,'UTF-8') }}</p></div><div><strong>After</strong><p>{{ $change['after']===''?'Sentence removed.':html_entity_decode(strip_tags($change['after']),ENT_QUOTES|ENT_HTML5,'UTF-8') }}</p></div></div>
@endforeach
@if($row['remaining'])<p class="small text-muted mb-0">Other COVID-related references remain for individual review.</p>@endif
@endif
@if(($row['reason'] || !count($row['changes'])) && $row['reference'])<details class="mt-3"><summary>View matching text</summary><p class="mt-2 text-muted">...{{ $row['reference'] }}...</p></details>@endif
</article>
@empty<div class="panel">No matching reports in this search range. {{ $batch['more']?'Choose Preview next batch to continue.':'Try a different report name, industry, or status.' }}</div>@endforelse
@if(count($batch['rows']))<div class="panel cleanup-actions"><button id="cleanup-apply-button" class="btn btn-primary" disabled>Apply to selected reports</button><span class="small text-muted">Preview expires after 20 minutes. Reports edited after preview will be skipped.</span></div>@endif
</form>
@if($batch['more'])<form method="post" action="{{ route('admin.cleanup.preview') }}">@csrf @foreach($filters as $name=>$value)@if($name!=='after')<input type="hidden" name="{{ $name }}" value="{{ $value }}">@endif @endforeach<input type="hidden" name="after" value="{{ $batch['after'] }}"><button class="btn btn-outline-primary mb-4">Skip this batch / Preview next batch</button></form>@endif
@endif
@endsection
@push('scripts')
<script>(()=>{const boxes=[...document.querySelectorAll('.cleanup-select')],all=document.querySelector('#cleanup-select-all'),button=document.querySelector('#cleanup-apply-button'),count=document.querySelector('#cleanup-selected-count');const update=()=>{const selected=boxes.filter(b=>b.checked).length;if(button)button.disabled=!selected;if(count)count.textContent=selected+' selected';if(all){all.checked=boxes.length>0&&selected===boxes.length;all.indeterminate=selected>0&&selected<boxes.length;}};boxes.forEach(b=>b.addEventListener('change',update));all?.addEventListener('change',()=>{boxes.forEach(b=>b.checked=all.checked);update();});document.querySelector('#cleanup-apply')?.addEventListener('submit',e=>{if(!boxes.some(b=>b.checked)){e.preventDefault();return;}if(button){button.disabled=true;button.textContent='Applying selected changes...';}});update();})();</script>
@endpush
