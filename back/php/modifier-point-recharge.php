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
require_once __DIR__ . '/fonctions.php' ;


// -------------------------------------------------------
// RECUPERATION DU POINT DE RECHARGE
// $id : récupéré depuis l'URL
// $point : tableau associatif avec toutes les infos du point
// -------------------------------------------------------
$id = isset($_GET['id']) ? $_GET['id'] : 0 ;
if ($id <= 0) {
    header('Location: /back/index.php') ;
    exit ;
}

$database = new Database() ;
$db = $database->getConnexion() ;
$pointRecharge = new PointRecharge($db) ;
$point = $pointRecharge->getDetails($id) ;

if (!$point) {
    header('Location: /back/index.php') ;
    exit ;
}

// -------------------------------------------------------
// CHARGEMENT DES DONNEES POUR LES SELECTS
// Nécessaire sur GET et POST (rendu du formulaire)
// -------------------------------------------------------
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

// Récupération du code INSEE actuel de la station
$stmtStation = $db->prepare("SELECT code_insee_commune FROM station WHERE id_station_itinerance = :id") ;
$stmtStation->bindParam(':id', $point['id_station_itinerance'], PDO::PARAM_STR) ;
$stmtStation->execute() ;
$stationActuelle = $stmtStation->fetch(PDO::FETCH_ASSOC) ;
$codeInseeActuel = $stationActuelle ? $stationActuelle['code_insee_commune'] : '' ;

$erreurs = [] ;


