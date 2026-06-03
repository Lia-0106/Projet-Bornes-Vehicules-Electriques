<?php

// Database constants.
define('DB_USER', 'irveuser') ;
define('DB_PASSWORD', 'irvepwd') ;
define('DB_NAME', 'irve') ;
define('DB_SERVER', 'localhost') ;

try {
    $db = new PDO('mysql:host='.DB_SERVER.';dbname='.DB_NAME.';charset=utf8', DB_USER, DB_PASSWORD) ;
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}
catch (PDOException $exception) {
    error_log('Connection error: '.$exception->getMessage()) ;
    return false ;
}

?>