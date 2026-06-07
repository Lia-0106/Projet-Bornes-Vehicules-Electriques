<?php
session_start() ;
if (!isset($_SESSION['admin'])) {
    header('Location: ../php/login.php') ;
    exit ;
}

require_once __DIR__ . '/API/Database.php' ;
require_once __DIR__ . '/API/constantes.php' ;
require_once __DIR__ . '/API/PointRecharge.php' ;

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header('Location: ../index.php');
    exit;
}

$database = new Database() ;
$db = $database->getConnexion() ;
$pointRecharge = new PointRecharge($db) ;
$p = $pointRecharge->getDetails($id);

if (!$p) {
    header('Location: ../index.php');
    exit;
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

<nav class="ev-nav">
  <a href="/back/index.php" class="brand">
    <img src="../../ressources/img/logo.jpeg" alt="Logo Elivolt" class="brand-logo"/>
    <span class="brand-name">EliVolt <span class="text-muted fw-normal" style="font-size:14px">Admin</span></span>
  </a>
<!-- Nav desktop -->
<div class="nav-links">
  <a href="/back/index.php">Accueil</a>
  <a href="/front/html/recherche.html">Recherche</a>
  <a href="/front/html/carte.html">Carte</a>
  <a href="/front/index.html" class="site">Aller au site</a>
</div>
</nav>

<!-- Nav mobile -->
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

  <div class="bc-card p-4 mb-4 d-flex align-items-center justify-content-between flex-wrap gap-3">
    <div class="d-flex align-items-center gap-3">
      <div class="bc-logo-box-lg"><i class="fa fa-bolt text-white fs-5"></i></div>
      <div>
        <div class="details-subtitle">Détails du point de recharge</div>
        <div class="details-title"><?= htmlspecialchars($p['id_station_itinerance']) ?></div>
      </div>
    </div>
    <a class="btn-prim" href="modifier-point-recharge.php?id=<?= $p['id'] ?>"><i class="fa fa-pen"></i> Modifier ce point</a>
  </div>

  <div class="row g-4">
    <div class="col-md-6">
      <div class="bc-card p-4 h-100">
        <div class="d-flex align-items-center gap-2 mb-3">
          <span class="fw-semibold">IDENTIFICATION</span>
        </div>
        <hr>
        <div class="info-row"><span class="info-label">Identifiant station</span><span><?= htmlspecialchars($p['id_station_itinerance']) ?></span></div>
        <div class="info-row"><span class="info-label">Aménageur</span><span><?= htmlspecialchars($p['nom_amenageur']) ?></span></div>
        <div class="info-row"><span class="info-label">Siren aménageur</span><span><?= htmlspecialchars($p['siren_amenageur'] ?? '—') ?></span></div>
        <div class="info-row"><span class="info-label">Contact aménageur</span>
          <span>
            <?php if (!empty($p['contact_amenageur'])): ?>
              <a href="mailto:<?= htmlspecialchars($p['contact_amenageur']) ?>" class="text-decoration-none" style="color:var(--accent);"><?= htmlspecialchars($p['contact_amenageur']) ?></a>
            <?php else: ?>—<?php endif; ?>
          </span>
        </div>
      </div>
    </div>

    <div class="col-md-6">
      <div class="bc-card p-4 h-100">
        <div class="d-flex align-items-center gap-2 mb-3">
          <span class="fw-semibold">LOCALISATION</span>
        </div>
        <hr>
        <div class="info-row"><span class="info-label">Adresse</span><span><?= htmlspecialchars($p['adresse_station']) ?></span></div>
        <div class="info-row"><span class="info-label">Commune</span><span><?= htmlspecialchars($p['nom_commune']) ?></span></div>
        <div class="info-row"><span class="info-label">Département</span><span><?= htmlspecialchars($p['nom_departement']) ?></span></div>
        <div class="info-row"><span class="info-label">Coordonnées</span><span><?= $p['consolidated_latitude'] ?> / <?= $p['consolidated_longitude'] ?></span></div>
        <div class="info-row"><span class="info-label">Implantation</span><span><?= htmlspecialchars($p['implantation_station'] ?? '—') ?></span></div>
      </div>
    </div>

    <div class="col-md-6">
      <div class="bc-card p-4 h-100">
        <div class="d-flex align-items-center gap-2 mb-3">
          <span class="fw-semibold">CARACTÉRISTIQUES</span>
        </div>
        <hr>
        <div class="info-row"><span class="info-label">Horaires</span><span><?= htmlspecialchars($p['horaires'] ?? '—') ?></span></div>
        <div class="info-row"><span class="info-label">Puissance</span><span><?= htmlspecialchars($p['puissance_nominale']) ?> kW</span></div>
        <div class="info-row"><span class="info-label">Type(s) de prise</span><span><?= htmlspecialchars($p['types_prises'] ?? '—') ?></span></div>
        <div class="info-row"><span class="info-label">Condition d'accès</span><span><?= htmlspecialchars($p['condition_acces'] ?? '—') ?></span></div>
        <div class="info-row"><span class="info-label">Gratuit</span><span><?= $p['gratuit'] ? 'Oui' : 'Non' ?></span></div>
        <div class="info-row"><span class="info-label">Tarification</span><span><?= htmlspecialchars($p['tarification'] ?? '—') ?></span></div>
        <div class="info-row"><span class="info-label">Type(s) de paiement</span><span><?= htmlspecialchars($p['types_paiement'] ?? '—') ?></span></div>
        <div class="info-row"><span class="info-label">Câble T2 attaché</span><span><?= $p['cable_t2_attache'] ? 'Oui' : 'Non' ?></span></div>
      </div>
    </div>

    <div class="col-md-6">
      <div class="bc-card p-4 h-100">
        <div class="d-flex align-items-center gap-2 mb-3">
          <span class="fw-semibold">EXPLOITATION</span>
        </div>
        <hr>
        <div class="info-row"><span class="info-label">Enseigne</span><span><?= htmlspecialchars($p['nom_enseigne'] ?? '—') ?></span></div>
        <div class="info-row"><span class="info-label">Mise en service</span><span><?= htmlspecialchars($p['date_mise_en_service'] ?? '—') ?></span></div>
        <div class="info-row"><span class="info-label">Opérateur</span><span><?= htmlspecialchars($p['nom_operateur'] ?? '—') ?></span></div>
        <div class="info-row"><span class="info-label">Contact opérateur</span><span><?= htmlspecialchars($p['contact_operateur'] ?? '—') ?></span></div>
        <div class="info-row"><span class="info-label">Tel. opérateur</span><span><?= htmlspecialchars($p['telephone_operateur'] ?? '—') ?></span></div>
      </div>
    </div>
  </div>
</main>

<footer class="ev-footer">
  <span>FEUARDENT Emma / ZADOROZNYJ Lia — Groupe CIN2</span>
  <span>2026</span>
</footer>

</body>
</html>