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
$studentId = $json->studentId;

$response = array();

$conn = new mysqli($hostname_localhost, $username_localhost, $password_localhost, $database_localhost);

if ($conn->connect_errno) {
    printf("Connect failed: %s\n", $conn->connect_error);
    exit();
}

$sqlSelect = "Select t.timetableId, eventStartTime, eventEndTime, eventTitle, activityType, venueName, registrationId, eventDescription "
			. "From eventtimetable t, eventapplication a, eventregistration r, venue v "
			. "Where t.eventId = a.eventId AND t.venueId = v.venueId AND t.timetableId = r.timetableId AND eventEndTime > NOW() AND r.studentId = ? AND r.status = 'Active' AND r.waitingListStatus = '' ORDER BY eventStartTime";

if ($stmt = $conn->prepare($sqlSelect)) {
    $stmt->bind_param("s", $studentId);
    $stmt->execute();
    $stmt->bind_result($timetableId, $eventStartTime, $eventEndTime, $eventTitle, $activityType, $venueName, $registrationId, $eventDescription);
    $stmt->store_result();
    
	while ($stmt->fetch()) {	
		$incomingArray = Array();
        $incomingArray["timetableId"] = $timetableId;
		$incomingArray["eventStartTime"] = $eventStartTime;
		$incomingArray["eventEndTime"] = $eventEndTime;
		$incomingArray["eventTitle"] = $eventTitle;
		$incomingArray["activityType"] = $activityType;
		$incomingArray["venueName"] = $venueName;
		$incomingArray["registrationId"] = $registrationId;
		$incomingArray["eventDescription"] = $eventDescription;
		array_push($response, $incomingArray);  
	}
} 
           
if ($mqtt->connect()) {
    echo "MQTT Connection successful!! and this " .$command ;
    $mqtt->publish($mqtt_client_topic.$studentId, ascii2hex(json_encode($response)));
	echo json_encode($response);
}

?>