<form class="catalogue-hero-search" action="{{ url()->current() }}" method="get" role="search">
@foreach(request()->except(['q','page','_token']) as $name=>$value) @if(is_scalar($value))<input type="hidden" name="{{ $name }}" value="{{ $value }}">@endif @endforeach
<svg class="catalogue-search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><circle cx="10.5" cy="10.5" r="6.5"/><path d="m16 16 5 5"/></svg>
<input type="search" name="q" aria-label="Search reports" placeholder="Search reports" value="{{ request('q') }}">
<button type="submit" aria-label="Search reports"><svg viewBox="0 0 32 32" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="M5 16h22M17 5l11 11-11 11"/></svg></button>
</form>