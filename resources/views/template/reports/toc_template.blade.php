@php 
$stack = $key_players;
$kp1 = array_shift($stack);
$br = '<br/>';
@endphp
<p class="l1"><strong>1 Methodology and Scope</strong></p>
<p class="l2">1.1 Market segmentation & scope<br/>1.2 Information Procurement</p>
<p class="l3">1.2.1 Purchased Database<br/>1.2.2 Eternityâ€™s Internal Database<br/>1.2.3 Secondary sources & third-party perspectives<br/>1.2.4 Primary Research</p>
<p class="l2">1.3 Information analysis<br/>1.4 Market formulation & data visualization<br/>1.5 Data validation & publishing</p>
<p class="l1"><strong>2 Executive Summary</strong></p>
<p class="l1"><strong>3 {{ $keyword }} Market Variables, Trends & Scope</strong></p>
<p class="l2">3.1 Market Dynamics</p>
<p class="l3">3.1.1 Market Driver Analysis<br/>3.1.2 Market Restraints<br/>3.1.3 Market Challenge Analysis<br/>3.1.4 Market Opportunities<br/>3.1.5 Micro & Macro Indicator</p>
<p class="l2">3.2 Impact of COVID-19 Pandemic on Global {{ $keyword }} Market</p>
<p class="l3">3.2.1 Overview of Changing Landscape<br/>3.2.2 Pre-covid & Post-Covid Market Analysis</p>
<p class="l2">3.3 Market Attractiveness Analysis<br/>3.4 Regulatory Scenario<br/>3.5 Industry Trend Analysis- Historic and Future Trend Assessment<br/>3.6 Merger & Acquisition<br/>3.7 New System Launch/Approvals<br/>3.8 Market Analysis Tools</p>
<p class="l3">3.8.1 Porterâ€™s Five Forces<br/>3.8.2 PEST Analysis<br/>3.8.3 Value Chain Analysis</p>
<p class="l2">3.9 Key Company Ranking Analysis, 2020</p>
<p class="l1"><strong>4 Global {{ $keyword }} Market Analysis and Forecast, By Type</strong></p>
<p class="l2">4.1 Overview & Definition<br/>4.2 Key Trends<br/>4.3 Global {{ $keyword }} Market Value (US$ Mn) Forecast, by Type (2019 â€“ 2028)</p>
<p class="l3">
@php 
    $ikp = 1;
     $i = 1; $ckpl = count($types);
    foreach ($types as $tys) {
        $bth = $i != $ckpl ? $br : '';
        echo '4.2.'.$ikp.' '.e($tys).$bth;
        $ikp++; $i++;
    }@endphp
</p>
<p class="l2">4.4 Type Comparison Matrix<br/>4.5 Global {{ $keyword }} Market Attractiveness, By Type</p>
<p class="l1"><strong>5 Global {{ $keyword }} Market Analysis and Forecast, By Application</strong></p>
<p class="l2">5.1 Overview & Definition<br/>5.2 Key Trends<br/>5.3 Global {{ $keyword }} Market Value (US$ Mn) Forecast, by Application (2019 â€“ 2028)</p>
<p class="l3">
@php 
    $ikp = 1;
     $i = 1; $ckpl = count($applications);

    foreach ($applications as $aps) {
        $bth = $i != $ckpl ? $br : '';
        echo '5.3.'.$ikp.' '.e($aps).$bth;
        $ikp++; $i++;
    }@endphp
</p>
<p class="l2">5.4 Application Comparison Matrix<br/>5.5 Global {{ $keyword }} Market Attractiveness, by Application</p>
<p class="l1"><strong>6 Global {{ $keyword }} Market Analysis and Forecast, By Region</strong></p>
<p class="l2">6.1 Overview & Definition<br/>6.2 Key Trends<br/>6.3 Global {{ $keyword }} Market Value (US$ Mn) Forecast, by Region (2019 â€“ 2028)<br/>6.4 North America<br/>6.5 Europe<br/>6.6 Asia Pacific<br/>6.7 Latin America<br/>6.8 Middle East & Africa</p>
<p class="l1"><strong>7 Global {{ $keyword }} Market Attractiveness, by Region</strong></p>
<p class="l1"><strong>8 North America {{ $keyword }} Market Analysis and Forecast</strong></p>
<p class="l2">8.1 Key Findings<br/>8.2 North America {{ $keyword }} Market Analysis (US$ Mn) and Forecast, By Type (2019-2028)</p>
<p class="l3">
@php 
    $ikp = 1;
     $i = 1; $ckpl = count($types);
    foreach ($types as $tys) {
        $bth = $i != $ckpl ? $br : '';
        echo '8.2.'.$ikp.' '.e($tys).$bth;
        $ikp++; $i++;
    }@endphp
