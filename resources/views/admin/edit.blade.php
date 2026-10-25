@extends('admin.layout')
@section('title',($record?'Edit ':'Add ').$def['label'])
@push('styles')
<link rel="stylesheet" href="/frontend/css/admin-editors.css?v=20261024-2">
@endpush
@section('content')
<h1 class="h3 mb-4">{{ $record?'Edit':'Add' }} {{ $def['label'] }}</h1>
<form method="post" enctype="multipart/form-data" data-admin-editor-form data-resource="{{ $def['resource'] }}" data-image-upload="{{ route('admin.images.upload', [], false) }}" action="{{ $record?route('admin.update',[$def['resource'],$record->{$def['pk']}]):route('admin.store',$def['resource']) }}">
@csrf
<div class="alert alert-danger" data-editor-error role="alert" hidden></div>
<div class="panel"><div class="row">
@foreach($fields as $field)
@continue($def['resource']==='reports' && $field==='key_info')
<div class="{{ in_array($field,['content','description','message','key_info','featured_img'])?'col-12':'col-md-6' }}">
<label for="{{ $field }}">{{ str($field)->replace('_',' ')->title() }}</label>
@if(in_array($field,['category_id','publisher_id']))
<select id="{{ $field }}" class="form-select" name="{{ $field }}"><option value="">Select</option>@foreach($field==='category_id'?$categories:$publishers as $option)<option value="{{ $option->id }}" @selected((string)old($field,$record->$field??'')===(string)$option->id)>{{ $option->name }}</option>@endforeach</select>
@elseif($field==='featured_img')
<div class="admin-image-field" data-image-field>
<input class="form-control" id="featured_img" name="featured_img" value="{{ old($field,$record->$field??'') }}" placeholder="/uploads/images/... or https://..." aria-describedby="featured-image-help">
<label for="featured_img_file" class="small">Upload a featured image</label>
<input class="form-control" id="featured_img_file" name="featured_img_file" type="file" accept="image/jpeg,image/png,image/gif,image/webp" data-featured-upload>
<p class="text-muted small" id="featured-image-help">JPG, PNG, GIF or WebP, up to 10 MB. A new upload replaces the image path when you save.</p>
<img class="admin-image-preview" data-featured-preview src="{{ old($field,$record->$field??'') }}" alt="Featured image preview" @if(!old($field,$record->$field??''))hidden @endif>
<button type="button" class="btn btn-outline-secondary btn-sm mb-3" data-clear-image>Clear image</button>
</div>
@elseif(in_array($field,['content','description','message','key_info','meta_description','meta_keywords','payment_info','address','comment','comment_content','feedback','Description']))
<textarea class="form-control {{ $field==='content' && in_array($def['resource'],['news','pages'])?'admin-rich-editor':'' }}" id="{{ $field }}" name="{{ $field }}" rows="{{ $field==='content'?12:4 }}">{{ old($field,$record->$field??'') }}</textarea>
@else
<input class="form-control" id="{{ $field }}" name="{{ $field }}" type="{{ $field==='password'?'password':(in_array($field,['email','email_id','contact_email'])?'email':($field==='pub_date'?'date':'text')) }}" value="{{ $field==='password'?'':old($field,$record->$field??(['status'=>'1','is_active'=>'1','pages'=>'0','region'=>'Global','pub_date'=>now()->toDateString()][$field]??'')) }}">
@endif
</div>
@endforeach
</div></div>
@if($def['resource']==='reports')
@include('admin.report-options')
<div class="panel"><h2 class="h5">Report content</h2><p class="text-muted small">Format content, insert tables and upload images. The editor uses the website typography. Use Source code for existing HTML layouts.</p>
@foreach(['content','toc','tables_figures'] as $field)
<label for="report_{{ $field }}">{{ str($field)->replace('_',' ')->title() }}</label>
<textarea id="report_{{ $field }}" class="form-control admin-rich-editor" name="{{ $field }}" rows="10">{{ old($field,$meta->$field??'') }}</textarea>
@endforeach

</div>
@endif
@if($def['resource']==='users'&&auth()->user()->canAccess('manage_access'))<div class="panel"><label>Assigned roles</label><input type="hidden" name="role_ids[]" value="">@foreach($roles as $role)<label class="d-block"><input type="checkbox" name="role_ids[]" value="{{ $role->ID }}" @checked(in_array($role->ID,$assigned))> {{ $role->Title }}</label>@endforeach</div>@endif
@if($def['resource']==='roles')<div class="panel"><label>Permissions</label><input type="hidden" name="permission_ids[]" value="">@foreach($permissions as $permission)<label class="d-block"><input type="checkbox" name="permission_ids[]" value="{{ $permission->ID }}" @checked(in_array($permission->ID,$assignedPermissions))> {{ $permission->Title }}</label>@endforeach</div>@endif
<button class="btn btn-success" type="submit">Save changes</button> <a class="btn btn-light" href="{{ route('admin.index',$def['resource']) }}">Cancel</a>
</form>
@endsection
@push('scripts')
<script src="/vendor/tinymce/tinymce.min.js?v=8.10.0" defer></script>
<script src="/frontend/js/admin-editors.js?v=20261024-2" defer></script>
@endpush
