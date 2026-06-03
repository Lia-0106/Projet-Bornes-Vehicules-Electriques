<?php

header('Content-Type: application/json; charset=utf-8') ;
header('Access-Control-Allow-Origin: *') ;

require_once('database.php') ;
require_once('Stats.php') ;

$database = new Database() ;
$db = $database->getConnexion() ;

if (!$db) {
    http_response_code(503) ;
    exit ;
}

$requestMethod = $_SERVER['REQUEST_METHOD'] ;
$request = substr($_SERVER['PATH_INFO'], 1) ;
$request = explode('/', $request) ;
$ressource = array_shift($request) ;

if ($requestMethod === 'GET' && $ressource === 'stats') {
    $stats = new Stats($db) ;
    $data = $stats->getStats() ;
    echo json_encode($data) ;
}


?>