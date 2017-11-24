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
$timetableId = $json->timetableId; 

$mqtt = new phpMQTT($mqtt_broker, 1883, "client1");


$sqlCheck = "Select noOfParticipants, (Select COUNT(*) FROM eventregistration WHERE status = 'Active' AND waitingListStatus = '' AND timetableId = ?) as availableSeat 
    FROM eventtimetable t, eventapplication a 
    WHERE a.eventId= t.eventId AND t.timetableId = ?";


if ($stmt = $conn->prepare($sqlCheck)) {
    $stmt->bind_param("ss", $timetableId, $timetableId);
    $stmt->execute();
    $stmt->bind_result($noOfParticipants, $availableSeat);
    $stmt->store_result();
    if ($stmt->fetch()) {
        if ($availableSeat < $noOfParticipants) {
			$noOfSeat = $noOfParticipants - $availableSeat;
			$response["success"] = 1;
            $response["message"] = "$noOfSeat Empty Seat(s) Available ($availableSeat/$noOfParticipants)";
			
        } else {
            $response["success"] = 0;
            $response["message"] = "Pending in Waiting List ($availableSeat/$noOfParticipants)";
        }
    } else {
        $response["success"] = 0;
        $response["message"] = "Error encountered.";
    }
} else {
    $response["success"] = 0;
    $response["message"] = "Error encountered.";
}

    if ($mqtt->connect()) {
            echo "MQTT Connection successful!! and this " . $command;
            $mqtt->publish($mqtt_client_topic.$studentId, ascii2hex(json_encode($response)));
        }

    echo json_encode($response);
?>