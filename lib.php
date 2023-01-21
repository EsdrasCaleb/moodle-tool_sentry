<?php 
require_once 'vendor/autoload.php';
$dns = get_config('tool_sentry', 'dns');
if($dns){
    \Sentry\init(['dsn' => $dns]);
    //TODO make only in events
    \Sentry\captureLastError();
}