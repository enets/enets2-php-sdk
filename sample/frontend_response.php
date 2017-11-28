<?php

require "enets2.php";
$enets = new Enets2;

date_default_timezone_set("Asia/Singapore");
try {
    $enets->setSecretKey("a7002075-a716-47b9-948a-43927cf4c634");
    $response = $enets->getFrontendResponse();
    var_dump($response);
    var_dump($enets->getNetsMessage());
} catch (Exception $e) {
    echo $e->getMessage();
}

?>
