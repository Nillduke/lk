<?php
namespace App\Http\Controllers;
use App\Services\CcavenueGateway;
use App\Services\CcavenueSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
class CcavenueController extends Controller
{
 public function redirect(Request $request,CcavenueGateway $gateway,CcavenueSettings $settings){
  abort_unless($settings->ready(),503,'CCAvenue is not available.');
  $order=DB::table('checkouts')->where('id',session('checkout_id'))->where('payment_by','CCAvenue')->first();abort_unless($order,404);
  if($order->payment_status==='Success')return view('checkout-result',['title'=>'Payment received','message'=>'This order has already been paid.']);
  $attempt=DB::table('lr_payment_attempts')->where('checkout_id',$order->id)->orderByDesc('created_at')->first();
  if(!$attempt||$attempt->status!=='Initiated'||now()->diffInMinutes($attempt->created_at,true)>30)return redirect()->route('checkout_form_report',$order->report_id)->withErrors(['payment'=>'Please submit a new checkout to continue.']);
  return response()->view('front.payment.ccavenue-redirect',$gateway->request($attempt,$order)+['title'=>'Continue to secure payment','order'=>$order])->header('Cache-Control','no-store, private')->header('Referrer-Policy','no-referrer');
 }
 public function response(Request $request,string $attempt,CcavenueGateway $gateway){
  $row=DB::table('lr_payment_attempts')->where('id',$attempt)->first();abort_unless($row,404);
  if(!$request->isMethod('post')||!is_string($request->input('encResp'))||strlen($request->input('encResp'))>32768)return response()->view('checkout-result',['title'=>'Payment not verified','message'=>'No valid payment confirmation was received. Please contact our team with your order number.'],422);
  try{$callback=$gateway->decode($row,$request->input('encResp'));}catch(\Throwable){return response()->view('checkout-result',['title'=>'Payment not verified','message'=>'The payment response could not be verified. No payment status was changed.'],422);}
  if($row->status!=='Success'){
   // A forged or edited browser response can never mark an order paid. Confirm success with the status API.
   if(($callback['order_status']??'')==='Success'){
    if(empty($callback['tracking_id'])||!preg_match('/\A\d{1,50}\z/',$callback['tracking_id']))return response('Invalid payment reference.',422);
    $row->tracking_id=$callback['tracking_id'];$result=$gateway->verify($row);
   }elseif(in_array($callback['order_status'],['Failure','Aborted','Invalid','Timeout'],true))$result=['state'=>'Failed','note'=>'CCAvenue response: '.$callback['order_status']];
   else $result=['state'=>'Pending','note'=>'Payment confirmation is pending.'];
   try{$gateway->record($row,$result,$callback);}catch(\RuntimeException){return response('Order requires review. Please contact our team.',409);}
  }
  if($request->routeIs('ccavenue.notify'))return response('OK',200)->header('Cache-Control','no-store');
  return redirect(URL::temporarySignedRoute('ccavenue.result',now()->addHour(),['attempt'=>$attempt]),303);
 }
 public function notify(Request $request,CcavenueGateway $gateway){
  abort_unless(filled(config('ccavenue.working_key')),503);
  try{parse_str($gateway->decrypt((string)$request->input('encResp'),config('ccavenue.working_key')),$data);}catch(\Throwable){return response('Invalid notification.',422);}
  if(!is_string($data['order_id']??null)||!is_string($data['merchant_param1']??null))return response('Invalid notification.',422);
  $row=DB::table('lr_payment_attempts')->where('order_number',$data['order_id'])->where('nonce',$data['merchant_param1'])->first();abort_unless($row,404);
  return $this->response($request,$row->id,$gateway);
 }
 public function result(string $attempt){
  $row=DB::table('lr_payment_attempts')->where('id',$attempt)->first();abort_unless($row,404);
  $title=$row->status==='Success'?'Payment received':($row->status==='Failed'?'Payment not completed':'Payment verification pending');
  $message=$row->status==='Success'?'Your payment is confirmed. Our team will contact you regarding report delivery.':($row->status==='Failed'?'Your payment was cancelled or could not be completed. You can return to the report and try again.':'We are waiting for payment confirmation. Please do not pay again if your account was debited. Contact our team with the order number below.');
  return response()->view('front.payment.ccavenue-result',compact('title','message','row'))->header('Cache-Control','no-store, private')->header('Referrer-Policy','no-referrer');
 }
 public function settings(Request $request,CcavenueSettings $settings){abort_unless($request->user()->canAccess('manage_access'),403);return view('admin.ccavenue-settings',['settings'=>$settings->values()]);}
 public function saveSettings(Request $request,CcavenueSettings $settings){
  abort_unless($request->user()->canAccess('manage_access'),403);
  $rules=['enabled'=>'required|boolean','mode'=>'required|in:test,live','merchant_id'=>'nullable|regex:/\A\d{1,20}\z/','clear_api'=>'nullable|boolean'];foreach(['access_code','working_key','api_access_code','api_working_key'] as $field)$rules[$field]='nullable|string|max:255|regex:/\A[A-Za-z0-9_-]+\z/';
  $data=$request->validate($rules);$current=$settings->values();$errors=[];
  if($data['enabled']){foreach(['merchant_id','access_code','working_key'] as $field)if(empty($data[$field])&&($field==='merchant_id'||!$current['has_'.$field]))$errors[$field]='This field is required before enabling payments.';}
  if(($current['merchant_id']!==($data['merchant_id']??'')||$current['mode']!==$data['mode'])&&($current['has_working_key']||$current['has_access_code']))foreach(['access_code','working_key'] as $field)if(empty($data[$field]))$errors[$field]='Re-enter the matching credentials when changing the merchant or environment.';
  if(!empty($data['api_access_code'])&&empty($data['api_working_key'])&&!$current['has_api_working_key'])$errors['api_working_key']='Enter the matching API working key.';
  if(!empty($data['api_working_key'])&&empty($data['api_access_code'])&&!$current['has_api_access_code'])$errors['api_access_code']='Enter the matching API access code.';
  if($errors)throw \Illuminate\Validation\ValidationException::withMessages($errors);
  try{$settings->save($data);}catch(\Throwable){return back()->withErrors(['settings'=>'Could not save payment settings. Check .env file permissions.']);}
  return back()->with('success','CCAvenue settings saved. New checkouts use these settings immediately.');
 }
 public function payments(Request $request){abort_unless($request->user()->canAccess('manage_checkouts')||$request->user()->canAccess('manage_access'),403);$attempts=DB::table('lr_payment_attempts')->select('id','checkout_id','order_number','mode','status','amount','currency','tracking_id','note','created_at','verified_at')->orderByDesc('created_at')->paginate(25);return view('admin.ccavenue-payments',compact('attempts'));}
 public function recheck(Request $request,string $attempt,CcavenueGateway $gateway){
  abort_unless($request->user()->canAccess('manage_checkouts')||$request->user()->canAccess('manage_access'),403);$row=DB::table('lr_payment_attempts')->where('id',$attempt)->first();abort_unless($row,404);
  if($row->status!=='Success'){try{$gateway->record($row,$gateway->verify($row));}catch(\RuntimeException){return back()->withErrors(['payment'=>'The saved order changed and requires manual review.']);}}
  return back()->with('success','CCAvenue status checked. Review the status and note below.');
 }
}
