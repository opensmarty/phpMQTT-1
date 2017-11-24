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
$json = json_decode($res,true);

$sql = "INSERT INTO `eventregistration`(`timetableId`, `registerDate`, `studentId`, `leaderId`"
        . ", `description`, `waitingListStatus`, `redeemedStatus`, `status`) VALUES (?,NOW(),?,?,?,'','','Active')";

$count = 0;

$studentId = "";
$mqtt = new phpMQTT($mqtt_broker, 1883, "client1");
foreach ($json as $users) {
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ssss",$users['timetableId'], $users['studentId'], $users['leaderId'],$users['description']);
        if($stmt->execute()){
            $response["success"] = 1;
            $response["message"] = "Success";
			$count++; 
        }else{
            $response["succces"] = 0;
            $response["message"] = $users['studentId']." insert record fail!!".$stmt->error;
            
        }
        $studentId = $users['leaderId'];    
    }
}

$response["rowAffected"] = $count;
 if ($mqtt->connect()) {
            $mqtt->publish($mqtt_client_topic.$studentId, ascii2hex(json_encode($response)));
        }


?>

