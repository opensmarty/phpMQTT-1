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
$registrationId = $json->registrationId;
	
$sqlSelect = "Select attendanceId, attendanceTime, eventSession, status
				From attendance WHERE registrationId = ? order by attendanceTime desc limit 1";
$mqtt = new phpMQTT($mqtt_broker, 1883, "client1");

if ($stmt = $conn->prepare($sqlSelect)) {
    $stmt->bind_param("s", $registrationId);
    $stmt->execute();
    $stmt->bind_result($attendanceId, $attendanceTime, $eventSession, $status);
    $stmt->store_result();
    
    if($stmt->fetch()){
			$response['attendanceId'] = $attendanceId;
			$response['attendanceTime'] = $attendanceTime;
			$response['eventSession'] = $eventSession;
			$response['status'] = $status;     
			$response["clientMsg"] = "";
			$response["registrationId"] = "";
			$response['success'] = 1;
			$response['message'] = "Result found!!";
			echo json_encode($response);
               
    }else{
        $response["success"] = 0;
		$response["clientMsg"] = "";
		$response["registrationId"] = "";
        $response["message"] = "No search result.";
		echo json_encode($response);    
    }
}else{
     $response["success"] = 0;
	 $response["clientMsg"] = "";
	 $response["registrationId"] = "";
     $response["message"] = "Oops! An error occurred.";
     echo json_encode($response);
}

    if ($mqtt->connect()) {
        echo "MQTT Connection successful!! ";
        echo json_encode($response);
        $mqtt->publish($mqtt_client_topic.$studentId, ascii2hex(json_encode($response)));
    }
    

?>
