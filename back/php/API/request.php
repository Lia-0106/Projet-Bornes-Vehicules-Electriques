<?php

// --------------------------------------------------------------------------------------------------------
// POINT D'ENTRÉE DE L'API
// Reçoit toutes les requêtes HTTP et les redirige vers la bonne classe selon la ressource demandée
// --------------------------------------------------------------------------------------------------------

header('Content-Type: application/json; charset=utf-8') ;
header('Access-Control-Allow-Origin: *') ;

require_once('Database.php') ;
require_once('Stats.php') ;
require_once('Recherche.php') ;
require_once('Carte.php') ;
require_once('Resultats.php') ;
require_once('PointRecharge.php') ;

//connexion à la base et si ça échoue ; erreur 503
$database = new Database() ;
$db = $database->getConnexion() ;

if (!$db) {
    http_response_code(503) ;
    exit ;
}

//lecture de la requête php 
$requestMethod = $_SERVER['REQUEST_METHOD'] ;
$request = substr($_SERVER['PATH_INFO'], 1) ;
$request = explode('/', $request) ;
$ressource = array_shift($request) ;

// -----------------------------------------------------------------------------------------------------
// ROUTAGE DES REQUÊTES GET
// chaque bloc vérifie la ressource et appelle la méthode qui correspond et retourne le résultat en JSON
// -----------------------------------------------------------------------------------------------------

// GET :stats: chiffres clés pour la page d'accueil
if ($requestMethod === 'GET' && $ressource === 'stats') {
    $stats = new Stats($db) ;
    $data = $stats->getStats() ;
    echo json_encode($data) ;
}

// GET: recherche:listes des filtres (aménageurs, prises, départements)
if ($requestMethod === 'GET' && $ressource === 'recherche') {
    $recherche = new Recherche($db) ;
    $data = $recherche->getRecherche() ;
    echo json_encode($data) ;
}

// GET  : carte : listes des filtres pour la carte (années, départements)
if ($requestMethod === 'GET' && $ressource === 'carte') {
    $carte = new Carte($db) ;
    $data = $carte->getRechercheCarte() ;
    echo json_encode($data) ;
}

// GET : marqueurs par annee et par dep :  points de recharge avec coordonnées GPS
if ($requestMethod === 'GET' && $ressource === 'marqueurs') {
    $annee = isset($_GET['annee']) ? $_GET['annee'] : '' ;
    $dep = isset($_GET['dep']) ? $_GET['dep'] : '' ;
    $carte = new Carte($db) ;
    $data = $carte->getMarqueurs($annee, $dep) ;
    echo json_encode($data) ;
}

// GET : marqueurs par annee et dep :  points de recharge avec coordonnées GPS
if ($requestMethod === 'GET' && $ressource === 'resultats') {
    $resultats = new Resultats($db) ;
    $amenageur = $_GET['amenageur'] ;
    $type_prise = $_GET['type_prise'] ;
    $code_dep = $_GET['code_dep'] ;
    $data = $resultats->getResultats($amenageur, $type_prise, $code_dep) ;
    echo json_encode($data) ;
}

// GET  : point-recharge :  détail complet d'un point de recharge
if ($requestMethod === 'GET' && $ressource === 'point-recharge') {
    $PointRecharge = new PointRecharge($db) ;
    $data = $PointRecharge->getDetails() ;
    echo json_encode($data) ;
}

?>