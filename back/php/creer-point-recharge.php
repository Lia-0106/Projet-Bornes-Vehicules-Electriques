<?php

// -------------------------------------------------------
// VÉRIFICATION DE SESSION
// Redirige vers login.php si l'admin n'est pas connecté
// -------------------------------------------------------
session_start() ;
if (!isset($_SESSION['admin'])) {
    header('Location: ../php/login.php') ;
    exit ;
}

require_once ('API/Database.php') ;
require_once ('API/constantes.php') ;
require_once ('API/PointRecharge.php') ;


// -------------------------------------------------------
// TRAITEMENT DU FORMULAIRE
// Récupère les données POST, crée les acteurs et l'enseigne si nécessaire
// Insère le point en base
// Redirige vers l'accueil après création
// -------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $database = new Database() ;
    $db = $database->getConnexion() ;
    $pointRecharge = new PointRecharge($db) ;

    // Récupération ou création de l'aménageur et de l'opérateur
    $idActeur = $pointRecharge->getOuCreerActeur(
        isset($_POST['amenageur']) ? $_POST['amenageur'] : '',
        isset($_POST['contact_amenageur']) ? $_POST['contact_amenageur'] : '',
        '',
        isset($_POST['siren_amenageur']) ? $_POST['siren_amenageur'] : '',
        'amenageur'
    ) ;
    $idOperateur = $pointRecharge->getOuCreerActeur(
        isset($_POST['operateur']) ? $_POST['operateur'] : '',
        isset($_POST['contact_operateur']) ? $_POST['contact_operateur'] : '',
        isset($_POST['telephone_operateur']) ? $_POST['telephone_operateur'] : '',
        '',
        'operateur'
    ) ;

    // Récupération ou création de l'enseigne
    $pointRecharge->getOuCreerEnseigne(
        isset($_POST['nom_enseigne']) ? $_POST['nom_enseigne'] : ''
    ) ;

    // Construction du tableau de données à insérer
    $data = [ 'id_station_itinerance'  => isset($_POST['id_station_itinerance']) ? $_POST['id_station_itinerance'] : '',
              'nom_station'            => isset($_POST['nom_station']) ? $_POST['nom_station'] : '',
              'adresse_station'        => isset($_POST['adresse_station']) ? $_POST['adresse_station'] : '',
              'nbre_pdc'               => isset($_POST['nbre_pdc']) ? $_POST['nbre_pdc'] : 1,
              'date_mise_en_service'   => isset($_POST['date_mise_en_service']) ? $_POST['date_mise_en_service'] : '',
              'code_insee_commune'     => isset($_POST['code_insee_commune']) ? $_POST['code_insee_commune'] : '',
              'id_acteur'              => $idActeur,
              'id_operateur'           => $idOperateur,
              'horaires'               => isset($_POST['horaires']) ? $_POST['horaires'] : '',
              'nom_enseigne'           => isset($_POST['nom_enseigne']) ? $_POST['nom_enseigne'] : '',
              'implantation_station'   => isset($_POST['implantation_station']) ? $_POST['implantation_station'] : '',
              'puissance_nominale'     => isset($_POST['puissance_nominale']) ? $_POST['puissance_nominale'] : 0,
              'cable_t2_attache'       => isset($_POST['cable_t2_attache']) ? 1 : 0,
              'gratuit'                => isset($_POST['gratuit']) ? 1 : 0,
              'tarification'           => isset($_POST['tarification']) ? $_POST['tarification'] : '',
              'consolidated_longitude' => isset($_POST['consolidated_longitude']) ? $_POST['consolidated_longitude'] : 0,
              'consolidated_latitude'  => isset($_POST['consolidated_latitude']) ? $_POST['consolidated_latitude'] : 0,
              'condition_acces'        => isset($_POST['condition_acces']) ? $_POST['condition_acces'] : '',
              'types_prises'           => isset($_POST['types_prises']) ? $_POST['types_prises'] : [] ,
              'types_paiement'         => isset($_POST['types_paiement']) ? $_POST['types_paiement'] : [] ] ;

    $pointRecharge->create($data) ;
    header('Location: ../index.php?succes=creation') ;
    exit ;
}

?>


<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>EliVolt</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="../css/style-back.css" />
</head>
<body>

