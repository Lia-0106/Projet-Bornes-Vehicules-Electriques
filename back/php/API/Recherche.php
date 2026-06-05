<?php


// ------------------------------------------------------------------
// CLASSE Recherche
// Fournit les listes pour alimenter les filtres de recherche
// ------------------------------------------------------------------
class Recherche {
    private $db ;

    public function __construct($db) {
        $this->db = $db ;
    }

    // -------------------------------------------------------
    // FILTRES DE RECHERCHE
    // retourne les listes pour les 3 menus déroulants
    // utilisé sur la page de recherche en front
    // -----------------------------------------------------

    public function getRecherche() {
        try {
            $recherche = [] ;

            // FILTRE 1 Par aménageur : 20 aménageurs tirés aléatoirement pour varier l'affichage
            $statement = $this->db->query("SELECT DISTINCT nom FROM acteur
                                           WHERE role = 'Amenageur' AND nom IS NOT NULL
                                           ORDER BY RAND() LIMIT 20") ;
            $result = $statement->fetchAll(PDO::FETCH_ASSOC) ;
            $recherche["liste_amenageurs"] = $result ;

            // FILTRE 2 : Par type de prise : tous les types 
            $statement = $this->db->query("SELECT DISTINCT type_prise FROM point_recharge_prise
                                           WHERE type_prise IS NOT NULL") ;
            $result = $statement->fetchAll(PDO::FETCH_ASSOC) ;
            $recherche["liste_types_prises"] = $result ;

            // FILTRE 3 : Par département : tous
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