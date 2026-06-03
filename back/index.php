<?php
require_once 'php/Database.php';
require_once 'php/constantes.php';
require_once 'php/PointRecharge.php';

$point = new PointRecharge();
$liste = $point->getListe();
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>BretagneCharge — Accueil</title>
  <link rel="stylesheet" href="css/styles.css" />
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
      <a class="nav-link active" href="index.php">Accueil</a>
      <a class="nav-link" href="php/recherche.php">Recherche</a>
      <a class="nav-link" href="php/carte.php">Carte</a>
    </nav>
  </header>

  <main class="container">

    <section class="hero">
      <div class="hero-body">
        <h1>Présentation du site</h1>
        <p>
          Bienvenue sur BretagneCharge, la plateforme de gestion des
          infrastructures de recharge pour véhicules électriques en région Bretagne.
        </p>
        <p>
          Depuis ce back-office, vous pouvez consulter, créer et modifier
          les points de recharge enregistrés en base de données.
        </p>
      </div>
    </section>

    <section class="card">
      <div class="card-header">
        <h2>Points de recharge</h2>
        <div class="spacer"></div>
        <a class="btn btn-primary" href="php/creer-point-recharge.php">+ Créer un point</a>
      </div>
      <div class="table-wrap">
        <table class="table">
          <thead>
            <tr>
              <th>Identifiant</th>
              <th>Commune</th>
              <th>Aménageur</th>
              <th>Puissance</th>
              <th>Département</th>
              <th class="center">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($liste as $p) : ?>
            <tr>
              <td><?= htmlspecialchars($p['id_station_itinerance']) ?></td>
              <td><?= htmlspecialchars($p['nom_commune']) ?></td>
              <td><?= htmlspecialchars($p['nom_amenageur']) ?></td>
              <td><?= htmlspecialchars($p['puissance_nominale']) ?> kW</td>
              <td><?= htmlspecialchars($p['nom_departement']) ?></td>
              <td class="center">
                <a href="php/details-point-recharge.php?id=<?= $p['id'] ?>" class="btn">Voir</a>
                <a href="php/modifier-point-recharge.php?id=<?= $p['id'] ?>" class="btn">Modifier</a>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </section>

  </main>

  <footer class="footer">
    FEUARDENT Emma / ZADOROZNYJ Lia — Groupe : CIN2 — 2026
  </footer>
</body>
</html>