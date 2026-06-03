<?php

class Resultats {
    private $db ;

    public function __construct($db) {
        $this->db = $db ;
    }

    public function getResultats() {
        try {
            
        }

        catch (PDOException $exception) {
            error_log('Request error: '.$exception->getMessage()) ;
            return false ;
        }
    }
}

?>