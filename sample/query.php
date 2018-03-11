<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Nets\Enets2;

if (isset($_POST["refno"])) {
    require "enets2.php";
    $refno = $_POST["refno"];
    $enets = new Enets2;

    date_default_timezone_set("Asia/Singapore");
    try {
        $enets->setUmid("UMID_877772009");
        $enets->setSecretKey("a7002075-a716-47b9-948a-43927cf4c634");
        $enets->setKeyId("989c273a-13d3-4f94-a5e6-57bdca3dc26f");
        $enets->setMerchantReference($refno);
        $enets->setEnvironment("TEST");
        $queryResult = $enets->query();
        var_dump($queryResult);
    } catch (Exception $e) {
        echo $e->getMessage();
    }
} else {
    $refno="";
}

echo "<form action=query.php method=POST>";
echo "Merchant Ref No: <input type=text name=refno value=".$refno."><br>";
echo "<input type=submit>";
echo "</form>";

?>
