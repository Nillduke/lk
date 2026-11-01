@extends('admin.layout')
@section('title','Report content cleanup')
@push('styles')
<style>.cleanup-progress-track{height:12px;border-radius:8px;background:#e8edf4;overflow:hidden}.cleanup-progress-track>div{height:100%;background:#244b8b;transition:width .2s}.cleanup-controls{display:flex;flex-wrap:wrap;gap:10px;align-items:center}.cleanup-select{width:18px;height:18px;flex:0 0 18px}.cleanup-heading{min-width:0;overflow-wrap:anywhere}.cleanup-change{display:grid;grid-template-columns:1fr 1fr;gap:14px}.cleanup-change>div{min-width:0;overflow-wrap:anywhere;background:#f8fafc;border:1px solid #dce4ed;padding:14px;border-radius:6px}.cleanup-row h3{font-size:17px;line-height:1.6}.cleanup-change p{white-space:pre-wrap;margin:8px 0 0}@@media(max-width:800px){.cleanup-change{grid-template-columns:1fr}}</style>
@endpush
@section('content')
<h1 class="h3">Report content cleanup</h1>
<p>Batch size: <strong>{{ number_format($progress['target']) }} reports</strong>. Review the preview before applying changes.</p>
<div class="panel" aria-live="polite">
<h2 class="h5" id="cleanup-phase">{{ ['previewing'=>'Preparing your preview','ready'=>'Preview ready','applying'=>'Applying selected changes','complete'=>'Batch complete'][$progress['phase']] }}</h2>
<p id="cleanup-progress-text">{{ number_format($progress['matched']) }} reports found; {{ number_format($progress['eligible']) }} eligible for cleanup.</p>
<div class="cleanup-progress-track mb-3"><div id="cleanup-progress-bar" style="width:0%"></div></div>
<div class="cleanup-controls"><button id="cleanup-resume" class="btn btn-outline-primary" hidden>Resume</button><button id="cleanup-pause" class="btn btn-outline-secondary" hidden>Pause</button><a href="{{ route('admin.cleanup.index') }}" class="btn btn-light">New search</a></div>
<p class="small text-muted mt-3 mb-0">Keep this page open while processing. You can pause and resume. Preview never changes report content; updates start only after you choose Apply. This batch expires after two hours.</p>
<p id="cleanup-error" class="text-danger mt-3 mb-0" role="alert" hidden></p>
</div>
@if($progress['phase']==='previewing')
<div class="panel"><p class="mb-0">Your {{ number_format($progress['target']) }}-report preview is being prepared. Before-and-after details will be available when it finishes.</p></div>
@else
<div class="panel">
<h2 class="h5">{{ number_format($progress['matched']) }} matching reports in this batch</h2>
<p>Showing page {{ $page }} of {{ max(1,$progress['pages']) }}, with up to 50 reports per page. Selection is kept when you move between pages.</p>
<p class="small text-muted">COVID-focused reports are protected. Unmatched historical text requires manual review. Reports edited after preview will be skipped.</p>
<div class="cleanup-controls">
<button class="btn btn-outline-primary cleanup-selection" id="cleanup-select-batch" @disabled($progress['phase']!=='ready')>Select all {{ number_format($progress['eligible']) }} eligible reports in this batch</button>
<button class="btn btn-outline-secondary cleanup-selection" id="cleanup-clear-batch" @disabled($progress['phase']!=='ready')>Clear selection</button>
<strong id="cleanup-selected-count">{{ number_format($progress['selected']) }} selected</strong>
</div>
<div class="cleanup-controls mt-3"><button id="cleanup-apply-batch" class="btn btn-primary" @disabled($progress['phase']!=='ready'||!$progress['selected'])>Apply to selected reports</button><span class="small text-muted">Only selected reports will be updated.</span></div>
</div>
@foreach($rows as $row)
<article class="panel cleanup-row">
<div class="d-flex gap-3 align-items-start">
@if(!$row['reason']&&count($row['changes']))<input type="checkbox" class="cleanup-select cleanup-selection" value="{{ $row['id'] }}" aria-label="Select report {{ $row['id'] }}" @checked($row['selected']) @disabled($progress['phase']!=='ready')>@endif
<div class="cleanup-heading"><h3 class="mb-1">{{ $row['title'] }}</h3><small class="text-muted">Report #{{ $row['id'] }} | {{ (int)$row['status']===1?'Published':'Draft / inactive' }}@if($row['result']) | {{ str_replace('_',' ',$row['result']) }}@endif</small></div>
</div>
<div class="cleanup-controls my-3"><a target="_blank" rel="noopener" href="{{ route('admin.edit',['reports',$row['id']]) }}">Open report editor</a>@if((int)$row['status']===1)<a target="_blank" rel="noopener" href="{{ route('rd_view',$row['slug']) }}">View website report</a>@endif</div>
@if($row['reason'])<p>{{ $row['reason'] }}</p>
@elseif(!count($row['changes']))<p>No matching automatic cleanup rule. Review this wording in the report editor.</p>
@else
<details><summary>{{ count($row['changes']) }} proposed changes - view before and after</summary>
@foreach($row['changes'] as $change)<p class="small fw-bold mt-3">{{ ['content'=>'Report description','toc'=>'Table of contents','tables_figures'=>'Tables & figures','description'=>'Short description','meta_description'=>'SEO description'][$change['field']] }}</p><div class="cleanup-change"><div><strong>Before</strong><p>{{ html_entity_decode(strip_tags($change['before']),ENT_QUOTES|ENT_HTML5,'UTF-8') }}</p></div><div><strong>After</strong><p>{{ $change['after']===''?'Sentence removed.':html_entity_decode(strip_tags($change['after']),ENT_QUOTES|ENT_HTML5,'UTF-8') }}</p></div></div>@endforeach
</details>
@if($row['remaining'])<p class="small text-muted mt-2">Other COVID references remain for individual review.</p>@endif
@endif
@if(($row['reason']||!count($row['changes']))&&$row['reference'])<details><summary>View matching text</summary><p>...{{ $row['reference'] }}...</p></details>@endif
</article>
@endforeach
@if(!$rows)<div class="panel">No matching reports found for these filters.</div>@endif
<div class="panel cleanup-controls" id="cleanup-pagination">
@if($page>1)<a class="btn btn-outline-primary" href="{{ route('admin.cleanup.job',['id'=>$progress['id'],'page'=>$page-1]) }}">Previous page</a>@endif
<span>Page {{ $page }} of {{ max(1,$progress['pages']) }}</span>
@if($page<$progress['pages'])<a class="btn btn-outline-primary" href="{{ route('admin.cleanup.job',['id'=>$progress['id'],'page'=>$page+1]) }}">Next page</a>@endif
</div>
@if($progress['more'])<div class="panel"><form method="post" action="{{ route('admin.cleanup.preview') }}">@csrf @foreach($filters as $name=>$value)@if($name!=='after')<input type="hidden" name="{{ $name }}" value="{{ $value }}">@endif @endforeach<input type="hidden" name="after" value="{{ $progress['after'] }}"><button class="btn btn-outline-primary" id="cleanup-next-batch">Preview next {{ number_format($progress['target']) }} reports</button></form><p class="small text-muted mt-2 mb-0">Unselected reports are skipped for this pass. Start a new search to revisit them.</p></div>@endif
@endif
@endsection
@push('scripts')
<script>
(()=>{
let state=@json($progress), running=false, paused=false, selecting=false;
const endpoint=@json(route('admin.cleanup.job.action',['id'=>$progress['id'],'action'=>'ACTION'])),page={{ $page }};
const el=id=>document.getElementById(id),boxes=[...document.querySelectorAll('.cleanup-select')];
const error=message=>{el('cleanup-error').textContent=message;el('cleanup-error').hidden=!message;};
async function request(action,data={}){
 const response=await fetch(endpoint.replace(/ACTION$/,action),{method:'POST',credentials:'same-origin',headers:{'Accept':'application/json','Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content},body:JSON.stringify(data)});
 if(!response.ok)throw new Error(response.status===419?'This batch or login expired. Open a new search and preview again.':'Processing stopped. Your completed progress is saved. Use Resume to continue.');
 state=await response.json();render();return state;
}
function render(){
 el('cleanup-phase').textContent=({previewing:'Preparing your preview',ready:'Preview ready',applying:'Applying selected changes',complete:'Batch complete'})[state.phase];
 const applying=['applying','complete'].includes(state.phase),total=applying?state.selected:state.target,value=applying?state.processed:state.matched;
 el('cleanup-progress-bar').style.width=(state.phase==='ready'||state.phase==='complete'?100:Math.min(100,total?100*value/total:0))+'%';
 el('cleanup-progress-text').textContent=applying?`${state.processed.toLocaleString()} of ${state.selected.toLocaleString()} selected reports processed: ${state.updated.toLocaleString()} updated, ${state.already_current.toLocaleString()} already current, ${state.skipped.toLocaleString()} skipped.`:`${state.matched.toLocaleString()} of up to ${state.target.toLocaleString()} reports found; ${state.eligible.toLocaleString()} eligible for cleanup.`;
 el('cleanup-resume').hidden=!['previewing','applying'].includes(state.phase)||running;
 el('cleanup-resume').textContent=state.phase==='applying'?'Resume applying':'Resume preview';
 el('cleanup-pause').hidden=!running;el('cleanup-pause').disabled=paused;el('cleanup-pause').textContent=paused?'Pausing...':'Pause';
 document.querySelectorAll('.cleanup-selection').forEach(b=>b.disabled=state.phase!=='ready'||selecting||running);
 if(el('cleanup-selected-count'))el('cleanup-selected-count').textContent=state.selected.toLocaleString()+' selected';
 if(el('cleanup-apply-batch'))el('cleanup-apply-batch').disabled=state.phase!=='ready'||!state.selected||selecting||running;
 const nav=el('cleanup-pagination');if(nav){nav.style.pointerEvents=selecting||running?'none':'';nav.style.opacity=selecting||running?'.5':'1';}
 if(el('cleanup-next-batch'))el('cleanup-next-batch').disabled=selecting||running||state.phase==='applying';
}
async function process(){
 if(running)return;running=true;paused=false;error('');render();
 try{
  while(!paused&&['previewing','applying'].includes(state.phase)){
   const previous=state.phase;await request(previous==='previewing'?'scan':'apply');
   if(state.phase!==previous){location.href=@json(route('admin.cleanup.job',$progress['id']));return;}
  }
 }catch(e){error(e.message);}finally{running=false;render();}
}
async function select(mode){
 if(selecting||running||state.phase!=='ready')return;selecting=true;error('');render();
 try{await request('select',{mode,page,ids:boxes.filter(b=>b.checked).map(b=>Number(b.value))});if(mode==='all'||mode==='none')boxes.forEach(b=>b.checked=mode==='all');}
 catch(e){error(e.message);location.reload();}finally{selecting=false;render();}
}
el('cleanup-select-batch')?.addEventListener('click',()=>select('all'));
el('cleanup-clear-batch')?.addEventListener('click',()=>select('none'));
boxes.forEach(b=>b.addEventListener('change',()=>select('page')));
el('cleanup-apply-batch')?.addEventListener('click',async()=>{if(running||selecting||!state.selected)return;running=true;render();try{await request('begin');running=false;process();}catch(e){error(e.message);running=false;render();}});
el('cleanup-resume').addEventListener('click',process);
el('cleanup-pause').addEventListener('click',()=>{paused=true;el('cleanup-pause').disabled=true;el('cleanup-pause').textContent='Pausing...';});
render();if(state.phase==='previewing')process();
})();
</script>
@endpush
