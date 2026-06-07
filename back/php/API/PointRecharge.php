<?php

// --------------------------------------------------------------
// CLASSE PointRecharge
// Contient toutes les requêtes SQL liées aux pts de recharge
// --------------------------------------------------------------
class PointRecharge {
    private $db ;

    public function __construct($db) {
        $this->db = $db ;
    }


    // -------------------------------------------------------
    // LISTE : récupère les points de recharge paginés
    // Filtrage optionnel par id_station_itinerance
    // Utilisé sur la page d'accueil du back
    // -------------------------------------------------------
    public function getListe($limite = 50, $offset = 0, $recherche = '') {
        $request = "SELECT p.id, s.id_station_itinerance, s.nom_station, s.adresse_station, s.date_mise_en_service, s.nbre_pdc,
                       p.puissance_nominale, c.nom_commune, d.nom_departement, a.nom AS nom_amenageur, d.code_dep
                FROM point_de_recharge p
                JOIN station s ON p.id_station_itinerance = s.id_station_itinerance
                LEFT JOIN commune c ON s.code_insee_commune = c.code_insee_commune
                LEFT JOIN departement d ON c.code_dep = d.code_dep
                LEFT JOIN acteur a ON s.id_acteur = a.id_acteur
                WHERE (:recherche = '' OR s.id_station_itinerance LIKE :recherche_like)
                LIMIT :limite OFFSET :offset" ;

        $statement = $this->db->prepare($request) ;
        $statement->bindParam(':recherche', $recherche, PDO::PARAM_STR) ;
        $rechercheLike = '%' . $recherche . '%' ;
        $statement->bindParam(':recherche_like', $rechercheLike, PDO::PARAM_STR) ;
        $statement->bindParam(':limite', $limite, PDO::PARAM_INT) ;
        $statement->bindParam(':offset', $offset, PDO::PARAM_INT) ;        $statement->execute() ;
        
        $statement->execute() ;
        $result = $statement->fetchAll(PDO::FETCH_ASSOC) ;
        
        return $result ;
    }


    // -------------------------------------------------------
    // TOTAL : Compte le nombre total de points de recharge
    // Utilisé pour le calcul de la pagination
    // ------------------------------------------------------
    public function getTotal($recherche = '') {
        $request = "SELECT COUNT(*) AS total_points
                    FROM point_de_recharge p
                    JOIN station s ON p.id_station_itinerance = s.id_station_itinerance
                    WHERE (:recherche = '' OR s.id_station_itinerance LIKE :recherche_like)" ;

        $statement = $this->db->prepare($request) ;
        $statement->bindParam(':recherche', $recherche, PDO::PARAM_STR) ;
        $rechercheLike = '%' . $recherche . '%' ;
        $statement->bindParam(':recherche_like', $rechercheLike, PDO::PARAM_STR) ;
        $statement->execute() ;
        $result = $statement->fetch(PDO::FETCH_ASSOC) ;
        return $result['total_points'] ;
    }


    // ---------------------------------------------------------
    // DETAIL : récupère toutes les infos d'un point par son id
    // GROUP_CONCAT fusionne les types de prises et paiement en une ligne
    // Utilisé en front et en back sur la page détail
    // ---------------------------------------------------------
    public function getDetails($id) {
        $request= "SELECT p.id, p.puissance_nominale, p.cable_t2_attache, p.gratuit, p.tarification, p.consolidated_longitude,
                          p.consolidated_latitude, p.condition_acces, s.id_station_itinerance, s.nom_station, s.adresse_station,
                          s.date_mise_en_service, s.nbre_pdc, s.horaires, s.nom_enseigne, c.nom_commune, d.nom_departement,
                          a.nom AS nom_amenageur, a.contact AS contact_amenageur, a.siren AS siren_amenageur, op.nom AS nom_operateur,
                          op.telephone AS telephone_operateur,
                   GROUP_CONCAT(DISTINCT p_prise.type_prise SEPARATOR ', ') AS types_prises,
                   GROUP_CONCAT(DISTINCT p_paie.type_paiement SEPARATOR ', ') AS types_paiement
                   FROM point_de_recharge p
                   LEFT JOIN station s ON p.id_station_itinerance = s.id_station_itinerance
                   LEFT JOIN commune c ON s.code_insee_commune = c.code_insee_commune
                   LEFT JOIN departement d ON c.code_dep = d.code_dep
                   LEFT JOIN acteur a ON s.id_acteur = a.id_acteur
                   LEFT JOIN acteur op ON s.id_acteur_operateur = op.id_acteur
                   LEFT JOIN point_recharge_prise p_prise ON p.id = p_prise.id
                   LEFT JOIN point_recharge_paiement p_paie ON p.id = p_paie.id
                   WHERE p.id = :id
                   GROUP BY p.id" ;

        $statement = $this->db->prepare($request) ;
        $statement->bindParam(':id', $id, PDO::PARAM_INT) ;
        $statement->execute() ;
        $result = $statement->fetch(PDO::FETCH_ASSOC) ;
        return $result ;
    }


