import csv
import mysql.connector
import re

# =====================================================================
# 1. CONNEXION À LA BASE DE DONNÉES
# =====================================================================
db = mysql.connector.connect(
    host="localhost",
    user="irveuser",
    password="irvepwd",  
    database="irve"
)
cursor = db.cursor()

BRETAGNE_DEPS = ['22', '29', '35', '56']

print("Connexion réussie ! Lancement de l'importation Bretagne...")

# =====================================================================
# FONCTIONS UTILITAIRES DE NETTOYAGE
# =====================================================================
def clean_bool(value):
    if not value or value.strip() == "":
        return None
    if value.lower().strip() == 'true' or value.strip() == '1':
        return 1
    return 0

def clean_date(value):
    if not value or value.strip() == "":
        return None
    return value.strip()

def format_phone(phone_str):
    if not phone_str or phone_str.strip() == "" or phone_str.lower().strip() == "null":
        return None
    digits = re.sub(r'\D', '', phone_str.strip())
    if digits.startswith('33') and len(digits) > 2:
        digits = '0' + digits[2:]
    if len(digits) == 10:
        return f"{digits[0:2]} {digits[2:4]} {digits[4:6]} {digits[6:8]} {digits[8:10]}"
    return phone_str.strip()

def clean_tarification(value):
    if not value or value.strip() == "" or value.strip().upper() == "NULL":
        return None
    cleaned = value.strip()
    cleaned = cleaned.replace('0.50?', '0.50€').replace('2.5 ', '2.50€ ')
    cleaned = cleaned.replace('?', ' ')
    return cleaned

# =====================================================================
# ÉTAPE 1 : IMPORTATION DES COMMUNES BRETONNES
# + construction du dictionnaire code_postal → code_insee (correctif)
# =====================================================================
print("Importation des départements et des communes...")

# Dictionnaire de secours : code_postal -> code_insee
# (si plusieurs communes ont le même CP, on garde la première trouvée)
cp_vers_insee = {}

with open('communes-france-2024-limite.csv', mode='r', encoding='utf-8') as f:
    reader = csv.DictReader(f, delimiter=';')
    
    headers = reader.fieldnames
    cp_col = None
    for h in headers:
        if 'postal' in h.lower() or 'cp' in h.lower() or 'code_post' in h.lower():
            cp_col = h
            print(f"-> Colonne code postal détectée : '{cp_col}'")
            break
            
    for row in reader:
        dep_code = row['dep_code'].strip()
        if dep_code in BRETAGNE_DEPS:
            # Insertion département
            sql_dep = "INSERT IGNORE INTO departement (code_dep, nom_departement) VALUES (%s, %s)"
            cursor.execute(sql_dep, (dep_code, row['dep_nom'].strip()))
            
            code_postal = row[cp_col].strip() if cp_col and row[cp_col] else '00000'
            code_insee  = row['code_insee'].strip()

            # Mémoriser l'association CP → INSEE pour le correctif stations
            if code_postal and code_insee and code_postal not in cp_vers_insee:
                cp_vers_insee[code_postal] = code_insee

            # Insertion commune
            sql_com = "INSERT IGNORE INTO commune (code_insee_commune, nom_commune, code_postal, code_dep) VALUES (%s, %s, %s, %s)"
            cursor.execute(sql_com, (code_insee, row['nom_standard'].strip(), code_postal, dep_code))

# Ensemble des codes INSEE valides (pour contrôle rapide)
communes_valides = set(cp_vers_insee.values())
# Ajouter aussi directement les codes INSEE
with open('communes-france-2024-limite.csv', mode='r', encoding='utf-8') as f:
    reader = csv.DictReader(f, delimiter=';')
    for row in reader:
        if row['dep_code'].strip() in BRETAGNE_DEPS:
            communes_valides.add(row['code_insee'].strip())

db.commit()

# =====================================================================
# FONCTION : résoudre le code commune (INSEE ou CP → INSEE)
# =====================================================================
def resoudre_code_insee(code_brut):
    """
    Retourne un code INSEE valide ou None.
    - Si le code est déjà un code INSEE connu : on le retourne tel quel.
    - Sinon, on cherche dans la table de corrections manuelles (anciens codes INSEE,
      codes postaux ambigus non présents dans le fichier communes).
    - Sinon, on cherche s'il correspond à un code postal connu → on retourne l'INSEE associé.
    - Sinon : None (station ignorée, on logge un avertissement).
    """
    # Corrections manuelles :
    # - anciens codes INSEE remplacés après fusion de communes
    # - codes postaux absents du fichier communes (ex: 35000 = Rennes, CP réel dans le fichier = 35700)
    CORRECTIONS_MANUELLES = {
        '35000': '35238',  # Rennes (CP courant absent du fichier communes, qui contient 35700)
        '22680': '22213',  # Plouër-sur-Rance (ancien code INSEE avant fusion)
        '35267': '35257',  # Maen Roch (ancien code INSEE avant fusion)
        '35508': '35080',  # Cintré (ancien code INSEE avant fusion)
    }

    code = code_brut.strip()
    if code in communes_valides:
        return code
    if code in CORRECTIONS_MANUELLES:
        return CORRECTIONS_MANUELLES[code]
    if code in cp_vers_insee:
        return cp_vers_insee[code]
    return None

