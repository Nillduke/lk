@extends('admin.layout')
@section('title','Import reports')
@section('content')
<h1 class="h3 mb-4">Import or update reports</h1>
<div class="panel">
<p>Upload CSV or XLSX with a header row, up to 5,000 reports and 20 MB per file.</p>
<p><a href="{{ route('admin.template') }}">Download the CSV template</a></p>
<ul><li>New reports need a title, category and publisher. Blank slugs are generated from the title.</li><li>Existing reports match by <code>id</code> or <code>slug</code>. Blank cells keep existing values, including content, images, prices and segments.</li><li>For new reports, blank content and TOC use your existing report templates. Supplied content and TOC are kept.</li><li>Use <code>image_url</code> for an uploaded image path or an HTTP(S) image URL. CSV files reference images; they do not contain uploaded image files.</li><li>Use <code>include_images</code> (0 or 1) and <code>regenerate_content</code> (0 or 1) to override the choices below for a row. Image settings apply to generated template content; supplied HTML keeps its images.</li><li>License prices must be whole numbers, matching the existing database. Blank price cells keep existing prices.</li></ul>
<form method="post" enctype="multipart/form-data" action="{{ route('import_reports') }}">@csrf
<label for="import-file">Spreadsheet</label><input id="import-file" class="form-control" type="file" name="file" accept=".csv,.xlsx" required>
<div class="row">
<div class="col-md-6"><label for="import-category">Default category for new reports</label><select id="import-category" class="form-select" name="category_id"><option value="">Use spreadsheet</option>@foreach($categories as $category)<option value="{{ $category->id }}">{{ $category->name }}</option>@endforeach</select></div>
<div class="col-md-6"><label for="import-publisher">Default publisher for new reports</label><select id="import-publisher" class="form-select" name="publisher_id"><option value="">Use spreadsheet</option>@foreach($publishers as $publisher)<option value="{{ $publisher->id }}">{{ $publisher->name }}</option>@endforeach</select></div>
</div>
<label class="d-block"><input type="checkbox" name="update_existing" value="1" id="update-existing"> Update existing reports matched by ID or slug</label>
<input type="hidden" name="include_images" value="0"><label class="d-block"><input type="checkbox" name="include_images" value="1" checked> Include images in generated template content</label>
<label class="d-block"><input type="checkbox" name="regenerate_content" value="1" id="regenerate-content"> Regenerate existing content and TOC where spreadsheet cells are blank</label>
<p class="text-muted small">Regeneration replaces saved content and TOC with the existing templates. Leave it unchecked to preserve your saved content. Row-level regeneration also requires updates to be enabled.</p>
<button class="btn btn-success mt-2">Import reports</button>
</form></div>
@if(session('import_results'))<div class="panel"><h2 class="h5">Import results</h2><p>Created: {{ session('import_results.created',0) }} &middot; Updated: {{ session('import_results.updated',0) }} &middot; Skipped: {{ session('import_results.skipped',0) }} &middot; Failed: {{ session('import_results.failed',0) }}</p>@foreach(session('import_results.errors',[]) as $error)<div class="text-danger">{{ $error }}</div>@endforeach</div>@endif
<div class="panel"><h2 class="h5">Upload report media</h2><p>Upload an image first, then copy its path into <code>image_url</code> or your content HTML. Images: JPG, PNG, GIF or WebP. Documents: PDF, CSV or XLSX. Maximum 20 MB.</p>
<form action="{{ route('images.upload') }}" method="post" enctype="multipart/form-data" id="import-media-form">@csrf<input type="file" name="file" class="form-control" accept=".jpg,.jpeg,.png,.gif,.webp,.pdf,.csv,.xlsx" aria-label="Report media" required><button class="btn btn-outline-primary">Upload file</button></form><div id="import-media-result" class="mt-3" role="status"></div></div>
@endsection
@push('scripts')
<script>
(()=>{
const update=document.getElementById('update-existing'),regen=document.getElementById('regenerate-content');
const adapt=()=>{regen.disabled=!update.checked;if(!update.checked)regen.checked=false};update.addEventListener('change',adapt);adapt();
const form=document.getElementById('import-media-form'),result=document.getElementById('import-media-result');
form.addEventListener('submit',async e=>{e.preventDefault();const button=form.querySelector('button');button.disabled=true;result.textContent='Uploading...';try{const response=await fetch(form.action,{method:'POST',body:new FormData(form),headers:{Accept:'application/json'}});const data=await response.json();if(!response.ok)throw Error(Object.values(data.errors||{}).flat().join(' ')||data.message||'Upload failed');result.replaceChildren();const label=document.createElement('label');label.textContent='Uploaded file path';const input=document.createElement('input');input.className='form-control';input.readOnly=true;input.value=data.url;input.setAttribute('aria-label','Uploaded file path');input.addEventListener('click',()=>input.select());const link=document.createElement('a');link.href=data.url;link.target='_blank';link.rel='noopener';link.textContent='Open uploaded file';result.append(label,input,link);}catch(error){result.textContent=error.message}finally{button.disabled=false}});
})();
</script>
@endpush
