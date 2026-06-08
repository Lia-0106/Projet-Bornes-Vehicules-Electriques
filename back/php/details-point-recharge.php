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
// RECUPERATION DU POINT DE RECHARGE
// $id : récupéré depuis l'URL
// $point : tableau associatif avec toutes les infos du point
// -------------------------------------------------------
$id = isset($_GET['id']) ? $_GET['id'] : 0 ;
if ($id <= 0) {
    header('Location: ../index.php') ;
    exit ;
}

$database = new Database() ;
$db = $database->getConnexion() ;
$pointRecharge = new PointRecharge($db) ;
$point = $pointRecharge->getDetails($id) ;

if (!$point) {
  header('Location: ../index.php') ;
  exit ;
}

?>


<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>EliVolt</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet" />
  <link href="../css/style-back.css" rel="stylesheet" />
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

  <a href="../index.php" class="back-link mb-4 d-inline-flex">
    <i class="fa fa-arrow-left"></i> Retour aux résultats
  </a>

  <!-- EN-TÊTE DE PAGE -->
  <div class="bc-card p-4 mb-4 d-flex align-items-center justify-content-between flex-wrap gap-3">
    <div class="d-flex align-items-center gap-3">
      <div class="bc-logo-box-lg"><i class="fa fa-bolt text-white fs-5"></i></div>
      <div>
        <div class="details-subtitle">Détails du point de recharge</div>
        <div class="details-title"><?= htmlspecialchars($point['id_station_itinerance']) ?></div>
      </div>
    </div>
    <a class="btn-prim" href="modifier-point-recharge.php?id=<?= $point['id'] ?>"><i class="fa fa-pen"></i> Modifier ce point</a>
  </div>

  <div class="row g-4">

    <!-- IDENTIFICATION -->
    <div class="col-md-6">
      <div class="bc-card p-4 h-100">
        <div class="d-flex align-items-center gap-2 mb-3">
          <span class="fw-semibold">IDENTIFICATION</span>
        </div>
        <hr>
        <div class="info-row"><span class="info-label">Identifiant station</span><span><?= htmlspecialchars($point['id_station_itinerance']) ?></span></div>
        <div class="info-row"><span class="info-label">Aménageur</span><span><?= htmlspecialchars($point['nom_amenageur']) ?></span></div>
        <div class="info-row"><span class="info-label">Siren aménageur</span><span><?= htmlspecialchars(isset($point['siren_amenageur']) ? $point['siren_amenageur'] : '—') ?></span></div>
        <div class="info-row">
          <span class="info-label">Contact aménageur</span>
          <span><?= htmlspecialchars(isset($point['contact_amenageur']) ? $point['contact_amenageur'] : '—') ?></span>
        </div>
      </div>
    </div>

    <!-- LOCALISATION -->
    <div class="col-md-6">
      <div class="bc-card p-4 h-100">
        <div class="d-flex align-items-center gap-2 mb-3">
          <span class="fw-semibold">LOCALISATION</span>
        </div>
        <hr>
        <div class="info-row"><span class="info-label">Adresse</span><span><?= htmlspecialchars($point['adresse_station']) ?></span></div>
        <div class="info-row"><span class="info-label">Commune</span><span><?= htmlspecialchars($point['nom_commune']) ?></span></div>
        <div class="info-row"><span class="info-label">Département</span><span><?= htmlspecialchars($point['nom_departement']) ?></span></div>
        <div class="info-row"><span class="info-label">Coordonnées</span><span><?= $point['consolidated_latitude'] ?> / <?= $point['consolidated_longitude'] ?></span></div>
        <div class="info-row"><span class="info-label">Implantation</span><span><?= htmlspecialchars(isset($point['implantation_station']) ? $point['implantation_station'] : '—') ?></span></div>
      </div>
    </div>

    <!-- CARACTERISTIQUES -->
    <div class="col-md-6">
      <div class="bc-card p-4 h-100">
        <div class="d-flex align-items-center gap-2 mb-3">
          <span class="fw-semibold">CARACTÉRISTIQUES</span>
        </div>
        <hr>
        <div class="info-row"><span class="info-label">Horaires</span><span><?= htmlspecialchars(isset($point['horaires']) ? $point['horaires'] : '—') ?></span></div>
        <div class="info-row"><span class="info-label">Puissance</span><span><?= htmlspecialchars($point['puissance_nominale']) ?> kW</span></div>
        <div class="info-row"><span class="info-label">Type(s) de prise</span><span><?= htmlspecialchars(isset($point['types_prises']) ? $point['types_prises'] : '—') ?></span></div>
        <div class="info-row"><span class="info-label">Condition d'accès</span><span><?= htmlspecialchars(isset($point['condition_acces']) ? $point['condition_acces'] : '—') ?></span></div>
        <div class="info-row"><span class="info-label">Gratuit</span><span><?= $point['gratuit'] ? 'Oui' : 'Non' ?></span></div>
        <div class="info-row"><span class="info-label">Tarification</span><span><?= htmlspecialchars(isset($point['tarification']) ? $point['tarification'] : '—') ?></span></div>
        <div class="info-row"><span class="info-label">Type(s) de paiement</span><span><?= htmlspecialchars(isset($point['types_paiement']) ? $point['types_paiement'] : '—') ?></span></div>
        <div class="info-row"><span class="info-label">Câble T2 attaché</span><span><?= $point['cable_t2_attache'] ? 'Oui' : 'Non' ?></span></div>
      </div>
    </div>

    <!-- EXPLOITATION -->
    <div class="col-md-6">
      <div class="bc-card p-4 h-100">
        <div class="d-flex align-items-center gap-2 mb-3">
          <span class="fw-semibold">EXPLOITATION</span>
        </div>
        <hr>
        <div class="info-row"><span class="info-label">Enseigne</span><span><?= htmlspecialchars(isset($point['nom_enseigne']) ? $point['nom_enseigne'] : '—') ?></span></div>
        <div class="info-row"><span class="info-label">Mise en service</span><span><?= htmlspecialchars(isset($point['date_mise_en_service']) ? $point['date_mise_en_service'] : '—') ?></span></div>
        <div class="info-row"><span class="info-label">Opérateur</span><span><?= htmlspecialchars(isset($point['nom_operateur']) ? $point['nom_operateur'] : '—') ?></span></div>
        <div class="info-row"><span class="info-label">Contact opérateur</span><span><?= htmlspecialchars(isset($point['contact_operateur']) ? $point['contact_operateur'] : '—') ?></span></div>
        <div class="info-row"><span class="info-label">Tel. opérateur</span><span><?= htmlspecialchars(isset($point['telephone_operateur']) ? $point['telephone_operateur'] : '—') ?></span></div>
      </div>
    </div>

  </div>
</main>

<!-- FOOTER -->
<footer class="ev-footer">
  <span>FEUARDENT Emma / ZADOROZNYJ Lia — Groupe CIN2</span>
  <span>2026</span>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
  const toggle = document.getElementById('navToggle') ;
  const menu = document.getElementById('navMobile') ;
  if (toggle && menu) toggle.addEventListener('click', () => menu.classList.toggle('open')) ;
</script>
</body>
</html>