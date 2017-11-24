<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

include_once("phpMQTT.php");
//include("files/mainProcess.php");
include_once ("Action.php");
include("config.php");
$mqtt = new phpMQTT($mqtt_broker, 1883, "client1");


$response = array();


$conn = new mysqli($hostname_localhost, $username_localhost, $password_localhost, $database_localhost);

if ($conn->connect_errno) {
    printf("Connect failed: %s\n", $conn->connect_error);
    exit();
}


$query = "Select timetableId, eventStartTime, eventEndTime, eventTitle from eventtimetable t, eventapplication e, venue v WHERE t.eventId = e.eventId AND t.venueId = v.venueId ";
$result = mysqli_query($conn, $query);
if ($result) {

    while ($row = mysqli_fetch_array($result)) {

        $allEvent = array();
        $allEvent["timetableId"] = $row["timetableId"];
        $allEvent["eventTitle"] = $row["eventTitle"];
        $allEvent["startTime"] = $row["eventStartTime"];
        $allEvent["endTime"] = $row["eventEndTime"];
        
    
        array_push($response, $allEvent);
    }
    echo json_encode($response);
    echo ascii2hex(json_encode($response));
    
    if ($mqtt->connect()) {
        echo "MQTT Connection successful!! and this " .$command ;
        $mqtt->publish($mqtt_client_topic,  ascii2hex(json_encode($response)));
       // $mqtt->publish($mqtt_client_topic, "Success");
        }
    
    
     mysqli_free_result($result);
}



?>