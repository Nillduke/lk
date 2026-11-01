<?php
return [
 'enabled'=>env('CCAVENUE_ENABLED',false), 'mode'=>env('CCAVENUE_MODE','test'),
 'merchant_id'=>env('CCAVENUE_MERCHANT_ID',''), 'access_code'=>env('CCAVENUE_ACCESS_CODE',''),
 'working_key'=>env('CCAVENUE_WORKING_KEY',''),
 'api_access_code'=>env('CCAVENUE_API_ACCESS_CODE',''), 'api_working_key'=>env('CCAVENUE_API_WORKING_KEY',''),
 'currency'=>'USD',
];