# =====================================================================
# ÉTAPE 2 : ACTEURS ET TABLES DE RÉFÉRENCE
# =====================================================================
print("Extraction et nettoyage des acteurs...")

amenageurs_ids = {}  # nom -> id_acteur
operateurs_ids = {}  # nom -> id_acteur

with open('irve_init.csv', mode='r', encoding='utf-8') as f:
    reader = csv.DictReader(f, delimiter=',')
    
    for row in reader:
        if row['code_dep'].strip() in BRETAGNE_DEPS:
            
            impl_station = row['implantation_station'].strip() if row['implantation_station'] else None
            cond_acces   = row['condition_acces'].strip()      if row['condition_acces']      else None
            horaires_clean = row['horaires'].strip()           if row['horaires']             else None
            enseigne_clean = row['nom_enseigne'].strip()       if row['nom_enseigne']         else None

            if impl_station:
                cursor.execute("INSERT IGNORE INTO implantation (implantation_station) VALUES (%s)", (impl_station,))
            if cond_acces:
                cursor.execute("INSERT IGNORE INTO condition_acces (condition_acces) VALUES (%s)", (cond_acces,))
            if horaires_clean:
                cursor.execute("INSERT IGNORE INTO horaires (horaires) VALUES (%s)", (horaires_clean,))
            if enseigne_clean:
                cursor.execute("INSERT IGNORE INTO enseigne (nom_enseigne) VALUES (%s)", (enseigne_clean,))

            # Aménageur — clé de dédup : nom uniquement (une entité = un nom)
            nom_am     = row['nom_amenageur'].strip().title()     if row['nom_amenageur']     else ""
            contact_am = row['contact_amenageur'].strip().lower() if row['contact_amenageur'] else ""
            siren_am   = row['siren_amenageur'].strip()            if row['siren_amenageur']   else None
            
            if nom_am and nom_am not in amenageurs_ids:
                sql_act = """
                    INSERT IGNORE INTO acteur (nom, siren, contact, role)
                    VALUES (%s, %s, %s, 'Amenageur')
                """
                cursor.execute(sql_act, (nom_am, siren_am, contact_am))
                db.commit()
                cursor.execute(
                    "SELECT id_acteur FROM acteur WHERE nom=%s AND role='Amenageur'",
                    (nom_am,)
                )
                amenageurs_ids[nom_am] = cursor.fetchone()[0]

            # Opérateur — clé de dédup : nom uniquement
            nom_op     = row['nom_operateur'].strip().title()     if row['nom_operateur']     else ""
            contact_op = row['contact_operateur'].strip().lower() if row['contact_operateur'] else ""
            tel_op     = format_phone(row['telephone_operateur'])
            
            if nom_op and nom_op not in operateurs_ids:
                sql_act = """
                    INSERT IGNORE INTO acteur (nom, contact, telephone, role)
                    VALUES (%s, %s, %s, 'Operateur')
                """
                cursor.execute(sql_act, (nom_op, contact_op, tel_op))
                db.commit()
                cursor.execute(
                    "SELECT id_acteur FROM acteur WHERE nom=%s AND role='Operateur'",
                    (nom_op,)
                )
                operateurs_ids[nom_op] = cursor.fetchone()[0]

db.commit()

# =====================================================================
# ÉTAPE 3 : STATIONS, BORNES ET TABLES PIVOTS
# =====================================================================
print("Importation finale des stations et points de recharge...")

for t in ['EF', 'T2', 'Combo CCS', 'CHAdeMO', 'Autre']:
    cursor.execute("INSERT IGNORE INTO type_prise (type_prise) VALUES (%s)", (t,))
for p in ['Acte', 'CB', 'Autre']:
    cursor.execute("INSERT IGNORE INTO type_paiement (type_paiement) VALUES (%s)", (p,))

stations_ignorees = []

