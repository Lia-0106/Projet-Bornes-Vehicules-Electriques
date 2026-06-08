<?php

// -------------------------------------------------------
// VERIFICATION DE SESSION
// Redirige vers login.php si l'admin n'est pas connecté
// -------------------------------------------------------
session_start() ;
if (!isset($_SESSION['admin'])) {
    header('Location: ../php/login.php') ;
    exit ;
}

require_once __DIR__ . '/API/Database.php' ;
require_once __DIR__ . '/API/constantes.php' ;
require_once __DIR__ . '/API/PointRecharge.php' ;



// -------------------------------------------------------
// CHARGEMENT DES DONNEES POUR LES SELECTS
// Nécessaire sur GET et POST (rendu du formulaire)
// -------------------------------------------------------
$database = new Database() ;
$db = $database->getConnexion() ;

$stmtCommunes = $db->query("SELECT c.code_insee_commune, c.nom_commune, c.code_dep, d.nom_departement
                            FROM commune c
                            JOIN departement d ON c.code_dep = d.code_dep
                            WHERE c.code_dep IN ('22','29','35','56')
                            ORDER BY c.code_dep, c.nom_commune") ;
$communesParDept = [] ;
foreach ($stmtCommunes->fetchAll(PDO::FETCH_ASSOC) as $commune) {
    $communesParDept[$commune['code_dep']]['nom'] = $commune['nom_departement'] ;
    $communesParDept[$commune['code_dep']]['communes'][] = $commune ;
}

$erreurs = [] ;


// -------------------------------------------------------
// TRAITEMENT DU FORMULAIRE
// Récupère les données POST, crée les acteurs et l'enseigne si nécessaire
// Insère le point en base
// Redirige vers l'accueil après création
// -------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pointRecharge = new PointRecharge($db) ;
    $estGratuit = isset($_POST['gratuit']) ;
    $typesPaiement = isset($_POST['types_paiement']) ? $_POST['types_paiement'] : [] ;
    $typesPrises = isset($_POST['types_prises']) ? $_POST['types_prises'] : [] ;

    // Déduction du code département depuis le code INSEE
    $codeInsee = isset($_POST['code_insee_commune']) ? $_POST['code_insee_commune'] : '' ;

    // --- Validation 1 : champs obligatoires ---
    $champsObligatoires = [
        'id_station_itinerance'  => 'Identifiant station',
        'nom_station'            => 'Nom de la station',
        'amenageur'              => 'Aménageur',
        'siren_amenageur'        => 'SIREN aménageur',
        'adresse_station'        => 'Adresse',
        'code_insee_commune'     => 'Commune',
        'consolidated_latitude'  => 'Latitude',
        'consolidated_longitude' => 'Longitude',
        'implantation_station'   => 'Implantation',
        'horaires'               => 'Horaires',
        'puissance_nominale'     => 'Puissance max',
        'nom_enseigne'           => 'Enseigne',
        'operateur'              => 'Opérateur',
        'contact_operateur'      => 'Contact opérateur',
    ] ;

    foreach ($champsObligatoires as $champ => $label) {
        if (!isset($_POST[$champ]) || trim($_POST[$champ]) === '') {
            $erreurs[] = 'Le champ "' . $label . '" est obligatoire.' ;
        }
    }

    // --- Validation 2 : au moins un type de prise ---
    if (empty($typesPrises)) {
        $erreurs[] = 'Veuillez sélectionner au moins un type de prise.' ;
    }

    // --- Validation 3 : cohérence gratuit / paiement ---
    if ($estGratuit && !empty($typesPaiement)) {
        $erreurs[] = 'Un service gratuit ne peut pas avoir de types de paiement sélectionnés.' ;
    }

    if (empty($erreurs)) {
        $idActeur = $pointRecharge->getOuCreerActeur(
            isset($_POST['amenageur']) ? $_POST['amenageur'] : '',
            isset($_POST['contact_amenageur']) ? $_POST['contact_amenageur'] : '',
            '',
            isset($_POST['siren_amenageur']) ? $_POST['siren_amenageur'] : '',
            'amenageur') ;
        $idOperateur = $pointRecharge->getOuCreerActeur(
            isset($_POST['operateur']) ? $_POST['operateur'] : '',
            isset($_POST['contact_operateur']) ? $_POST['contact_operateur'] : '',
            isset($_POST['telephone_operateur']) ? $_POST['telephone_operateur'] : '',
            '',
            'operateur') ;

        $pointRecharge->getOuCreerEnseigne(isset($_POST['nom_enseigne']) ? $_POST['nom_enseigne'] : '' ) ;

        $data = [ 'id_station_itinerance'  => isset($_POST['id_station_itinerance']) ? $_POST['id_station_itinerance'] : '',
                  'nom_station'            => isset($_POST['nom_station']) ? $_POST['nom_station'] : '',
                  'adresse_station'        => isset($_POST['adresse_station']) ? $_POST['adresse_station'] : '',
                  'nbre_pdc'               => 1,
                  'date_mise_en_service'   => isset($_POST['date_mise_en_service']) ? $_POST['date_mise_en_service'] : '',
                  'code_insee_commune'     => $codeInsee,
                  'id_acteur'              => $idActeur,
                  'id_operateur'           => $idOperateur,
                  'horaires'               => isset($_POST['horaires']) ? $_POST['horaires'] : '',
                  'nom_enseigne'           => isset($_POST['nom_enseigne']) ? $_POST['nom_enseigne'] : '',
                  'implantation_station'   => isset($_POST['implantation_station']) ? $_POST['implantation_station'] : '',
                  'puissance_nominale'     => isset($_POST['puissance_nominale']) ? $_POST['puissance_nominale'] : 0,
                  'cable_t2_attache'       => isset($_POST['cable_t2_attache']) ? 1 : 0,
                  'gratuit'                => $estGratuit ? 1 : 0,
                  'tarification'           => $estGratuit ? '—' : (isset($_POST['tarification']) ? $_POST['tarification'] : ''),
                  'consolidated_longitude' => isset($_POST['consolidated_longitude']) ? $_POST['consolidated_longitude'] : 0,
                  'consolidated_latitude'  => isset($_POST['consolidated_latitude']) ? $_POST['consolidated_latitude']  : 0,
                  'condition_acces'        => isset($_POST['condition_acces']) ? $_POST['condition_acces'] : '',
                  'types_prises'           => $typesPrises,
                  'types_paiement'         => $estGratuit ? [] : $typesPaiement ] ;

        $pointRecharge->create($data) ;
        header('Location: ../index.php?succes=creation') ;
        exit ;
    }
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

  <!-- MESSAGES D'ERREUR -->
  <?php if (!empty($erreurs)) : ?>
    <div class="bc-card p-3 mb-4 error-card">
      <strong><i class="fa fa-triangle-exclamation me-2 text-danger"></i>Le formulaire contient des erreurs :</strong>
      <ul class="mb-0 mt-2">
        <?php foreach ($erreurs as $e) : ?>
          <li><?= htmlspecialchars($e) ?></li>
        <?php endforeach ?>
      </ul>
    </div>
  <?php endif ?>

  <form method="POST" action="/back/php/creer-point-recharge.php" class="form-grid" novalidate>

    <!-- IDENTIFICATION -->
    <fieldset>
      <legend>Identification</legend>
      <div class="grid-2 mt-3">

        <div class="field">
          <label>Identifiant station <span>*</span></label>
          <input type="text" name="id_station_itinerance" required placeholder="Ex : FR-EXX-E0001" class="filter-input"
                 value="<?= htmlspecialchars(isset($_POST['id_station_itinerance']) ? $_POST['id_station_itinerance'] : '') ?>" />
          <small class="text-muted">Si la station existe déjà, ses informations seront conservées.</small>
        </div>
        <div class="field">
          <label>Nom de la station <span>*</span></label>
          <input type="text" name="nom_station" required placeholder="Nom de la station" class="filter-input"
                 value="<?= htmlspecialchars(isset($_POST['nom_station']) ? $_POST['nom_station'] : '') ?>" />
        </div>
        <div class="field">
          <label>Aménageur <span>*</span></label>
          <input type="text" name="amenageur" required placeholder="Ex : IZIVIA" class="filter-input"
                 value="<?= htmlspecialchars(isset($_POST['amenageur']) ? $_POST['amenageur'] : '') ?>" />
        </div>
        <div class="field">
          <label>SIREN aménageur <span>*</span></label>
          <input type="text" name="siren_amenageur" required placeholder="Ex : 785412369" class="filter-input"
                 value="<?= htmlspecialchars(isset($_POST['siren_amenageur']) ? $_POST['siren_amenageur'] : '') ?>" />
        </div>
        <div class="field">
          <label>Contact aménageur</label>
          <input type="text" name="contact_amenageur" placeholder="Ex : contact@amenageur.fr" class="filter-input"
                 value="<?= htmlspecialchars(isset($_POST['contact_amenageur']) ? $_POST['contact_amenageur'] : '') ?>" />
        </div>

      </div>
    </fieldset>

    <!-- LOCALISATION -->
    <fieldset>
    <legend>Localisation</legend>
    <div class="grid-2 mt-3">

        <div class="field span-2">
        <label>Adresse <span>*</span></label>
        <input type="text" name="adresse_station" required placeholder="Ex : 4 allée de la Robiquette, 35000 Rennes" class="filter-input"
                value="<?= htmlspecialchars(isset($_POST['adresse_station']) ? $_POST['adresse_station'] : '') ?>" />
        </div>

        <div class="field filter-select-wrap">
        <label>Commune <span>*</span></label>
        <select name="code_insee_commune" required>
            <option value="">-- Choisir une commune --</option>
            <?php foreach ($communesParDept as $codeDept => $dept) : ?>
            <optgroup label="<?= htmlspecialchars($dept['nom']) ?> (<?= $codeDept ?>)">
                <?php foreach ($dept['communes'] as $c) : ?>
                <option value="<?= htmlspecialchars($c['code_insee_commune']) ?>"
                    <?= (isset($_POST['code_insee_commune']) && $_POST['code_insee_commune'] === $c['code_insee_commune']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($c['nom_commune']) ?>
                </option>
                <?php endforeach ?>
            </optgroup>
            <?php endforeach ?>
        </select>
        </div>

        <div class="field filter-select-wrap">
        <label>Implantation <span>*</span></label>
        <select name="implantation_station" required>
            <option value="">-- Choisir --</option>
            <?php foreach (['Parking public','Parking privé à usage public','Parking privé réservé à la clientèle','Station dédiée à la recharge rapide','Voirie'] as $opt) : ?>
            <option value="<?= $opt ?>"
                <?= (isset($_POST['implantation_station']) && $_POST['implantation_station'] === $opt) ? 'selected' : '' ?>>
                <?= $opt ?>
            </option>
            <?php endforeach ?>
        </select>
        </div>

        <div class="field">
        <label>Latitude <span>*</span></label>
        <input type="text" name="consolidated_latitude" required placeholder="Ex : 48.1173" class="filter-input"
                value="<?= htmlspecialchars(isset($_POST['consolidated_latitude']) ? $_POST['consolidated_latitude'] : '') ?>" />
        </div>
        <div class="field">
        <label>Longitude <span>*</span></label>
        <input type="text" name="consolidated_longitude" required placeholder="Ex : -1.6778" class="filter-input"
                value="<?= htmlspecialchars(isset($_POST['consolidated_longitude']) ? $_POST['consolidated_longitude'] : '') ?>" />
        </div>

    </div>
    </fieldset>

    <!-- CARACTÉRISTIQUES -->
    <fieldset>
      <legend>Caractéristiques</legend>
      <div class="grid-2 mt-3">

        <div class="field">
          <label>Horaires <span>*</span></label>
          <input type="text" name="horaires" required placeholder="Ex : 24/7" class="filter-input"
                 value="<?= htmlspecialchars(isset($_POST['horaires']) ? $_POST['horaires'] : '') ?>" />
        </div>
        <div class="field">
          <label>Puissance max (kW) <span>*</span></label>
          <input type="number" name="puissance_nominale" required placeholder="Ex : 30.5" class="filter-input"
                 value="<?= htmlspecialchars(isset($_POST['puissance_nominale']) ? $_POST['puissance_nominale'] : '') ?>" />
        </div>

        <div class="field filter-select-wrap">
          <label>Condition d'accès <span>*</span></label>
          <select name="condition_acces" required>
            <?php foreach (['Accès libre','Accès réservé'] as $opt) : ?>
              <option value="<?= $opt ?>"
                <?= (isset($_POST['condition_acces']) && $_POST['condition_acces'] === $opt) ? 'selected' : '' ?>>
                <?= $opt ?>
              </option>
            <?php endforeach ?>
          </select>
        </div>

        <div class="field">
            <label>
            Tarification
            <span class="text-muted ms-2 field-hint">
                <i class="fa fa-circle-info me-1"></i>Impossible si le service est gratuit
            </span>
            </label>
            <input type="text" name="tarification" placeholder="Ex : 0,36€/kWh" class="filter-input"
                    value="<?= htmlspecialchars(isset($_POST['tarification']) ? $_POST['tarification'] : '') ?>" />
            </div>

        <div class="field span-2">
          <label>
            Types de prises <span>*</span>
            <?php if (!empty($erreurs) && empty($_POST['types_prises'])) : ?>
              <span class="ms-2 field-error-inline">
                <i class="fa fa-circle-exclamation"></i> Au moins une prise requise
              </span>
            <?php endif ?>
          </label>
          <div class="checkline mt-1">
            <?php foreach (['T2','Combo CCS','CHAdeMO','EF','Autre'] as $prise) : ?>
              <label>
                <input type="checkbox" name="types_prises[]" value="<?= $prise ?>"
                  <?= (isset($_POST['types_prises']) && in_array($prise, $_POST['types_prises'])) ? 'checked' : '' ?> />
                <?= $prise ?>
              </label>
            <?php endforeach ?>
          </div>
        </div>

        <div class="field span-2">
          <label>Types de paiement</label>
          <div class="details mb-2">
            <i class="fa fa-circle-info me-1"></i>
            Impossible si le service est gratuit
          </div>
          <div class="checkline mt-1">
            <?php foreach (['CB','Acte','Autre'] as $paie) : ?>
              <label>
                <input type="checkbox" name="types_paiement[]" value="<?= $paie ?>"
                  <?= (isset($_POST['types_paiement']) && in_array($paie, $_POST['types_paiement'])) ? 'checked' : '' ?> />
                <?= $paie ?>
              </label>
            <?php endforeach ?>
          </div>
        </div>

        <div class="field span-2">
          <label>Options</label>
          <div class="checkline mt-1">
            <label>
              <input type="checkbox" name="gratuit"
                     <?= isset($_POST['gratuit']) ? 'checked' : '' ?> />
              Service gratuit
            </label>
            <label>
              <input type="checkbox" name="cable_t2_attache"
                     <?= isset($_POST['cable_t2_attache']) ? 'checked' : '' ?> />
              Câble T2 attaché
            </label>
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
          <input type="text" name="nom_enseigne" required placeholder="Ex : IZIVIA" class="filter-input"
                 value="<?= htmlspecialchars(isset($_POST['nom_enseigne']) ? $_POST['nom_enseigne'] : '') ?>" />
        </div>
        <div class="field">
          <label>Date de mise en service</label>
          <input type="date" name="date_mise_en_service" class="filter-input"
                 value="<?= htmlspecialchars(isset($_POST['date_mise_en_service']) ? $_POST['date_mise_en_service'] : '') ?>" />
        </div>
        <div class="field">
          <label>Opérateur <span>*</span></label>
          <input type="text" name="operateur" required placeholder="Nom de l'opérateur" class="filter-input"
                 value="<?= htmlspecialchars(isset($_POST['operateur']) ? $_POST['operateur'] : '') ?>" />
        </div>
        <div class="field">
          <label>Contact opérateur <span>*</span></label>
          <input type="text" name="contact_operateur" required placeholder="Ex : contact@operateur.fr" class="filter-input"
                 value="<?= htmlspecialchars(isset($_POST['contact_operateur']) ? $_POST['contact_operateur'] : '') ?>" />
        </div>
        <div class="field">
          <label>Téléphone opérateur</label>
          <input type="text" name="telephone_operateur" placeholder="Ex : 0615849874" class="filter-input"
                 value="<?= htmlspecialchars(isset($_POST['telephone_operateur']) ? $_POST['telephone_operateur'] : '') ?>" />
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
  const toggle = document.getElementById('navToggle') ;
  const mobile = document.getElementById('navMobile') ;
  toggle.addEventListener('click', () => mobile.classList.toggle('open')) ;
</script>
</body>
</html>
