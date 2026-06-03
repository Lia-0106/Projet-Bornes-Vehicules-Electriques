<?php

require_once('constantes.php') ;

function dbConnect() {
    try {
        $db = new PDO('mysql:host='.DB_SERVER.';dbname='.DB_NAME.';charset=utf8', DB_USER, DB_PASSWORD) ;
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION) ;
    }
    catch (PDOException $exception) {
        error_log('Connection error: '.$exception->getMessage()) ;
        return false ;
    }
    return $db ;
}

function dbRequestStats($db) {
    try {
        $stats = [] ;

        // STAT 1 : Nb total de points de recharge
        $statement = $db->query("SELECT COUNT(*) AS total_points FROM point_de_recharge") ;
        $result = $statement->fetch(PDO::FETCH_ASSOC) ;
        $stats['total_points'] = $result['total_points'] ;

        // STAT 2 : Nb de points de recharge de l'année la + récente
        $statement = $db->query("SELECT YEAR(date_mise_en_service) AS annee_recente, COUNT(p.id) AS nb_points
                                 FROM point_de_recharge p
                                 JOIN station s ON p.id_station_itinerance = s.id_station_itinerance
                                 WHERE s.date_mise_en_service IS NOT NULL
                                 GROUP BY YEAR(date_mise_en_service)
                                 ORDER BY annee_recente DESC LIMIT 1") ;
        $result = $statement->fetch(PDO::FETCH_ASSOC) ;
        $stats["points_annee_recente"] = $result["points_annee_recente"] ;

        // STAT 3 : Nb de points de recharge par département
        $statement = $db->query("SELECT d.nom_departement, d.code_dep, COUNT(p.id) AS nb_points
                                 FROM point_de_recharge p
                                 JOIN station s ON p.id_station_itinerance = s.id_station_itinerance
                                 JOIN commune c ON s.code_insee_commune = c.code_insee_commune
                                 JOIN departement d ON c.code_dep = d.code_dep
                                 GROUP BY d.code_dep, d.nom_departement
                                 ORDER BY d.code_dep") ;
        $result = $statement->fetchAll(PDO::FETCH_ASSOC) ;
        $stats["points_par_dep"] = $result["points_par_dep"] ;

        // STAT 4 : Nb de points de recharge par département pour l'année la plus récente
    $statement = $db->query("SELECT d.nom_departement, d.code_dep, MAX") ;

    }
    catch (PDOException $exception) {
      error_log('Request error: '.$exception->getMessage()) ;
      return false ;
    }
}


?>