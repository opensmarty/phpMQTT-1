<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Action
 *
 * @author User
 */


function hex2ascii($hex) {
        $str = '';
        for ($i = 0; $i < strlen($hex); $i+=2) {
            $str .= chr(hexdec(substr($hex, $i, 2)));
        }
        return $str;
    }

function ascii2hex($ascii) {
        $hex = '';
        for ($i = 0; $i < strlen($ascii); $i++) {
            $byte = strtoupper(dechex(ord($ascii{$i})));
            $byte = str_repeat('0', 2 - strlen($byte)) . $byte;
            $hex.=$byte;
        }
        return $hex;
    }
    
    ?>

