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

$jsonMsg = hex2ascii($msg);
$json = json_decode($jsonMsg);
echo $jsonMsg;
$searchCriteria = $json->criteria;
$studentId = $json->studentId;

$response = array();


$conn = new mysqli($hostname_localhost, $username_localhost, $password_localhost, $database_localhost);

if ($conn->connect_errno) {
    printf("Connect failed: %s\n", $conn->connect_error);
    exit();
}

if (strcmp("All Events", $searchCriteria) == 0) {
    echo "select all events";
    $query = "Select timetableId, eventStartTime, eventEndTime, eventTitle, activityType "
            . "from eventtimetable t, eventapplication e, venue v "
            . "WHERE t.eventId = e.eventId AND t.venueId = v.venueId AND t.eventStartTime > NOW() AND e.status = 'Active' ORDER BY eventStartTime";
    $result = mysqli_query($conn, $query);

    if ($result) {

        while ($row = mysqli_fetch_array($result)) {

            $allEvent = array();
            $allEvent["timetableId"] = $row["timetableId"];
            $allEvent["eventTitle"] = $row["eventTitle"];
            $allEvent["eventStartTime"] = $row["eventStartTime"];
            $allEvent["eventEndTime"] = $row["eventEndTime"];
			$allEvent["activityType"] = $row["activityType"];

            array_push($response, $allEvent);
        }
        echo json_encode($response);
        //echo ascii2hex(json_encode($response));
    }
} else if (strcmp("User Subscription", $searchCriteria) == 0) {
    $query = "SELECT char_length(subscription) as length, subscription FROM subscription where studentId = ?";
    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param("s", $studentId);
        $stmt->execute();
		$length = '';
        $stmt->bind_result($length, $subscription);
        $stmt->store_result();
		$stmt->fetch();
        $subscriptionArray = Array();
        if ($stmt->num_rows == 0 || $length == '2') {
            //search all 

            $query = "Select timetableId, eventStartTime, eventEndTime, eventTitle, activityType "
                    . "from eventtimetable t, eventapplication e, venue v "
                    . "WHERE t.eventId = e.eventId AND t.venueId = v.venueId AND t.eventStartTime > NOW() AND e.status = 'Active' ORDER BY eventStartTime";
            $result = mysqli_query($conn, $query);

            if ($result) {

                while ($row = mysqli_fetch_array($result)) {

                    $allEvent = array();
                    $allEvent["timetableId"] = $row["timetableId"];
                    $allEvent["eventTitle"] = $row["eventTitle"];
                    $allEvent["eventStartTime"] = $row["eventStartTime"];
                    $allEvent["eventEndTime"] = $row["eventEndTime"];
					$allEvent["activityType"] = $row["activityType"];

                    array_push($response, $allEvent);
                }
                echo json_encode($response);
                //echo ascii2hex(json_encode($response));
            }
        } else {
            $test = "";
            
            $test = str_replace('[', '', $subscription);
            $test = str_replace(']', '', $test);
            $test = str_replace('"', "'", $test);

            $sql = "Select timetableId, eventStartTime, eventEndTime, eventTitle, activityType "
                    . "from eventtimetable t, eventapplication e, venue v "
                    . "WHERE t.eventId = e.eventId AND t.venueId = v.venueId AND t.eventStartTime > NOW() AND e.status = 'Active' AND activityType IN (" . $test . ") ORDER BY eventStartTime";

            echo $sql;
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $stmt->bind_result($timetableId, $eventStartTime, $eventEndTime, $eventTitle, $activityType);
            $stmt->store_result();

            while ($stmt->fetch()) {

                $allEvent = array();
                $allEvent["timetableId"] = $timetableId;
                $allEvent["eventTitle"] = $eventTitle;
                $allEvent["eventStartTime"] = $eventStartTime;
                $allEvent["eventEndTime"] = $eventEndTime;
				$allEvent["activityType"] = $activityType;
                array_push($response, $allEvent);
            }

            //echo json_encode($response);
        }


        echo json_encode($response);
    }
} else {
    // SEARCH EVENT BY CATEGORY

    $sqlSelect = "SELECT timetableId, eventStartTime, eventEndTime, eventTitle, activityType "
            . "FROM eventtimetable t, eventapplication e, venue v "
            . "WHERE t.eventId = e.eventId AND t.venueId = v.venueId AND activityType = ? AND t.eventStartTime > NOW() ORDER BY eventStartTime";

    if ($stmt = $conn->prepare($sqlSelect)) {
        //echo "Criteria is ".$criteria."<br/>";
        
        $stmt->bind_param("s", $searchCriteria);
        $stmt->execute();
        $stmt->bind_result($timetableId, $eventStartTime, $eventEndTime, $title, $activityType);
        $stmt->store_result();


        while ($stmt->fetch()) {

            $allEvent = array();
            $allEvent["timetableId"] = $timetableId;
            $allEvent["eventTitle"] = $title;
            $allEvent["eventStartTime"] = $eventStartTime;
            $allEvent["eventEndTime"] = $eventEndTime;
			$allEvent["activityType"] = $activityType;
            array_push($response, $allEvent);
        }

        echo json_encode($response);
    }
}


   if ($mqtt->connect()) {
        echo "MQTT Connection successful!! and this " .$command ;
		echo $mqtt_client_topic.$studentId;
        $mqtt->publish($mqtt_client_topic.$studentId,  ascii2hex(json_encode($response)));
        }
   

?>