</p>
<p class="l2">8.3 North America {{ $keyword }} Market Analysis (US$ Mn) and Forecast, By Application (2019-2028)</p>
<p class="l3">
@php 
    $ikp = 1;
     $i = 1; $ckpl = count($applications);

    foreach ($applications as $aps) {
        $bth = $i != $ckpl ? $br : '';
        echo '8.3.'.$ikp.' '.e($aps).$bth;
        $ikp++; $i++;
    }@endphp
</p>
<p class="l2">8.4 North America {{ $keyword }} Market Analysis (US$ Mn) and Forecast, By Country (2019-2028) </p>
<p class="l3">8.4.1 U.S.<br/>8.4.2 Canada<br/>8.4.3 Rest of North America</p>
<p class="l2">8.5 North America {{ $keyword }} Market Attractiveness Analysis </p>
<p class="l3">8.5.1 By Type<br/>8.5.2 By Application<br/>8.5.3 By Country</p>
<p class="l1"><strong>9 Europe {{ $keyword }} Market Analysis and Forecast</strong></p>
<p class="l2">9.1 Key Findings<br/>9.2 Europe {{ $keyword }} Market Analysis (US$ Mn) and Forecast, By Type (2019-2028)
</p>
<p class="l3">
@php 
    $ikp = 1;
     $i = 1; $ckpl = count($types);
    foreach ($types as $tys) {
        $bth = $i != $ckpl ? $br : '';
        echo '9.2.'.$ikp.' '.e($tys).$bth;
        $ikp++; $i++;
    }@endphp
</p>
<p class="l2">9.3 Europe {{ $keyword }} Market Analysis (US$ Mn) and Forecast, By Application (2019-2028)</p> 
<p class="l3">
@php 
    $ikp = 1;
     $i = 1; $ckpl = count($applications);

    foreach ($applications as $aps) {
        $bth = $i != $ckpl ? $br : '';
        echo '9.3.'.$ikp.' '.e($aps).$bth;
        $ikp++; $i++;
    }@endphp
</p>
<p class="l2">9.4 Europe {{ $keyword }} Market Analysis (US$ Mn) and Forecast, By Country (2019-2028) </p>
<p class="l3">9.4.1 U.K.<br/>9.4.2 Germany<br/>9.4.3 France<br/>9.4.4 Italy<br/>9.4.5 Rest of Europe</p>
<p class="l2">9.5 Europe {{ $keyword }} Market Attractiveness Analysis</p> 
<p class="l3">9.5.1 By Type<br/>9.5.2 By Application<br/>9.5.3 By Country</p>
<p class="l1"><strong>10 Asia Pacific {{ $keyword }} Market Analysis and Forecast</strong></p>
<p class="l2">10.1 Key Findings<br/>
10.2 Asia Pacific {{ $keyword }} Market Analysis (US$ Mn) and Forecast, By Type (2019-2028)
</p>
<p class="l3">
@php 
    $ikp = 1;
     $i = 1; $ckpl = count($types);
    foreach ($types as $tys) {
        $bth = $i != $ckpl ? $br : '';
        echo '10.2.'.$ikp.' '.e($tys).$bth;
        $ikp++; $i++;
    }@endphp
</p>
<p class="l2">10.3 Asia Pacific {{ $keyword }} Market Analysis (US$ Mn) and Forecast, By Application (2019-2028) </p>
<p class="l3">
@php 
    $ikp = 1;
     $i = 1; $ckpl = count($applications);

    foreach ($applications as $aps) {
        $bth = $i != $ckpl ? $br : '';
        echo '10.3.'.$ikp.' '.e($aps).$bth;
        $ikp++; $i++;
    }@endphp
