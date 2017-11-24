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

$jsonMsg = hex2ascii($msg);
$json = json_decode($jsonMsg);
$studentId = $json->studentId;
	
$sqlSelect = "Select name
				From student WHERE studentId = ?";
$mqtt = new phpMQTT($mqtt_broker, 1883, "client1");

if ($stmt = $conn->prepare($sqlSelect)) {
    $stmt->bind_param("s", $studentId);
    $stmt->execute();
    $stmt->bind_result($name);
    $stmt->store_result();
    
    if($stmt->fetch()){
		$response["name"] = $name;
		$response["studentId"] = $studentId;
		$response['success'] = 1;
		$response['message'] = "Result found!!";
		echo json_encode($response);
               
    }else{
        $response["success"] = 0;
        $response["message"] = "No search result.";
		echo json_encode($response);    
    }
}

    if ($mqtt->connect()) {
        echo "MQTT Connection successful!! ";
        echo json_encode($response);
        $mqtt->publish($mqtt_client_topic.$studentId, ascii2hex(json_encode($response)));
    }
    

?>
