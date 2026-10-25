                    <div class="latRepbox catalogue-report-card">
                        <div class="d-flex">
                            <div class="flex-shrink-0 repcover-min listcover">
                                <div class="book-min ">
                                    <img src="{{ url('frontend/images/cover-1.jpg') }}" height="100" width="85" class="img-fluid img-min-front"
                                    alt="eternity report cover" />
                                    <div class="imgmiddle-min"></div>
                                    <img src="{{ url('frontend/images/cover-1.jpg') }}" height="100" width="85" class="img-fluid img-min-back"
                                    alt="eternity report cover" />
                                </div>
                                <div class="txt-min">{{ $report->keyword }} Market</div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h4 class="similar-rep-title catalogue-report-title">
                                    <a href="{{ route('rd_view',$report->slug) }}">{{ $report->title }}</a>
                                </h4>
                                <div class="title-toggle-slot"><button class="catalogue-title-toggle" type="button" aria-expanded="false" hidden>Show full title <span aria-hidden="true">+</span></button></div>
                                <ul class="list-inline rd-info latrep-info mt-2 mb-3">
                                    <li class="list-inline-item latrep-contents catalogue-report-code">Report ID: {{ $report->id }}</li>
                                    <li class="list-inline-item latrep-contents">{{ site_date($report->pub_date, 'M Y') }}</li>
                                    <li class="list-inline-item latrep-contents">{{ $report->category_name }}</li>
                                    <li class="list-inline-item latrep-contents">{{ $report->pages }} PAGES</li>
                                    <li class="list-inline-item latrep-contents">$ {{ $report->lic_price }}</li>
                                </ul>
                                @if(!empty($report->listing_description))<p class="catalogue-report-description">{{ \Illuminate\Support\Str::limit(strip_tags($report->listing_description), 390) }}</p>@endif
                                <div class="catalogue-card-actions"><a href="{{ route('rd_view',$report->slug) }}" class="btn btn-latrepView">View Report &amp; ToC</a>
                                <a href="{{ route('request_sample',$report->id) }}" class="btn btn-latrepView">Download Free Sample</a></div>
                            </div>
                        </div>
                    </div>
