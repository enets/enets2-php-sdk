<?php

require "enets2.php";
$enets = new Enets2;

date_default_timezone_set("Asia/Singapore");
try {
    $enets->setUmid("UMID_877772009");
    $enets->setSecretKey("a7002075-a716-47b9-948a-43927cf4c634");
    $enets->setKeyId("989c273a-13d3-4f94-a5e6-57bdca3dc26f");
    $enets->setAmount("60.00");
    $enets->setReturnUrl("http://18.217.88.5/enets/frontend_response.php");
    $enets->setNotifyUrl("http://18.217.88.5/enets/backend_response.php");
    $enets->setMerchantReference("TESTING".rand(10000,99999));
    $enets->setEnvironment("TEST");
    $enets->run();
} catch (Exception $e) {
    echo $e->getMessage();
}

?>
