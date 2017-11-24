<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

include_once("../phpMQTT.php");
include_once ("../Action.php");
include("../config.php");

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

$sqlSelect = "Select t.timetableId, eventStartTime, eventEndTime, eventTitle, activityType, venueName, registrationId "
			. "From eventtimetable t, eventapplication a, eventregistration r, venue v "
			. "Where t.eventId = a.eventId AND t.venueId = v.venueId AND t.timetableId = r.timetableId AND eventStartTime > NOW() AND r.studentId = ? AND r.waitingListStatus = 'Active' ORDER BY eventStartTime";
			
$mqtt = new phpMQTT($mqtt_broker, 1883, "heyme");

if ($stmt = $conn->prepare($sqlSelect)) {
    $stmt->bind_param("s", $studentId);
    $stmt->execute();
    $stmt->bind_result($timetableId, $eventStartTime, $eventEndTime, $eventTitle, $activityType, $venueName, $registrationId);
    $stmt->store_result();
    
	while ($stmt->fetch()) {	
		$waitingArray = Array();
        $waitingArray["timetableId"] = $timetableId;
		$waitingArray["eventStartTime"] = $eventStartTime;
		$waitingArray["eventEndTime"] = $eventEndTime;
		$waitingArray["eventTitle"] = $eventTitle;
		$waitingArray["activityType"] = $activityType;
		$waitingArray["venueName"] = $venueName;
		$waitingArray["registrationId"] = $registrationId;
		array_push($response, $waitingArray);  
	}
} 
           
if ($mqtt->connect()) {
    echo "MQTT Connection successful!! and this " .$command ;
    $mqtt->publish($mqtt_client_topic.$studentId, ascii2hex(json_encode($response)));
	echo json_encode($response);
}

?>