</p>
<p class="l2">10.4 Asia Pacific {{ $keyword }} Market Analysis (US$ Mn) and Forecast, By Country (2019-2028)</p> 
<p class="l3">10.4.1 China<br/>10.4.2 India<br/>10.4.3 Japan<br/>10.4.4 South Korea<br/>10.4.5 Rest of APAC</p>
<p class="l2">10.5 Asia Pacific {{ $keyword }} Market Attractiveness Analysis </p>
<p class="l3">10.5.1 By Type<br/>10.5.2 By Application<br/>10.5.3 By Country</p>
<p class="l1"><strong>11 Latin America {{ $keyword }} Market Analysis and Forecast</strong></p>
<p class="l2">11.1 Key Findings<br/>11.2 Latin America {{ $keyword }} Market Analysis (US$ Mn) and Forecast, By Type (2019-2028)</p>
<p class="l3">
@php 
    $ikp = 1;
     $i = 1; $ckpl = count($types);
    foreach ($types as $tys) {
        $bth = $i != $ckpl ? $br : '';
        echo '11.2.'.$ikp.' '.e($tys).$bth;
        $ikp++; $i++;
    }@endphp
</p>
<p class="l2">11.3 Latin America {{ $keyword }} Market Analysis (US$ Mn) and Forecast, By Application (2019-2028)</p> 
<p class="l3">
@php 
    $ikp = 1;
     $i = 1; $ckpl = count($applications);

    foreach ($applications as $aps) {
        $bth = $i != $ckpl ? $br : '';
        echo '11.3.'.$ikp.' '.e($aps).$bth;
        $ikp++; $i++;
    }@endphp
</p>
<p class="l2">11.4 Latin America {{ $keyword }} Market Analysis (US$ Mn) and Forecast, By Country (2019-2028)</p> 
<p class="l3">11.4.1 Brazil<br/>11.4.2 Rest of Latin America China</p>
<p class="l2">11.5 Latin America {{ $keyword }} Market Attractiveness Analysis </p>
<p class="l3">11.5.1 By Type<br/>11.5.2 By Application<br/>11.5.3 By Country</p>
<p class="l1"><strong>12 Middle East & Africa (MEA) {{ $keyword }} Market Analysis and Forecast</strong></p>
<p class="l2">12.1 Key Findings<br/>12.2 Middle East & Africa (MEA) {{ $keyword }} Market Analysis (US$ Mn) and Forecast, By Product Type (2019-2028)</p> 
<p class="l3">
@php 
    $ikp = 1;
     $i = 1; $ckpl = count($types);
    foreach ($types as $tys) {
        $bth = $i != $ckpl ? $br : '';
        echo '12.2.'.$ikp.' '.e($tys).$bth;
        $ikp++; $i++;
    }@endphp 
</p>
<p class="l2">12.3 Middle East & Africa (MEA) {{ $keyword }} Market Analysis (US$ Mn) and Forecast, By Application (2019-2028)</p> 
<p class="l3">
@php 
    $ikp = 1;
     $i = 1; $ckpl = count($applications);

    foreach ($applications as $aps) {
        $bth = $i != $ckpl ? $br : '';
        echo '12.3.'.$ikp.' '.e($aps).$bth;
        $ikp++; $i++;
    }@endphp 
</p>
<p class="l2">12.4 Middle East & Africa (MEA) {{ $keyword }} Market Analysis (US$ Mn) and Forecast, By Country (2019-2028)</p> 
<p class="l3">12.4.1 GCC Countries<br/>12.4.2 South Africa<br/>12.4.3 Rest of MEA</p>
<p class="l2">12.5 Middle East & Africa (MEA) {{ $keyword }} Market Attractiveness Analysis </p>
<p class="l3">12.5.1 By Type<br/>12.5.2 By Application<br/>12.5.3 By Country</p>
<p class="l1"><strong>13 Competitive Landscape</strong></p>
<p class="l2">13.1 Market Players â€“ Competition Matrix<br/>13.2 Market Share Analysis by Key Players, 2020 (%)<br/>13.3 Company Profiles</p>
<p class="l3">13.3.1 {{ $kp1 }}</p>
<p class="l4">13.3.1.1 Company Overview<br/>13.3.1.2 Company Snapshot<br/>13.3.1.3 Financial Overview<br/>13.3.1.4 Product Portfolio<br/>13.3.1.5 Key Developments/Strategies</p>
<p class="l3">
    @php 
    $ikp = 2;
     $i = 1; $ckpl = count($stack);
     $br = '<br/>';
    foreach ($stack as $kps) {
        $bth = $i != $ckpl ? $br : '';
        echo '13.3.'.$ikp.' '.e($kps).$bth;
        $ikp++; $i++;
    }@endphp    
</p>
<p class="l1"><strong>14 Key Takeaways</strong></p>
