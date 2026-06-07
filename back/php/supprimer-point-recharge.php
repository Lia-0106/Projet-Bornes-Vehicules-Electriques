<?php

// -------------------------------------------------------
// VÉRIFICATION DE SESSION
// Redirige vers login.php si l'admin n'est pas connecté
// -------------------------------------------------------
session_start() ;
if (!isset($_SESSION['admin'])) {
    header('Location: ../php/login.php') ;
    exit ;
}

require_once ('API/Database.php') ;
require_once ('API/constantes.php') ;
require_once ('API/PointRecharge.php') ;


// -------------------------------------------------------
// SUPPRESSION DU POINT DE RECHARGE
// $id : récupéré depuis l'URL
// Supprime dans l'ordre : prises → paiements → point → station → acteur
// -------------------------------------------------------
$id = isset($_GET['id']) ? $_GET['id'] : 0 ;

if ($id <= 0) {
    header('Location: ../index.php') ;
    exit ;
}

$database = new Database() ;
$db = $database->getConnexion() ;
$pointRecharge = new PointRecharge($db) ;
$pointRecharge->delete($id) ;

header('Location: ../index.php?succes=suppression') ;
exit ;