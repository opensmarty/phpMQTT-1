<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
include ('../config.php');
include_once ('../Action.php');
include_once("../phpMQTT.php");



$studentId = "16war10396";
$timetableId = "1";

$response = array();

$command = "";
$res = hex2ascii($msg);
echo $res;
$json = json_decode($res);
$leaderId = "";
$leaderId = $json->leaderId;
$timetableId = $json->timetableId;
$studentId = $json->studentId;
$mqtt = new phpMQTT($mqtt_broker, 1883, "client1");
$conn = new mysqli($hostname_localhost, $username_localhost, $password_localhost, $database_localhost);

$sqlCheckStudent = "SELECT name FROM Student WHERE studentId = ?";
$sqlCheckExisting = "SELECT count(*) FROM `eventregistration` where studentId = ? AND timetableId= ? AND status = 'Active'";

if ($stmt = $conn->prepare($sqlCheckStudent)) {
    $stmt->bind_param("s", $studentId);
    $stmt->execute();
    $stmt->bind_result($studentName);
    $stmt->store_result();
    if ($stmt->fetch()) {
        if ($stmt->num_rows == 0) {
            $response["success"] = 0;
            $response["message"] = "No student data found in the database!!";
        } else {

            if ($stmt = $conn->prepare($sqlCheckExisting)) {
                $stmt->bind_param("ss", $studentId, $timetableId);
                $stmt->execute();
                $stmt->bind_result($registeredStatus);
                $stmt->store_result();
                if ($stmt->fetch()) {
                    if ($registeredStatus == 0) {
                        $response['success'] = 1;
                        
                        $response["studentName"] = $studentName;
                        $response["message"] = "Student can register for the event.";
                    } else {
                        $response["success"] = 0;
                        $response["message"] = "Student is registered for the event, cannot register anymore.";
                    }
                } else {
                    $response["success"] = 0;
                    $response["message"] = "Error while retrieving student registered status.";
                }
            } else {
                $response["success"] = 0;
                $response["message"] = "Error while checking student registered status.";
            }
        }
    } else {
        $response["success"] = 0;
        $response["message"] = "Error while searching student record.";
    }
} else {
    $response["success"] = 0;
    $response["message"] = "Error while connecting student table.";
}


if ($mqtt->connect()) {
    echo "MQTT Connection successful!! and this " . $command;
    $mqtt->publish($mqtt_client_topic.$leaderId, ascii2hex(json_encode($response)));
    // $mqtt->publish($mqtt_client_topic, "Success");
}

echo json_encode($response);
?>
