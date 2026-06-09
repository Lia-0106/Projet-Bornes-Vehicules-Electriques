# EliVolt : Application de gestion des bornes de recharge électrique

EliVolt est une application web de visualisation et de gestion des infrastructures de recharge pour les véhicules électriques en Bretagne. Elle permet d'afficher les points de recharge, d'obtenir des statistiques et de visualiser leur répartition sur une carte interactive.

Les données sont issues de l'open data national IRVE.

Le côté back office permet l'ajout, la modification et la suppression d'un point de recharge.

-----

## I - Auteurs et Date

```ZADOROZNYJ Lia / FEUARDENT Emma``` - ```Juin 2026```  
  
  
  
-----

## II - Technologies utilisés

### Front-end :
 - ```HTML, CSS, JavaScript```
 - ```Bootstrap 5.3```
 - ```Leaflet.js``` (carte interactive avec OpenStreetMap)
 - Font Awesome (pour les icones)

### Back-end : 
 - ```PHP 8.2```
 - PDO (interactions avec la base de données)
        
### Base de données :
 - ```MariaDB``` / ```MySQL```

### Serveur :
 - ```Apache 2.4```
 - ```VirtualHost```

-----

## III - Fonctionnalités principales
    
### Front-end :

**1. Accueil : présentation du site avec statistiques dynamiques**
 - Total des points de recharge
 - Nb de points par année
 - Nb de points par département
 - Nb de points par année et par département
 - Nb d'aménageurs
 - Types de prises
 - Nb de stations

**2. Recherche : formulaire avec filtres**
 - Par aménageur (limité à 20 items aléatoires)
 - Par type de prise
 - Par département
 - Affichage des résultats en tableau sans rechargement de page

**3. Carte interactive : visualisation des bornes sur OpenStreetMap (Leaflet) avec des filtres**
 - Par année
 - Par département
 - Popup  sur chaque marqueur avec la localité, la puissance et un lien vers le détail du point

**4. Détail d'un point : affichage complet des informations d'une installation**
 - Identification
 - Localisation
 - Caractéristiques
 - Exploitation

**5. Affichage responsive et ergonomique, adapté pour mobiles et tablettes**

### Back-end :
        
**1. Accueil : présentation du back-office avec un tableau des points de recharge**
 - Affichage de la liste des points
 - Lien vers la page détails du point
 - Lien vers la page de modification du point
 - Accès à la création d'un point

**2. Page de création d'un point**
 - Possibilité de créer un nouveau point de recharge à l'aide d'un formulaire

**3. Page de modification d'un point**
 - Possibilité de modifier un point existant à l'aide d'un formulaire  

-----

## IV - Fonctionnalités supplémentaires

- Système de connexion administrateur : accès au back protégé par un login et un mot de passe avec un système de hash

- Bouton "Administration" sur la page d'accueil du front : accès rapide au back depuis le site public

- Bouton "Aller au site" dans le menu du back, pour retourner rapidement sur le site principal

- Option de suppression d'un point dans le tableau de la liste des points

- Pagination :
 - Affichage d'un nombre de points défini dans le tableau du back (ex : afficher 50 points par page)
 - Possibilité de renseigner directement la page où l'on souhaite se rendre

- Système de recherche d'un point par son identifiant et de suppression du filtre actuel

- Graphiques de statistiques sur la page d'accueil

-----

## V - Structure des dossiers et fichiers du projet