<!-- NAVIGATION -->
<nav class="ev-nav">
  <a href="/back/index.php" class="brand">
    <img src="../../ressources/img/logo.jpeg" alt="Logo Elivolt" class="brand-logo"/>
    <span class="brand-name">EliVolt <span class="text-muted fw-normal">Admin</span></span>
  </a>
<div class="nav-links">
  <a href="/back/index.php">Accueil</a>
  <a href="/front/html/recherche.html">Recherche</a>
  <a href="/front/html/carte.html">Carte</a>
  <a href="/front/index.html" class="site">Aller au site</a>
</div>
  <button class="nav-toggle" id="navToggle" aria-label="Menu">
    <i class="fa fa-bars"></i>
  </button>
</nav>

<!-- NAVIGATION MOBILE -->
<div class="nav-mobile" id="navMobile">
  <a href="/back/index.php">Accueil</a>
  <a href="/front/html/recherche.html">Recherche</a>
  <a href="/front/html/carte.html">Carte</a>
  <a href="/front/index.html" class="site">Aller au site</a>
</div>

<main class="container-xl px-4 pt-4 pb-5 flex-grow-1">

  <a href="../index.php" class="back-link mb-4 d-inline-flex">
    <i class="fa fa-arrow-left"></i> Retour à l'accueil
  </a>

  <!-- EN-TÊTE DE PAGE -->
  <div class="bc-card p-4 mb-4">
    <div class="d-flex align-items-center gap-3">
      <div class="bc-logo-box-lg">
        <i class="fa fa-plus text-white fs-5"></i>
      </div>
      <div>
        <div class="details-subtitle">Nouveau point de recharge</div>
        <div class="details-title">Ajouter à la base de données</div>
        <div class="details">Les champs obligatoires sont identifiés par <span>*</span></div>
      </div>
    </div>
  </div>

  <form method="POST" action="" class="form-grid">

    <!-- IDENTIFICATION -->
    <fieldset>
      <legend>Identification</legend>
      <div class="grid-2 mt-3">

        <div class="field">
          <label>Identifiant station <span>*</span></label>
          <input type="text" name="id_station_itinerance" required placeholder="Ex : FR-EXX-E0001" class="filter-input" />
        </div>
        <div class="field">
          <label>Nom de la station <span>*</span></label>
          <input type="text" name="nom_station" required placeholder="Nom de la station" class="filter-input" />
        </div>

        <div class="field">
          <label>Aménageur <span>*</span></label>
          <input type="text" name="amenageur" required placeholder="Ex : IZIVIA" class="filter-input" />
        </div>
        <div class="field">
          <label>SIREN aménageur <span>*</span></label>
          <input type="text" name="siren_amenageur" required placeholder="Ex : 785412369" class="filter-input" />
        </div>
        <div class="field">
          <label>Contact aménageur</label>
          <input type="text" name="contact_amenageur" placeholder="Ex : contact@amenageur.fr" class="filter-input" />
        </div>
      </div>
    </fieldset>

    <!-- LOCALISATION -->
    <fieldset>
      <legend>Localisation</legend>
      <div class="grid-2 mt-3">

        <div class="field span-2">
          <label>Adresse <span>*</span></label>
          <input type="text" name="adresse_station" required placeholder="Ex : 4 allée de la Robiquette, 35000 Rennes" class="filter-input" />
        </div>
        <div class="field">
          <label>Commune</label>
          <input type="text" name="commune" placeholder="Ex : Rennes" class="filter-input" />
        </div>
        <div class="field">
          <label>Département</label>
          <input type="text" name="departement" placeholder="Ex : 35" class="filter-input" />
        </div>
        <div class="field">
          <label>Latitude <span>*</span></label>
          <input type="text" name="consolidated_latitude" required placeholder="Ex : 48.1173" class="filter-input" />
        </div>
        <div class="field">
          <label>Longitude <span>*</span></label>
          <input type="text" name="consolidated_longitude" required placeholder="Ex : -1.6778" class="filter-input" />
        </div>
        <div class="field filter-select-wrap">
          <label>Implantation <span>*</span></label>
          <select name="implantation_station" required>
            <option value="">-- Choisir --</option>
            <option value="Parking public">Parking public</option>
            <option value="Parking privé à usage public">Parking privé à usage public</option>
            <option value="Parking privé réservé à la clientèle">Parking privé réservé à la clientèle</option>
            <option value="Station dédiée à la recharge rapide">Station dédiée à la recharge rapide</option>
            <option value="Voirie">Voirie</option>
          </select>
        </div>

      </div>
    </fieldset>

    <!-- CARACTÉRISTIQUES -->
    <fieldset>
      <legend>Caractéristiques</legend>
      <div class="grid-2 mt-3">

        <div class="field">
          <label>Horaires <span>*</span></label>
          <input type="text" name="horaires" required placeholder="Ex : 24/7" class="filter-input" />
        </div>
        <div class="field">
          <label>Puissance max (kW) <span>*</span></label>
          <input type="number" name="puissance_nominale" required placeholder="Ex : 30.5" class="filter-input" />
        </div>

        <div class="field filter-select-wrap">
          <label>Condition d'accès <span>*</span></label>
          <select name="condition_acces" required>
            <option value="Accès libre">Accès libre</option>
            <option value="Accès réservé">Accès réservé</option>
          </select>
        </div>

        <div class="field">
          <label>Tarification</label>
          <input type="text" name="tarification" placeholder="Ex : 0,36€/kWh" class="filter-input" />
        </div>

        <div class="field span-2">
          <label>Types de prises <span>*</span></label>
          <div class="checkline mt-1">
            <label><input type="checkbox" name="types_prises" value="T2" /> T2</label>
            <label><input type="checkbox" name="types_prises" value="Combo CCS" /> Combo CCS</label>
            <label><input type="checkbox" name="types_prises" value="CHAdeMO" /> CHAdeMO</label>
            <label><input type="checkbox" name="types_prises" value="EF" /> EF</label>
            <label><input type="checkbox" name="types_prises" value="Autre" /> Autre</label>
          </div>
        </div>

        <div class="field span-2">
          <label>Types de paiement</label>
          <div class="checkline mt-1">
            <label><input type="checkbox" name="types_paiement" value="CB" /> CB</label>
            <label><input type="checkbox" name="types_paiement" value="Acte" /> Acte</label>
            <label><input type="checkbox" name="types_paiement" value="Autre" /> Autre</label>
          </div>
        </div>

        <div class="field span-2">
          <label>Options</label>
          <div class="checkline mt-1">
            <label><input type="checkbox" name="gratuit" /> Service gratuit</label>
            <label><input type="checkbox" name="cable_t2_attache" /> Câble T2 attaché</label>
          </div>
        </div>

      </div>
    </fieldset>

    <!-- EXPLOITATION -->
    <fieldset>
      <legend>Exploitation</legend>
      <div class="grid-2 mt-3">
        
        <div class="field">
          <label>Enseigne <span>*</span></label>
          <input type="text" name="nom_enseigne" required placeholder="Ex : IZIVIA" class="filter-input" />
        </div>
        <div class="field">
          <label>Date de mise en service</label>
          <input type="date" name="date_mise_en_service" class="filter-input" />
        </div>
        <div class="field">
          <label>Opérateur <span>*</span></label>
          <input type="text" name="operateur" placeholder="Nom de l'opérateur" required class="filter-input" />
        </div>
        <div class="field">
          <label>Contact opérateur <span>*</span></label>
          <input type="text" name="contact_operateur" placeholder="Ex : contact@operateur.fr" required class="filter-input" />
        </div>
        <div class="field">
          <label>Téléphone opérateur</label>
          <input type="text" name="telephone_operateur" placeholder=" Ex : 0615849874" class="filter-input" />
        </div>
      </div>
    </fieldset>

    <!-- ACTIONS -->
    <div class="form-actions mt-2 border-top pt-4">
      <button type="submit" class="btn-prim">Enregistrer le point</button>
      <a class="btn-sec" href="../index.php">Annuler</a>
    </div>

  </form>
</main>

<!-- FOOTER -->
<footer class="ev-footer">
  <span>FEUARDENT Emma / ZADOROZNYJ Lia — Groupe CIN2</span>
  <span>2026</span>
</footer>

<script>
  const toggle = document.getElementById('navToggle');
  const mobile = document.getElementById('navMobile');
  toggle.addEventListener('click', () => mobile.classList.toggle('open'));
</script>
</body>
</html>