with open('irve_init.csv', mode='r', encoding='utf-8') as f:
    reader = csv.DictReader(f, delimiter=',')
    
    for row in reader:
        if row['code_dep'].strip() not in BRETAGNE_DEPS:
            continue

        nom_am = row['nom_amenageur'].strip().title() if row['nom_amenageur'] else ""
        nom_op = row['nom_operateur'].strip().title() if row['nom_operateur'] else ""

        id_am = amenageurs_ids.get(nom_am)
        id_op = operateurs_ids.get(nom_op)
        
        impl_station   = row['implantation_station'].strip() if row['implantation_station'] else None
        horaires_clean = row['horaires'].strip()             if row['horaires']             else None
        enseigne_clean = row['nom_enseigne'].strip()         if row['nom_enseigne']         else None

        # CORRECTIF 2 : résolution code INSEE / code postal
        code_insee_final = resoudre_code_insee(row['code_insee_commune'])
        if code_insee_final is None:
            stations_ignorees.append({
                'id_station': row['id_station_itinerance'].strip(),
                'code_brut':  row['code_insee_commune'].strip(),
                'adresse':    row['a0dresse_station'].strip()
            })
            continue  # on passe cette station (et ses PDC), code inconnu

        # Station
        sql_station = """
            INSERT IGNORE INTO station (
                id_station_itinerance, id_station_local, nom_station, adresse_station, 
                nbre_pdc, date_mise_en_service, implantation_station, code_insee_commune, 
                id_acteur, id_acteur_operateur, horaires, nom_enseigne
            ) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
        """
        cursor.execute(sql_station, (
            row['id_station_itinerance'].strip(),
            row['id_station_local'].strip() if row['id_station_local'] else None,
            row['nom_station'].strip(),
            row['a0dresse_station'].strip(),
            int(row['nbre_pdc']),
            clean_date(row['date_mise_en_service']),
            impl_station,
            code_insee_final,   # ← code résolu
            id_am,
            id_op,
            horaires_clean,
            enseigne_clean
        ))

        # Point de recharge
        sql_pdc = """
            INSERT IGNORE INTO point_de_recharge (
                id, puissance_nominale, cable_t2_attache, gratuit, tarification, 
                consolidated_longitude, consolidated_latitude, id_station_itinerance, condition_acces
            ) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s)
        """
        pdc_id        = int(row['id'])
        cond_acces    = row['condition_acces'].strip() if row['condition_acces'] else None
        tarif_clean   = clean_tarification(row['tarification'])
        
        cursor.execute(sql_pdc, (
            pdc_id,
            float(row['puissance_nominale']),
            clean_bool(row['cable_t2_attache']),
            clean_bool(row['gratuit']),
            tarif_clean,
            float(row['consolidated_longitude']),
            float(row['consolidated_latitude']),
            row['id_station_itinerance'].strip(),
            cond_acces
        ))

        # Pivots Prises
        if clean_bool(row['prise_type_ef']):
            cursor.execute("INSERT IGNORE INTO point_recharge_prise (id, type_prise) VALUES (%s, 'EF')", (pdc_id,))
        if clean_bool(row['prise_type_2']):
            cursor.execute("INSERT IGNORE INTO point_recharge_prise (id, type_prise) VALUES (%s, 'T2')", (pdc_id,))
        if clean_bool(row['prise_type_combo_ccs']):
            cursor.execute("INSERT IGNORE INTO point_recharge_prise (id, type_prise) VALUES (%s, 'Combo CCS')", (pdc_id,))
        if clean_bool(row['prise_type_chademo']):
            cursor.execute("INSERT IGNORE INTO point_recharge_prise (id, type_prise) VALUES (%s, 'CHAdeMO')", (pdc_id,))
        if clean_bool(row['prise_type_autre']):
            cursor.execute("INSERT IGNORE INTO point_recharge_prise (id, type_prise) VALUES (%s, 'Autre')", (pdc_id,))

        # Pivots Paiements
        if clean_bool(row['paiement_acte']):
            cursor.execute("INSERT IGNORE INTO point_recharge_paiement (type_paiement, id) VALUES ('Acte', %s)", (pdc_id,))
        if clean_bool(row['paiement_cb']):
            cursor.execute("INSERT IGNORE INTO point_recharge_paiement (type_paiement, id) VALUES ('CB', %s)", (pdc_id,))
        if clean_bool(row['paiement_autre']):
            cursor.execute("INSERT IGNORE INTO point_recharge_paiement (type_paiement, id) VALUES ('Autre', %s)", (pdc_id,))

db.commit()
cursor.close()
db.close()

# =====================================================================
# RAPPORT FINAL
# =====================================================================
print("\n TOUT EST PARFAIT ! Base de données recréée et nettoyée avec succès !")

if stations_ignorees:
    print(f"\n  {len(stations_ignorees)} station(s) ignorées (code commune introuvable) :")
    for s in stations_ignorees:
        print(f"   - {s['id_station']} | code brut: {s['code_brut']} | {s['adresse']}")
else:
    print("  Aucune station ignorée pour cause de code commune inconnu.")