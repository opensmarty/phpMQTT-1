<?php

require("../phpMQTT.php");

	
$mqtt = new phpMQTT("iot.eclipse.org", 1883, "phpMQTT Pub Example"); //Change client name to something unique

if ($mqtt->connect()) {
	$mqtt->publish("testingBBCCD","Hello World! at ".date("r"),0);
	$mqtt->close();
}

?>
