<?php

require_once 'Database.php';

// Classe PointRecharge : contient toutes les requêtes SQL liées aux pts de recharge
 
class PointRecharge {

    private $db; // connexion à la base de données

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnexion();
    }

    // -------------------------------------------------------
    // 1.LISTE : récupère les 100 premiers points
    //    Utilisé sur la page d'accueil du back
    // -------------------------------------------------------
    public function getListe() {
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
                    a.nom AS nom_amenageur
                FROM point_de_recharge p
                JOIN station s ON p.id_station_itinerance = s.id_station_itinerance
                JOIN commune c ON s.code_insee_commune = c.code_insee_commune
                JOIN departement d ON c.code_dep = d.code_dep
                JOIN acteur a ON s.id_acteur = a.id_acteur
                LIMIT 100";

        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // -------------------------------------------------------
    // 2.DETAIL : on récupère toutes les infos d'un point par son id
    //    Utilisé sur la page détail
    // -------------------------------------------------------
    public function getById($id) {
        $sql = "SELECT 
                    p.id,
                    p.puissance_nominale,
                    p.cable_t2_attache,
                    p.gratuit,
                    p.tarification,
                    p.consolidated_longitude,
                    p.consolidated_latitude,
                    p.condition_acces,
                    s.id_station_itinerance,
                    s.nom_station,
                    s.adresse_station,
                    s.date_mise_en_service,
                    s.nbre_pdc,
                    s.horaires,
                    s.nom_enseigne,
                    c.nom_commune,
                    d.nom_departement,
                    a.nom        AS nom_amenageur,
                    a.contact    AS contact_amenageur,
                    op.nom       AS nom_operateur
                FROM point_de_recharge p
                JOIN station s  ON p.id_station_itinerance = s.id_station_itinerance
                JOIN commune c  ON s.code_insee_commune    = c.code_insee_commune
                JOIN departement d ON c.code_dep           = d.code_dep
                JOIN acteur a   ON s.id_acteur             = a.id_acteur
                LEFT JOIN acteur op ON s.id_acteur_operateur = op.id_acteur
                WHERE p.id = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(array(':id' => $id));
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // -------------------------------------------------------
    // 3. CREER: insère un nv pt de recharge
    // Utilisé sur la page créer
    // -------------------------------------------------------
    public function create($data) {
        // on insère d'abord dans station
        $sqlStation = "INSERT INTO station 
                        (id_station_itinerance, nom_station, adresse_station, nbre_pdc, date_mise_en_service, code_insee_commune, id_acteur, horaires, nom_enseigne)
                       VALUES 
                        (:id_station_itinerance, :nom_station, :adresse_station, :nbre_pdc, :date_mise_en_service, :code_insee_commune, :id_acteur, :horaires, :nom_enseigne)";

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
        ));

        //puis ds point_de_recharge
        $sqlPoint = "INSERT INTO point_de_recharge 
                        (puissance_nominale, cable_t2_attache, gratuit, tarification, consolidated_longitude, consolidated_latitude, id_station_itinerance, condition_acces)
                     VALUES 
                        (:puissance_nominale, :cable_t2_attache, :gratuit, :tarification, :consolidated_longitude, :consolidated_latitude, :id_station_itinerance, :condition_acces)";

        $stmtPoint = $this->db->prepare($sqlPoint);
        $stmtPoint->execute(array(
            ':puissance_nominale'       => $data['puissance_nominale'],
            ':cable_t2_attache'         => $data['cable_t2_attache'],
            ':gratuit'                  => $data['gratuit'],
            ':tarification'             => $data['tarification'],
            ':consolidated_longitude'   => $data['consolidated_longitude'],
            ':consolidated_latitude'    => $data['consolidated_latitude'],
            ':id_station_itinerance'    => $data['id_station_itinerance'],
            ':condition_acces'          => $data['condition_acces'],
        ));

        return true;
    }

    // -------------------------------------------------------
    // 4. MODIFIER: met à jour un point existant par son id
    //Utilisé sur la page modifier
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
    // 5. RECUP OU CREE UN ACTEUR (aménageur)
    // si l'aménageur n'existe pas en base, on le crée
    // -------------------------------------------------------
    public function getOuCreerActeur($nom, $contact = '', $role = 'amenageur') {
        // on cherche si l'acteur existe déjà
        $stmt = $this->db->prepare("SELECT id_acteur FROM acteur WHERE nom = :nom LIMIT 1");
        $stmt->execute(array(':nom' => $nom));
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            return $row['id_acteur']; // on retourne son id
        }

        // sinon on le crée
        $stmt = $this->db->prepare("INSERT INTO acteur (nom, contact, role) VALUES (:nom, :contact, :role)");
        $stmt->execute(array(
            ':nom'     => $nom,
            ':contact' => $contact,
            ':role'    => $role,
        ));

        return $this->db->lastInsertId(); // on retourne le nouvel id
    }

    // -------------------------------------------------------
    // 6. RECUP OU CREE UNE COMMUNE
    //si la commune n'existe pas en base, on la crée
    // -------------------------------------------------------
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
    //7. RECUPERE OU CREE UNE ENSEIGNE
    //si l'enseigne n'existe pas en base, on la crée
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
}
?>