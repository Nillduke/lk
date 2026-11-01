<?php
namespace App\Services;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;
class CcavenueGateway
{
 // CCAvenue's integration-kit wire format (fixed IV and MD5-derived AES key).
 public function encrypt(string $plain,string $key):string {
  $result=openssl_encrypt($plain,'AES-128-CBC',md5($key,true),OPENSSL_RAW_DATA,hex2bin('000102030405060708090a0b0c0d0e0f'));
  if($result===false)throw new RuntimeException('Payment encryption unavailable.');return bin2hex($result);
 }
 public function decrypt(string $encrypted,string $key):string {
  if(strlen($encrypted)>32768||!preg_match('/\A(?:[a-fA-F0-9]{32})+\z/',$encrypted))throw new RuntimeException('Invalid encrypted response.');
  $plain=openssl_decrypt(hex2bin($encrypted),'AES-128-CBC',md5($key,true),OPENSSL_RAW_DATA,hex2bin('000102030405060708090a0b0c0d0e0f'));
  if($plain===false||$plain==='')throw new RuntimeException('Invalid encrypted response.');return $plain;
 }
 public static function money(mixed $amount):?int {
  if(!is_scalar($amount)||!preg_match('/\A\d{1,10}(?:\.\d{1,2})?\z/',(string)$amount))return null;
  [$whole,$fraction]=array_pad(explode('.',(string)$amount),2,'');return ((int)$whole)*100+(int)str_pad($fraction,2,'0');
 }
 public function create(object $order):object {
  $credentials=[];foreach(['mode','merchant_id','access_code','working_key','api_access_code','api_working_key'] as $field)$credentials[$field]=config('ccavenue.'.$field);
  $id=(string)Str::uuid();$now=now();
  DB::table('lr_payment_attempts')->insert(['id'=>$id,'checkout_id'=>$order->id,'order_number'=>$order->order_number,'mode'=>$credentials['mode'],'credentials'=>Crypt::encryptString(json_encode($credentials)),'nonce'=>Str::random(48),'status'=>'Initiated','amount'=>$order->amt_tobe_paid,'currency'=>$order->currency,'created_at'=>$now,'updated_at'=>$now]);
  return DB::table('lr_payment_attempts')->where('id',$id)->first();
 }
 public function credentials(object $attempt):array{return json_decode(Crypt::decryptString($attempt->credentials),true,512,JSON_THROW_ON_ERROR);}
 public function request(object $attempt,object $order):array {
  $credentials=$this->credentials($attempt);
  $params=['merchant_id'=>$credentials['merchant_id'],'order_id'=>$attempt->order_number,'currency'=>$attempt->currency,'amount'=>number_format((float)$attempt->amount,2,'.',''),'redirect_url'=>route('ccavenue.response',$attempt->id),'cancel_url'=>route('ccavenue.cancel',$attempt->id),'language'=>'EN','merchant_param1'=>$attempt->nonce,
   'billing_name'=>Str::limit($order->name,60,''),'billing_address'=>Str::limit($order->address??'',150,''),'billing_country'=>Str::limit($order->country,50,''),'billing_tel'=>preg_replace('/[^0-9+ -]/','',$order->contact_no),'billing_email'=>$order->email];
  return ['action'=>($credentials['mode']==='live'?'https://secure.ccavenue.com':'https://test.ccavenue.com').'/transaction/transaction.do?command=initiateTransaction','encRequest'=>$this->encrypt(http_build_query($params,'','&',PHP_QUERY_RFC1738),$credentials['working_key']),'accessCode'=>$credentials['access_code']];
 }
 public function decode(object $attempt,string $response):array {
  parse_str($this->decrypt($response,$this->credentials($attempt)['working_key']),$data);
  foreach(['order_id','currency','amount','order_status','merchant_param1'] as $field)if(!isset($data[$field])||!is_string($data[$field]))throw new RuntimeException('Missing payment fields.');
  if(!hash_equals($attempt->order_number,$data['order_id'])||!hash_equals($attempt->nonce,$data['merchant_param1'])||$data['currency']!==$attempt->currency||self::money($data['amount'])!==self::money($attempt->amount))throw new RuntimeException('Payment does not match order.');
  $safe=[];foreach(['order_status','tracking_id','bank_ref_no','payment_mode','status_code','status_message','failure_message'] as $field)if(isset($data[$field])&&is_string($data[$field]))$safe[$field]=Str::limit($data[$field],250,'');return $safe;
 }
 public function verify(object $attempt):array {
  $c=$this->credentials($attempt);$key=$c['api_working_key']?:$c['working_key'];$code=$c['api_access_code']?:$c['access_code'];
  try{
   $response=Http::asForm()->connectTimeout(5)->timeout(15)->withOptions(['allow_redirects'=>false])->post(($attempt->mode==='live'?'https://api.ccavenue.com':'https://apitest.ccavenue.com').'/apis/servlet/DoWebTrans',[
    'enc_request'=>$this->encrypt(json_encode(['order_no'=>$attempt->order_number,'reference_no'=>$attempt->tracking_id??'']),$key),'access_code'=>$code,'command'=>'orderStatusTracker','request_type'=>'JSON','response_type'=>'JSON','version'=>'1.2']);
   if(!$response->successful())return ['state'=>'Verification pending','note'=>'CCAvenue status API is unavailable. Retry verification.'];
   parse_str($response->body(),$envelope);
   if((string)($envelope['status']??'')!=='0'||!is_string($envelope['enc_response']??null))return ['state'=>'Verification pending','note'=>'Status API rejected the request. Check API credentials and registered server IP.'];
   $data=json_decode($this->decrypt($envelope['enc_response'],$key),true,512,JSON_THROW_ON_ERROR);$data=$data['Order_Status_Result']??$data;
   if(!is_array($data)||!in_array((string)($data['error_code']??''),['','0'],true))return ['state'=>'Verification pending','note'=>'CCAvenue has not returned a valid order status yet.'];
   if(($data['order_no']??null)!==$attempt->order_number||($data['order_curr']??null)!==$attempt->currency||self::money($data['order_amt']??null)!==self::money($attempt->amount)||empty($data['reference_no'])||(!empty($attempt->tracking_id)&&(string)$data['reference_no']!==$attempt->tracking_id))return ['state'=>'Verification pending','note'=>'Status API order details did not match. Manual review required.'];
   if(in_array(strtolower((string)($data['order_fraud_status']??'')),['fr','fraud','fraudulent'],true))return ['state'=>'Verification pending','note'=>'CCAvenue flagged this payment for review.'];
   $status=strtolower((string)($data['order_status']??''));
   $state=match($status){'success','successful','shipped'=>'Success','unsuccessful','aborted','invalid','timeout','cancelled'=>'Failed',default=>'Pending'};
   return ['state'=>$state,'tracking_id'=>(string)$data['reference_no'],'note'=>'CCAvenue status: '.Str::limit($data['order_status']??'Unknown',50,'')];
  }catch(\Throwable){return ['state'=>'Verification pending','note'=>'Could not verify with CCAvenue. Retry after checking API access and server connectivity.'];}
 }
 public function record(object $attempt,array $result,array $callback=[]):void {
  $paid=DB::transaction(function()use($attempt,$result,$callback){
   $a=DB::table('lr_payment_attempts')->where('id',$attempt->id)->lockForUpdate()->first();$order=DB::table('checkouts')->where('id',$a->checkout_id)->lockForUpdate()->first();if(!$order)throw new RuntimeException('Order unavailable.');
   if($a->status==='Success')return null;
   if($order->order_number!==$a->order_number||$order->currency!==$a->currency||self::money($order->amt_tobe_paid)!==self::money($a->amount)||$order->payment_by!=='CCAvenue')throw new RuntimeException('Saved order was changed.');
   $state=$result['state'];$updates=['status'=>$state,'note'=>$result['note']??'','updated_at'=>now()];
   if(!empty($result['tracking_id']))$updates['tracking_id']=$result['tracking_id'];elseif(!empty($callback['tracking_id']))$updates['tracking_id']=$callback['tracking_id'];
   if($callback)$updates['response']=json_encode($callback);if($state==='Success')$updates['verified_at']=now();DB::table('lr_payment_attempts')->where('id',$a->id)->update($updates);
   if($order->payment_status==='Success')return null;
   $paymentStatus=$state==='Success'?'Success':($state==='Failed'?'Failed':'Pending');
   DB::table('checkouts')->where('id',$order->id)->update(['payment_status'=>$paymentStatus,'payment_info'=>json_encode(['gateway'=>'CCAvenue','attempt_id'=>$a->id,'tracking_id'=>$updates['tracking_id']??$a->tracking_id,'status'=>$state,'mode'=>$a->mode]),'updated_at'=>now()]);
   return $state==='Success'?$order:null;
  });
  if($paid && $attempt->mode==='live'){\App\Http\Controllers\SiteController::notify(config('site.admin_email'),'CCAvenue payment confirmed',$paid->order_number.' - '.$paid->currency.' '.number_format((float)$paid->amt_tobe_paid,2),true);\App\Http\Controllers\SiteController::notify($paid->email,'Payment received - Eternity Insights','We have received your payment for order '.$paid->order_number.'. Our team will contact you regarding report delivery.');}
 }
}
