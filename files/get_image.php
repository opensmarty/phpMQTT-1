<?php 

$mysqli = mysqli_connect("localhost","root","","test");
if(!$mysqli)
die("Cannot connect to MySQL: ".mysqli.connect_error());
$timetableId = $_GET['timetableId'];
$stmt = $mysqli->prepare("SELECT eventPicture FROM eventapplication a, eventtimetable t WHERE a.eventId = t.eventId AND timetableId = ?");
$stmt->bind_param("i", $timetableId);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($image);
$stmt->fetch();
header("Content-Type: image/jpg");
echo $image; 
?>