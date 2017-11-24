<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

include_once("../phpMQTT.php");
include_once ("../Action.php");
include("../config.php");
$mqtt = new phpMQTT($mqtt_broker, 1883, "client1");

$jsonMsg = hex2ascii($msg);
$json = json_decode($jsonMsg);
echo $jsonMsg;
$studentId2 = $json->studentId;
$leaderId = $json->leaderId;
$timetableId = $json->timetableId;

$response = array();

$conn = new mysqli($hostname_localhost, $username_localhost, $password_localhost, $database_localhost);

if ($conn->connect_errno) {
    printf("Connect failed: %s\n", $conn->connect_error);
    exit();
}

$sqlSelect = "Select e.studentId, leaderId, name, registrationId "
			. "From eventregistration e, student s "
			. "Where e.studentId = s.studentId AND leaderId = ? AND timetableId = ? AND status = 'Active' AND waitingListStatus = ''";

if ($stmt = $conn->prepare($sqlSelect)) {
    $stmt->bind_param("ss", $leaderId, $timetableId);
    $stmt->execute();
    $stmt->bind_result($studentId, $leaderId, $name,$registrationId);
    $stmt->store_result();
    
	while ($stmt->fetch()) {	
		$groupArr = Array();
		$groupArr["name"] = $name;
        $groupArr["studentId"] = $studentId;
		$groupArr["leaderId"] = $leaderId;
                $groupArr["registrationId"] = $registrationId;
		array_push($response, $groupArr);  
	}
} 
           
if ($mqtt->connect()) {
    echo "MQTT Connection successful!! and this " .$command ;
    $mqtt->publish($mqtt_client_topic.$studentId2, ascii2hex(json_encode($response)));
	echo json_encode($response);
}

?>