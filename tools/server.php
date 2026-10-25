<?php
$public=realpath(__DIR__.'/../public');
$path=rawurldecode(parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH)?:'/');
if(preg_match('~(?:^|/)\.|\.(?:php[0-9]*|phtml|phar|env|sql|gz|zip|log|config)$~i',$path)&&$path!=='/index.php'){http_response_code(404);exit('Not found');}
$file=realpath($public.$path);
$types=['css'=>'text/css; charset=UTF-8','js'=>'application/javascript; charset=UTF-8','svg'=>'image/svg+xml','png'=>'image/png','jpg'=>'image/jpeg','jpeg'=>'image/jpeg','webp'=>'image/webp','gif'=>'image/gif','ico'=>'image/x-icon','woff'=>'font/woff','woff2'=>'font/woff2','ttf'=>'font/ttf','pdf'=>'application/pdf','avif'=>'image/avif','txt'=>'text/plain; charset=UTF-8','xml'=>'application/xml'];
$ext=strtolower(pathinfo($file?:'',PATHINFO_EXTENSION));
if($file&&str_starts_with($file,$public.DIRECTORY_SEPARATOR)&&is_file($file)&&isset($types[$ext])){
 header('Content-Type: '.$types[$ext]);header('X-Content-Type-Options: nosniff');
 header('Cache-Control: public, max-age='.(str_contains($path,'/dist/')||str_contains($path,'/optimized/')?'31536000, immutable':'604800'));
 $etag='"'.dechex(filemtime($file)).'-'.dechex(filesize($file)).'"';header('ETag: '.$etag);header('Vary: Accept-Encoding');
 if(($_SERVER['HTTP_IF_NONE_MATCH']??'')===$etag){http_response_code(304);exit;}
 $body=file_get_contents($file);
 if(in_array($ext,['css','js','svg','txt','xml'])&&preg_match('~(?:^|,)\s*gzip(?:\s*;\s*q=([0-9.]+))?\s*(?:,|$)~i',$_SERVER['HTTP_ACCEPT_ENCODING']??'',$encoding)&&(!isset($encoding[1])||(float)$encoding[1]>0)){$body=gzencode($body,6);header('Content-Encoding: gzip');}
 header('Content-Length: '.strlen($body));if($_SERVER['REQUEST_METHOD']!=='HEAD')echo $body;exit;
}
// PHP's built-in server otherwise treats existing upload names as the script.
$_SERVER['SCRIPT_FILENAME']=$public.'/index.php';$_SERVER['SCRIPT_NAME']='/index.php';$_SERVER['PHP_SELF']='/index.php';
require $public.'/index.php';
