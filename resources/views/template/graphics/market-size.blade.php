<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 620" role="img" aria-labelledby="chart-title chart-desc">
<title id="chart-title">{{ $keyword }} Market Size, {{ $period['range'] }}</title>
<desc id="chart-desc">{{ $period['label'] }}. Illustrative preview; the base-year placeholder does not represent a published market value.</desc>
<rect width="1200" height="620" fill="#fff"/>
<rect width="1200" height="96" fill="#182e75"/>
<text x="40" y="40" font-family="Arial, sans-serif" font-size="24" font-weight="700" fill="#fff">Market Size Overview | {{ $period['range'] }}</text>
<text x="40" y="73" font-family="Arial, sans-serif" font-size="18" fill="#dfe8f8">Eternity Insights</text>
<g font-family="Arial, sans-serif" text-anchor="middle">
@foreach($period['years'] as $i=>$year)
@php $x=90+$i*79; $height=90+($i%4)*28; $color=$year<=$period['historical_end']?'#8cacc9':($year===$period['base_year']?'#182e75':'#427ca9'); @endphp
<rect x="{{ $x-21 }}" y="{{ 437-$height }}" width="42" height="{{ $height }}" rx="3" fill="{{ $color }}"/>
@if($year === $period['base_year'])
<text x="{{ $x }}" y="{{ 421-$height }}" font-size="15" fill="#34445a">XX</text>
@endif
<text x="{{ $x }}" y="465" font-size="15" fill="#34445a">{{ $year }}</text>
@endforeach
</g>
<path d="M50 438H1150" stroke="#acbbce" stroke-width="2"/>
<g font-family="Arial, sans-serif" font-size="16" fill="#34445a">
<rect x="330" y="493" width="14" height="14" fill="#8cacc9"/><text x="352" y="506">Historical</text>
<rect x="490" y="493" width="14" height="14" fill="#182e75"/><text x="512" y="506">Base year</text>
<rect x="650" y="493" width="14" height="14" fill="#427ca9"/><text x="672" y="506">Forecast</text>
<text x="600" y="545" text-anchor="middle" font-size="14" fill="#65758a">Illustrative preview | Market values available in the report</text>
</g>
<rect y="566" width="1200" height="54" fill="#eef3fa"/>
<text x="600" y="600" text-anchor="middle" font-family="Arial, sans-serif" font-size="18" fill="#182e75">{{ $period['label'] }}</text>
</svg>