// -------------------------------------------------------
// TRAITEMENT DU FORMULAIRE
// Récupère les données POST et met à jour le point en base
// Redirige vers la page détail avec message de succès
// -------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $estGratuit = isset($_POST['gratuit']) ;
    $typesPaiement = isset($_POST['types_paiement']) ? $_POST['types_paiement'] : [] ;
    $typesPrises = isset($_POST['types_prises']) ? $_POST['types_prises'] : [] ;
    $codeInsee = isset($_POST['code_insee_commune']) ? $_POST['code_insee_commune'] : '' ;

    // --- Validation 1 : champs obligatoires ---
    $champsObligatoires = [
        'nom_station'            => 'Nom de la station',
        'nom_amenageur'          => 'Aménageur',
        'siren_amenageur'        => 'SIREN aménageur',
        'adresse_station'        => 'Adresse',
        'code_insee_commune'     => 'Commune',
        'implantation_station'   => 'Implantation',
        'consolidated_latitude'  => 'Latitude',
        'consolidated_longitude' => 'Longitude',
        'horaires'               => 'Horaires',
        'puissance_nominale'     => 'Puissance max',
        'nom_enseigne'           => 'Enseigne',
        'nom_operateur'          => 'Opérateur',
        'contact_operateur'      => 'Contact opérateur',
    ] ;

    foreach ($champsObligatoires as $champ => $label) {
        if (!isset($_POST[$champ]) || $_POST[$champ] === '') {
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
        $data = [
            'nom_station'            => isset($_POST['nom_station']) ? $_POST['nom_station'] : '',
            'adresse_station'        => isset($_POST['adresse_station']) ? $_POST['adresse_station'] : '',
            'date_mise_en_service'   => isset($_POST['date_mise_en_service']) ? $_POST['date_mise_en_service'] : '',
            'horaires'               => isset($_POST['horaires']) ? $_POST['horaires'] : '',
            'nom_enseigne'           => isset($_POST['nom_enseigne']) ? $_POST['nom_enseigne'] : '',
            'puissance_nominale'     => isset($_POST['puissance_nominale']) ? $_POST['puissance_nominale'] : 0,
            'cable_t2_attache'       => isset($_POST['cable_t2_attache']) ? 1 : 0,
            'gratuit'                => $estGratuit ? 1 : 0,
            'tarification'           => $estGratuit ? '—' : (isset($_POST['tarification']) ? $_POST['tarification'] : ''),
            'condition_acces'        => isset($_POST['condition_acces']) ? $_POST['condition_acces'] : '',
            'implantation_station'   => isset($_POST['implantation_station']) ? $_POST['implantation_station'] : '',
            'consolidated_latitude'  => isset($_POST['consolidated_latitude']) ? $_POST['consolidated_latitude'] : 0,
            'consolidated_longitude' => isset($_POST['consolidated_longitude']) ? $_POST['consolidated_longitude'] : 0,
            'code_insee_commune'     => $codeInsee,
            'nom_amenageur'          => isset($_POST['nom_amenageur']) ? $_POST['nom_amenageur'] : '',
            'siren_amenageur'        => isset($_POST['siren_amenageur']) ? $_POST['siren_amenageur'] : '',
            'contact_amenageur'      => isset($_POST['contact_amenageur']) ? $_POST['contact_amenageur'] : '',
            'nom_operateur'          => isset($_POST['nom_operateur']) ? $_POST['nom_operateur'] : '',
            'contact_operateur'      => isset($_POST['contact_operateur']) ? $_POST['contact_operateur'] : '',
            'telephone_operateur'    => isset($_POST['telephone_operateur']) ? $_POST['telephone_operateur'] : '',
            'types_prises'           => $typesPrises,
            'types_paiement'         => $estGratuit ? [] : $typesPaiement,
        ] ;

        $pointRecharge->update($id, $data) ;
        header('Location: /back/php/details-point-recharge.php?id=' . $id . '&succes=modification') ;
        exit ;
    }

    // En cas d'erreur, on recharge les données POST dans $point pour pré-remplir
    $point = array_merge($point, [
        'nom_station'          => isset($_POST['nom_station'])          ? $_POST['nom_station']          : $point['nom_station'],
        'adresse_station'      => isset($_POST['adresse_station'])      ? $_POST['adresse_station']      : $point['adresse_station'],
        'horaires'             => isset($_POST['horaires'])             ? $_POST['horaires']             : $point['horaires'],
        'puissance_nominale'   => isset($_POST['puissance_nominale'])   ? $_POST['puissance_nominale']   : $point['puissance_nominale'],
        'tarification'         => isset($_POST['tarification'])         ? $_POST['tarification']         : $point['tarification'],
        'implantation_station' => isset($_POST['implantation_station']) ? $_POST['implantation_station'] : $point['implantation_station'],
        'nom_amenageur'        => isset($_POST['nom_amenageur'])        ? $_POST['nom_amenageur']        : $point['nom_amenageur'],
        'siren_amenageur'      => isset($_POST['siren_amenageur'])      ? $_POST['siren_amenageur']      : $point['siren_amenageur'],
        'contact_amenageur'    => isset($_POST['contact_amenageur'])    ? $_POST['contact_amenageur']    : $point['contact_amenageur'],
        'nom_operateur'        => isset($_POST['nom_operateur'])        ? $_POST['nom_operateur']        : $point['nom_operateur'],
        'contact_operateur'    => isset($_POST['contact_operateur'])    ? $_POST['contact_operateur']    : $point['contact_operateur'],
        'telephone_operateur'  => isset($_POST['telephone_operateur'])  ? $_POST['telephone_operateur']  : $point['telephone_operateur'],
        'nom_enseigne'         => isset($_POST['nom_enseigne'])         ? $_POST['nom_enseigne']         : $point['nom_enseigne'],
        'gratuit'              => $estGratuit                           ? 1                              : 0,
        'cable_t2_attache'     => isset($_POST['cable_t2_attache'])     ? 1                              : 0,
    ]) ;
    $codeInseeActuel = $codeInsee !== '' ? $codeInsee : $codeInseeActuel ;
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
    <span class="brand-name">EliVolt <span class="text-muted fw-normal fs-sm">Admin</span></span>
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

  <a href="/back/php/details-point-recharge.php?id=<?= $id ?>" class="back-link mb-4 d-inline-flex">
    <i class="fa fa-arrow-left"></i> Retour au détail
  </a>

  <!-- EN-TÊTE DE PAGE -->
  <div class="bc-card p-4 mb-4">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
      <div class="d-flex align-items-center gap-3">
        <div class="bc-logo-box-lg bc-logo-box--accent">
          <i class="fa fa-pen text-white fs-5"></i>
        </div>
        <div>
          <div class="details-subtitle">Modification du point de recharge</div>
          <div class="details-title"><?= htmlspecialchars(isset($point['id_station_itinerance']) ? $point['id_station_itinerance'] : 'Point #' . $id) ?></div>
          <div class="details">Les champs obligatoires sont identifiés par <span>*</span></div>
        </div>
      </div>
      <span class="table-badge"><?= htmlspecialchars(isset($point['nom_station']) ? $point['nom_station'] : '') ?></span>
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

  <form method="POST" action="" class="form-grid" novalidate>

    <!-- IDENTIFICATION -->
    <fieldset>
      <legend>Identification</legend>
      <div class="grid-2 mt-3">

        <div class="field">
          <label>Nom de la station <span>*</span></label>
          <input type="text" name="nom_station"
                 value="<?= htmlspecialchars(isset($point['nom_station']) ? $point['nom_station'] : '') ?>"
                 class="filter-input" />
        </div>
        <div class="field">
          <label>Aménageur <span>*</span></label>
          <input type="text" name="nom_amenageur"
                 value="<?= htmlspecialchars(isset($point['nom_amenageur']) ? $point['nom_amenageur'] : '') ?>"
                 class="filter-input" />
        </div>
        <div class="field">
          <label>SIREN aménageur <span>*</span></label>
          <input type="text" name="siren_amenageur"
                 value="<?= htmlspecialchars(isset($point['siren_amenageur']) ? $point['siren_amenageur'] : '') ?>"
                 class="filter-input" />
        </div>
        <div class="field">
          <label>Contact aménageur</label>
          <input type="text" name="contact_amenageur"
                 value="<?= htmlspecialchars(isset($point['contact_amenageur']) ? $point['contact_amenageur'] : '') ?>"
                 class="filter-input" />
        </div>

      </div>
    </fieldset>

    <!-- LOCALISATION -->
    <fieldset>
      <legend>Localisation</legend>
      <div class="grid-2 mt-3">

        <div class="field span-2">
          <label>Adresse <span>*</span></label>
          <input type="text" name="adresse_station"
                 value="<?= htmlspecialchars(isset($point['adresse_station']) ? $point['adresse_station'] : '') ?>"
                 class="filter-input" />
        </div>

        <div class="field filter-select-wrap">
          <label>Commune <span>*</span></label>
          <select name="code_insee_commune">
            <option value="">-- Choisir une commune --</option>
            <?php foreach ($communesParDept as $codeDept => $dept) : ?>
              <optgroup label="<?= htmlspecialchars($dept['nom']) ?> (<?= $codeDept ?>)">
                <?php foreach ($dept['communes'] as $c) : ?>
                  <option value="<?= htmlspecialchars($c['code_insee_commune']) ?>"
                    <?= $c['code_insee_commune'] === $codeInseeActuel ? 'selected' : '' ?>>
                    <?= htmlspecialchars($c['nom_commune']) ?>
                  </option>
                <?php endforeach ?>
              </optgroup>
            <?php endforeach ?>
          </select>
        </div>

        <div class="field filter-select-wrap">
          <label>Implantation <span>*</span></label>
          <select name="implantation_station">
            <option value="">-- Choisir --</option>
            <?php foreach (['Parking public','Parking privé à usage public','Parking privé réservé à la clientèle','Station dédiée à la recharge rapide','Voirie'] as $opt) : ?>
              <option value="<?= $opt ?>"
                <?= (isset($point['implantation_station']) && $point['implantation_station'] === $opt) ? 'selected' : '' ?>>
                <?= $opt ?>
              </option>
            <?php endforeach ?>
          </select>
        </div>

        <div class="field">
          <label>Latitude <span>*</span></label>
          <input type="text" name="consolidated_latitude"
                 value="<?= htmlspecialchars(isset($point['consolidated_latitude']) ? $point['consolidated_latitude'] : '') ?>"
                 class="filter-input" />
        </div>
        <div class="field">
          <label>Longitude <span>*</span></label>
          <input type="text" name="consolidated_longitude"
                 value="<?= htmlspecialchars(isset($point['consolidated_longitude']) ? $point['consolidated_longitude'] : '') ?>"
                 class="filter-input" />
        </div>

      </div>
    </fieldset>

    <!-- CARACTÉRISTIQUES -->
    <fieldset>
      <legend>Caractéristiques</legend>
      <div class="grid-2 mt-3">

        <div class="field">
          <label>Horaires <span>*</span></label>
          <input type="text" name="horaires"
                 value="<?= htmlspecialchars(isset($point['horaires']) ? $point['horaires'] : '') ?>"
                 class="filter-input" />
        </div>
        <div class="field">
          <label>Puissance max (kW) <span>*</span></label>
          <input type="number" name="puissance_nominale"
                 value="<?= htmlspecialchars(isset($point['puissance_nominale']) ? $point['puissance_nominale'] : '') ?>"
                 class="filter-input" />
        </div>

        <div class="field filter-select-wrap">
          <label>Condition d'accès <span>*</span></label>
          <select name="condition_acces">
            <?php foreach (['Accès libre','Accès réservé'] as $opt) : ?>
              <option value="<?= $opt ?>"
                <?= (isset($point['condition_acces']) && $point['condition_acces'] === $opt) ? 'selected' : '' ?>>
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
          <input type="text" name="tarification" placeholder="Ex : 0,36€/kWh"
                 value="<?= htmlspecialchars(isset($point['tarification']) ? $point['tarification'] : '') ?>"
                 class="filter-input" />
        </div>

        <div class="field span-2">
          <label>
            Types de prises <span>*</span>
            <?php if (!empty($erreurs) && empty($typesPrises ?? [])) : ?>
              <span class="ms-2 field-error-inline">
                <i class="fa fa-circle-exclamation"></i> Au moins une prise requise
              </span>
            <?php endif ?>
          </label>
          <div class="checkline mt-1">
            <?php foreach (['T2','Combo CCS','CHAdeMO','EF','Autre'] as $prise) : ?>
              <label>
                <input type="checkbox" name="types_prises[]" value="<?= $prise ?>"
                  <?= inList(isset($point['types_prises']) ? $point['types_prises'] : '', $prise) ? 'checked' : '' ?> />
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
            <?php foreach (['CB','Acte','Autre'] as $paiement) : ?>
              <label>
                <input type="checkbox" name="types_paiement[]" value="<?= $paiement ?>"
                  <?= inList(isset($point['types_paiement']) ? $point['types_paiement'] : '', $paiement) ? 'checked' : '' ?> />
                <?= $paiement ?>
              </label>
            <?php endforeach ?>
          </div>
        </div>

        <div class="field span-2">
          <label>Options</label>
          <div class="checkline mt-1">
            <label>
              <input type="checkbox" name="gratuit"
                     <?= !empty($point['gratuit']) ? 'checked' : '' ?> />
              Service gratuit
            </label>
            <label>
              <input type="checkbox" name="cable_t2_attache"
                     <?= !empty($point['cable_t2_attache']) ? 'checked' : '' ?> />
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
          <input type="text" name="nom_enseigne"
                 value="<?= htmlspecialchars(isset($point['nom_enseigne']) ? $point['nom_enseigne'] : '') ?>"
                 class="filter-input" />
        </div>
        <div class="field">
          <label>Date de mise en service</label>
          <input type="date" name="date_mise_en_service"
                 value="<?= htmlspecialchars(isset($point['date_mise_en_service']) ? $point['date_mise_en_service'] : '') ?>"
                 class="filter-input" />
        </div>
        <div class="field">
          <label>Opérateur <span>*</span></label>
          <input type="text" name="nom_operateur"
                 value="<?= htmlspecialchars(isset($point['nom_operateur']) ? $point['nom_operateur'] : '') ?>"
                 class="filter-input" />
        </div>
        <div class="field">
          <label>Contact opérateur <span>*</span></label>
          <input type="text" name="contact_operateur"
                 value="<?= htmlspecialchars(isset($point['contact_operateur']) ? $point['contact_operateur'] : '') ?>"
                 class="filter-input" />
        </div>
        <div class="field">
          <label>Téléphone opérateur</label>
          <input type="text" name="telephone_operateur"
                 value="<?= htmlspecialchars(isset($point['telephone_operateur']) ? $point['telephone_operateur'] : '') ?>"
                 class="filter-input" />
        </div>

      </div>
    </fieldset>

    <!-- ACTIONS -->
    <div class="form-actions mt-2 border-top pt-4">
      <button type="submit" class="btn-prim">Enregistrer les modifications</button>
      <a class="btn-sec" href="/back/php/details-point-recharge.php?id=<?= $id ?>">Annuler</a>
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
