<?php

header('Content-Type: application/json; charset=utf-8') ;
header('Access-Control-Allow-Origin: *') ;

require_once('Database.php') ;
require_once('../../front/php/Stats.php') ;
require_once('../../front/php/Recherche.php') ;
require_once('../../front/php/Carte.php') ;
require_once('../../front/php/Resultats

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

if ($requestMethod === 'GET' && $ressource === 'carte') {
    $carte = new Carte($db) ;
    $data = $carte->getRechercheCarte() ;
    echo json_encode($data) ;
}

if ($requestMethod === 'GET' && $ressource === 'resultats') {
    $resultats = new Resultats($db) ;
    $data = $resultats->getResultats() ;
    echo json_encode($data) ;
}


?>