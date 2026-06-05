<?php

require_once('Database.php') ;

// --------------------------------------------------------------
// CLASSE PointRecharge
// Contient toutes les requêtes SQL liées aux pts de recharge
// --------------------------------------------------------------
class PointRecharge {

    private $db ;

    // -------------------------------------------------------
    // CONSTRUCTEUR : initialise la co à la BDD
    // -----------------------------------------------------
    public function __construct() {
        $database = new Database() ;
        $this->db = $database->getConnexion() ;
    }

    // -------------------------------------------------------
    // LISTE : récupère les points de recharge paginés
    // filtrage optionnel par id_station_itinerance
    // utilisé sur la page d'accueil du back
    // -------------------------------------------------------
    public function getListe($limite = 50, $offset = 0, $recherche = '') {
        // filtre par identifiant station si recherche non vide
        $where = '' ;
        $params = array() ;

        if (!empty($recherche)) {
            $where = "WHERE s.id_station_itinerance LIKE :recherche" ;
            $params[':recherche'] = '%' . $recherche . '%' ;
        }

        //LEFT car sinon, quand on créer un point, et qu'on veut le rechercher on ne le trouve pas car on créer un code INSEE temporaire mais qui n'existe pas dans la table commune donc le JOIN échoue 
        $sql = "SELECT 
                    p.id,
                    s.id_station_itinerance,
                    s.nom_station,
                    s.adresse_station,
                    s.date_mise_en_service,
                    s.nbre_pdc,
                    p.puissance_nominale,
                    c.nom_commune,
                    d.nom_departement,
                    a.nom AS nom_amenageur,
                    d.code_dep
                FROM point_de_recharge p
                JOIN station s ON p.id_station_itinerance = s.id_station_itinerance
                LEFT JOIN commune c ON s.code_insee_commune = c.code_insee_commune
                LEFT JOIN departement d ON c.code_dep = d.code_dep
                LEFT JOIN acteur a ON s.id_acteur = a.id_acteur
                $where
                LIMIT :limite OFFSET :offset" ;

        $stmt = $this->db->prepare($sql) ;
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT) ;
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT) ;

        // on ajoute le paramètre recherche si nécessaire
        if (!empty($recherche)) {
            $stmt->bindValue(':recherche', '%' . $recherche . '%') ;
        }

        $stmt->execute() ;
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ;
    }

    // -------------------------------------------------------
    // TOTAL : compte le nombre total de points de recharge
    // utilisé pour le calcul de la pagination
    // ------------------------------------------------------
    public function getTotal() {
        $stmt = $this->db->query("SELECT COUNT(*) FROM point_de_recharge") ;
        return (int)$stmt->fetchColumn() ;
    }

    // ---------------------------------------------------------
    // DETAIL : récupère toutes les infos d'un point par son id
    // jointures sur prises et paiements via GROUP_CONCAT
    // utilisé en front et en back sur la page détail
    // ---------------------------------------------------------
    public function getDetails() {
        $id = isset($_GET['id']) ? $_GET['id'] : 0 ;

        $sql= "SELECT p.id, p.puissance_nominale, p.cable_t2_attache, p.gratuit, p.tarification, p.consolidated_longitude,
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

        $statement = $this->db->prepare($sql) ;
        $statement->bindParam(':id', $id, PDO::PARAM_INT) ;
        $statement->execute() ;
        $result = $statement->fetch(PDO::FETCH_ASSOC) ;
        return $result ;
    }

    // ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
    // CREER : insère un nouveau pt de recharge
    // ordre d'insertion : station → point → prises → paiements : on insère dans cet ordre car chaque table référence la précédente : station (id_station) → point (id) → prises et paiements (id du point)
    // utilisé sur la page créer du back
    // ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
    public function create($data) {
        // on insère d'abord dans station
        $sqlStation = "INSERT INTO station 
                (id_station_itinerance, nom_station, adresse_station, nbre_pdc, date_mise_en_service, code_insee_commune, id_acteur, horaires, nom_enseigne, implantation_station)
               VALUES 
                (:id_station_itinerance, :nom_station, :adresse_station, :nbre_pdc, :date_mise_en_service, :code_insee_commune, :id_acteur, :horaires, :nom_enseigne, :implantation_station)";

        $stmtStation = $this->db->prepare($sqlStation);
        $stmtStation->execute(array(
            ':id_station_itinerance' => $data['id_station_itinerance'],
            ':nom_station'           => $data['nom_station'],
            ':adresse_station'       => $data['adresse_station'],
            ':nbre_pdc'              => $data['nbre_pdc'],
            ':date_mise_en_service'  => $data['date_mise_en_service'],
            ':code_insee_commune'    => $data['code_insee_commune'],
            ':id_acteur'             => $data['id_acteur'],
            ':horaires'              => $data['horaires'],
            ':nom_enseigne'          => $data['nom_enseigne'],
            ':implantation_station'  => !empty($data['implantation_station']) ? $data['implantation_station'] : null,
        )) ;

        // on récupère le prochain id disponible
        $stmtId = $this->db->query("SELECT MAX(id) + 1 AS prochain_id FROM point_de_recharge") ;
        $rowId = $stmtId->fetch(PDO::FETCH_ASSOC) ;
        $idPoint = $rowId['prochain_id'] ;

        // puis ds point_de_recharge
        $sqlPoint = "INSERT INTO point_de_recharge 
                        (id, puissance_nominale, cable_t2_attache, gratuit, tarification, consolidated_longitude, consolidated_latitude, id_station_itinerance, condition_acces)
                    VALUES 
                        (:id, :puissance_nominale, :cable_t2_attache, :gratuit, :tarification, :consolidated_longitude, :consolidated_latitude, :id_station_itinerance, :condition_acces)" ;

        $stmtPoint = $this->db->prepare($sqlPoint) ;
        $stmtPoint->execute(array(
            ':id'                       => $idPoint,
            ':puissance_nominale'       => $data['puissance_nominale'],
            ':cable_t2_attache'         => $data['cable_t2_attache'],
            ':gratuit'                  => $data['gratuit'],
            ':tarification'             => $data['tarification'],
            ':consolidated_longitude'   => $data['consolidated_longitude'],
            ':consolidated_latitude'    => $data['consolidated_latitude'],
            ':id_station_itinerance'    => $data['id_station_itinerance'],
            ':condition_acces'          => $data['condition_acces'],
        )) ;

        // on insère les types de prises associés au pt
        if (!empty($data['types_prises'])) {
            foreach ($data['types_prises'] as $typePrise) {
                $stmt = $this->db->prepare("INSERT INTO point_recharge_prise (id, type_prise) VALUES (:id, :type_prise)") ;
                $stmt->execute(array(
                    ':id'         => $idPoint,
                    ':type_prise' => $typePrise,
                )) ;
            }
        }

        // on insère les types de paiement
        if (!empty($data['types_paiement'])) {
            foreach ($data['types_paiement'] as $typePaiement) {
                $stmt = $this->db->prepare("INSERT INTO point_recharge_paiement (id, type_paiement) VALUES (:id, :type_paiement)") ;
                $stmt->execute(array(
                    ':id'            => $idPoint,
                    ':type_paiement' => $typePaiement,
                )) ;
            }
        }

        return true;
    }

    // -------------------------------------------------------
    // MODIFIER : met à jour un point existant par son id
    // maj en deux temps : station puis point
    // utilisé sur la page modifier du back
    // -------------------------------------------------------
    public function update($id, $data) {
        // on récupère d'abord l'id_station_itinerance du pt
        $stmtGet = $this->db->prepare("SELECT id_station_itinerance FROM point_de_recharge WHERE id = :id");
        $stmtGet->execute(array(':id' => $id));
        $row = $stmtGet->fetch(PDO::FETCH_ASSOC);

        if (!$row) return false;
        $idStation = $row['id_station_itinerance'];

        // maj de la station
        $sqlStation = "UPDATE station SET
                        nom_station          = :nom_station,
                        adresse_station      = :adresse_station,
                        date_mise_en_service = :date_mise_en_service,
                        horaires             = :horaires,
                        nom_enseigne         = :nom_enseigne
                       WHERE id_station_itinerance = :id_station_itinerance";

        $stmtStation = $this->db->prepare($sqlStation);
        $stmtStation->execute(array(
            ':nom_station'           => $data['nom_station'],
            ':adresse_station'       => $data['adresse_station'],
            ':date_mise_en_service'  => $data['date_mise_en_service'],
            ':horaires'              => $data['horaires'],
            ':nom_enseigne'          => $data['nom_enseigne'],
            ':id_station_itinerance' => $idStation,
        ));

        // maj du point de recharge
        $sqlPoint = "UPDATE point_de_recharge SET
                        puissance_nominale = :puissance_nominale,
                        cable_t2_attache   = :cable_t2_attache,
                        gratuit            = :gratuit,
                        tarification       = :tarification,
                        condition_acces    = :condition_acces
                     WHERE id = :id";

        $stmtPoint = $this->db->prepare($sqlPoint);
        $stmtPoint->execute(array(
            ':puissance_nominale' => $data['puissance_nominale'],
            ':cable_t2_attache'   => $data['cable_t2_attache'],
            ':gratuit'            => $data['gratuit'],
            ':tarification'       => $data['tarification'],
            ':condition_acces'    => $data['condition_acces'],
            ':id'                 => $id,
        ));

        return true;
    }

    // -------------------------------------------------------
    // RECUP OU CREE UN ACTEUR (aménageur / opérateur)
    // si l'acteur n'existe pas en base, on le crée
    // -----------------------------------------------------
    public function getOuCreerActeur($nom, $contact = '', $telephone = '', $siren = '', $role = 'amenageur') {
        // on cherche si l'acteur existe déjà
        $stmt = $this->db->prepare("SELECT id_acteur FROM acteur WHERE nom = :nom LIMIT 1") ;
        $stmt->execute(array(':nom' => $nom)) ;
        $row = $stmt->fetch(PDO::FETCH_ASSOC) ;

        if ($row) {
            return $row['id_acteur'] ; // on retourne son id
        }

        // sinon on le crée avec tous ses champs
        $stmt = $this->db->prepare("INSERT INTO acteur (nom, contact, telephone, siren, role) VALUES (:nom, :contact, :telephone, :siren, :role)") ;
        $stmt->execute(array(
            ':nom'       => $nom,
            ':contact'   => $contact,
            ':telephone' => $telephone,
            ':siren'     => $siren,
            ':role'      => $role,
        )) ;

        return $this->db->lastInsertId() ;
    }

    // -----------------------------------------------------------
    // RECUP OU CREE UNE COMMUNE
    // si la commune n'existe pas en base, on la crée
    // avec un code INSEE temporaire basé sur le code postal
    // retourne toujours le code_insee_commune
    // -----------------------------------------------------------
    public function getOuCreerCommune($nomCommune, $codePostal = '', $codeDep = '') {
        // on cherche si la commune existe déjà
        $stmt = $this->db->prepare("SELECT code_insee_commune FROM commune WHERE nom_commune = :nom LIMIT 1");
        $stmt->execute(array(':nom' => $nomCommune));
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            return $row['code_insee_commune']; //on retourne son code INSEE
        }

        //sinon on crée un code INSEE temporaire basé sur le code postal
        $codeInsee = $codePostal ?: '00000';

        $stmt = $this->db->prepare("INSERT INTO commune (code_insee_commune, nom_commune, code_postal, code_dep) VALUES (:code, :nom, :cp, :dep)");
        $stmt->execute(array(
            ':code' => $codeInsee,
            ':nom'  => $nomCommune,
            ':cp'   => $codePostal,
            ':dep'  => $codeDep ?: null,
        ));

        return $codeInsee;
    }

    // -------------------------------------------------------
    // RECUP OU CREE UNE ENSEIGNE
    // si l'enseigne n'existe pas en base, on la crée
    // retourne toujours le nom_enseigne
    // -------------------------------------------------------
    public function getOuCreerEnseigne($nomEnseigne) {
        //on cherche si l'enseigne existe déjà
        $stmt = $this->db->prepare("SELECT nom_enseigne FROM enseigne WHERE nom_enseigne = :nom LIMIT 1");
        $stmt->execute(array(':nom' => $nomEnseigne));
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            return $row['nom_enseigne']; //elle existe déja
        }

        // sinon on la crée
        $stmt = $this->db->prepare("INSERT INTO enseigne (nom_enseigne) VALUES (:nom)");
        $stmt->execute(array(':nom' => $nomEnseigne));

        return $nomEnseigne;
    }

    // -------------------------------------------------------
    //cSUPPRIMER : supprime un point de recharge par son id
    // ordre de suppression : prises, paiements, point, station (si vide), acteur (si plus de stations)
    // -------------------------------------------------------
    public function supprimer($id) {
        // on supprime d'abord les tables liées pour respecter les clés étrangères
        $stmt = $this->db->prepare("DELETE FROM point_recharge_prise WHERE id = :id") ;
        $stmt->execute(array(':id' => $id)) ;

        $stmt = $this->db->prepare("DELETE FROM point_recharge_paiement WHERE id = :id") ;
        $stmt->execute(array(':id' => $id)) ;

        // on récupère l'id_station et l'id_acteur avant de supprimer le point
        $stmt = $this->db->prepare("SELECT s.id_station_itinerance, s.id_acteur FROM point_de_recharge p JOIN station s ON p.id_station_itinerance = s.id_station_itinerance WHERE p.id = :id") ;
        $stmt->execute(array(':id' => $id)) ;
        $row = $stmt->fetch(PDO::FETCH_ASSOC) ;

        // on supprime le point
        $stmt = $this->db->prepare("DELETE FROM point_de_recharge WHERE id = :id") ;
        $stmt->execute(array(':id' => $id)) ;

        if ($row) {
            $idStation = $row['id_station_itinerance'] ;
            $idActeur  = $row['id_acteur'] ;

            // on supprime la station si elle n'a plus de points
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM point_de_recharge WHERE id_station_itinerance = :id_station") ;
            $stmt->execute(array(':id_station' => $idStation)) ;
            $nb = (int)$stmt->fetchColumn() ;

            if ($nb === 0) {
                $stmt = $this->db->prepare("DELETE FROM station WHERE id_station_itinerance = :id_station") ;
                $stmt->execute(array(':id_station' => $idStation)) ;

                // on supprime l'acteur si il n'a plus de stations
                $stmt = $this->db->prepare("SELECT COUNT(*) FROM station WHERE id_acteur = :id_acteur") ;
                $stmt->execute(array(':id_acteur' => $idActeur)) ;
                $nbStations = (int)$stmt->fetchColumn() ;

                if ($nbStations === 0) {
                    $stmt = $this->db->prepare("DELETE FROM acteur WHERE id_acteur = :id_acteur") ;
                    $stmt->execute(array(':id_acteur' => $idActeur)) ;
                }
            }
        }

    return true ;
}
}
?>