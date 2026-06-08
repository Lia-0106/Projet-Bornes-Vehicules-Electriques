<?php

// -------------------------------------------------------
// VERIFICATION DE SESSION
// Redirige vers login.php si l'admin n'est pas connecté
// -------------------------------------------------------
session_start() ;
if (!isset($_SESSION['admin'])) {
    header('Location: php/login.php') ;
    exit ;
}

require_once __DIR__ . '/php/API/Database.php' ;
require_once __DIR__ . '/php/API/constantes.php' ;
require_once __DIR__ . '/php/API/PointRecharge.php' ;
require_once __DIR__ . '/php/fonctions.php' ;


// -------------------------------------------------------
// PAGINATION
// $parPage : nb de points par page (défaut 50)
// $page : page courante
// $offset : nb de points à sauter pour atteindre la page
// -------------------------------------------------------
$parPage = isset($_GET['par_page']) ? $_GET['par_page'] : 50 ;
if ($parPage < 1) $parPage = 50 ;
$page = isset($_GET['page']) ? $_GET['page'] : 1 ;
if ($page < 1) $page = 1 ;
$offset = ($page - 1) * $parPage ;


// -------------------------------------------------------
// RECUPERATION DES DONNÉES
// $recherche : filtre optionnel par identifiant station
// $total : tient compte du filtre pour la pagination
// -------------------------------------------------------
$database = new Database() ;
$db = $database->getConnexion() ;
$pointRecharge = new PointRecharge($db) ;

$recherche = isset($_GET['recherche']) ? $_GET['recherche'] : '' ;
$liste = $pointRecharge->getListe($parPage, $offset, $recherche) ;
$total = $pointRecharge->getTotal($recherche) ;
$nbPages = max(1, ceil($total / $parPage)) ;

?>


<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>EliVolt</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="css/style-back.css" />
</head>
<body>

<!-- NAVIGATION -->
<nav class="ev-nav">
  <a href="index.php" class="brand">
    <img src="../ressources/img/logo.jpeg" alt="Logo EliVolt" class="brand-logo" />
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

<!-- MESSAGE DU SUCCES -->
<?php if (isset($_GET['succes'])) : ?>
  <?php if ($_GET['succes'] === 'creation') : ?>
    <div class="alert-succes">Point de recharge créé avec succès !</div>
  <?php elseif ($_GET['succes'] === 'modification') : ?>
    <div class="alert-succes">Point de recharge modifié avec succès !</div>
  <?php elseif ($_GET['succes'] === 'suppression') : ?>
    <div class="alert-succes">Point de recharge supprimé avec succès !</div>
  <?php endif ; ?>
<?php endif ; ?>

