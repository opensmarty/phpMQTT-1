<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
include ('../config.php');
include_once ('../Action.php');
include_once("../phpMQTT.php");

$conn = new mysqli($hostname_localhost, $username_localhost, $password_localhost, $database_localhost);

if ($conn->connect_errno) {
    printf("Connect failed: %s\n", $conn->connect_error);
    exit();
}      

$timetableId = '4';
$jsonMsg = hex2ascii($msg);

$json = json_decode($jsonMsg);

$studentId = $json->studentId;
$timetableId = $json->timetableId;




$sqlSelect = "Select t.timetableId, a.eventTitle, t.eventStartTime, t.eventEndTime, noOfParticipants, (Select COUNT(*) FROM eventregistration WHERE status = 'Active') as availableSeat, activityType 
    FROM eventtimetable t, eventApplication a 
    WHERE a.eventId= t.eventId AND t.timetableId = ? AND eventStartTime > NOW()";
$mqtt = new phpMQTT($mqtt_broker, 1883, "client1");


if ($stmt = $conn->prepare($sqlSelect)) {
    $stmt->bind_param("s", $timetableId);
    $stmt->execute();
    $stmt->bind_result($timetableId, $eventTitle,$eventStartTime,$eventEndTime,$noOfParticipants,$availableSeat,$activityType);
    $stmt->store_result();
    
    if($stmt->fetch()){
        $response['timetableId'] = $timetableId;
        $response['eventTitle'] = $eventTitle;
        $response['eventStartTime'] = $eventStartTime;
        $response['eventEndTime'] = $eventEndTime;
        $response['availableSeat'] = $availableSeat;
        $response['noOfParticipants'] = $noOfParticipants;
        $response['activityType'] = $activityType;
        
        $response['success'] = 1;
        $response['message'] = "Result found!!";
        echo json_encode($response);
        
        
        
    }else{
          $response["success"] = 0;
          $response["message"] = "No search result.";
     echo json_encode($response);
        
    }
}else{
     $response["success"] = 0;
     $response["message"] = "Oops! An error occurred.";
     echo json_encode($response);
}

    if ($mqtt->connect()) {
        echo "MQTT Connection successful!! ";
        echo json_encode($response);
        $mqtt->publish($mqtt_client_topic.$studentId, ascii2hex(json_encode($response)));
        //$mqtt->publish($mqtt_client_topic, json_encode($response));
        // $mqtt->publish($mqtt_client_topic, "Success");
    }
    
    

?>
