<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of testSecond
 *
 * @author User
 */
require("config.php");


$testStr = "This is a  test";

$testStr = Action::ascii2hex($testStr);
echo $testStr." ";

$testStr = Action::hex2ascii($testStr);
echo $testStr; 




?>
