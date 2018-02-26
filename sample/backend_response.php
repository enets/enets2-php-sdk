<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Nets\Enets2;

$enets = new Enets2;

$file = fopen("log.txt","a+");
fwrite($file,date("Y-m-d H:i:s".": "));
date_default_timezone_set("Asia/Singapore");
try {
    $enets->setSecretKey("a7002075-a716-47b9-948a-43927cf4c634");
    $response = $enets->getBackendResponse();
    fwrite($file,print_r($response,true).PHP_EOL);
    fwrite($file,print_r($enets->getNetsMessage(),true).PHP_EOL);
} catch (Exception $e) {
    fwrite($file,$e->getMessage().PHP_EOL);
}
fclose($file);

?>
