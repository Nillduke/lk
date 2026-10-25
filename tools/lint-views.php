<?php
$failures=0;
foreach(glob(__DIR__.'/../storage/framework/views/*.php') as $file){try{token_get_all(file_get_contents($file),TOKEN_PARSE);}catch(ParseError $e){echo basename($file).': '.$e->getMessage().PHP_EOL;$failures++;}}
echo 'Compiled view syntax errors: '.$failures.PHP_EOL;
exit($failures?1:0);
