<?php

// ---------------------------------------------------------
// CLASSE Resultats
// Retourne les points de recharge filtrés pour la recherche
// --------------------------------------------------------
class Resultats {
    private $db ;

    public function __construct($db) {
        $this->db = $db ;
    }

    // -------------------------------------------------------
    //RESULTATS FILTRES 
    //les 3 filtres sont optionnels : si vide (''),
    //la condition est ignorée grâce au OR
    //GROUP_CONCAT fusionne les types de prises en une ligne
    //utilisé sur la page de résultats en front
    // -------------------------------------------------------
    public function getResultats($amenageur, $type_prise, $code_dep) {
        try {
            $request = "SELECT s.date_mise_en_service, s.nbre_pdc AS nb_points, p.puissance_nominale AS puissance,
                        s.adresse_station, c.code_postal, c.nom_commune, p.id AS point_id,
                        GROUP_CONCAT(DISTINCT p_prise.type_prise SEPARATOR ', ') AS types_prises
                        FROM point_de_recharge p
                        JOIN station s ON p.id_station_itinerance = s.id_station_itinerance 
                        JOIN commune c ON s.code_insee_commune = c.code_insee_commune
                        LEFT JOIN point_recharge_prise p_prise ON p.id = p_prise.id
                        LEFT JOIN acteur a ON s.id_acteur = a.id_acteur
                        WHERE (:amenageur = '' OR a.nom = :amenageur)
                        AND (:type_prise = '' OR p_prise.type_prise = :type_prise)
                        AND (:code_dep = '' OR c.code_dep = :code_dep)
                        GROUP BY p.id" ;

            $statement = $this->db->prepare($request) ;
            $statement->bindParam(':amenageur', $amenageur, PDO::PARAM_STR) ;
            $statement->bindParam(':type_prise', $type_prise, PDO::PARAM_STR) ;
            $statement->bindParam(':code_dep', $code_dep, PDO::PARAM_STR) ;
            $statement->execute() ;
            $result = $statement->fetchAll(PDO::FETCH_ASSOC) ;
        }
        catch (PDOException $exception) {
            error_log('Request error: ' . $exception->getMessage()) ;
            return false ;
        }

        return $result ;
    }
}

?>