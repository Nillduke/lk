<?php
namespace App\Services;
use Dotenv\Dotenv;
use Illuminate\Support\Facades\Crypt;
use RuntimeException;
class CcavenueSettings
{
 public function __construct(private ?string $path=null){$this->path??=app()->environmentFilePath();}
 private function read():array{return is_file($this->path)?Dotenv::parse(file_get_contents($this->path)):[];}
 private function secret(array $env,string $name):string {
  if(!empty($env['CCAVENUE_'.strtoupper($name).'_ENCRYPTED'])){try{return Crypt::decryptString($env['CCAVENUE_'.strtoupper($name).'_ENCRYPTED']);}catch(\Throwable){return '';}}
  return (string)($env['CCAVENUE_'.strtoupper($name)]??config('ccavenue.'.$name,''));
 }
 public function apply():void {
  $env=$this->read();if(!array_key_exists('CCAVENUE_ENABLED',$env))return;
  config(['ccavenue.enabled'=>filter_var($env['CCAVENUE_ENABLED'],FILTER_VALIDATE_BOOLEAN),'ccavenue.mode'=>$env['CCAVENUE_MODE']??'test','ccavenue.merchant_id'=>$env['CCAVENUE_MERCHANT_ID']??'']);
  foreach(['access_code','working_key','api_access_code','api_working_key'] as $name)config(['ccavenue.'.$name=>$this->secret($env,$name)]);
 }
 public function ready():bool{return (bool)config('ccavenue.enabled')&&filled(config('ccavenue.merchant_id'))&&filled(config('ccavenue.access_code'))&&filled(config('ccavenue.working_key'));}
 public function values():array {
  $values=['enabled'=>(bool)config('ccavenue.enabled'),'mode'=>config('ccavenue.mode'),'merchant_id'=>config('ccavenue.merchant_id'),'ready'=>$this->ready()];
  foreach(['access_code','working_key','api_access_code','api_working_key'] as $name)$values['has_'.$name]=filled(config('ccavenue.'.$name));
  return $values;
 }
 public function save(array $data):void {
  $lock=fopen(storage_path('framework/email-settings.lock'),'c');if(!$lock||!flock($lock,LOCK_EX))throw new RuntimeException('Cannot lock settings.');$temp=null;
  try {
   $env=$this->read();$updates=['CCAVENUE_ENABLED'=>!empty($data['enabled'])?'true':'false','CCAVENUE_MODE'=>$data['mode'],'CCAVENUE_MERCHANT_ID'=>$data['merchant_id']??''];
   foreach(['access_code','working_key','api_access_code','api_working_key'] as $name){$value=trim($data[$name]??'')?:$this->secret($env,$name);if(!empty($data['clear_api'])&&str_starts_with($name,'api_'))$value='';$updates['CCAVENUE_'.strtoupper($name)]='';$updates['CCAVENUE_'.strtoupper($name).'_ENCRYPTED']=$value===''?'':Crypt::encryptString($value);}
   $content=is_file($this->path)?file_get_contents($this->path):'';
   foreach($updates as $key=>$value){$line=$key.'="'.str_replace(['\\','"','$',"\r","\n"],['\\\\','\\"','\\$','',''],$value).'"';$pattern='/^(?:export\s+)?'.preg_quote($key,'/').'\s*=.*$/m';$content=preg_match($pattern,$content)?preg_replace_callback($pattern,fn()=>$line,$content):rtrim($content)."\n".$line."\n";}
   Dotenv::parse($content);$temp=tempnam(dirname($this->path),'.ccavenue-');if(!$temp||file_put_contents($temp,$content)!==strlen($content))throw new RuntimeException('Cannot write settings.');chmod($temp,0600);if(!rename($temp,$this->path))throw new RuntimeException('Cannot replace settings.');$temp=null;$this->apply();
  }finally{if($temp&&is_file($temp))unlink($temp);flock($lock,LOCK_UN);fclose($lock);}
 }
}
