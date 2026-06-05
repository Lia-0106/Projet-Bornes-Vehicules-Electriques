<?php

session_start() ;
if (!isset($_SESSION['admin'])) {
    header('Location: ../php/login.php') ;
    exit ;
}

require_once __DIR__ . '/API/Database.php' ;
require_once __DIR__ . '/API/constantes.php' ;
require_once __DIR__ . '/API/PointRecharge.php' ;

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0 ;

if ($id <= 0) {
    header('Location: ../index.php') ;
    exit ;
}

$pointRecharge = new PointRecharge() ;
$pointRecharge->supprimer($id) ;

header('Location: ../index.php') ;
exit ;