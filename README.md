# Elivolt : Application de gestion des bornes de recharge électrique

## I_ Description 
    Elivolt est une application web de visualistaion et de gestion des infrastructures de recharge pour les véhicules électriques, en Bretagne. Elle permet d'afficher les points de recharge, d'obtenir des statistiques et de visualiser leur répartition sur une carte interactive. 

## II_ Technologies utilisés
    Front-end : 
        - HTML, CSS, JavaScript
        - Bootstrap 5.3
        - Leaflet.js (carte interactive avce OpenStreetMap)
        - Font Awesome (pour les icones)

    Back-end : 
        - PHP 8.2
        - PDO (interactions avec la base de données)
        
    Base de données : 
        - MairaDB 10 / MySQL

    Serveur : 
        - Apache 2.4
        - VirtualHost

## III_ Structure des dossiers 

```
projet-cir2-37/
├── back/                              # Back-office (administration)
│   ├── css/
│   │   └── style-back.css
│   ├── php/
│   │   ├── API/
│   │   │   ├── Carte.php               # Classe requêtes SQL carte
│   │   │   ├── constantes.php          # Identifiants base de données
│   │   │   ├── Database.php            # Connexion PDO
│   │   │   ├── PointRecharge.php       # Classe requêtes SQL points de recharge
│   │   │   ├── Recherche.php           # Classe requêtes SQL recherche
│   │   │   ├── request.php             # Point d'entrée API REST (JSON)
│   │   │   ├── Resultats.php           # Classe requêtes SQL résultats
│   │   │   └── Stats.php               # Classe requêtes SQL statistiques
│   │   ├── creer-point-recharge.php    # Page création d'un point
│   │   ├── details-point-recharge.php  # Page détail d'un point (back)
│   │   ├── fonctions.php               # Fonctions utilitaires partagées 
│   │   ├── login.php                   # Page de connexion administrateur
│   │   └── modifier-point-recharge.php # Page modification d'un point
│   └── index.php                       # Accueil back-office
├── front/                              # Front-end (site public)
│   ├── css/
│   │   └── style-front.css
│   ├── html/
│   │   ├── carte.html                  # Page carte interactive
│   │   ├── charts.js                   # Graphiques Chart.js (pie départements, doughnut prises, bar année, etc...)
│   │   ├── recherche.html              # Page recherche + résultats
│   │   └── point-recharge.html         # Page détail d'un point (front)
│   ├── js/
│   │   ├── carte.js                    # Initialisation carte Leaflet + marqueurs
│   │   ├── recherche.js                # Chargement des filtres du formulaire
│   │   ├── resultats.js                # Affichage du tableau de résultats
│   │   └── stats.js                    # Chargement et affichage des statistiques
│   └── index.html                      # Accueil front
├── ressources/
│   ├── CSV/
│   │   ├── communes-france-2024-limite.csv
│   │   └── irve_init.csv
│   ├── CSV/
│   │   └── logo.jpeg
│   ├── maquettes/
│   │   ├── back-1.png
│   │   ├── back-2.png
│   │   ├── back-3.png
│   │   ├── back-4.png
│   │   ├── Croquis1.jpeg
│   │   ├── Croquis2.jpeg
│   │   ├── front-1.png
│   │   ├── front-2.png
│   │   ├── front-3.png
│   │   └── front-4.png
│   ├── MCD-MPD/
│   │   ├── actuels/
│   │   │   ├── MCD-actuel.png
│   │   │   └── MPD-actuel.png
│   │   └── anciens/
│   │       ├── MDC.png
│   │       └── MPD.png
│   ├── planning/
│   │   ├── planning-base.png
│   │   └── planning-V2.pdf
│   └── import_irve.py                  # Script d'import des données CSV
├── sql/
│   └── sql-JMerise.sql                 # Script de création des tables
├── index.html
└── README.md
```

