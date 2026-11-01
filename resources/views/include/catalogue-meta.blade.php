@php
    // Retain content-changing filters, but consolidate tracking parameters and page one.
    $canonicalQuery = array_filter(request()->only(['page', 'per_page', 'keySectors', 'keyRegionsCovered', 'selectPrice', 'selectPeriod', 'q']),
        fn ($value, $key) => is_scalar($value) && trim((string) $value) !== '' && ($key === 'q' || $value !== 'all'), ARRAY_FILTER_USE_BOTH);
    if (isset($canonicalQuery['page'])) {
        $page = filter_var($canonicalQuery['page'], FILTER_VALIDATE_INT);
        if ($page > 1) $canonicalQuery['page'] = $page;
        else unset($canonicalQuery['page']);
    }
    if (isset($canonicalQuery['per_page'])) {
        $perPage = (int) $canonicalQuery['per_page'];
        if (in_array($perPage, [20, 25, 50, 100], true)) $canonicalQuery['per_page'] = $perPage;
        else unset($canonicalQuery['per_page']);
    }
    ksort($canonicalQuery);
    $canonicalUrl = request()->url().($canonicalQuery ? '?'.http_build_query($canonicalQuery, '', '&', PHP_QUERY_RFC3986) : '');
@endphp
<link rel="canonical" href="{{ $canonicalUrl }}" />
