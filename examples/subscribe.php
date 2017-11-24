<?php

require("../phpMQTT.php");
require("../config.php");
	
$mqtt = new phpMQTT("iot.eclipse.org", 1883, "phpMQTT Sub Example"); //Change client name to something unique

if(!$mqtt->connect()){
        echo "Connection failed";
	exit(1);
}

$topics['test'] = array("qos"=>0, "function"=>"procmsg");
//$topics['test'] = array("qos"=>0, "function"=>"procmsg");
$mqtt->subscribe($topics,0);

while($mqtt->proc()){
	
}


$mqtt->close();

function procmsg($topic,$msg){
		echo "Msg Recieved: ".date("r")."\nTopic:{$topic}\n   $msg   \n";
                
                
                

                
}
	


?>
