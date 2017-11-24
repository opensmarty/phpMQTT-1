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

$mqtt = new phpMQTT($mqtt_broker, 1883, "client1");


$sqlCheck = "Select COUNT(*) as count FROM eventregistration r, eventtimetable t WHERE r.timetableId = t.timetableId AND studentId = ? AND status = 'Active' AND waitingListStatus = '' AND t.eventEndTime > NOW()";


if ($stmt = $conn->prepare($sqlCheck)) {
    $stmt->bind_param("s", $studentId);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->store_result();
    if ($stmt->fetch()) {
        $response["success"] = 1;
		$response["count"] = $count;
        $response["message"] = "Successful";
    } else {
        $response["success"] = 0;
		$response["count"] = 0;
        $response["message"] = "Error encountered.";
    }
} else {
    $response["success"] = 0;
	$response["count"] = 0;
    $response["message"] = "Error encountered.";
}

    if ($mqtt->connect()) {
            echo "MQTT Connection successful!! and this " . $command;
            $mqtt->publish($mqtt_client_topic.$studentId, ascii2hex(json_encode($response)));
        }

    echo json_encode($response);
?>