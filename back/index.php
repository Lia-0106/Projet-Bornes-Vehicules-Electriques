<?php
session_start() ;
if (!isset($_SESSION['admin'])) {
    header('Location: php/login.php') ;
    exit ;
}

require_once 'php/API/Database.php';
require_once 'php/API/constantes.php';
require_once 'php/API/PointRecharge.php';

// Pagination : nombre d'éléments par page (modifiable)
$parPage = isset($_GET['par_page']) ? (int)$_GET['par_page'] : 50 ;
if ($parPage < 1) $parPage = 50 ;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1 ;
if ($page < 1) $page = 1 ;
$offset = ($page - 1) * $parPage ;

$point = new PointRecharge() ;
$recherche = isset($_GET['recherche']) ? trim($_GET['recherche']) : '' ; 
$liste = $point->getListe($parPage, $offset, $recherche) ;
$total = $point->getTotal() ;
$nbPages = ceil($total / $parPage) ;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>EliVolt — Administration</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="css/style-back.css" />
</head>
<body>

<nav class="ev-nav">
  <a href="index.php" class="brand">
    <img src="../ressources/img/logo.jpeg" alt="Logo Elivolt" class="brand-logo"/>
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
  
  <div class="hero-block">
    <div class="hero-overlay"></div>
    <div class="hero-content">
      <h1>Présentation du site</h1>
      <p>Bienvenue sur le back-office EliVolt. Depuis cette interface de gestion, vous pouvez consulter, créer, modifier et supprimer les infrastructures de recharge pour véhicules électriques en région Bretagne.</p>
    </div>
  </div>

  <div class="carte-title-row mb-4 d-flex justify-content-between align-items-center">
    <div>
      <h1 class="page-title">Points de recharge</h1>
      <p class="section-subtitle">Gérez la base de données régionale</p>
    </div>
    <a class="btn-prim" href="php/creer-point-recharge.php"><i class="fa fa-plus"></i> Créer un point</a>
  </div>

  <div class="bc-card p-4 mb-4">
    <form method="GET" action="index.php" class="filter-row align-items-end flex-wrap">
      <div class="filter-group" style="flex: 0 0 auto; width: 130px;">
        <span class="filter-label">Affichage</span>
        <div class="filter-select-wrap">
          <select name="par_page" onchange="this.form.submit()">
            <option value="10"  <?= $parPage == 10  ? 'selected' : '' ?>>10 par page</option>
            <option value="25"  <?= $parPage == 25  ? 'selected' : '' ?>>25 par page</option>
            <option value="50"  <?= $parPage == 50  ? 'selected' : '' ?>>50 par page</option>
            <option value="100" <?= $parPage == 100 ? 'selected' : '' ?>>100 par page</option>
          </select>
        </div>
      </div>
      <div class="filter-group" style="flex: 1; min-width: 200px;">
        <span class="filter-label">Rechercher par identifiant</span>
        <input type="text" name="recherche" value="<?= htmlspecialchars($recherche) ?>" class="filter-input" placeholder="Ex: FR-EXX-E0001..." />
      </div>
      <div class="filter-group" style="flex: 0 0 auto;">
        <span class="filter-label filter-label--hidden">Action</span>
        <button type="submit" class="filter-btn"><i class="fa fa-magnifying-glass"></i> Filtrer</button>
      </div>
      <?php if ($recherche) : ?>
      <div class="filter-group" style="flex: 0 0 auto;">
        <span class="filter-label filter-label--hidden">Clear</span>
        <a href="index.php" class="btn-sec" style="height: 38px; justify-content: center;">Effacer</a>
      </div>
      <?php endif ; ?>
    </form>
  </div>

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
            <td class="fw-medium text-dark"><?= htmlspecialchars($p['id_station_itinerance']) ?></td>
            <td><?= htmlspecialchars($p['nom_amenageur']) ?></td>
            <td><?= htmlspecialchars($p['nom_commune']) ?> — <?= htmlspecialchars($p['code_dep']) ?></td>
            <td><?= htmlspecialchars($p['puissance_nominale']) ?> kW</td>
            <td><?php 
              if (!empty($p['date_mise_en_service']) && substr($p['date_mise_en_service'], 0, 4) !== '0000') {
                $date = explode('-', $p['date_mise_en_service']) ;
                echo $date[2] . '/' . $date[1] . '/' . $date[0] ;
              } else {
                echo '—' ;
              }
            ?></td>
            <td class="text-center" style="white-space: nowrap;">
              <a href="php/details-point-recharge.php?id=<?= $p['id'] ?>"><i class="fa fa-eye"></i></a>
              <a href="php/modifier-point-recharge.php?id=<?= $p['id'] ?>"><i class="fa fa-pen"></i></a>
              <a href="php/supprimer-point-recharge.php?id=<?= $p['id'] ?>" class="action-danger" onclick="return confirm('Voulez-vous vraiment supprimer ce point ?')"><i class="fa fa-trash"></i></a>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    
    <div class="d-flex justify-content-between align-items-center p-3 border-top" style="border-color: var(--border) !important; background: var(--surface2);">
      <?php if ($page > 1) : ?>
        <a href="?page=<?= $page - 1 ?>&par_page=<?= $parPage ?>&recherche=<?= urlencode($recherche) ?>" class="btn-sec"><i class="fa fa-arrow-left"></i> Précédent</a>
      <?php else: ?>
        <div style="width:110px;"></div>
      <?php endif ; ?>

      <form method="GET" action="index.php" class="d-flex align-items-center gap-2 m-0">
        <input type="hidden" name="par_page" value="<?= $parPage ?>" />
        <input type="hidden" name="recherche" value="<?= htmlspecialchars($recherche) ?>" />
        <span class="muted" style="font-size:13px;">Page</span>
        <input type="number" name="page" min="1" max="<?= $nbPages ?>" value="<?= $page ?>" class="filter-input" style="width:70px; text-align:center; padding: 6px;" />
        <span class="muted" style="font-size:13px;">/ <?= $nbPages ?></span>
        <button type="submit" class="btn-sec" style="padding: 6px 12px;">Aller</button>
      </form>

      <?php if ($page < $nbPages) : ?>
        <a href="?page=<?= $page + 1 ?>&par_page=<?= $parPage ?>&recherche=<?= urlencode($recherche) ?>" class="btn-prim">Suivant <i class="fa fa-arrow-right"></i></a>
      <?php else: ?>
        <div style="width:110px;"></div>
      <?php endif ; ?>
    </div>
  </div>

</main>

<footer class="ev-footer">
  <span>FEUARDENT Emma / ZADOROZNYJ Lia — Groupe CIN2</span>
  <span>2026</span>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
  const toggle = document.getElementById('navToggle');
  const menu   = document.getElementById('navMobile');
  if(toggle && menu) toggle.addEventListener('click', () => menu.classList.toggle('open'));
</script>
</body>
</html>