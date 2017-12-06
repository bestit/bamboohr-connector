<?php
/**
 * Created by PhpStorm.
 * User: gdamyanov
 * Date: 25.10.17
 * Time: 18:01
 */


$data = array(  "api_token" => "dnad0PIgOFixZae0AEnVUl6YwsrpJa1hUYUHcWvD",
                    "absence" => array("absence_type_external_id" => "Urlaub", "user_external_id" =>"79", "starts_at" => "2017-10-24T00:00:00+02:00", "ends_at" => "2017-10-24T24:00:00+02:00", "full_day" => false)
);
$data_string = json_encode($data);

//echo date("c");exit;

$ch = curl_init('https://app.papershift.com/public_api/v1/absences');
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Content-Length: ' . strlen($data_string),
        'Accept: application/json')
);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);

//execute post
$result = curl_exec($ch);

//close connection
curl_close($ch);

echo $result;