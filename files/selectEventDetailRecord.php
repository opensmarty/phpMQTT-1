<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


include_once("../phpMQTT.php");
//include("files/mainProcess.php");
include_once ("../Action.php");
include("../config.php");
$mqtt = new phpMQTT($mqtt_broker, 1883, "client1");

$conn = new mysqli($hostname_localhost, $username_localhost, $password_localhost, $database_localhost);

//$timetableId = '1';

$jsonMsg = hex2ascii($msg);
echo $jsonMsg;
$json = json_decode($jsonMsg);

$timetableId = $json->timetableId;
$studentId = $json->studentId;

if ($conn->connect_errno) {
    printf("Connect failed: %s\n", $conn->connect_error);
    exit();
}

$query = "Select eventStartTime, eventEndTime, eventTitle, eventDescription,eventBrochure, venueName, venueDescription ,e.noOfParticipants, (SELECT count(*) from eventregistration r where status = 'Active' AND r.timetableId = t.timetableId )AS currentParticipants, e.minTeam, e.maxTeam, (Select count(DISTINCT(leaderId)) FROM eventRegistration r WHERE status = 'Active' AND r.timetableId = t.timetableId AND leaderId <> ''  ) AS groupLimit from eventtimetable t, eventapplication e, venue v WHERE t.eventId = e.eventId AND t.venueId = v.venueId AND t.timetableId = ?";

$sqlCheckExisting = "SELECT count(*) FROM `eventregistration` where studentId = ? AND timetableId = ? AND status = 'Active'";

if ($stmt = $conn->prepare($query)) {
    $stmt->bind_param("s", $timetableId);
    $stmt->execute();
    $stmt->bind_result($eventStartTime, $eventEndTime, $eventTitle, $eventDescription,$eventBrochure, $venueName, $venueDescription,$noOfParticipants,$currentParticipants,$minTeam,$maxTeam,$countTeam);
    $stmt->store_result();
    
    if($stmt->fetch()){
        $response["eventStartTime"] = $eventStartTime;
        $response["eventEndTime"] = $eventEndTime;
        $response["eventTitle"] = $eventTitle;
        $response["eventDescription"] = $eventDescription;
        $response["venueName"] = $venueName;
        $response["venueDescription"] = $venueDescription;
        $response["noOfParticipants"] = $noOfParticipants;
        $response["currentParticipants"] = $currentParticipants;
        $response["eventBrochure"] = $eventBrochure;
        $response["minTeam"] = $minTeam;
        $response["maxTeam"] = $maxTeam;
        $response["teamLimit"] = $countTeam;
                
    }
    
    $response["success"] = 1;
    $response["message"] = "Data successfully retrieved";
    echo json_encode($response);
    
}else{
    
     $response["success"] = 0;
     $response["message"] = "Oops! An error occurred.";
      echo json_encode($response);
    
}

if ($stmt = $conn->prepare($sqlCheckExisting)) {
    $stmt->bind_param("ss", $studentId, $timetableId);
    $stmt->execute();
    $stmt->bind_result($registeredStatus);
    $stmt->store_result();
    if ($stmt->fetch()) {
        if ($registeredStatus == 0) {
            $response["successStatus"] = 1;
            $response["messageStatus"] = "student can continue";

       } else {
            $response["successStatus"] = 0;
            $response["messageStatus"] = "Student is registered for the event, cannot register anymore.";
        }
    } else {
        $response["successStatus"] = 0;
        $response["messageStatus"] = "Error while retrieving student registered status.";
    }
} else {
    $response["successStatus"] = 0;
    $response["messageStatus"] = "Error while checking student registered status.";
}



  if ($mqtt->connect(true, NULL, "cyumorkp", "DIQ-EjuHMCzJ")) {
        echo "MQTT Connection successful!! ";
        $mqtt->publish($mqtt_client_topic.$studentId, ascii2hex(json_encode($response)));
        //$mqtt->publish($mqtt_client_topic, json_encode($response));
        // $mqtt->publish($mqtt_client_topic, "Success");
    }


?>