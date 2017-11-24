<?php
    require('config.php');
    
class Db {



    private static $instance = NULL;

    private function __construct() {
        
    }

    private function __clone() {
        
    }

    public static function getInstance() {
        if (!isset(self::$instance)) {
            $pdo_options[PDO::ATTR_ERRMODE] = PDO::ERRMODE_EXCEPTION;
            self::$instance = new PDO('mysql:host=$hostname_localhost; dbname=$database_localhost', '$username_localhost', '$password_localhost', $pdo_options);
        }

        return self::$instance;
    }

}

?>