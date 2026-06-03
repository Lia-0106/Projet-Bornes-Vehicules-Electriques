<?php

class Recherche {
    private $db ;

    public function __construct($db) {
        $this->db = $db ;
    }

    public function getRecherche() {
        try {
            $recherche = [] ;

            // FILTRE 1 Par aménageur
            $statement = $this->db->query("SELECT DISTINCT nom FROM acteur
                                           WHERE role = 'Amenageur' AND nom IS NOT NULL
                                           ORDER BY RAND() LIMIT 20") ;
            $result = $statement->fetchAll(PDO::FETCH_ASSOC) ;
            $recherche["liste_amenageurs"] = $result ;

            // FILTRE 2 : Par type de prise
            $statement = $this->db->query("SELECT DISTINCT type_prise FROM point_recharge_prise
                                           WHERE type_prise IS NOT NULL") ;
            $result = $statement->fetchAll(PDO::FETCH_ASSOC) ;
            $recherche["liste_types_prises"] = $result ;

            // FILTRE 3 : Par département
            $statement = $this->db->query("SELECT code_dep, nom_departement FROM departement ORDER BY nom_departement") ;
            $result = $statement->fetchAll(PDO::FETCH_ASSOC) ;
            $recherche["liste_dep"] = $result ;

            return $recherche ;            
        }

        catch (PDOException $exception) {
            error_log('Request error: '.$exception->getMessage()) ;
            return false ;
        }
    }
}

?>