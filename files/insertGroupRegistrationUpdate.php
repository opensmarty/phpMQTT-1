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

$registrationId = '';
$timetbaleId = '';
$studentId = '';


$res = hex2ascii($msg);
$json = json_decode($res);
//$registrationId = $json->registrationId;
$timetableId = $json->timetableId;
$studentId = $json->studentId;
$leaderId = $json->leaderId;

$mqtt = new phpMQTT($mqtt_broker, 1883, "client1");

$sql = "Update eventregistration SET status = 'Inactive' WHERE timetableId = ? AND studentId = ?";

if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("ss", $timetableId, $studentId);
    if($stmt->execute()){
        $response["success"]=1;
        $response["message"] = "Student cancel successfully!";
    }else{
        $response["success"] = 0 ;
        $response["message"] = "Student cancel failed!!";
        
    }
} else {
    $response["success"] = 0;
    $response["message"] = "Error occured.";
}  

if ($mqtt->connect()) {
            echo "MQTT Connection successful!! and this " . $command;
            $mqtt->publish($mqtt_client_topic.$leaderId, ascii2hex(json_encode($response)));
    }

    echo json_encode($response);

?>