    // ------------------------------------------------------------------------------------------------------------------------------------------
    // CREER : insère un nouveau pt de recharge
    // Ordre d'insertion : station → point → prises → paiements
    // On insère dans cet ordre car chaque table référence la précédente : station (id_station) → point (id) → prises et paiements (id du point)
    // Utilisé sur la page créer du back
    // ------------------------------------------------------------------------------------------------------------------------------------------
    public function create($data) {
        // ÉTAPE 1 : insertion dans station
        $requestStation = "INSERT INTO station (id_station_itinerance, nom_station, adresse_station, nbre_pdc, date_mise_en_service,
                                       code_insee_commune, id_acteur, id_acteur_operateur, horaires, nom_enseigne, implantation_station)
                           VALUES (:id_station_itinerance, :nom_station, :adresse_station, :nbre_pdc, :date_mise_en_service,
                                   :code_insee_commune, :id_acteur, :id_acteur_operateur, :horaires, :nom_enseigne, :implantation_station)" ;

        $stmtStation = $this->db->prepare($requestStation) ;
        $stmtStation->bindParam(':id_station_itinerance', $data['id_station_itinerance'], PDO::PARAM_STR) ;
        $stmtStation->bindParam(':nom_station', $data['nom_station'], PDO::PARAM_STR) ;
        $stmtStation->bindParam(':adresse_station', $data['adresse_station'], PDO::PARAM_STR) ;
        $stmtStation->bindParam(':nbre_pdc', $data['nbre_pdc'], PDO::PARAM_INT) ;
        $stmtStation->bindParam(':date_mise_en_service', $data['date_mise_en_service'], PDO::PARAM_STR) ;
        $stmtStation->bindParam(':code_insee_commune', $data['code_insee_commune'], PDO::PARAM_STR) ;
        $stmtStation->bindParam(':id_acteur', $data['id_acteur'], PDO::PARAM_INT) ;
        $stmtStation->bindParam(':id_acteur_operateur', $data['id_operateur'], PDO::PARAM_INT) ;
        $stmtStation->bindParam(':horaires', $data['horaires'], PDO::PARAM_STR) ;
        $stmtStation->bindParam(':nom_enseigne', $data['nom_enseigne'], PDO::PARAM_STR) ;
        $stmtStation->bindParam(':implantation_station', $data['implantation_station'], PDO::PARAM_STR) ;
        $stmtStation->execute() ;

        // ÉTAPE 2 : calcul du prochain id disponible
        $stmtId = $this->db->query("SELECT MAX(id) + 1 AS prochain_id FROM point_de_recharge") ;
        $resultat = $stmtId->fetch(PDO::FETCH_ASSOC) ;
        $idPoint = $resultat['prochain_id'] ;

