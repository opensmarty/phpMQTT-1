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
$studentId = $json->studentId;
$registrationId = $json->registrationId;
$description = $json->description;

$mqtt = new phpMQTT($mqtt_broker, 1883, "client1");

$sql = "UPDATE eventregistration SET description = ? WHERE registrationId = ?";

if ($stmt = $conn->prepare($sql)) {
				$stmt->bind_param("ss", $description, $registrationId);
				$stmt->execute();
				
				if ($stmt->errno) {
					$response["success"] = 0;
					$response["message"] = $stmt->errno;
					
				} else {
					$response["success"] = 1;
					$response["message"] = "Registration Details Updated! ($description)";
				}
			} else {
				$response["success"] = 0;
				$response["message"] = "Error occured.";
			}

    if ($mqtt->connect()) {
            echo "MQTT Connection successful!! and this " . $command;
            $mqtt->publish($mqtt_client_topic.$studentId, ascii2hex(json_encode($response)));
    }

    echo json_encode($response);
?>