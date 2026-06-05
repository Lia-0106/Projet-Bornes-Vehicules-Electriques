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
    header('Location: /back/index.php');
    exit;
}

$pointRecharge = new PointRecharge();
$p = $pointRecharge->getDetails($id);

if (!$p) {
    header('Location: /back/index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'nom_station'          => $_POST['nom_station']          ?? '',
        'adresse_station'      => $_POST['adresse_station']      ?? '',
        'date_mise_en_service' => $_POST['date_mise_en_service'] ?? '',
        'horaires'             => $_POST['horaires']             ?? '',
        'nom_enseigne'         => $_POST['nom_enseigne']         ?? '',
        'puissance_nominale'   => $_POST['puissance_nominale']   ?? 0,
        'cable_t2_attache'     => isset($_POST['cable_t2_attache']) ? 1 : 0,
        'gratuit'              => isset($_POST['gratuit'])          ? 1 : 0,
        'tarification'         => $_POST['tarification']         ?? '',
        'condition_acces'      => $_POST['condition_acces']      ?? '',
        'implantation_station' => $_POST['implantation_station'] ?? '',
        'consolidated_latitude'  => $_POST['consolidated_latitude']  ?? 0,
        'consolidated_longitude' => $_POST['consolidated_longitude'] ?? 0,
        'nom_amenageur'        => $_POST['nom_amenageur']        ?? '',
        'siren_amenageur'      => $_POST['siren_amenageur']      ?? '',
        'contact_amenageur'    => $_POST['contact_amenageur']    ?? '',
        'telephone_amenageur'  => $_POST['telephone_amenageur']  ?? '',
        'nom_operateur'        => $_POST['nom_operateur']        ?? '',
        'contact_operateur'    => $_POST['contact_operateur']    ?? '',
        'telephone_operateur'  => $_POST['telephone_operateur']  ?? '',
        'types_prises'         => $_POST['types_prises']         ?? [],
        'types_paiement'       => $_POST['types_paiement']       ?? [],
    ];

    $pointRecharge->update($id, $data);
    header('Location: /back/php/details-point-recharge.php?id=' . $id);
    exit;
}

