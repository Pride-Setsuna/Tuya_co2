<?php
include "Sdk.php";
date_default_timezone_set('Asia/Tokyo');

$config = array(
    "clientID"  => "x95g4f5ysutefhy3qehv",
    "accessUrl" => "https://openapi.tuyaus.com",
    "secret"    => "0e905a81cd8c4dbf83cc038464a8d3fb",
    "nonce"     => "",
);

$request = new SendClient($config);
$co2 = $request->device();

$sensor_values = array(
    "datetime" => date('Y-m-d H:i:s'),
);

foreach ($co2["result"]["devices"] as $device) {
    $name = $device["name"];
    $co2_value = null;
    foreach ($device["status"] as $status) {
        if ($status["code"] == "co2_value") {
            $co2_value = $status["value"];
            break;
        }
    }
    if ($co2_value !== null) {
        $sensor_values[$name] = $co2_value;
    }
}

$json_data = json_encode($sensor_values);

file_put_contents('/home/23TE/web/co2.ditu.jp/public_html/co2/output.json', $json_data.PHP_EOL , FILE_APPEND);

?>
