<?php

require_once('constantes.php') ;


class Database {
    private $connexion ;
    public function __construct() {
        try {
            $db = new PDO('mysql:host='.DB_SERVER.';dbname='.DB_NAME.';charset=utf8', DB_USER, DB_PASSWORD) ;
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION) ;
            $this->connexion = $db ;
        }
        catch (PDOException $exception) {
            error_log('Connection error: '.$exception->getMessage()) ;
        }
    }
    public function getConnexion() {
        return $this->connexion ;
    }
}

?>