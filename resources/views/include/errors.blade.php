@php
    if (isset($validation)) {@endphp
        <div class="alert alert-danger" role="alert">
            @php    echo $validation->listErrors();@endphp
        </div>
@php  } @endphp