```
projet-cir2-37/                                                                    
├── back/                               # Back-office (administration)             
│   ├── css/                                                                       
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
│   ├── html/                                                                      
│   │   ├── carte.html                  # Page carte interactive                   
│   │   ├── recherche.html              # Page recherche + résultats               
│   │   └── point-recharge.html         # Page détail d'un point (front)           
│   ├── js/                                                                        
│   │   ├── carte.js                    # Initialisation carte Leaflet + marqueurs 
│   │   ├── charts.js                   # Graphiques Chart.js                      
│   │   ├── recherche.js                # Chargement des filtres du formulaire     
│   │   ├── resultats.js                # Affichage du tableau de résultats        
│   │   └── stats.js                    # Chargement et affichage des statistiques 
│   └── index.html                      # Accueil front                            
├── ressources/                                                                    
│   ├── CSV/                                                                       
│   │   ├── communes-france-2024-limite.csv                                        
│   │   └── irve_init.csv                                                          
│   ├── img/                                                                       
│   ├── maquettes/                                                                 
│   ├── MCD-MPD/                                                                   
│   │   ├── actuels/                                                               
│   │   └── anciens/                                                               
│   ├── planning/                       # Planning et tableau de suivi du projet   
│   └── import_irve.py                  # Script d'import des données CSV          
├── sql/                                # Script de création des tables            
└── README.md                                                                      
```

-----

## VI - Installation 

### A) Prérequis
- ```Apache 2.4``` (déjà installé sur la VM)
- ```PHP 8.2``` (déjà installé sur la VM)
- ```MariaDB 10``` (déjà installé sur la VM)
- Python 3 avec le module ```mysql-connector-python```

### B) Etapes d'installation

#### 1. Configuration du VirtualHost

- Extraire l'archive fournie `projet-cir2-37`
- Placer le dossier dans `/var/www/html`
- Créer le fichier de configuration avec la commande : `sudo nano /etc/apache2/sites-available/projet-cir2-37.conf`
- Coller le contenu suivant :

```apache
<VirtualHost *:80>

    ServerName projet-cir2-37
    ServerAlias 10.10.51.37
    DocumentRoot /var/www/html/projet-cir2-37/front

    <Directory /var/www/html/projet-cir2-37/front>
        AllowOverride All
        Require all granted
        Options -Indexes
    </Directory>

    Alias /back /var/www/html/projet-cir2-37/back
    <Directory /var/www/html/projet-cir2-37/back>
        AllowOverride All
        Require all granted
        Options -Indexes
    </Directory>

    Alias /ressources /var/www/html/projet-cir2-37/ressources
    <Directory /var/www/html/projet-cir2-37/ressources>
        AllowOverride All
        Require all granted
        Options -Indexes
    </Directory>

</VirtualHost>
```

- Exécuter `sudo a2ensite projet-cir2-37`
- Exécuter `sudo systemctl reload apache2`
- Modifier le fichier hosts pour associer le nom de domaine à l'IP :
  - Linux/Mac : `sudo nano /etc/hosts` → ajouter `10.10.51.37 projet-cir2-37`
  - Windows : éditer `C:\Windows\System32\drivers\etc\hosts` → ajouter `10.10.51.37 projet-cir2-37`


#### 2. Créer la base de données et la remplir

- **Connexion BDD**  -> `mysql -u irveuser -p irvepwd`  
- **Créer la base et sélectionner** -> `CREATE DATABASE irve ;`puis `use irve ;`  
- **Créer les tables** -> coller le contenu de `sql-JMerise.sql` dans le terminal MySQL  
- **Importer les données** -> `python3 import_irve.py` (se placer dans le dossier courant, et les .csv fournis doivent être également dans le même dossier)  


### C) Accès au site

- Via VirtualHost  : http://projet-cir2-37/
                     http://projet-cir2-37/back/ (pour la back)
- Via adresse IP   : http://10.10.51.37/

-----

## VII - Identifiants administrateur

- LOGIN : ```cin2 ```  
- MDP : ```cin2mdp```
  
-----

## VIII - Notes

- Présence d'Alias dans la config du Vhost pour simplifier les liens les accès
  
- Lors de la création d'un pt avec une donnée existante liée à d'autres, le champ ne sera pas rempli (ex : on utilise un aménageur existant mais avec un nouveau contact ou numéro de SIREN -> ceux en base sont conservés, même si vides)