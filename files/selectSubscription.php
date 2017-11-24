<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$response = array();

include ('../config.php');
include_once ('../Action.php');

$conn = new mysqli($hostname_localhost, $username_localhost, $password_localhost, $database_localhost);

if ($conn->connect_errno) {
    printf("Connect failed: %s\n", $conn->connect_error);
    exit();
}

//$studentId = "16war10395";
//$msg = '0016093030303030303030303030303030303030303030303030307b2273747564656e744964223a2231367761723130333935227d';
//
//$msg = substr($msg,54);
$res = hex2ascii($msg);

echo $res;
$json = json_decode($res);

$studentId = $json->studentId;


echo $studentId;
//$msg = '["Sports","Education","chk1"]';

//$arr = json_decode($msg);

//foreach($arr as $mssg){
  //  echo $mssg."<br/>";
//    
//}
//$subscription = $json->subscription;


//$msg = "";

$mqtt = new phpMQTT($mqtt_broker, 1883, "client1");


$query = "SELECT studentId, subscription FROM subscription where studentId = ?";

if ($stmt = $conn->prepare($query)) {
    $stmt->bind_param("s", $studentId);
    $stmt->execute();
    $stmt->bind_result($studentId, $subscription);
    $stmt->store_result();


    if ($stmt->num_rows == 0) {
		$result["subscription"] = '[]';
        echo "No search result";
    } else {
        while ($stmt->fetch()) {

            //$result["studentId"] = $studentId;
            $result["subscription"] = $subscription;

            array_push($response, $result);
        }

    
    }

    if ($mqtt->connect()) {
            echo "MQTT Connection successful!! and this " . $command;
            $mqtt->publish($mqtt_client_topic.$studentId, ascii2hex(json_encode($response)));
            // $mqtt->publish($mqtt_client_topic, "Success");
        }

    echo json_encode($response);
} else {

    echo "nothing";
}
?>

