<?php

class Carte {
    private $db ;

    public function __construct($db) {
        $this->db = $db ;
    }

    public function getRechercheCarte() {
        try {
            $rechercheCarte = [] ;

            // FILTRE 1 Par année
            $statement = $this->db->query("SELECT DISTINCT YEAR(date_mise_en_service) AS annee FROM station
                                           WHERE date_mise_en_service IS NOT NULL
                                           AND YEAR(date_mise_en_service) > 0
                                           ORDER BY annee DESC") ;
            $result = $statement->fetchAll(PDO::FETCH_ASSOC) ;
            $rechercheCarte["liste_annees"] = $result ;

            // FILTRE 3 : Par département
            $statement = $this->db->query("SELECT code_dep, nom_departement FROM departement ORDER BY nom_departement") ;
            $result = $statement->fetchAll(PDO::FETCH_ASSOC) ;
            $rechercheCarte["liste_dep"] = $result ;

            return $rechercheCarte ;            
        }

        catch (PDOException $exception) {
            error_log('Request error: '.$exception->getMessage()) ;
            return false ;
        }
    }
}

?>