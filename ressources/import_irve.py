import csv
import mysql.connector

# =====================================================================
# CONNEXION À LA BASE DE DONNÉES
# =====================================================================
db = mysql.connector.connect(
    host="localhost",
    user="irveuser",
    password="irvepwd",
    database="irve"
)

# Le curseur est l'objet qu'on utilise pour envoyer des requêtes SQL à la base
cursor = db.cursor()

BRETAGNE_DEPS = ['22', '29', '35', '56']


# =====================================================================
# FONCTIONS DE NETTOYAGE
# Préparent les valeurs du CSV avant insertion en base
# =====================================================================

# Corrige les booléens dans le CSV
def clean_bool(value) :
    if not value or value.strip() == "" :
        return None
    if value.lower().strip() == 'true' or value.strip() == '1' :
        return 1
    return 0

def clean_date(value) :
    if not value or value.strip() == "" :
        return None
    return value.strip()

# Reformate certaines des données de tarification
def clean_tarification(value):
    if not value or value.strip() == "" or value.strip().upper() == "NULL" :
        return None
    cleaned = value.strip()
    cleaned = cleaned.replace('0.50?', '0.50€').replace('2.5 ', '2.50€ /')
    cleaned = cleaned.replace('?', ' ')
    return cleaned


# =====================================================================
# ÉTAPE 1 : COMMUNES ET DÉPARTEMENTS BRETONS
# Lit communes-france-2024-limite.csv et remplit les tables
# "departement" et "commune" pour les 4 départements bretons
# =====================================================================

def importer_communes() :
    global cp_vers_insee

    with open('communes-france-2024-limite.csv', mode='r', encoding='utf-8') as file :
        reader = csv.DictReader(file, delimiter=';')

        for row in reader :
            if row['dep_code'].strip() not in BRETAGNE_DEPS :
                continue

            # Insertion des départements
            cursor.execute("INSERT IGNORE INTO departement (code_dep, nom_departement) VALUES (%s, %s)",
                (row['dep_code'].strip(), row['dep_nom'].strip())
            )

            code_postal = row['code_postal'].strip()
            code_insee  = row['code_insee'].strip()

            # Remplissage du dictionnaire de secours pour correction des codes INSEE
            if code_postal and code_insee and code_postal not in cp_vers_insee:
                cp_vers_insee[code_postal] = code_insee

            # Insertion des communes
            cursor.execute("INSERT IGNORE INTO commune (code_insee_commune, nom_commune, code_postal, code_dep) VALUES (%s, %s, %s, %s)",
                (code_insee, row['nom_standard'].strip(), code_postal, row['dep_code'].strip())
            )

    db.commit()


# =============================================================================================
# FONCTION : RÉSOLUTION DU CODE INSEE
# 3 cas : code valide,
#         code avec une correction manuelle,
#         code postal à la place du code INSEE
# =============================================================================================
def resoudre_code_insee(code_brut) :
    global communes_valides

    CORRECTIONS_MANUELLES = {
        '35000': '35238',
        '22680': '22213',
        '35267': '35257',
        '35508': '35080',
    }

    code = code_brut.strip()

    if code in communes_valides :
        return code
    if code in CORRECTIONS_MANUELLES :
        return CORRECTIONS_MANUELLES[code]
    if code in cp_vers_insee :
        return cp_vers_insee[code]
    return None


# ==========================================================================
# ÉTAPE 2 : ACTEURS ET TABLES DE RÉFÉRENCE
# Lit irve_init.csv et remplit :
# - Tables de référence (implantation, condition_acces, horaires, enseigne)
# - Table acteur (contient à la fois les aménageurs et les opérateurs)
# ==========================================================================
def importer_acteurs_et_references() :
    global amenageurs_id, operateurs_id

    with open('irve_init.csv', mode='r', encoding='utf-8') as file :
        reader = csv.DictReader(file, delimiter=',')

        for row in reader :

            # Nettoyage des valeurs et insertion des tables de référence
            cursor.execute("INSERT IGNORE INTO implantation (implantation_station) VALUES (%s)", (row['implantation_station'].strip(),))
            cursor.execute("INSERT IGNORE INTO condition_acces (condition_acces) VALUES (%s)", (row['condition_acces'].strip(),))
            cursor.execute("INSERT IGNORE INTO horaires (horaires) VALUES (%s)", (row['horaires'].strip(),))
            cursor.execute("INSERT IGNORE INTO enseigne (nom_enseigne) VALUES (%s)", (row['nom_enseigne'].strip(),))

            # Nettoyage et insertion des aménageurs
            nom_amenageur = row['nom_amenageur'].strip().title()
            if nom_amenageur and nom_amenageur not in amenageurs_id :
                cursor.execute("INSERT IGNORE INTO acteur (nom, siren, contact, role) VALUES (%s, %s, %s, 'Amenageur')",
                    (nom_amenageur,
                     row['siren_amenageur'].strip(),
                     row['contact_amenageur'].strip().lower() if row['contact_amenageur'] else None)
                )
                db.commit()
                amenageurs_id[nom_amenageur] = cursor.lastrowid

            # Nettoyage et insertion des opérateurs
            nom_op = row['nom_operateur'].strip().title()
            if nom_op and nom_op not in operateurs_id :
                cursor.execute("INSERT IGNORE INTO acteur (nom, contact, telephone, role) VALUES (%s, %s, %s, 'Operateur')",
                    (nom_op,
                     row['contact_operateur'].strip().lower(),
                     row['telephone_operateur'].strip() if row['telephone_operateur'] else None)
                )
                db.commit()
                operateurs_id[nom_op] = cursor.lastrowid

    db.commit()


