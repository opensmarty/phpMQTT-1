
<?php

ignore_user_abort(true);
set_time_limit(0); // disable the time limit for this script

include_once('config.php');
include_once("phpMQTT.php");

$path = 'C:/Users/User/Desktop/phpDownload/'; // change the path to fit your websites document structure

$dl_file = preg_replace("([^\w\s\d\-_~,;:\[\]\(\).]|[\.]{2,})", '', "event2.pdf"); // simple file name validation
$dl_file = filter_var($dl_file, FILTER_SANITIZE_URL); // Remove (more) invalid characters
$fullPath = $path . $dl_file;

$mqtt = new phpMQTT($mqtt_broker, 1883, "client1");

if (file_exists($fullPath) && $fd = fopen($fullPath, "r")) {
    $fsize = filesize($fullPath);
    $path_parts = pathinfo($fullPath);
    $ext = strtolower($path_parts["extension"]);
    switch ($ext) {
        case "pdf":
            header("Content-type: application/pdf");
            header("Content-Disposition: attachment; filename=\"" . $path_parts["basename"] . "\""); // use 'attachment' to force a file download
            break;
        // add more headers for other content types here
        default;
            header("Content-type: application/octet-stream");
            header("Content-Disposition: filename=\"" . $path_parts["basename"] . "\"");
            break;
    }
    header("Content-length: $fsize");
    header("Cache-control: private"); //use this to open files directly


   
    if ($mqtt->connect()) {
        //echo "MQTT Connection successful!! ";
        //echo json_encode($response);
        //$mqtt->publish($mqtt_client_topic, ascii2hex(json_encode($response)));
        //$mqtt->publish($mqtt_client_topic, json_encode($response));
        // $mqtt->publish($mqtt_client_topic, "Success");
    }
$mqtt->publish($mqtt_client_topic, "wew");
    while (!feof($fd)) {
        // echo "sini";
          $buffer = fread($fd, 2048);
        $mqtt->publish($mqtt_client_topic, $buffer);
        //echo base64_decode($buffer);
        echo $buffer;
        //$mqtt->publish($mqtt_client_topic, ascii2hex(json_encode($response)));
    }
     fclose($fd);
} else {
    //echo "File do not exist";
}
?>


