<?php

header('Content-Type: application/json; charset=utf-8') ;
header('Access-Control-Allow-Origin: *') ;

require_once('Database.php') ;
require_once('Stats.php') ;
require_once('Recherche.php') ;

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

if ($requestMethod === 'GET' && $ressource === 'recherche') {
    $recherche = new Recherche($db) ;
    $data = $recherche->getRecherche() ;
    echo json_encode($data) ;
}


?>