// Helper : est-ce qu'une valeur est dans la liste CSV stockée ?
function inList(?string $list, string $val): bool {
    if (!$list) return false;
    return in_array($val, array_map('trim', explode(',', $list)));
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

  <a href="/back/php/details-point-recharge.php?id=<?= $id ?>" class="back-link mb-4 d-inline-flex">
    <i class="fa fa-arrow-left"></i> Retour au détail
  </a>

  <div class="bc-card p-4 mb-4">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
      <div class="d-flex align-items-center gap-3">
        <div class="bc-logo-box-lg" style="background:var(--accent);">
          <i class="fa fa-pen text-white fs-5"></i>
        </div>
        <div>
          <div class="details-subtitle">Modification du point de recharge</div>
          <div class="details-title"><?= htmlspecialchars($p['id_station_itinerance'] ?? 'Point #' . $id) ?></div>
          <div class="details">Les champs obligatoires sont identifiés par <span>*</span></div>
        </div>
      </div>
      <span class="table-badge"><?= htmlspecialchars($p['nom_station'] ?? '') ?></span>
    </div>
  </div>

  <form method="POST" action="modifier-point-recharge.php?id=<?= $id ?>" class="form-grid">

    <!-- ── IDENTIFICATION ─────────────────────────────── -->
    <fieldset>
      <legend>Identification</legend>
      <div class="grid-2 mt-3">

        <div class="field">
          <label>Nom de la station <span>*</span></label>
          <input type="text" name="nom_station" required
                 value="<?= htmlspecialchars($p['nom_station'] ?? '') ?>"
                 class="filter-input" />
        </div>

        <div class="field">
          <label>Aménageur <span>*</span></label>
          <input type="text" name="nom_amenageur"
                 value="<?= htmlspecialchars($p['nom_amenageur'] ?? '') ?>"
                 class="filter-input" />
        </div>
        <div class="field">
          <label>SIREN aménageur <span>*</span></label>
          <input type="text" name="siren_amenageur"
                 value="<?= htmlspecialchars($p['siren_amenageur'] ?? '') ?>"
                 class="filter-input" />
        </div>
        <div class="field">
          <label>Contact aménageur</label>
          <input type="text" name="contact_amenageur"
                 value="<?= htmlspecialchars($p['contact_amenageur'] ?? '') ?>"
                 class="filter-input" />
        </div>
      </div>
    </fieldset>

    <!-- ── LOCALISATION ───────────────────────────────── -->
    <fieldset>
      <legend>Localisation</legend>
      <div class="grid-2 mt-3">

        <div class="field span-2">
          <label>Adresse <span>*</span></label>
          <input type="text" name="adresse_station" required
                 value="<?= htmlspecialchars($p['adresse_station'] ?? '') ?>"
                 class="filter-input" />
        </div>

        <!-- Commune en lecture seule : liée au code INSEE, non modifiable ici -->
        <div class="field">
          <label>Commune</label>
          <input type="text"
                 value="<?= htmlspecialchars($p['nom_commune'] ?? '') ?>"
                 class="filter-input"
                 disabled
                 title="La commune est liée au code INSEE et ne peut pas être modifiée ici." />
        </div>
        <div class="field">
          <label>Département</label>
          <input type="text"
                 value="<?= htmlspecialchars($p['nom_departement'] ?? '') ?>"
                 class="filter-input"
                 disabled />
        </div>

        <div class="field filter-select-wrap">
          <label>Implantation <span>*</span></label>
          <select name="implantation_station">
            <option value="">— Choisir —</option>
            <?php
            $implantations = [
              'Parking public',
              'Parking privé à usage public',
              'Parking privé réservé à la clientèle',
              'Station dédiée à la recharge rapide',
              'Voirie',
            ];
            foreach ($implantations as $imp):
              $sel = ($p['implantation_station'] ?? '') === $imp ? 'selected' : '';
            ?>
            <option value="<?= $imp ?>" <?= $sel ?>><?= $imp ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="field"><!-- spacer --></div>

        <div class="field">
          <label>Latitude <span>*</span></label>
          <input type="text" name="consolidated_latitude" required
                 value="<?= htmlspecialchars($p['consolidated_latitude'] ?? '') ?>"
                 class="filter-input" />
        </div>
        <div class="field">
          <label>Longitude <span>*</span></label>
          <input type="text" name="consolidated_longitude" required
                 value="<?= htmlspecialchars($p['consolidated_longitude'] ?? '') ?>"
                 class="filter-input" />
        </div>

      </div>
    </fieldset>

    <!-- ── CARACTÉRISTIQUES ───────────────────────────── -->
    <fieldset>
      <legend>Caractéristiques</legend>
      <div class="grid-2 mt-3">

        <div class="field">
          <label>Horaires <span>*</span></label>
          <input type="text" name="horaires" required
                 value="<?= htmlspecialchars($p['horaires'] ?? '') ?>"
                 class="filter-input" />
        </div>
        <div class="field">
          <label>Puissance max (kW) <span>*</span></label>
          <input type="number" name="puissance_nominale" required
                 value="<?= htmlspecialchars($p['puissance_nominale'] ?? '') ?>"
                 class="filter-input" />
        </div>
        <div class="field filter-select-wrap">
          <label>Condition d'accès <span>*</span></label>
          <select name="condition_acces" required>
            <option value="Accès libre"   <?= ($p['condition_acces'] ?? '') === 'Accès libre'    ? 'selected' : '' ?>>Accès libre</option>
            <option value="Accès réservé" <?= ($p['condition_acces'] ?? '') === 'Accès réservé'  ? 'selected' : '' ?>>Accès réservé</option>
          </select>
        </div>
        <div class="field">
          <label>Tarification</label>
          <input type="text" name="tarification"
                 value="<?= htmlspecialchars($p['tarification'] ?? '') ?>"
                 placeholder="Gratuit / payant" class="filter-input" />
        </div>

        <div class="field span-2">
          <label>Types de prises <span>*</span></label>
          <div class="checkline mt-1">
            <?php foreach (['T2','Combo CCS','CHAdeMO','EF','Autre'] as $prise): ?>
            <label>
              <input type="checkbox" name="types_prises[]" value="<?= $prise ?>"
                     <?= inList($p['types_prises'] ?? '', $prise) ? 'checked' : '' ?> />
              <?= $prise ?>
            </label>
            <?php endforeach; ?>
          </div>
        </div>

        <div class="field span-2">
          <label>Types de paiement</label>
          <div class="checkline mt-1">
            <?php foreach (['CB','Acte','Autre'] as $paie): ?>
            <label>
              <input type="checkbox" name="types_paiement[]" value="<?= $paie ?>"
                     <?= inList($p['types_paiement'] ?? '', $paie) ? 'checked' : '' ?> />
              <?= $paie ?>
            </label>
            <?php endforeach; ?>
          </div>
        </div>

        <div class="field span-2">
          <label>Options</label>
          <div class="checkline mt-1">
            <label>
              <input type="checkbox" name="gratuit" <?= !empty($p['gratuit']) ? 'checked' : '' ?> />
              Service gratuit
            </label>
            <label>
              <input type="checkbox" name="cable_t2_attache" <?= !empty($p['cable_t2_attache']) ? 'checked' : '' ?> />
              Câble T2 attaché
            </label>
          </div>
        </div>

      </div>
    </fieldset>

    <!-- ── EXPLOITATION ───────────────────────────────── -->
    <fieldset>
      <legend>Exploitation</legend>
      <div class="grid-2 mt-3">

        <div class="field">
          <label>Enseigne <span>*</span></label>
          <input type="text" name="nom_enseigne" required
                 value="<?= htmlspecialchars($p['nom_enseigne'] ?? '') ?>"
                 class="filter-input" />
        </div>
        <div class="field">
          <label>Date de mise en service</label>
          <input type="date" name="date_mise_en_service" required
                 value="<?= htmlspecialchars($p['date_mise_en_service'] ?? '') ?>"
                 class="filter-input" />
        </div>

                <div class="field">
          <label>Opérateur <span>*</span></label>
          <input type="text" name="nom_operateur"
                 value="<?= htmlspecialchars($p['nom_operateur'] ?? '') ?>"
                 class="filter-input" />
        </div>
        <div class="field">
          <label>Contact opérateur <span>*</span></label>
          <input type="text" name="contact_operateur"
                 value="<?= htmlspecialchars($p['contact_operateur'] ?? '') ?>"
                 class="filter-input" />
        </div>
        <div class="field">
          <label>Téléphone opérateur</label>
          <input type="text" name="telephone_operateur"
                 value="<?= htmlspecialchars($p['telephone_operateur'] ?? '') ?>"
                 class="filter-input" />
        </div>

      </div>
    </fieldset>

    <!-- ── ACTIONS ────────────────────────────────────── -->
    <div class="form-actions mt-2 border-top pt-4">
      <button type="submit" class="btn-prim">Enregistrer les modifications</button>
      <a class="btn-sec" href="/back/php/details-point-recharge.php?id=<?= $id ?>">Annuler</a>
    </div>

  </form>
</main>

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