## IV_ Fonctionnalités principales
    Front-end :
        1. Accueil : présentation du site avec statistiques dynamiques (total des points, points par année, points par département, points par année et département, nombre d'aménageurs, types de prises).
        2. Recherche : formulaire avec filtres (aménageur limité à 20 items aléatoires, type de prise, département) et affichage des résultats en tableau sans rechargement de page.
        3. Carte intéractive : visualisation des bornes sur OpenStreetMap (Leaflet) avecdes filtres par année et département. Chaque marqueur affiche un popup avec la localité, la puissance et un lien vers le détail du point. 
        4. Détail d'un point : affichage complet des informations d'une installation (identification, localisation, caractéristiques, exploitation). 

    Back-end :
        1. Accueil : tableau des points de recharge avec un lien vers les détails du point, ainsi qu'un lien vers la page de modification du point et un accés à la création de point. 

## V_ Fonctionnalités supplémentaires
    - système de connexion administrateur : accés au back protégé par un login et un mot de passe. 
    - bouton administration sur le front : accés rapide au back depuis le site public.
    - pagination : navigation par pages du nombres d'éléments souhaités dans le tableau du back + possibilité de renseigner la page que l'on souhaite directement.

## VI_ Installation 
    A_ Prérequis 
        - Apache 2.4 (déjà installé sur la VM)
        - PHP 8.2 (déjà installé sur la VM)
        - MariaDB 10 (déjà installé sur la VM)
        - Python 3 avec le module mysql-connector-python

    B_ Etapes d'installation

        1. Extraire l'archive
            Déposer projetweb_groupe7.zip sur la VM et extraire :
            
            unzip projetweb_groupe7.zip -d /var/www/html/
            cd /var/www/html/

        2. Installer le connecteur Python
            
            pip install mysql-connector-python

        3. Créer la base de données
            Les identifiants sont déjà configurés dans back/php/API/constantes.php et ressources/import_irve.py.
            Il suffit juste de créer la base avec les mêmes identifiants :

            CREATE DATABASE irve CHARACTER SET utf8 COLLATE utf8_general_ci;
            CREATE USER 'irveuser'@'localhost' IDENTIFIED BY 'irvepwd';
            GRANT ALL PRIVILEGES ON irve.* TO 'irveuser'@'localhost';
            FLUSH PRIVILEGES;

        4. Importer le schéma SQL
            
            mysql -u irveuser -p irve < sql/sql-JMerise.sql

        5. Importer les données CSV
            Les fichiers CSV sont déjà inclus dans ressources/CSV/.
            
            python3 ressources/import_irve.py

        6. Configurer le VirtualHost Apache
            Créer le fichier /etc/apache2/sites-available/projet-cir2-37.conf :

            <VirtualHost *:80>
                ServerName projet-cir2-XX
                DocumentRoot /var/www/html
                <Directory /var/www/html>
                    AllowOverride All
                    Require all granted
                </Directory>
            </VirtualHost>

            Activer le site :
            sudo a2ensite projet-cir2-XX.conf
            sudo systemctl reload apache2

    C_ Accès au site
        - Via VirtualHost  : http://projet-cir2-37/
        - Via adresse IP   : http://10.10.51.37/


## VII_ Accès 
```
    |        Page         |                         URL                      |
    |---------------------|--------------------------------------------------|
    | Front-end           | http://projet-cir2-XX/front/                     |
    | Recherche           | http://projet-cir2-XX/front/html/recherche.html  |
    | Carte               | http://projet-cir2-XX/front/html/carte.html      |
    | Back-office         | http://projet-cir2-XX/back/                      |
    | Connexion admin     | http://projet-cir2-XX/back/php/login.php         |
    | API                 | http://projet-cir2-XX/back/php/API/request.php/  |
```

## VIII_ Identifiants administrateur
    |     Champ    |  Valeur |
    |--------------|---------|
    | Login        | cin2    |
    | Mot de passe | cin2mdp |

## IX_ Pour information
    - Lorsque l'on créait un point de charge avec une donnée déjà existante, alors le champs ne se remplira pas. Ex : si l'on renseigne un aménageur existant, et que 'lon met un numéro de SIREN différent de celui de l'aménageur déjà enregistré, alors le numéro de SIREN sera renseigné comme un champ vide.

