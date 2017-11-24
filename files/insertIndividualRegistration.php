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

$timetableId = '1';
$studentId = '16war10395';
$description = "This is descrption";
$waitinglistStatus = '';


$res = hex2ascii($msg);

$json = json_decode($res);

$timetableId = $json->timetableId;
$studentId = $json->studentId;
$description = $json->description;
$waitinglistStatus = $json->waitinglistStatus;
$mqtt = new phpMQTT($mqtt_broker, 1883, "client1");


$sql = "INSERT INTO `eventregistration`(`timetableId`, `registerDate`, `studentId`, `leaderId`"
        . ", `description`, `waitingListStatus`, `redeemedStatus`, `status`) VALUES (?,NOW(),?,'',?,?,'','Active')";

$sqlCheck = "Select noOfParticipants, (Select COUNT(*) FROM eventregistration WHERE status = 'Active') as availableSeat 
    FROM eventtimetable t, eventApplication a 
    WHERE a.eventId= t.eventId AND t.timetableId = ?";

$sqlCheckExisting = "SELECT count(*) FROM `eventregistration` where studentId = ? AND timetableId= ? AND status = 'Active'";


if ($stmt = $conn->prepare($sqlCheckExisting)) {
    $stmt->bind_param("ss", $studentId, $timetableId);
    $stmt->execute();
    $stmt->bind_result($registeredStatus);
    $stmt->store_result();
    if ($stmt->fetch()) {
        if ($registeredStatus == 0) {

            if ($stmt = $conn->prepare($sqlCheck)) {
                $stmt->bind_param("s", $timetableId);
                $stmt->execute();
                $stmt->bind_result($noOfParticipants, $availableSeat);
                $stmt->store_result();

                if ($stmt->fetch()) {
                    if ($availableSeat < $noOfParticipants) {

                        if ($stmt = $conn->prepare($sql)) {
                            $stmt->bind_param("ssss", $timetableId, $studentId,$description,$waitinglistStatus);
                            if ($stmt->execute()) {
                                $response["success"] = 1;
                                $response["message"] = "Record successfully insert into the database.";
                            } else {
                                $response["success"] = 0;
                                $response["message"] = "Error happens when insert the student registration record!!";
                            }
                        } else {
                            $response["success"] = 0;
                            $response["message"] = "Error happens when insert the student registration record!!";
                        }
                    } else {
                        $response["success"] = 0;
                        $response["message"] = "Student register fail, because event is full of participants.";
                    }
                } else {
                    $response["success"] = 0;
                    $response["message"] = "Error while fetching the checking of availability event record.!";
                }
            } else {
                $response["success"] = 0;
                $response["message"] = "Error,some database connection error occurs";
            }
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

    if ($mqtt->connect()) {
            echo "MQTT Connection successful!! and this " . $command;
            $mqtt->publish($mqtt_client_topic.$studentId, ascii2hex(json_encode($response)));
            // $mqtt->publish($mqtt_client_topic, "Success");
        }

    echo json_encode($response);
?>