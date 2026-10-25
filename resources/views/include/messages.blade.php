@if($errors->any())<div class="alert alert-danger"><ul>@foreach($errors->all() as $message)<li>{{ $message }}</li>@endforeach</ul></div>@endif
@foreach(['error_message'=>'danger','warning_message'=>'warning','success_message'=>'success'] as $key=>$style)@if(session($key))<div class="alert alert-{{ $style }}">{{ session($key) }}</div>@endif @endforeach
