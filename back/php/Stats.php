<?php

class Stats {
    private $db ;

    public function __construct($db) {
        $this->db = $db ;
    }

    public function getStats() {
        try {
            $stats = [] ;

            // STAT 1 : Nb total de points de recharge
            $statement = $this->db->query("SELECT COUNT(*) AS total_points FROM point_de_recharge") ;
            $result = $statement->fetch(PDO::FETCH_ASSOC) ;
            $stats["total_points"] = $result["total_points"] ;

            // STAT 2 : Nb de points de recharge de l'année la + récente
            $statement = $this->db->query("SELECT YEAR(date_mise_en_service) AS annee_recente, COUNT(p.id) AS nb_points
                                    FROM point_de_recharge p
                                    JOIN station s ON p.id_station_itinerance = s.id_station_itinerance
                                    WHERE s.date_mise_en_service IS NOT NULL
                                    GROUP BY YEAR(date_mise_en_service)
                                    ORDER BY annee_recente DESC LIMIT 1") ;
            $result = $statement->fetch(PDO::FETCH_ASSOC) ;
            $stats["points_annee_recente"] = $result ;

            // STAT 3 : Nb de points de recharge par département
            $statement = $this->db->query("SELECT d.nom_departement, d.code_dep, COUNT(p.id) AS nb_points
                                    FROM point_de_recharge p
                                    JOIN station s ON p.id_station_itinerance = s.id_station_itinerance
                                    JOIN commune c ON s.code_insee_commune = c.code_insee_commune
                                    JOIN departement d ON c.code_dep = d.code_dep
                                    GROUP BY d.code_dep, d.nom_departement
                                    ORDER BY d.code_dep") ;
            $result = $statement->fetchAll(PDO::FETCH_ASSOC) ;
            $stats["points_par_dep"] = $result ;

            // STAT 4 : Nb de points de recharge par département pour l'année la plus récente
            $statement = $this->db->query("SELECT d.nom_departement, d.code_dep, COUNT(p.id) AS nb_points
                                    FROM point_de_recharge p
                                    JOIN station s ON p.id_station_itinerance = s.id_station_itinerance
                                    JOIN commune c ON s.code_insee_commune = c.code_insee_commune
                                    JOIN departement d ON c.code_dep = d.code_dep
                                    WHERE YEAR(s.date_mise_en_service) =
                                    (SELECT MAX(YEAR(date_mise_en_service)) FROM station WHERE date_mise_en_service IS NOT NULL)
                                    GROUP BY d.code_dep, d.nom_departement
                                    ORDER BY d.code_dep") ;
            $result = $statement->fetchAll(PDO::FETCH_ASSOC) ;
            $stats["points_par_dep_recent"] = $result ;

            // STAT 5 : Nb d'aménageurs
            $statement = $this->db->query("SELECT  COUNT(*) AS nb_amenageurs FROM acteur WHERE role = 'Amenageur'") ;
            $result = $statement->fetch(PDO::FETCH_ASSOC) ;
            $stats["nb_amenageurs"] = $result["nb_amenageurs"] ;

            // STAT 6 : Nb de prises par type
            $statement = $this->db->query("SELECT type_prise, COUNT(id) AS nb_prises FROM point_recharge_prise
                                    GROUP BY type_prise ORDER BY nb_prises DESC") ;
            $result = $statement->fetchAll(PDO::FETCH_ASSOC) ;
            $stats["prises_par_type"] = $result ;

            return $stats ;
        }
        catch (PDOException $exception) {
            error_log('Request error: '.$exception->getMessage()) ;
            return false ;
        }
    }
}

?>