        // ÉTAPE 3 : insertion dans point_de_recharge
        $requestPoint = "INSERT INTO point_de_recharge (id, puissance_nominale, cable_t2_attache, gratuit, tarification,
                                     consolidated_longitude, consolidated_latitude, id_station_itinerance, condition_acces)
                         VALUES (:id, :puissance_nominale, :cable_t2_attache, :gratuit, :tarification, :consolidated_longitude,
                                 :consolidated_latitude, :id_station_itinerance, :condition_acces)" ;

        $stmtPoint = $this->db->prepare($requestPoint) ;
        $stmtPoint->bindParam(':id', $idPoint, PDO::PARAM_INT) ;
        $stmtPoint->bindParam(':puissance_nominale', $data['puissance_nominale'], PDO::PARAM_STR) ;
        $stmtPoint->bindParam(':cable_t2_attache', $data['cable_t2_attache'], PDO::PARAM_INT) ;
        $stmtPoint->bindParam(':gratuit', $data['gratuit'], PDO::PARAM_INT) ;
        $stmtPoint->bindParam(':tarification', $data['tarification'], PDO::PARAM_STR) ;
        $stmtPoint->bindParam(':consolidated_longitude', $data['consolidated_longitude'], PDO::PARAM_STR) ;
        $stmtPoint->bindParam(':consolidated_latitude', $data['consolidated_latitude'], PDO::PARAM_STR) ;
        $stmtPoint->bindParam(':id_station_itinerance', $data['id_station_itinerance'], PDO::PARAM_STR) ;
        $stmtPoint->bindParam(':condition_acces', $data['condition_acces'], PDO::PARAM_STR) ;
        $stmtPoint->execute() ;

        // ÉTAPE 4 : insertion des types de prises
        if (!empty($data['types_prises'])) {
            $stmtPrise = $this->db->prepare("INSERT INTO point_recharge_prise (id, type_prise) VALUES (:id, :type_prise)") ;
            foreach ($data['types_prises'] as $typePrise) {
                $stmtPrise->bindParam(':id', $idPoint, PDO::PARAM_INT) ;
                $stmtPrise->bindParam(':type_prise', $typePrise, PDO::PARAM_STR) ;
                $stmtPrise->execute() ;
            }
        }

        // ÉTAPE 5 : insertion des types de paiement
        if (!empty($data['types_paiement'])) {
            $stmtPaiement = $this->db->prepare("INSERT INTO point_recharge_paiement (id, type_paiement) VALUES (:id, :type_paiement)") ;
            foreach ($data['types_paiement'] as $typePaiement) {
                $stmtPaiement->bindParam(':id', $idPoint, PDO::PARAM_INT) ;
                $stmtPaiement->bindParam(':type_paiement', $typePaiement, PDO::PARAM_STR) ;
                $stmtPaiement->execute() ;
            }
        }

        return true ;
    }


    // -------------------------------------------------------
    // MODIFIER : met à jour un point existant à partir de son id
    // Maj en deux temps : station puis point
    // Utilisé sur la page modifier du back
    // -------------------------------------------------------
    public function update($id, $data) {
        // ÉTAPE 1 : récupération de l'id_station lié au point
        $stmtGet = $this->db->prepare("SELECT id_station_itinerance FROM point_de_recharge WHERE id = :id") ;
        $stmtGet->bindParam(':id', $id, PDO::PARAM_INT) ;
        $stmtGet->execute() ;
        $resultat = $stmtGet->fetch(PDO::FETCH_ASSOC) ;

        $idStation = $resultat['id_station_itinerance'] ;

        // ÉTAPE 2 : mise à jour ou création de l'aménageur et de l'opérateur
        $idAmenageur = $this->getOuCreerActeur($data['nom_amenageur'], $data['contact_amenageur'], '', $data['siren_amenageur']) ;
        $idOperateur = $this->getOuCreerActeur($data['nom_operateur'], $data['contact_operateur'], $data['telephone_operateur'], '') ;
        $nomEnseigne = $this->getOuCreerEnseigne($data['nom_enseigne']) ;

        // ÉTAPE 3 : mise à jour de la station
        $requestStation = "UPDATE station SET nom_station = :nom_station,
                                          adresse_station = :adresse_station,
                                          date_mise_en_service = :date_mise_en_service,
                                          horaires = :horaires,
                                          nom_enseigne = :nom_enseigne,
                                          implantation_station = :implantation_station,
                                          id_acteur = :id_acteur,
                                          id_acteur_operateur = :id_acteur_operateur
                           WHERE id_station_itinerance = :id_station_itinerance" ;

        $stmtStation = $this->db->prepare($requestStation) ;
        $stmtStation->bindParam(':nom_station', $data['nom_station'], PDO::PARAM_STR) ;
        $stmtStation->bindParam(':adresse_station', $data['adresse_station'], PDO::PARAM_STR) ;
        $stmtStation->bindParam(':date_mise_en_service', $data['date_mise_en_service'], PDO::PARAM_STR) ;
        $stmtStation->bindParam(':horaires', $data['horaires'], PDO::PARAM_STR) ;
        $stmtStation->bindParam(':nom_enseigne', $nomEnseigne, PDO::PARAM_STR) ;
        $stmtStation->bindParam(':implantation_station', $data['implantation_station'], PDO::PARAM_STR) ;
        $stmtStation->bindParam(':id_acteur', $idAmenageur, PDO::PARAM_INT) ;
        $stmtStation->bindParam(':id_acteur_operateur', $idOperateur, PDO::PARAM_INT) ;
        $stmtStation->bindParam(':id_station_itinerance', $idStation, PDO::PARAM_STR) ;
        $stmtStation->execute() ;

        // ÉTAPE 4 : mise à jour du point de recharge
        $requestPoint = "UPDATE point_de_recharge SET puissance_nominale = :puissance_nominale,
                                                  cable_t2_attache = :cable_t2_attache,
                                                  gratuit = :gratuit,
                                                  tarification = :tarification,
                                                  condition_acces = :condition_acces,
                                                  consolidated_latitude = :consolidated_latitude,
                                                  consolidated_longitude = :consolidated_longitude
                         WHERE id = :id" ;

        $stmtPoint = $this->db->prepare($requestPoint) ;
        $stmtPoint->bindParam(':puissance_nominale', $data['puissance_nominale'], PDO::PARAM_STR) ;
        $stmtPoint->bindParam(':cable_t2_attache', $data['cable_t2_attache'], PDO::PARAM_INT) ;
        $stmtPoint->bindParam(':gratuit', $data['gratuit'], PDO::PARAM_INT) ;
        $stmtPoint->bindParam(':tarification', $data['tarification'], PDO::PARAM_STR) ;
        $stmtPoint->bindParam(':condition_acces', $data['condition_acces'], PDO::PARAM_STR) ;
        $stmtPoint->bindParam(':consolidated_latitude', $data['consolidated_latitude'], PDO::PARAM_STR) ;
        $stmtPoint->bindParam(':consolidated_longitude', $data['consolidated_longitude'], PDO::PARAM_STR) ;
        $stmtPoint->bindParam(':id', $id, PDO::PARAM_INT) ;
        $stmtPoint->execute() ;

        // ÉTAPE 5 : remplacement des prises et paiements
        $this->remplacerValeurs('point_recharge_prise', 'type_prise', $id, $data['types_prises']) ;
        $this->remplacerValeurs('point_recharge_paiement', 'type_paiement', $id, $data['types_paiement']) ;

        return true ;
    }


    // -------------------------------------------------------
    // REMPLACER VALEURS : remplace les entrées d'une table de liaison
    // Supprime les anciennes lignes puis réinsère les nouvelles
    // -------------------------------------------------------
    private function remplacerValeurs($table, $colonne, $id, $valeurs) {
        $stmtDel = $this->db->prepare("DELETE FROM $table WHERE id = :id") ;
        $stmtDel->bindParam(':id', $id, PDO::PARAM_INT) ;
        $stmtDel->execute() ;

        if (!empty($valeurs)) {
            $stmtInsere = $this->db->prepare("INSERT INTO $table (id, $colonne) VALUES (:id, :valeur)") ;
            foreach ($valeurs as $valeur) {
                $stmtInsere->bindParam(':id', $id, PDO::PARAM_INT) ;
                $stmtInsere->bindParam(':valeur', $valeur, PDO::PARAM_STR) ;
                $stmtInsere->execute() ;
            }
        }
    }


    // -------------------------------------------------------
    // RECUP OU CREE UN ACTEUR (aménageur / opérateur)
    // Si l'acteur n'existe pas en base, on le crée
    // -----------------------------------------------------
    public function getOuCreerActeur($nom, $contact = '', $telephone = '', $siren = '', $role = 'amenageur') {
        // On cherche si l'acteur existe déjà
        $statement = $this->db->prepare("SELECT id_acteur FROM acteur WHERE nom = :nom LIMIT 1") ;
        $statement->bindParam(':nom', $nom, PDO::PARAM_STR) ;
        $statement->execute() ;
        $result = $statement->fetch(PDO::FETCH_ASSOC) ;

        if ($result) {
            return $result['id_acteur'] ;
        }

        // Sinon on le crée
        $statement = $this->db->prepare("INSERT INTO acteur (nom, contact, telephone, siren, role) VALUES (:nom, :contact, :telephone, :siren, :role)") ;
        $statement->bindParam(':nom', $nom, PDO::PARAM_STR) ;
        $statement->bindParam(':contact', $contact, PDO::PARAM_STR) ;
        $statement->bindParam(':telephone', $telephone, PDO::PARAM_STR) ;
        $statement->bindParam(':siren', $siren, PDO::PARAM_STR) ;
        $statement->bindParam(':role', $role, PDO::PARAM_STR) ;
        $statement->execute() ;

        return $this->db->lastInsertId() ;
    }


    // -------------------------------------------------------
    // RECUP OU CREE UNE ENSEIGNE
    // Si l'enseigne n'existe pas en base, on la crée
    // Retourne nom_enseigne
    // -------------------------------------------------------
    public function getOuCreerEnseigne($nomEnseigne) {
        // On cherche si l'enseigne existe déjà
        $statement = $this->db->prepare("SELECT nom_enseigne FROM enseigne WHERE nom_enseigne = :nom LIMIT 1") ;
        $statement->bindParam(':nom', $nomEnseigne, PDO::PARAM_STR) ;
        $statement->execute() ;
        $result = $statement->fetch(PDO::FETCH_ASSOC) ;

        if ($result) {
            return $result['nom_enseigne'] ;
        }

        // Sinon on la crée
        $statement = $this->db->prepare("INSERT INTO enseigne (nom_enseigne) VALUES (:nom)") ;
        $statement->bindParam(':nom', $nomEnseigne, PDO::PARAM_STR) ;
        $statement->execute() ;

        return $nomEnseigne ;
    }


    // -------------------------------------------------------
    // SUPPRIMER : supprime un point de recharge par son id
    // Ordre de suppression : prises → paiements → point → station (si vide) → acteur (si plus de stations)
    // -------------------------------------------------------
    public function delete($id) {
        // ÉTAPE 1 : suppression des prises et paiements liés au point
        $statement = $this->db->prepare("DELETE FROM point_recharge_prise WHERE id = :id") ;
        $statement->bindParam(':id', $id, PDO::PARAM_INT) ;
        $statement->execute() ;

        $statement = $this->db->prepare("DELETE FROM point_recharge_paiement WHERE id = :id") ;
        $statement->bindParam(':id', $id, PDO::PARAM_INT) ;
        $statement->execute() ;

        // ÉTAPE 2 : récupération de l'id_station et l'id_acteur avant suppression du point
        $statement = $this->db->prepare("SELECT s.id_station_itinerance, s.id_acteur
                                         FROM point_de_recharge p
                                         JOIN station s ON p.id_station_itinerance = s.id_station_itinerance
                                         WHERE p.id = :id") ;
        $statement->bindParam(':id', $id, PDO::PARAM_INT) ;
        $statement->execute() ;
        $result = $statement->fetch(PDO::FETCH_ASSOC) ;

        // ÉTAPE 3 : suppression du point
        $statement = $this->db->prepare("DELETE FROM point_de_recharge WHERE id = :id") ;
        $statement->bindParam(':id', $id, PDO::PARAM_INT) ;
        $statement->execute() ;

        $idStation = $result['id_station_itinerance'] ;
        $idActeur  = $result['id_acteur'] ;

        // ÉTAPE 4 : suppression de la station si elle n'a plus de points
        $statement = $this->db->prepare("SELECT COUNT(*) AS nb FROM point_de_recharge WHERE id_station_itinerance = :id_station") ;
        $statement->bindParam(':id_station', $idStation, PDO::PARAM_STR) ;
        $statement->execute() ;
        $nbPoints = (int)$statement->fetch(PDO::FETCH_ASSOC)['nb'] ;

        if ($nbPoints === 0) {
            $statement = $this->db->prepare("DELETE FROM station WHERE id_station_itinerance = :id_station") ;
            $statement->bindParam(':id_station', $idStation, PDO::PARAM_STR) ;
            $statement->execute() ;

            // ÉTAPE 5 : suppression de l'acteur s'il n'a plus de stations
            $statement = $this->db->prepare("SELECT COUNT(*) AS nb FROM station WHERE id_acteur = :id_acteur OR id_acteur_operateur = :id_acteur") ;
            $statement->bindParam(':id_acteur', $idActeur, PDO::PARAM_INT) ;
            $statement->execute() ;
            $nbStations = $statement->fetch(PDO::FETCH_ASSOC)['nb'] ;

            if ($nbStations === 0) {
                $statement = $this->db->prepare("DELETE FROM acteur WHERE id_acteur = :id_acteur") ;
                $statement->bindParam(':id_acteur', $idActeur, PDO::PARAM_INT) ;
                $statement->execute() ;
            }
        }
        return true ;
    }

}

?>