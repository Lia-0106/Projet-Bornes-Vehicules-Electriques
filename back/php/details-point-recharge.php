<?php
require_once 'Database.php';
require_once 'constantes.php';
require_once 'PointRecharge.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header('Location: ../index.php');
    exit;
}

$pointRecharge = new PointRecharge();
$p = $pointRecharge->getById($id);

if (!$p) {
    header('Location: ../index.php');
    exit;
}

?>

<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>BretagneCharge — Détail du point</title>
  <link rel="stylesheet" href="../css/styles.css" />
</head>
<body>
  <header class="topbar">
    <div class="brand">
      <div class="logo">
        <span class="bolt">logo</span>
      </div>
      <span class="brand-text"><strong>Bretagne</strong>Charge</span>
    </div>
    <nav class="nav">
      <a class="nav-link" href="../index.php">Accueil</a>
      <a class="nav-link" href="recherche.php">Recherche</a>
      <a class="nav-link" href="carte.php">Carte</a>
    </nav>
  </header>

  <main class="container">
    <a class="link" href="../index.php">← Retour aux résultats</a>

    <section class="card header-detail">
      <div class="detail-icon"></div>
      <div class="detail-title">
        <div class="muted">Détails du point de recharge</div>
        <h2><?= htmlspecialchars($p['id_station_itinerance']) ?></h2>
      </div>
      <div class="spacer"></div>
      <a class="btn" href="modifier-point-recharge.php?id=<?= $p['id'] ?>">Modifier</a>
    </section>

    <section class="grid-2 gap">
      <div class="card">
        <h3>Identification</h3>
        <div class="desc">
          <div><strong>Identifiant station</strong><br><?= htmlspecialchars($p['id_station_itinerance']) ?></div>
          <div><strong>Aménageur</strong><br><?= htmlspecialchars($p['nom_amenageur']) ?></div>
          <div><strong>Contact aménageur</strong><br>
            <a href="mailto:<?= htmlspecialchars($p['contact_amenageur'] ?? '') ?>">
              <?= htmlspecialchars($p['contact_amenageur'] ?? '—') ?>
            </a>
          </div>
        </div>
      </div>

      <div class="card">
        <h3>Localisation</h3>
        <div class="desc">
          <div><strong>Adresse</strong><br><?= htmlspecialchars($p['adresse_station']) ?></div>
          <div><strong>Commune</strong><br><?= htmlspecialchars($p['nom_commune']) ?></div>
          <div><strong>Département</strong><br><?= htmlspecialchars($p['nom_departement']) ?></div>
          <div><strong>Coordonnées</strong><br><?= $p['consolidated_latitude'] ?> / <?= $p['consolidated_longitude'] ?></div>
        </div>
      </div>

      <div class="card">
        <h3>Caractéristiques</h3>
        <div class="desc">
          <div><strong>Horaires</strong><br><?= htmlspecialchars($p['horaires'] ?? '—') ?></div>
          <div><strong>Puissance maximale</strong><br><?= htmlspecialchars($p['puissance_nominale']) ?> kW</div>
          <div><strong>Condition d'accès</strong><br><?= htmlspecialchars($p['condition_acces'] ?? '—') ?></div>
          <div><strong>Gratuit</strong><br><?= $p['gratuit'] ? 'Oui' : 'Non' ?></div>
          <div><strong>Câble T2 attaché</strong><br><?= $p['cable_t2_attache'] ? 'Oui' : 'Non' ?></div>
        </div>
      </div>

      <div class="card">
        <h3>Exploitation</h3>
        <div class="desc">
          <div><strong>Enseigne</strong><br><?= htmlspecialchars($p['nom_enseigne'] ?? '—') ?></div>
          <div><strong>Opérateur</strong><br><?= htmlspecialchars($p['nom_operateur'] ?? '—') ?></div>
          <div><strong>Mise en service</strong><br><?= htmlspecialchars($p['date_mise_en_service'] ?? '—') ?></div>
        </div>
      </div>
    </section>
  </main>

  <footer class="footer">
    FEUARDENT Emma / ZADOROZNYJ Lia — Groupe : CIN2 — 2026
  </footer>
</body>
</html>