<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of insertOrUpdateSubscription
 *
 * @author User
 */
include ('../config.php');
include_once ('../Action.php');
include_once("../phpMQTT.php");

$response = array();

$conn = new mysqli($hostname_localhost, $username_localhost, $password_localhost, $database_localhost);


//
$jsonMsg = hex2ascii($msg);

$json = json_decode($jsonMsg);

$studentId = $json->studentId;
$subscription = $json->subscription;
/*
$studentId = '16war10395';
$subscription = '["Education","Sports"]';
//$subscription = '["Sports"]';
*/

//echo "Student ".$studentId." subscription: ".$subscription."  <br/>";
$mqtt = new phpMQTT($mqtt_broker, 1883, "client1");




if ($conn->connect_errno) {
    printf("Connect failed: %s\n", $conn->connect_error);
    exit();
}
$sqlSelect = "Select count(studentId) FROM subscription WHERE studentId = ?";

if ($stmt = $conn->prepare($sqlSelect)) {
    $stmt->bind_param("s", $studentId);
    $stmt->execute();
    $stmt->bind_result($nums);
    $stmt->store_result();

    $stmt->fetch();
    if ($nums == 0) {
        $sql = "INSERT INTO subscription VALUES (?,?);";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ss", $studentId, $subscription);
            $stmt->execute();
            if ($stmt->errno) {
                $response["success"] = 0;
                $response["message"] = $stmt->errno;
            } else {
                $response["success"] = $stmt->affected_rows;
                $response["message"] = "Successfully inserted";
            }
        } else {
            $response["success"] = 0;
            $response["message"] = "Oops! An error occurred.";
        }

        echo json_encode($response);
    } else if ($nums > 0) {

        $sql = "UPDATE Subscription SET subscription = ? WHERE studentId = ?";

        if ($stmt = $conn->prepare($sql)) {

            $stmt->bind_param("ss", $subscription, $studentId);
            $stmt->execute();

            if ($stmt->errno) {
                $response["success"] = 0;
                $response["message"] = $stmt->errno;
            } else {
                $response["success"] = $stmt->affected_rows;
                $response["message"] = "Successfully updated";
            }
            $stmt->close();
            echo json_encode($response);
        } else {
            $response["success"] = 0;
            $response["message"] = "Oops! An error occurred.";
        }
        echo json_encode($response);
    } else {
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
    
    
}
?>