# =====================================================================
# ÉTAPE 3 : STATIONS, POINTS DE RECHARGE ET TABLES PIVOT
# Lit irve_init.csv une deuxième fois et remplit les tables :
# - station
# - point_de_recharge
# - point_recharge_prise
# - point_recharge_paiement
# =====================================================================

# Insertion des prises
def inserer_prises(pdc_id, row) :
    if clean_bool(row['prise_type_ef']) : cursor.execute("INSERT IGNORE INTO point_recharge_prise VALUES (%s, 'EF')", (pdc_id,))
    if clean_bool(row['prise_type_2']) : cursor.execute("INSERT IGNORE INTO point_recharge_prise VALUES (%s, 'T2')", (pdc_id,))
    if clean_bool(row['prise_type_combo_ccs']) : cursor.execute("INSERT IGNORE INTO point_recharge_prise VALUES (%s, 'Combo CCS')", (pdc_id,))
    if clean_bool(row['prise_type_chademo']) : cursor.execute("INSERT IGNORE INTO point_recharge_prise VALUES (%s, 'CHAdeMO')", (pdc_id,))
    if clean_bool(row['prise_type_autre']) : cursor.execute("INSERT IGNORE INTO point_recharge_prise VALUES (%s, 'Autre')", (pdc_id,))

# Insertion des moyens de paiement
def inserer_paiements(pdc_id, row) :
    if clean_bool(row['paiement_acte']) : cursor.execute("INSERT IGNORE INTO point_recharge_paiement VALUES ('Acte', %s)", (pdc_id,))
    if clean_bool(row['paiement_cb']) : cursor.execute("INSERT IGNORE INTO point_recharge_paiement VALUES ('CB', %s)", (pdc_id,))
    if clean_bool(row['paiement_autre']) : cursor.execute("INSERT IGNORE INTO point_recharge_paiement VALUES ('Autre', %s)", (pdc_id,))

# Insertion des stations
def importer_stations() :
    global amenageurs_id, operateurs_id

    # Initialisation des types de prises et de paiements
    for t in ['EF', 'T2', 'Combo CCS', 'CHAdeMO', 'Autre'] :
        cursor.execute("INSERT IGNORE INTO type_prise (type_prise) VALUES (%s)", (t,))
    for p in ['Acte', 'CB', 'Autre'] :
        cursor.execute("INSERT IGNORE INTO type_paiement (type_paiement) VALUES (%s)", (p,))

    with open('irve_init.csv', mode='r', encoding='utf-8') as file:
        reader = csv.DictReader(file, delimiter=',')

        for row in reader :

            # Résolution du code INSEE
            code_insee_final = resoudre_code_insee(row['code_insee_commune'])
            if code_insee_final is None :
                code_insee_final = row['code_insee_commune'].strip()

            # Récupération des id_acteur depuis les dictionnaires de l'étape 2
            id_amenageur = amenageurs_id.get(row['nom_amenageur'].strip().title())
            id_operateur = operateurs_id.get(row['nom_operateur'].strip().title())

            # Insertion de la station
            cursor.execute("""INSERT IGNORE INTO station (id_station_itinerance, id_station_local, nom_station, adresse_station,
                              nbre_pdc, date_mise_en_service, implantation_station, code_insee_commune, id_acteur, id_acteur_operateur,
                              horaires, nom_enseigne) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)""",
                (row['id_station_itinerance'].strip(),
                 row['id_station_local'].strip() if row['id_station_local'] else None,
                 row['nom_station'].strip(),
                 row['a0dresse_station'].strip(),
                 int(row['nbre_pdc']),
                 clean_date(row['date_mise_en_service']),
                 row['implantation_station'].strip(),
                 code_insee_final,
                 id_amenageur,
                 id_operateur,
                 row['horaires'].strip(),
                 row['nom_enseigne'].strip()))

            # Insertion du point de recharge lié à cette station
            pdc_id = int(row['id'])
            cursor.execute("""INSERT IGNORE INTO point_de_recharge (id, puissance_nominale, cable_t2_attache, gratuit, tarification,
                              consolidated_longitude, consolidated_latitude, id_station_itinerance, condition_acces)
                              VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s)""",
                (pdc_id,
                 float(row['puissance_nominale']),
                 clean_bool(row['cable_t2_attache']),
                 clean_bool(row['gratuit']),
                 clean_tarification(row['tarification']),
                 float(row['consolidated_longitude']),
                 float(row['consolidated_latitude']),
                 row['id_station_itinerance'].strip(),
                 row['condition_acces'].strip()))

            inserer_prises(pdc_id, row)
            inserer_paiements(pdc_id, row)

    db.commit()


# =====================================================================
# INITIALISATION DES VARIABLES GLOBALES
# =====================================================================

cp_vers_insee  = {}
amenageurs_id  = {}
operateurs_id  = {}


# =====================================================================
# EXÉCUTION DES ÉTAPES DANS L'ORDRE
# Chaque étape dépend de la précédente
# =====================================================================

print("Connexion réussie ! Lancement de l'importation Bretagne...")

importer_communes()
communes_valides = set(cp_vers_insee.values())

importer_acteurs_et_references()
importer_stations()

cursor.close()
db.close()

print("\nBase de données importée avec succès !")