<main class="container-xl px-4 pt-4 pb-5 flex-grow-1">
  <!-- PRESENTATION -->
  <div class="hero-block">
    <div class="hero-overlay"></div>
    <div class="hero-content">
      <h1>Présentation du site</h1>
      <p>Bienvenue sur le back-office EliVolt. Depuis cette interface de gestion, vous pouvez consulter, créer, modifier et supprimer les bornes de recharge pour véhicules électriques en Bretagne.</p>
    </div>
  </div>

  <div class="carte-title-row mb-4 d-flex justify-content-between align-items-center">
    <div>
      <h1 class="page-title">Points de recharge</h1>
      <p class="section-subtitle">Gérez la base de données régionale</p>
    </div>
    <a class="btn-prim" href="php/creer-point-recharge.php"><i class="fa fa-plus"></i> Créer un point</a>
  </div>

  <!-- FILTRES -->
  <div class="bc-card p-4 mb-4">
    <form method="GET" action="" class="filter-row align-items-end flex-wrap">

      <!-- NB LIGNES PAR PAGE -->
      <div class="filter-group filter-col-sm">
        <span class="filter-label">Affichage</span>
        <div class="filter-select-wrap">
          <select name="par_page">
            <option value="10" <?= $parPage == 10  ? 'selected' : '' ?> >10 par page</option>
            <option value="25" <?= $parPage == 25  ? 'selected' : '' ?> >25 par page</option>
            <option value="50" <?= $parPage == 50  ? 'selected' : '' ?> >50 par page</option>
            <option value="100" <?= $parPage == 100 ? 'selected' : '' ?> >100 par page</option>
          </select>
        </div>
      </div>

      <!-- RECHERCHE PAR ID STATION -->
      <div class="filter-group filter-col-grow">
        <span class="filter-label">Rechercher par identifiant</span>
        <input type="text" name="recherche" value="<?= htmlspecialchars($recherche) ?>" class="filter-input" placeholder="Ex: FR-EXX-E0001..." />
      </div>
      <div class="filter-group filter-col-auto">
        <span class="filter-label filter-label--hidden">Action</span>
        <button type="submit" class="filter-btn">
          <i class="fa fa-magnifying-glass"></i> Rechercher
        </button>
      </div>

      <?php if ($recherche) : ?>
      <div class="filter-group filter-col-auto">
        <span class="filter-label filter-label--hidden">Clear</span>
        <a href="index.php" class="btn-sec btn-effacer">Effacer</a>
      </div>
      <?php endif ; ?>

    </form>
  </div>

  <!-- TABLEAU -->
  <div class="bc-card overflow-hidden">
    <div class="table-responsive">
      <table class="table bc-table mb-0">
        <thead>
          <tr>
            <th>Identifiant station</th>
            <th>Aménageur</th>
            <th>Localisation</th>
            <th>Puissance max</th>
            <th>Date mise en service</th>
            <th class="text-center">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($liste as $p) : ?>
          <tr>
            <td class="fw-medium"><?= htmlspecialchars($p['id_station_itinerance']) ?></td>
            <td><?= htmlspecialchars($p['nom_amenageur']) ?></td>
            <td><?= htmlspecialchars($p['nom_commune']) ?> — <?= htmlspecialchars($p['code_dep']) ?></td>
            <td><?= htmlspecialchars($p['puissance_nominale']) ?> kW</td>
            <td><?= formatDate($p['date_mise_en_service']) ?></td>
            <td class="text-center td-actions">
              <a href="php/details-point-recharge.php?id=<?= $p['id'] ?>"><i class="fa fa-eye"></i></a>
              <a href="php/modifier-point-recharge.php?id=<?= $p['id'] ?>"><i class="fa fa-pen"></i></a>
              <a href="php/supprimer-point-recharge.php?id=<?= $p['id'] ?>"
                 class="action-danger"
                 onclick="return confirm('Voulez-vous vraiment supprimer ce point ?')">
                <i class="fa fa-trash"></i>
              </a>
            </td>
          </tr>
          <?php endforeach ; ?>
        </tbody>
      </table>
    </div>

    <!-- PAGINATION -->
    <div class="d-flex justify-content-between align-items-center p-3 border-top pagination-bar">

      <?php if ($page > 1) : ?>
        <a href="?page=<?= $page - 1 ?>&par_page=<?= $parPage ?>&recherche=<?= urlencode($recherche) ?>" class="btn-sec">
          <i class="fa fa-arrow-left"></i> Précédent
        </a>
      <?php else : ?>
        <div class="pagination-spacer"></div>
      <?php endif ; ?>

      <form method="GET" action="" class="d-flex align-items-center gap-2 m-0">
        <input type="hidden" name="par_page" value="<?= $parPage ?>" />
        <input type="hidden" name="recherche" value="<?= htmlspecialchars($recherche) ?>" />
        <span class="muted">Page</span>
        <input type="number" name="page" min="1" max="<?= $nbPages ?>" value="<?= $page ?>" class="filter-input page-input" />
        <span class="muted">/ <?= $nbPages ?></span>
        <button type="submit" class="btn-sec btn-aller">Aller</button>
      </form>

      <?php if ($page < $nbPages) : ?>
        <a href="?page=<?= $page + 1 ?>&par_page=<?= $parPage ?>&recherche=<?= $recherche ?>" class="btn-prim">
          Suivant <i class="fa fa-arrow-right"></i>
        </a>
      <?php else : ?>
        <div class="pagination-spacer"></div>
      <?php endif ; ?>

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