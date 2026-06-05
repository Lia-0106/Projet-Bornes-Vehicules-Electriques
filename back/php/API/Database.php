<?php

require_once('constantes.php') ;


// -------------------------------------------------------
// CONNEXION À LA BASE DE DONNÉES
// -------------------------------------------------------
class Database {
    private $connexion ;

    // -------------------------------------------------------
    // INITIALISATION DE LA CONNEXION PDO
    // -------------------------------------------------------
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

    // -----------------------------------------------------------------
    // GETTER DE CONNEXION
    // Retourne l'objet PDO pour être injecté dans les autres classes
    // -----------------------------------------------------------------
    public function getConnexion() {
        return $this->connexion ;
    }
}

?>