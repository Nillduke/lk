<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    public function add(Request $request)
    {
        $request->validate(['report_id' => 'required|integer|exists:reports,id']);
        session(['chk_report_license' => $request->input('license')]);

        return redirect()->route('checkout_form_report', $request->report_id);
    }

    public function form(Request $request, $id = null)
    {
        $checkout = null;
        $is_global = false;
        if (str_starts_with($request->path(), 'checkout/')) {
            $checkout = DB::table('checkouts')->where('id', $id)->where('is_active', 1)->first();
            abort_unless($checkout, 404);
            $id = $checkout->report_id;
            $is_global = true;
        }
        $id = $id ?: session('chk_report');
        abort_unless($id, 404);
        $report = DB::table('reports')->where('id', $id)->where('status', 1)->first();
        abort_unless($report, 404);
        $licenses = DB::table('reports_licenses')->where('report_id', $id)->orderBy('lic_price')->get()->all();
        abort_unless($licenses, 404);
        $license = $licenses[0];
        $selected = explode(':', session('chk_report_license', ''))[0];
        foreach ($licenses as $l) {
            if ((string) $l->id === $selected) {
                $license = $l;
            }
        }
        session(['chk_report' => $id]);

        return view('front.payment.checkout_form', ['title' => 'Checkout: Billing Details', 'id' => $id, 'checkout' => $checkout, 'report' => $report, 'licenses' => $licenses, 'license_type' => $checkout->license_type ?? $license->lic_name, 'license_price' => $checkout->amt_tobe_paid ?? $license->lic_price, 'license_id' => $license->id, 'single_user' => $licenses[0], 'is_global' => $is_global, 'countries' => DB::table('country')->get()->all()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate(['report_id' => 'required|integer|exists:reports,id', 'name' => 'required|string|max:100', 'email' => 'required|email|max:100', 'contact_no' => 'required|string|max:100', 'country' => 'required|string|max:100', 'company' => 'nullable|string|max:50', 'address' => 'nullable|string|max:500', 'message' => 'nullable|string|max:500', 'payment_mode' => 'required|in:PayPal,Wire Transfer,wire transfer']);
        $q = DB::table('reports_licenses')->where('report_id', $data['report_id']);
        $licenseName = explode(':', (string) $request->input('license'))[0];
        $license = $request->filled('report_lic_id') ? (clone $q)->where('id', $request->report_lic_id)->first() : (clone $q)->where('lic_name', $licenseName)->first();
        abort_unless($license, 422, 'Select a valid report license.');
        if (strtolower($data['payment_mode']) === 'paypal' && ! config('site.paypal.enabled')) {
            return back()->withInput()->withErrors(['payment_mode' => 'PayPal is not configured. Select Wire Transfer or configure PayPal in .env.']);
        }
        $order = 'ORD'.now()->format('YmdHis').Str::upper(Str::random(6));
        $payment = strtolower($data['payment_mode']) === 'paypal' ? 'PayPal' : 'Wire Transfer';
        unset($data['payment_mode']);
        $row = $data + ['uuid' => (string) Str::uuid(), 'report_license_id' => $license->id, 'order_number' => $order, 'license_type' => $license->lic_name, 'total_amount' => $license->lic_price, 'amt_tobe_paid' => $license->lic_price, 'currency' => config('site.paypal.currency'), 'payment_by' => $payment, 'payment_status' => 'Pending', 'status' => '1', 'is_active' => 1, 'created_at' => now(), 'updated_at' => now()];
        $checkoutId = DB::transaction(function () use ($row) {
            $row['checkout_id'] = DB::table('checkouts')->lockForUpdate()->max('checkout_id') + 1;

            return DB::table('checkouts')->insertGetId($row);
        });
        session(['order_number' => $order, 'checkout_id' => $checkoutId]);
        SiteController::notify(config('site.admin_email'), 'New checkout attempt', $order.' - '.$data['email'], true);

        return redirect()->route($payment === 'PayPal' ? 'paypal_redirect' : 'wt_success');
    }

    private function paypal()
    {
        abort_unless(config('site.paypal.enabled'), 503, 'Configure PayPal in .env first.');
        $base = config('site.paypal.mode') === 'live' ? 'https://api-m.paypal.com' : 'https://api-m.sandbox.paypal.com';
        $res = Http::withBasicAuth(config('site.paypal.client_id'), config('site.paypal.secret'))->asForm()->timeout(20)->post($base.'/v1/oauth2/token', ['grant_type' => 'client_credentials'])->throw();

        return [$base, $res->json('access_token')];
    }

    public function paypalRedirect()
    {
        $order = DB::table('checkouts')->where('id', session('checkout_id'))->first();
        abort_unless($order, 404);
        [$base,$token] = $this->paypal();
        $res = Http::withToken($token)->timeout(20)->post($base.'/v2/checkout/orders', ['intent' => 'CAPTURE', 'purchase_units' => [['reference_id' => $order->order_number, 'amount' => ['currency_code' => $order->currency, 'value' => number_format($order->amt_tobe_paid, 2, '.', '')]]], 'application_context' => ['return_url' => route('paypal_success'), 'cancel_url' => route('paypal_cancle')]])->throw();
        session(['paypal_order_id' => $res->json('id')]);
        foreach ($res->json('links', []) as $link) {
            if ($link['rel'] === 'approve') {
                return redirect()->away($link['href']);
            }
        }abort(502, 'PayPal approval URL unavailable');
    }

    public function paypalSuccess(Request $request)
    {
        abort_unless($request->input('token') && hash_equals((string) session('paypal_order_id'), (string) $request->input('token')), 422);
        [$base,$token] = $this->paypal();
        $capture = Http::withToken($token)->withHeaders(['PayPal-Request-Id' => 'capture-'.session('paypal_order_id')])->withBody('{}', 'application/json')->timeout(20)->post($base.'/v2/checkout/orders/'.urlencode(session('paypal_order_id')).'/capture')->throw();
        abort_unless($capture->json('status') === 'COMPLETED', 422, 'Payment has not completed');
        $order = DB::table('checkouts')->where('id', session('checkout_id'))->first();
        $unit = $capture->json('purchase_units.0');
        $paid = $unit['payments']['captures'][0]['amount'] ?? [];
        abort_unless($order && ($unit['reference_id'] ?? null) === $order->order_number && ($paid['currency_code'] ?? null) === $order->currency && round((float) ($paid['value'] ?? 0), 2) === round((float) $order->amt_tobe_paid, 2), 422, 'Payment amount verification failed');
        DB::table('checkouts')->where('id', $order->id)->update(['payment_status' => 'Success', 'payment_info' => json_encode(['paypal_order_id' => session('paypal_order_id'), 'status' => 'COMPLETED']), 'updated_at' => now()]);

        return view('checkout-result', ['title' => 'Payment received', 'message' => 'Your verified payment has been received.']);
    }

    public function result(Request $request)
    {
        return view('checkout-result',['title' => str_starts_with($request->path(),'wt/') ? 'Order received' : 'Payment status', 'message' => str_starts_with($request->path(),'wt/') ? 'Your order is recorded. Payment remains pending until your wire transfer is confirmed.' : 'Your payment was cancelled or could not be completed.']);
    }
}
