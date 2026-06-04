<?php
require_once 'API/Database.php';
require_once 'API/constantes.php';
require_once 'API/PointRecharge.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header('Location: ../index.php');
    exit;
}

$pointRecharge = new PointRecharge();
$p = $pointRecharge->getDetails();

if (!$p) {
    header('Location: ../index.php');
    exit;
}

// traitement du formulaire quand il est soumis
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
    ];

    $pointRecharge->update($id, $data);

    //redirige vers la page détail après modif
    header('Location: point-recharge.php?id=' . $id);
    exit;
}
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>BretagneCharge — Modifier un point</title>
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
    <a class="link" href="point-recharge.php?id=<?= $id ?>">← Retour au détail</a>

    <section class="card form-card">
      <h2>Modifier le point de recharge</h2>
      <p class="muted">Mettez à jour les informations du point</p>

      <form method="POST" action="modifier-point-recharge.php?id=<?= $id ?>" class="form-grid">

        <fieldset>
          <legend>Identification</legend>
          <div class="grid-2">
            <div class="field">
              <label>Nom de la station</label>
              <input name="nom_station" value="<?= htmlspecialchars($p['nom_station'] ?? '') ?>" />
            </div>
            <div class="field">
              <label>Enseigne</label>
              <input name="nom_enseigne" value="<?= htmlspecialchars($p['nom_enseigne'] ?? '') ?>" />
            </div>
          </div>
        </fieldset>

        <fieldset>
          <legend>Localisation</legend>
          <div class="grid-2">
            <div class="field span-2">
              <label>Adresse</label>
              <input name="adresse_station" value="<?= htmlspecialchars($p['adresse_station'] ?? '') ?>" />
            </div>
          </div>
        </fieldset>

        <fieldset>
          <legend>Caractéristiques</legend>
          <div class="grid-2">
            <div class="field">
              <label>Horaires</label>
              <input name="horaires" value="<?= htmlspecialchars($p['horaires'] ?? '') ?>" />
            </div>
            <div class="field">
              <label>Puissance maximale (kW)</label>
              <input name="puissance_nominale" type="number" value="<?= htmlspecialchars($p['puissance_nominale'] ?? '') ?>" />
            </div>
            <div class="field">
              <label>Condition d'accès</label>
              <input name="condition_acces" value="<?= htmlspecialchars($p['condition_acces'] ?? '') ?>" />
            </div>
            <div class="field">
              <label>Tarification</label>
              <input name="tarification" value="<?= htmlspecialchars($p['tarification'] ?? '') ?>" />
            </div>
            <div class="checkline">
              <label>
                <input type="checkbox" name="gratuit" <?= $p['gratuit'] ? 'checked' : '' ?> /> Service gratuit
              </label>
              <label>
                <input type="checkbox" name="cable_t2_attache" <?= $p['cable_t2_attache'] ? 'checked' : '' ?> /> Câble T2 attaché
              </label>
            </div>
          </div>
        </fieldset>

        <fieldset>
          <legend>Exploitation</legend>
          <div class="grid-2">
            <div class="field">
              <label>Mise en service</label>
              <input name="date_mise_en_service" type="date" value="<?= htmlspecialchars($p['date_mise_en_service'] ?? '') ?>" />
            </div>
          </div>
        </fieldset>

        <div class="form-actions">
          <a class="btn" href="details-point-recharge.php?id=<?= $id ?>">Annuler</a>
          <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
        </div>

      </form>
    </section>
  </main>

  <footer class="footer">
    FEUARDENT Emma / ZADOROZNYJ Lia — Groupe : CIN2 — 2026
  </footer>
</body>
</html>