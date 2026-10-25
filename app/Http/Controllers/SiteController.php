<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class SiteController extends Controller
{
    public static function catalogue()
    {
        return DB::table('reports as r')->leftJoin('categories as c', 'c.id', '=', 'r.category_id')
            ->select('r.*', 'c.name as category_name', 'c.slug as category_slug')
            ->selectSub(DB::table('reports_licenses as l')->selectRaw('MIN(l.lic_price)')->whereColumn('l.report_id', 'r.id'), 'lic_price');
    }

    private function reports($category = null, $limit = 4)
    {
        return self::catalogue()->leftJoin('reports_meta as m', 'm.report_id', '=', 'r.id')->addSelect(DB::raw('LEFT(m.content,250) as description'))->where('r.status', 1)->when($category, fn ($q) => $q->where('r.category_id', $category))->orderByDesc('r.pub_date')->limit($limit)->get()->all();
    }

    private function countries()
    {
        return DB::table('country')->orderBy('nicename')->get()->all();
    }

    public function home(Request $request)
    {
        $data = ['title' => 'Eternity Insights: Market Research Reports, Industry Analysis', 'meta_description' => 'Market research reports and industry analysis', 'indnews' => DB::table('news')->where('status', 1)->whereNull('deleted_at')->orderByDesc('created_at')->limit(6)->get()->all()];
        foreach (['one' => 15, 'two' => 19, 'three' => 21, 'four' => 25, 'five' => 26, 'six' => 12] as $key => $id) {
            $data['reports_'.$key] = $this->reports($id);
        }
        $data['reports'] = $this->reports(null, 10);

        return view($request->path() === 'amp' ? 'front.amp.home_amp' : 'home', $data);
    }

    public function page(Request $request)
    {
        $pages = ['aboutus' => ['about', 'About Us'], 'services' => ['services', 'Services'], 'faqs' => ['front.faqs', 'FAQs'], 'how-to-order' => ['front.how_to_order', 'How to Order'], 'disclaimer' => ['front.disclaimer', 'Disclaimer'], 'terms-and-conditions' => ['front.terms_conditions', 'Terms and Conditions'], 'refund-policy' => ['front.refund_policy', 'Refund Policy'], 'privacy-policy' => ['front.privacy_policy', 'Privacy Policy'], 'contactus' => ['contact_form1', 'Contact Us'], 'html-sitemap' => ['front.html_sitemap', 'HTML Sitemap']];
        [$view,$title] = $pages[$request->path()];
        $custom = DB::table('lr_pages')->where('slug', $request->path())->where('status', 1)->first();
        if ($custom) {
            return view('cms-page', ['title' => $custom->title, 'content' => $custom->content]);
        }

return view($view, ['title' => $title, 'countries' => $this->countries(), 'industries' => DB::table('categories')->get()->all()]);
    }

    public function listing(Request $request, $slug = null)
    {
        $industry = $slug ? DB::table('categories')->where('slug', $slug)->first() : null;
        abort_if($slug && ! $industry, 404);
        $q = self::catalogue()->where('r.status', 1)
            ->selectSub(DB::table('reports_meta')->selectRaw('LEFT(content, 850)')->whereColumn('report_id', 'r.id')->limit(1), 'listing_description');
        if ($industry) {
            $q->where('r.category_id', $industry->id);
        }
        if ($request->filled('keySectors') && $request->keySectors !== 'all') {
            $q->where('r.category_id', (int) $request->keySectors);
        }
        if ($request->filled('keyRegionsCovered') && $request->keyRegionsCovered !== 'all') {
            $q->where('r.title', 'like', '%'.$request->keyRegionsCovered.'%');
        }
        $price = $request->selectPrice;
        $minimumPrice = '(SELECT MIN(pl.lic_price) FROM reports_licenses AS pl WHERE pl.report_id = r.id)';
        if ($price === 'upto2000') {
            $q->whereRaw($minimumPrice.' <= ?', [2000]);
        } elseif ($price === 'above') {
            $q->whereRaw($minimumPrice.' >= ?', [4000]);
        } elseif ($price === '2000-4000') {
            $q->whereRaw($minimumPrice.' BETWEEN ? AND ?', [2000, 4000]);
        }
        $days = ['less3months' => 90, 'less6months' => 180, 'less1year' => 365][$request->selectPeriod] ?? null;
        if ($days) {
            $q->where('r.pub_date', '>=', now()->subDays($days));
        }
        if ($request->filled('q')) {
            $q->where('r.title', 'like', '%'.trim((string) $request->input('q')).'%');
        }
        $perPage = (int) $request->input('per_page', 12);
        if (! in_array($perPage, [12, 20, 25, 50, 100], true)) {
            $perPage = 12;
        }
        $list = $q->orderByDesc('r.pub_date')->paginate($perPage)->withQueryString();

        return view($industry ? 'front.industry_list' : 'front.all_reports', ['title' => $industry ? ($industry->meta_title ?: $industry->name.' Market Research Reports') : 'All Reports | '.config('app.name'), 'industry' => $industry, 'cataloguePagination' => $list, 'list' => $list->items(), 'pager' => $list->links()->toHtml(), 'categories' => DB::table('categories')->get()->all(), 'keySector' => $request->keySectors, 'keyRegionsCovered' => $request->keyRegionsCovered, 'selectPrice' => $price, 'selectPeriod' => $request->selectPeriod]);
    }

    public function report(Request $request, $slug)
    {
        $report = self::catalogue()->leftJoin('publishers as p', 'p.id', '=', 'r.publisher_id')->leftJoin('reports_meta as m', 'm.report_id', '=', 'r.id')->addSelect('p.name as publisher_name', 'm.content', 'm.toc', 'm.tables_figures')->where('r.slug', $slug)->where('r.status', 1)->first();
        abort_unless($report, 404);
        $key = json_decode($report->key_info ?? '', true) ?: [];
        $keyword = $report->keyword ?: preg_replace('/\s+market.*$/i', '', $report->title);
        $similar = self::catalogue()->where('r.category_id', $report->category_id)->where('r.status', 1)->where('r.id', '!=', $report->id)->orderByDesc('r.pub_date')->limit(10)->get()->all();

        return view(str_starts_with($request->path(), 'report/amp/') ? 'front.amp.rd_amp_view' : 'front.rd_view', ['title' => $report->meta_title ?: $report->title, 'meta_description' => $report->meta_description ?: $report->title, 'report' => $report, 'keyword' => $keyword, 'types' => $key['types'] ?? '', 'applications' => $key['applications'] ?? '', 'key_players' => $key['key_players'] ?? '', 'licenses' => DB::table('reports_licenses')->where('report_id', $report->id)->orderBy('lic_price')->get()->all(), 'similar' => $similar, 'countries' => $this->countries()]);
    }

    private function reportSearchTerm(Request $request): string
    {
        $request->validate(['s' => 'nullable|string|max:120', 'term' => 'nullable|string|max:120']);
        return trim(preg_replace('/\s+/u', ' ', (string) $request->input('s', $request->input('term', ''))));
    }

    private function matchReportSearch($query, string $term)
    {
        foreach (array_slice(preg_split('/\s+/u', $term), 0, 10) as $word) {
            $literal = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $word);
            $query->where(fn ($q) => $q->where('r.title', 'like', '%'.$literal.'%')->orWhere('r.keyword', 'like', '%'.$literal.'%'));
        }
        $literal = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $term);
        return $query->orderByRaw('CASE WHEN r.keyword LIKE ? THEN 0 WHEN r.keyword LIKE ? THEN 1 WHEN r.title LIKE ? THEN 2 ELSE 3 END', [$literal.'%', '%'.$literal.'%', $literal.'%']);
    }

    public function search(Request $request)
    {
        $s = $this->reportSearchTerm($request);
        $q = self::catalogue()->where('r.status', 1);
        $searchPagination = $s !== '' ? $this->matchReportSearch($q, $s)->orderByDesc('r.pub_date')->orderByDesc('r.id')->paginate(12)->withQueryString() : null;
        $list = $searchPagination ? $searchPagination->items() : [];

        return view('front.search', ['title' => 'Search Results for '.$s, 's' => $s, 'list' => $list, 'searchPagination' => $searchPagination, 'countries' => $this->countries()]);
    }

    public function autocomplete(Request $request)
    {
        $s = $this->reportSearchTerm($request);
        if (mb_strlen($s) < 2) {
            return response()->json(['status' => 1, 'message' => 'Type at least two characters.', 'data' => ['q' => $s, 'reports' => [], 'news' => []]]);
        }
        $reports = $this->matchReportSearch(DB::table('reports as r')->where('r.status', 1), $s)
            ->orderByDesc('r.pub_date')->orderByDesc('r.id')->limit(10)->get(['r.id', 'r.uuid', 'r.title', 'r.keyword', 'r.slug', 'r.status', 'r.pub_date']);
        foreach ($reports as $r) {
            $r->short_title = trim($r->keyword ?: $r->title);
            if (!preg_match('/\bmarket$/i', $r->short_title)) $r->short_title .= ' Market';
            $r->date_string = site_date($r->pub_date, 'd M Y');
            $r->rd_link = route('rd_view', $r->slug);
        }
        // Retain news in the API for existing consumers; report search suggestions use reports.
        $literal = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $s);
        $news = DB::table('news')->where('status', 1)->whereNull('deleted_at')->where('headline', 'like', '%'.$literal.'%')->limit(10)->get(['id', 'uuid', 'headline', 'slug', 'created_at']);
        foreach ($news as $n) {
            $n->date_string = site_date($n->created_at, 'd M Y');
            $n->news_link = route('news_details', $n->slug);
        }

        return response()->json(['status' => 1, 'message' => 'search results.', 'data' => ['q' => $s, 'reports' => $reports, 'news' => $news]]);
    }

    public function news(Request $request, $slug = null)
    {
        $q = DB::table('news as n')->leftJoin('categories as c', 'c.id', '=', 'n.category_id')->select('n.*', 'c.name as category', 'c.name as category_name', 'c.slug as category_slug')->where('n.status', 1)->whereNull('n.deleted_at');
        if ($slug) {
            $news = $q->where('n.slug', $slug)->first();
            abort_unless($news, 404);

            return view('front.news_details_view', ['title' => $news->headline, 'news' => $news]);
        }
        $list = $q->orderByDesc('n.created_at')->paginate(25);

        return view('front.news_list', ['title' => 'Industry News', 'list' => $list->items(), 'pager' => $list->links()->toHtml()]);
    }

    public function enquiryForm(Request $request, $id)
    {
        $report = self::catalogue()->where('r.id', $id)->where('r.status', 1)->first();
        abort_unless($report, 404);
        $type = explode('/', $request->path())[0];
        $map = ['request-a-sample' => ['req_sample_new', 'Request Sample'], 'ask-for-discount' => ['ask_discount', 'Ask Discount'], 'enquiry-before-buying' => ['enq_before_buying', 'Enquiry Before Buying'], 'request-for-customization' => ['req_customization', 'Request Customization']];
        [$template,$label] = $map[$type];

        return view('front.forms.'.$template, ['title' => $label.': '.$report->title, 'report' => $report, 'countries' => $this->countries(), 'form_for' => $label]);
    }

    public function enquiry(Request $request, $id = null)
    {
        $type = explode('/', $request->path())[0];
        $types = ['request-a-sample' => '1', 'ask-for-discount' => '2', 'enquiry-before-buying' => '3', 'request-for-customization' => '4', 'contactus' => '5', 'searchfor' => '6', 'request-toc' => '7'];
        $data = $request->validate(['full_name' => 'required|string|max:100', 'email_id' => 'required|email|max:100', 'contact_no' => 'required|string|max:100', 'company' => 'nullable|string|max:50', 'role' => 'nullable|string|max:50', 'country' => 'nullable|string|max:100', 'message' => 'nullable|string|max:500']);
        if ($id) {
            abort_unless(DB::table('reports')->where('id', $id)->where('status', 1)->exists(), 404);
        }
        if (config('site.recaptcha.enabled')) {
            $request->validate(['g-recaptcha-response' => 'required|string']);
            $check = Http::asForm()->timeout(15)->post('https://www.google.com/recaptcha/api/siteverify', ['secret' => config('site.recaptcha.secret'), 'response' => $request->input('g-recaptcha-response'), 'remoteip' => $request->ip()]);
            abort_unless($check->json('success'), 422, 'Captcha verification failed');
        }
        $data += ['uuid' => (string) Str::uuid(), 'report_id' => $id, 'type' => $types[$type], 'status' => '1', 'created_at' => now(), 'updated_at' => now()];
        $enquiryId = DB::table('reports_enquiries')->insertGetId($data);
        self::notify($data['email_id'], 'Thank you for your enquiry', 'We have received your enquiry and will contact you shortly.');
        if (config('site.admin_email') && config('site.mail_notifications')) {
            try {
                $report = $id ? DB::table('reports')->where('id', $id)->first(['title', 'slug']) : null;
                $mail = Mail::to(config('site.admin_email'));
                if ($bcc = \App\Support\AdminMailRecipients::bcc()) $mail->bcc($bcc);
                $mail->send(new \App\Mail\WebsiteEnquiry($data, $report, $request->ip(), $enquiryId));
                \Illuminate\Support\Facades\Log::info('Admin enquiry email accepted by transport', ['mailer' => config('mail.default'), 'enquiry_id' => $enquiryId]);
            } catch (\Throwable $exception) {
                report($exception);
            }
        }

        return redirect()->route('enquiry_success', [$type])->with('success_message', 'Your enquiry has been received.');
    }

    public function thanks($type)
    {
        return view('front.enq_success', ['title' => 'Thank You', 'enqfor' => Str::headline($type)]);
    }

    public function unsubscribe(Request $request)
    {
        if ($request->isMethod('get')) {
            return view('front.unsubscribe', ['title' => 'Unsubscribe', 'email' => $request->input('e'), 'type' => $request->input('type'), 'isUnsubscribed' => DB::table('unsubscribed_list')->where('email_id', $request->input('e'))->exists()]);
        }
        $data = $request->validate(['email_id' => 'required|email|max:100', 'feedback' => 'nullable|string|max:250', 'feedback1' => 'nullable|string|max:40']);
        DB::table('unsubscribed_list')->updateOrInsert(['email_id' => $data['email_id']], ['feedback' => trim(($data['feedback1'] ?? '').': '.($data['feedback'] ?? ''), ': '), 'created_at' => now()]);

        return response()->json(['status' => 1, 'message' => 'unsubscribed']);
    }

    public static function notify($email,$subject,$body,$adminNotification = false)
    {
        if (! $email || ! config('site.mail_notifications')) {
            return;
        }
        try {
            $sent = Mail::raw($body,function ($m) use ($email,$subject,$adminNotification) {
                $m->to($email)->subject($subject);
                if ($adminNotification && ($bcc = \App\Support\AdminMailRecipients::bcc())) $m->bcc($bcc);
            });
            \Illuminate\Support\Facades\Log::info('Website email accepted by transport', ['mailer' => config('mail.default'), 'subject' => $subject, 'message_id' => $sent?->getMessageId()]);
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
