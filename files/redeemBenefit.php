<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
include ('../config.php');
include_once ('../Action.php');
include_once("../phpMQTT.php");

$response = array();
$conn = new mysqli($hostname_localhost, $username_localhost, $password_localhost, $database_localhost);

$res = hex2ascii($msg);
$json = json_decode($res);
echo $res;
$studentId = $json->studentId;
$registrationId = $json->registrationId;

$mqtt = new phpMQTT($mqtt_broker, 1883, "client1");

$sql = "UPDATE eventregistration SET redeemedStatus = 'Redeemed' WHERE registrationId = ?";
$sqlSelect = "Select studentId
				From eventregistration WHERE registrationId = ?";
$sqlCheck = "SELECT redeemedStatus FROM eventregistration WHERE registrationId = ?";

if ($stmt = $conn->prepare($sqlCheck)) {
    $stmt->bind_param("s", $registrationId);
    $stmt->execute();
    $stmt->bind_result($redeemedStatus);
    $stmt->store_result();

    if ($stmt->fetch()) {
		if ($stmt = $conn->prepare($sqlSelect)) {
			$stmt->bind_param("s", $registrationId);
			$stmt->execute();
			$stmt->bind_result($studentId2);
			$stmt->store_result();
			
			if($stmt->fetch()){
				$response['studentId'] = $studentId2;			   
			}
		}
        if ($redeemedStatus == NULL) {
			if ($stmt = $conn->prepare($sql)) {
				$stmt->bind_param("s", $registrationId);
				$stmt->execute();
				
				if ($stmt->errno) {
					$response["success"] = 0;
					$response["message"] = $stmt->errno;
					
				} else {
					$response["success"] = 1;
					$response["clientMsg"] = "Redeemed";
					$response["registrationId"] = $registrationId;
					$response["message"] = "Redeemed. RegId: $registrationId";
				}
			} else {
				$response["success"] = 0;
				$response["message"] = "Error occured.";
			}
		} else {
			$response["success"] = 0;
			$response["message"] = "Already redeemed.";
		}
	} else {
		$response["success"] = 0;
		$response["message"] = "Error occured.";
	}
} else {
	$response["success"] = 0;
	$response["message"] = "Error occured.";
}




    if ($mqtt->connect()) {
            echo "MQTT Connection successful!! and this " . $command;
            $mqtt->publish($mqtt_client_topic.$studentId, ascii2hex(json_encode($response)));
    }
	
	if ($mqtt->connect()) {
            echo "MQTT Connection successful!! and this " . $command;
            $mqtt->publish($mqtt_client_topic.$studentId2, ascii2hex(json_encode($response)));
    }

    echo json_encode($response);
?>