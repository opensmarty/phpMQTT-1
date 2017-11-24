<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
//set_time_limit(300);
set_time_limit(0);
//set_time_limit(60);
include_once("../phpMQTT.php");
include_once("../config.php");
$command = "remain same command";


$mqtt = new phpMQTT($mqtt_broker, 1883, "phpMQTT"); //Change client name to something unique

if (!$mqtt->connect()) {
    echo "Connection failed";
    exit(1);
}

$topics[$mqtt_main_topic] = array("qos" => 0, "function" => "procmsg");
//$topics['test'] = array("qos"=>0, "function"=>"procmsg");
$mqtt->subscribe($topics, 0);

while ($mqtt->proc()) {
    
}
$mqtt->close();

function procmsg($topic, $msg) {
    echo "Msg Received: " . date("r") . "\nTopic:{$topic}\n   $msg   <br/>";
    $command = substr($msg, 0, 6);
    $msg = substr($msg, 54);


    switch ($command) {
        case "001601": //09
            echo "select event <br/>";
            include("selectEvent.php");
            break;
        case "001602": //10
            echo "read subscription <br/>";
            include ("selectSubscription.php");
            break;
        case "001603": //11
            echo "create or update subscription <br/>";
            include ("insertOrUpdateSubscription.php");
            break;
        case "001604": //12
            echo "Read event detail description <br/>";
            include("selectEventDetailRecord.php");
            break;
        case "001605": //13
            echo "Individual Registration";
            include("insertIndividualRegistration.php");
            break;
        case "001606": //14
            echo "select event details walk in registration";
            include('selectEventByTimetableId.php');
            break;
        case "001607": //15
            echo "validate student registered status.";
            include('validateStudentRegistration.php');
            break;
        case "001608": //16
            echo "insert group registration student record";
            include('insertGroupRegistration.php');
            break;
        case "001609": //30
            echo "Read incoming events";
            include('selectIncomingEventList.php');
            break;
        case "001610": //31
            echo "Read waiting list";
            include('selectWaitingEventList.php');
            break;
        case "001611": //32
            echo "Read registration information";
            include('selectRegistrationByRegId.php');
            break;
        case "001612": //33
            echo "Read attendance status";
            include('selectAttendanceStatus.php');
            break;
        case "001613": //34
            echo "Cancel registration";
            include("cancelRegistration.php");
            break;
        case "001614": //36
            echo "Cancel waiting list";
            include("cancelWaiting.php");
            break;
        case "001615": //37
            echo "Check waiting list status";
            include("checkWaitingListStatus.php");
            break;
        case "001616": //38
            echo "Register waiting list";
            include("registerWaitingList.php");
            break;
        case "001617": //39
            echo "Read past joined events";
            include("selectPastJoined.php");
            break;
        case "001618": //40
            echo "Read benefit";
            include("selectBenefit.php");
            break;
        case "001619": //41
            echo "Redeem benefit";
            include("redeemBenefit.php");
            break;
        case "001620": //42
            echo "Select soft skill point";
            include("selectSoftSkillPoint.php");
            break;
        case "001621": //35
            echo "Mark attendance";
            include("markAttendance.php");
            break;
        case "001622": //43
            echo "Update registration details";
            include("updateRegistrationDetails.php");
            break;
        case "001623": //44
            echo "Select group information";
            include("selectGroupInfo.php");
            break;
        case "001624":
            echo "Login";
            include("login.php");
            break;
        case "001625":
            echo "Check upcoming number of events";
            include("checkUpcoming.php");
            break;
        
        //new 
        case "001626":
            echo "insertGroupRegistgrationInsert";
            include("insertGroupRegistrationInsert.php");
            break;
        case "001627":
            echo "insertGroupRegistrationUpdate";
            include("insertGroupRegistrationUpdate.php");
            break;
        case "001628":
            echo "changeGroupRegistrationLeader";
            include("changeGroupRegistrationLeader.php");
            break;
        
        
        default :
            echo "No command found!! <br/>";
    }
}

?>