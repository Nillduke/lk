<?php
namespace App\Support;
/** Repair only complete UTF-8 sequences mistakenly decoded as Latin-1/Windows-1252. */
class TextEncoding
{
 private static function byte(string $character):?int {
  $cp=mb_ord($character,'UTF-8');if($cp<=255)return $cp;
  return [0x20ac=>0x80,0x201a=>0x82,0x0192=>0x83,0x201e=>0x84,0x2026=>0x85,0x2020=>0x86,0x2021=>0x87,0x02c6=>0x88,0x2030=>0x89,0x0160=>0x8a,0x2039=>0x8b,0x0152=>0x8c,0x017d=>0x8e,0x2018=>0x91,0x2019=>0x92,0x201c=>0x93,0x201d=>0x94,0x2022=>0x95,0x2013=>0x96,0x2014=>0x97,0x02dc=>0x98,0x2122=>0x99,0x0161=>0x9a,0x203a=>0x9b,0x0153=>0x9c,0x017e=>0x9e,0x0178=>0x9f][$cp]??null;
 }
 public static function repair(string $text,?array &$replacements=null):string {
  if(!mb_check_encoding($text,'UTF-8'))return $text;
  $continuation='[\x{0080}-\x{00BF}\x{20AC}\x{201A}\x{0192}\x{201E}\x{2026}\x{2020}\x{2021}\x{02C6}\x{2030}\x{0160}\x{2039}\x{0152}\x{017D}\x{2018}\x{2019}\x{201C}\x{201D}\x{2022}\x{2013}\x{2014}\x{02DC}\x{2122}\x{0161}\x{203A}\x{0153}\x{017E}\x{0178}]';
  $pattern='~[\x{00C2}-\x{00DF}]'.$continuation.'|[\x{00E0}-\x{00EF}]'.$continuation.'{2}|[\x{00F0}-\x{00F4}]'.$continuation.'{3}~u';
  for($pass=0;$pass<4;$pass++){
   $before=$text;
   $text=preg_replace_callback($pattern,function($m)use(&$replacements){$bytes='';foreach(preg_split('//u',$m[0],-1,PREG_SPLIT_NO_EMPTY) as $char){$byte=self::byte($char);if($byte===null)return $m[0];$bytes.=chr($byte);}if(!mb_check_encoding($bytes,'UTF-8'))return $m[0];if($replacements!==null)$replacements[$m[0]]=$bytes;return $bytes;},$text);
   // The same corruption sometimes arrived encoded as HTML entities. Decode only
   // a token that contains a proven broken UTF-8 sequence, never unrelated HTML.
   $text=preg_replace_callback('~(?:&(?:[A-Za-z][A-Za-z0-9]+|\#\d+|\#x[0-9a-fA-F]+);){2,}~',fn($m)=>self::entities($m[0],$replacements),$text);
   // A lost CP1252 0x9D is recoverable only as the closing quote of an intact quoted phrase.
   $text=preg_replace_callback('~\x{201C}([^<>\r\n\x{201D}]{0,2000})\x{00E2}\x{20AC}\x{FFFD}~u',function($m)use(&$replacements){if($replacements!==null)$replacements[$m[0]]="\u{201c}".$m[1]."\u{201d}";return "\u{201c}".$m[1]."\u{201d}";},$text);
   if($text===$before)break;
  }
  return $text;
 }
 private static function entities(string $text,?array &$replacements):string {
  preg_match_all('~&(?:[A-Za-z][A-Za-z0-9]+|\#\d+|\#x[0-9a-fA-F]+);~',$text,$matches);$tokens=$matches[0];$chars=array_map(fn($t)=>html_entity_decode($t,ENT_QUOTES|ENT_HTML5,'UTF-8'),$tokens);$out='';
  for($i=0;$i<count($tokens);$i++){$lead=mb_strlen($chars[$i])===1?self::byte($chars[$i]):null;$size=$lead>=0xc2&&$lead<=0xdf?2:($lead>=0xe0&&$lead<=0xef?3:($lead>=0xf0&&$lead<=0xf4?4:0));$bytes=$size?chr($lead):'';
   for($j=1;$j<$size;$j++){$b=isset($chars[$i+$j])&&mb_strlen($chars[$i+$j])===1?self::byte($chars[$i+$j]):null;if($b===null||$b<0x80||$b>0xbf){$bytes='';break;}$bytes.=chr($b);}
   if($bytes!==''&&mb_check_encoding($bytes,'UTF-8')){$from=implode('',array_slice($tokens,$i,$size));if($replacements!==null)$replacements[$from]=$bytes;$out.=$bytes;$i+=$size-1;}else $out.=$tokens[$i];
  }return $out;
 }
 public static function json(string $text,?array &$replacements=null):string {
  try{$data=json_decode($text,true,512,JSON_THROW_ON_ERROR);}catch(\Throwable){return self::repair($text,$replacements);}
  $walk=function($v)use(&$walk,&$replacements){if(is_string($v))return self::repair($v,$replacements);if(is_array($v))foreach($v as $k=>$value)$v[$k]=$walk($value);return $v;};$fixed=$walk($data);
  return $data===$fixed?$text:json_encode($fixed,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR);
 }
}
