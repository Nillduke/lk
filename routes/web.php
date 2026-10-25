<?php

use App\Http\Controllers\AdminController as Admin;
use App\Http\Controllers\AuthController as Auth;
use App\Http\Controllers\CheckoutController as Checkout;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\ReportTransferController as Transfer;
use App\Http\Controllers\SiteController as Site;
use App\Http\Middleware\AdminAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

Route::get('/', [Site::class, 'home'])->name('home');
Route::get('/amp', [Site::class, 'home'])->name('home_amp');
foreach (['aboutus' => 'aboutus', 'services' => 'services', 'faqs' => 'faqs', 'how-to-order' => 'howToOrder', 'disclaimer' => 'disclaimer', 'terms-and-conditions' => 'termsConditions', 'refund-policy' => 'refundPolicy', 'privacy-policy' => 'privacyPolicy', 'contactus' => 'contactus', 'html-sitemap' => 'html_sitemap'] as $path => $name) {
    Route::get($path, [Site::class, 'page'])->name($name);
}
Route::get('all-reports', [Site::class, 'listing'])->name('all_reports');
Route::get('report/amp/{slug}', [Site::class, 'report'])->name('rd_amp');
Route::get('report/{slug}', [Site::class, 'report'])->name('rd_view');
Route::get('industry/{slug}', [Site::class, 'listing'])->name('industry_list');
Route::get('searchfor', [Site::class, 'search'])->name('search');
Route::get('industry-news', [Site::class, 'news'])->name('news_list');
Route::get('industry-news/view/{slug}', [Site::class, 'news'])->name('news_details');
foreach (['request-a-sample' => ['request_sample', 'request_sample_process'], 'ask-for-discount' => ['ask_discount', 'ask_discount_process'], 'enquiry-before-buying' => ['enquiry_buying', 'enquiry_buying_process'], 'request-for-customization' => ['request_customization', 'request_customization_process']] as $path => $names) {
    Route::get($path.'/{id}', [Site::class, 'enquiryForm'])->whereNumber('id')->name($names[0]);
    Route::post($path.'/{id}', [Site::class, 'enquiry'])->whereNumber('id')->middleware('throttle:15,1')->name($names[1]);
}
Route::post('request-toc/{id}', [Site::class, 'enquiry'])->whereNumber('id')->middleware('throttle:15,1')->name('request_toc_process');
Route::post('contactus', [Site::class, 'enquiry'])->middleware('throttle:15,1')->name('contactUsProcess');
Route::post('searchfor', [Site::class, 'enquiry'])->middleware('throttle:15,1')->name('search_form_process');
Route::get('{type}/thankyou', [Site::class, 'thanks'])->whereIn('type', ['request-a-sample', 'ask-for-discount', 'ask-discount', 'enquiry-before-buying', 'request-for-customization', 'request-toc', 'contactus', 'searchfor'])->name('enquiry_success');
Route::get('unsubscribe', [Site::class, 'unsubscribe'])->name('unsubscribe');
Route::post('unsubscribe', [Site::class, 'unsubscribe'])->middleware('throttle:15,1')->name('unsubscribe_process');
Route::get('api/autocomplete/search', [Site::class, 'autocomplete'])->middleware('throttle:120,1')->name('search_auto');
Route::post('api/getNewsSlug', fn (Request $r) => response()->json(['status' => 1, 'slug' => Str::slug($r->input('headline', $r->input('title', '')))]));
Route::post('add-report-checkout', [Checkout::class, 'add'])->name('addPurchase');
Route::get('add-report-checkout-amp', [Checkout::class, 'add'])->name('addPurchaseAmp');
Route::get('checkout-form/{id}', [Checkout::class, 'form'])->whereNumber('id')->name('checkout_form_report');
Route::get('checkout/{id}', [Checkout::class, 'form'])->whereNumber('id')->name('checkout_form1');
Route::get('checkout', [Checkout::class, 'form'])->name('checkout_form');
Route::post('checkoutprocess', [Checkout::class, 'store'])->middleware('throttle:15,1')->name('checkoutFormProcess');
Route::get('paypal/redirecting', [Checkout::class, 'paypalRedirect'])->name('paypal_redirect');
Route::get('paypal/success', [Checkout::class, 'paypalSuccess'])->name('paypal_success');
Route::get('paypal/cancel', [Checkout::class, 'result'])->name('paypal_cancle');
Route::get('paypal/failure', [Checkout::class, 'result'])->name('paypal_failed');
Route::get('wt/success', [Checkout::class, 'result'])->name('wt_success');
Route::middleware('guest')->group(function () {
    Route::match(['get', 'post'], 'auth/v/login', [Auth::class, 'login'])->middleware('throttle:30,1')->name('login');
    Route::get('auth/v/create-account', [Auth::class, 'register'])->name('register');
    Route::post('auth/v/create-account-process', [Auth::class, 'register'])->middleware('throttle:5,1')->name('register.process');
    Route::match(['get', 'post'], 'auth/v/forgot-password', [Auth::class, 'forgot'])->middleware('throttle:10,1')->name('forgot-password');
    Route::post('auth/v/send-reset-password', [Auth::class, 'forgot'])->middleware('throttle:10,1')->name('sendresetpassword');
    Route::get('auth/v/resetpassword/{token}', [Auth::class, 'reset'])->name('resetpassword');
    Route::post('auth/v/updatePassword', [Auth::class, 'reset'])->middleware('throttle:10,1')->name('update_pasword');
    Route::post('auth/v/resendemailverifylink', [Auth::class, 'resend'])->middleware('throttle:5,1')->name('verification.resend');
    Route::get('auth/v/emailverify/{token}', [Auth::class, 'verify'])->name('emailverify');
    Route::get('auth/google', [Auth::class, 'google'])->name('google.login');
    Route::get('auth/google/callback', [Auth::class, 'googleCallback'])->name('google.callback');
});
Route::post('authuser/logout', [Auth::class, 'logout'])->middleware('auth')->name('logout.post');
Route::get('authuser/logout', [Auth::class, 'logout'])->middleware('auth')->name('logout');
Route::prefix('authuser')->middleware(['auth', AdminAccess::class])->group(function () {
    Route::get('dashboard', [Admin::class, 'dashboard'])->name('dashboard');
    Route::get('settings/email', [\App\Http\Controllers\EmailSettingsController::class, 'edit'])->name('admin.email.edit');
    Route::post('settings/email', [\App\Http\Controllers\EmailSettingsController::class, 'update'])->middleware('throttle:10,1')->name('admin.email.update');
    Route::post('settings/email/test', [\App\Http\Controllers\EmailSettingsController::class, 'test'])->middleware('throttle:3,1')->name('admin.email.test');
    Route::get('manage/{resource}', [Admin::class, 'index'])->name('admin.index');
    Route::get('manage/{resource}/create', [Admin::class, 'edit'])->name('admin.create');
    Route::post('manage/{resource}', [Admin::class, 'save'])->name('admin.store');
    Route::get('manage/{resource}/{id}/edit', [Admin::class, 'edit'])->name('admin.edit');
    Route::post('manage/{resource}/bulk', [Admin::class, 'bulk'])->name('admin.bulk');
    Route::post('manage/{resource}/{id}', [Admin::class, 'save'])->name('admin.update');
    Route::delete('manage/{resource}/{id}', [Admin::class, 'delete'])->name('admin.delete');
    Route::get('export/{resource}', [Admin::class, 'export'])->name('admin.export');
    Route::get('reports/import', [Transfer::class, 'form'])->name('admin.import');
    Route::post('reports/import', [Transfer::class, 'import'])->name('import_reports');
    Route::get('reports/import/template', [Transfer::class, 'template'])->name('admin.template');
    Route::post('files/upload', [Admin::class, 'upload'])->name('images.upload');
    Route::post('media/images', [Admin::class, 'uploadImage'])->name('admin.images.upload');
    foreach (['reports/manage' => ['reports', 'reports_manage'], 'categories' => ['categories', 'manage_categories'], 'subcategories' => ['categories', 'subcategories'], 'publishers/manage' => ['publishers', 'manage_publishers'], 'news/manage' => ['news', 'news_manage'], 'users' => ['users', 'manage_users'], 'enquiries' => ['enquiries', 'enquiries_manage'], 'checkouts' => ['checkouts', 'checkouts_manage'], 'unsubscribed-users' => ['unsubscribed-users', 'unsubcribed_list'], 'report-bug' => ['bugs', 'bugs_manage'], 'roles-list' => ['roles', 'roles_list'], 'permissions-list' => ['permissions', 'permissions_list'], 'assign-permissions-to-roles' => ['roles', 'manage_access'], 'assign-role-users' => ['users', 'assign_roles'], 'cms' => ['pages', 'manage_cms']] as $path => $info) {
        Route::get($path, fn (Request $r) => app(Admin::class)->index($r, $info[0]))->name($info[1]);
    }
    foreach (['reports/add' => ['reports', 'reports_add'], 'reports/edit' => ['reports', 'report_edit'], 'news/new' => ['news', 'news_create_form'], 'publishers/edit' => ['publishers', 'publisher_edit'], 'checkouts/create' => ['checkouts', 'create_checkout'], 'checkouts/edit' => ['checkouts', 'edit_checkout'], 'role-add' => ['roles', 'role_add'], 'permission-add' => ['permissions', 'permission_add']] as $path => $info) {
        Route::get($path, fn (Request $r) => app(Admin::class)->edit($r, $info[0]))->name($info[1]);
    }
    Route::get('reports/import-with-template', [Transfer::class, 'form'])->name('reports_import_template');
    Route::get('reports/export', fn (Request $r) => app(Admin::class)->export($r, 'reports'))->name('reports_export');
    Route::get('reports/upload-static', [Transfer::class, 'form'])->name('upload_static');
    Route::get('users/view/{id}', fn (Request $r, $id) => app(Admin::class)->edit($r, 'users', $id))->name('view_user');
    Route::get('news/edit/{id}', fn (Request $r, $id) => app(Admin::class)->edit($r, 'news', $id))->name('news_edit_view');
    Route::post('news/new', fn (Request $r) => app(Admin::class)->save($r, 'news'))->name('news_create');
    Route::post('news/edit/{id}', fn (Request $r, $id) => app(Admin::class)->save($r, 'news', $id))->name('news_edit');
    Route::get('enquiries/edit/{id}', fn (Request $r, $id) => app(Admin::class)->edit($r, 'enquiries', $id))->name('enquiry_edit');
    Route::post('checkouts/store', fn (Request $r) => app(Admin::class)->save($r, 'checkouts'))->name('store_checkout');
    Route::post('checkouts/update/{id}', fn (Request $r, $id) => app(Admin::class)->save($r, 'checkouts', $id))->name('update_checkout');
    Route::prefix('api')->group(function () {
        foreach (['reports', 'categories', 'publishers', 'users', 'enquiries', 'checkouts', 'unsubscribed-users', 'bugs'] as $resource) {
            Route::get($resource, fn (Request $r) => app(Admin::class)->index($r, $resource));
            Route::get($resource.'/all', fn (Request $r) => app(Admin::class)->index($r, $resource));
            Route::post($resource.'/createslug', fn (Request $r) => response()->json(['status' => 1, 'slug' => Str::slug($r->input('title', $r->input('name', '')))]));
            Route::match(['get', 'post'], $resource.'/export', fn (Request $r) => app(Admin::class)->export($r, $resource));
            Route::post($resource.'/bulkActions', fn (Request $r) => app(Admin::class)->bulk($r, $resource));
            Route::put($resource.'/bulkActions', fn (Request $r) => app(Admin::class)->bulk($r, $resource));
            Route::post($resource.'/delete', fn (Request $r) => app(Admin::class)->delete($r, $resource));
            Route::delete($resource.'/delete', fn (Request $r) => app(Admin::class)->delete($r, $resource));
            Route::post($resource.'/add', fn (Request $r) => app(Admin::class)->save($r, $resource));
            Route::post($resource, fn (Request $r) => app(Admin::class)->save($r, $resource));
            Route::patch($resource.'/edit/{id}', fn (Request $r, $id) => app(Admin::class)->save($r, $resource, $id));
            Route::patch($resource.'/{id}', fn (Request $r, $id) => app(Admin::class)->save($r, $resource, $id));
            Route::post($resource.'/{id}', fn (Request $r, $id) => app(Admin::class)->save($r, $resource, $id))->whereNumber('id');
            Route::delete($resource.'/{id}', fn (Request $r, $id) => app(Admin::class)->delete($r, $resource, $id))->whereNumber('id');
        }
        Route::get('{resource}/{id}', [Admin::class, 'show'])->whereIn('resource', ['reports', 'categories', 'publishers', 'users', 'enquiries', 'checkouts', 'unsubscribed-users', 'bugs'])->where('id', '[0-9a-fA-F-]+');
        Route::patch('reports/meta/{id}', [Admin::class, 'metadata']);
        Route::post('reports/importone', fn (Request $r) => app(Admin::class)->save($r, 'reports'));
        Route::post('reports/import', [Transfer::class, 'import']);
        Route::post('reports/template-import', [Transfer::class, 'import']);
        Route::post('reports/importfile', [Admin::class, 'upload']);
        Route::post('reports/files/upload', [Admin::class, 'upload']);
        Route::get('reports/import/status', fn () => response()->json(['status' => 1, 'message' => 'Imports run synchronously', 'data' => session('report_import')]));
    });
});
Route::get('sitemap.xml', [\App\Http\Controllers\SitemapController::class, 'index'])->name('sitemap.index');
Route::get('sitemap-{kind}-{page}.xml', [\App\Http\Controllers\SitemapController::class, 'part'])
    ->whereIn('kind', \App\Services\SitemapCatalogue::KINDS)->where('page', '[1-9][0-9]{0,6}')->name('sitemap.part');
Route::get('robots.txt', [\App\Http\Controllers\SitemapController::class, 'robots'])->name('robots');

Route::get('uploads/{path}', [MediaController::class, 'show'])->where('path', '.*')->name('media.original');

// Publish active CMS pages at their saved slug without overriding existing routes.
Route::fallback(function (Request $request) {
    abort_unless($request->isMethod('get') || $request->isMethod('head'), 404);
    abort_if(preg_match('~^(?:authuser|auth|uploads|storage|frontend|vendor)(?:/|$)~i', $request->path()), 404);
    $page = DB::table('lr_pages')->where('slug', $request->path())->where('status', 1)->first();
    abort_unless($page, 404);
    return view('cms-page', ['title' => $page->title, 'content' => $page->content]);
})->name('cms.page');
