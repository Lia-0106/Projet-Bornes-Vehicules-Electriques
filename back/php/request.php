<?php

require_once('database.php') ;

$db = dbConnect() ;
if (!$db) {
    header('HTTP/1.1 503 Service Unavailable') ;
    exit ;
}

?>