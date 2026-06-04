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

            // FILTRE 2 : Par département
            $statement = $this->db->query("SELECT code_dep, nom_departement FROM departement ORDER BY nom_departement") ;
            $result = $statement->fetchAll(PDO::FETCH_ASSOC) ;
            $rechercheCarte["liste_dep"] = $result ;

            return $rechercheCarte ;
        }

        catch (PDOException $exception) {
            error_log('Request error: ' . $exception->getMessage()) ;
            return false ;
        }
    }

    // Récupère les points de recharge avec leurs coordonnées
    // Filtrage optionnel par année et par département
    public function getMarqueurs($annee, $dep) {
        try {
            $sql = "SELECT p.id, p.consolidated_latitude AS latitude, p.consolidated_longitude AS longitude,
                    p.puissance_nominale, s.nom_station, c.nom_commune, d.nom_departement
                    FROM point_de_recharge p
                    JOIN station s ON p.id_station_itinerance = s.id_station_itinerance
                    JOIN commune c ON s.code_insee_commune = c.code_insee_commune
                    JOIN departement d ON c.code_dep = d.code_dep
                    WHERE (:annee = '' OR YEAR(s.date_mise_en_service) = :annee)
                    AND (:code_dep = '' OR d.code_dep = :code_dep)
                    AND p.consolidated_latitude IS NOT NULL
                    AND p.consolidated_longitude IS NOT NULL" ;

            $statement = $this->db->prepare($sql) ;
            $statement->bindParam(':annee', $annee, PDO::PARAM_STR) ;
            $statement->bindParam(':code_dep', $dep, PDO::PARAM_STR) ;
            $statement->execute() ;
            $result = $statement->fetchAll(PDO::FETCH_ASSOC) ;

            return $result ;
        }

        catch (PDOException $exception) {
            error_log('Request error: ' . $exception->getMessage()) ;
            return false ;
        }
    }
}