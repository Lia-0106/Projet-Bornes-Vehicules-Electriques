<?php

header('Content-Type: application/json; charset=utf-8') ;
header('Access-Control-Allow-Origin: *') ;

require_once('Database.php') ;
require_once('../../front/php/Stats.php') ;
require_once('../../front/php/Recherche.php') ;
require_once('../../front/php/Carte.php') ;
require_once('../../front/php/Resultats.php') ;
require_once('../../front/php/PointRecharge.php') ;

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

if ($requestMethod === 'GET' && $ressource === 'marqueurs') {
    $annee = isset($_GET['annee']) ? $_GET['annee'] : '' ;
    $dep = isset($_GET['dep']) ? $_GET['dep'] : '' ;
    $carte = new Carte($db) ;
    $data = $carte->getMarqueurs($annee, $dep) ;
    echo json_encode($data) ;
}

if ($requestMethod === 'GET' && $ressource === 'resultats') {
    $resultats = new Resultats($db) ;
    $amenageur = $_GET['amenageur'] ;
    $type_prise = $_GET['type_prise'] ;
    $code_dep = $_GET['code_dep'] ;
    $data = $resultats->getResultats($amenageur, $type_prise, $code_dep) ;
    echo json_encode($data) ;
}

if ($requestMethod === 'GET' && $ressource === 'point-recharge') {
    $id = isset($_GET['id']) ? $_GET['id'] : 0 ;
    $PointRecharge = new PointRecharge($db) ;
    $data = $PointRecharge->getDetails() ;
    echo json_encode($data) ;
}

?>