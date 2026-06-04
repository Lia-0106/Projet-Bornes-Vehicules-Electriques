<?php
require_once 'Database.php';
require_once 'constantes.php';
require_once '../../front/php/PointRecharge.php';

$erreur = '';

// Traitement du formulaire qd il est soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $pointRecharge = new PointRecharge();

    //récup ou crée l'acteur et la commune
    $idActeur  = $pointRecharge->getOuCreerActeur($_POST['amenageur'] ?? '');
    $codeInsee = $pointRecharge->getOuCreerCommune($_POST['commune'] ?? '');
    $pointRecharge->getOuCreerEnseigne($_POST['nom_enseigne'] ?? '');

    $data = [
        'id_station_itinerance'  => $_POST['id_station_itinerance'] ?? '',
        'nom_station'            => $_POST['nom_station']            ?? '',
        'adresse_station'        => $_POST['adresse_station']        ?? '',
        'nbre_pdc'               => $_POST['nbre_pdc']               ?? 1,
        'date_mise_en_service'   => $_POST['date_mise_en_service']   ?? '',
        'code_insee_commune'     => $codeInsee,
        'id_acteur'              => $idActeur,
        'horaires'               => $_POST['horaires']               ?? '',
        'nom_enseigne'           => $_POST['nom_enseigne']           ?? '',
        'puissance_nominale'     => $_POST['puissance_nominale']     ?? 0,
        'cable_t2_attache'       => isset($_POST['cable_t2_attache']) ? 1 : 0,
        'gratuit'                => isset($_POST['gratuit'])          ? 1 : 0,
        'tarification'           => $_POST['tarification']           ?? '',
        'consolidated_longitude' => $_POST['consolidated_longitude'] ?? 0,
        'consolidated_latitude'  => $_POST['consolidated_latitude']  ?? 0,
        'condition_acces'        => $_POST['condition_acces']        ?? '',
    ];

    $pointRecharge->create($data);
    header('Location: ../index.php');
    exit;
}

?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>BretagneCharge — Créer un point</title>
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
    <a class="link" href="../index.php">← Retour à l'accueil</a>

    <section class="card form-card">
      <h2>Créer un point de recharge</h2>
      <p class="muted">Ajoutez un nouveau point IRVE à la base de données régionale</p>

      <?php if ($erreur) : ?>
        <p style="color:red; margin-bottom:15px;"><?= htmlspecialchars($erreur) ?></p>
      <?php endif; ?>

      <form method="POST" action="creer-point-recharge.php" class="form-grid">

        <fieldset>
          <legend>Identification</legend>
          <div class="grid-2">
            <div class="field">
              <label>Identifiant station *</label>
              <input name="id_station_itinerance" required placeholder="FR-EXX-E0001" />
            </div>
            <div class="field">
              <label>Nom station *</label>
              <input name="nom_station" required placeholder="Nom de la station" />
            </div>
            <div class="field">
              <label>Aménageur *</label>
              <input name="amenageur" required placeholder="IZIVIA" />
            </div>
            <div class="field">
              <label>Enseigne *</label>
              <input name="nom_enseigne" required placeholder="IZIVIA" />
            </div>
          </div>
        </fieldset>

        <fieldset>
          <legend>Localisation</legend>
          <div class="grid-2">
            <div class="field span-2">
              <label>Adresse *</label>
              <input name="adresse_station" required placeholder="4 allée de la Robiquette, 35000 Rennes" />
            </div>
            <div class="field">
              <label>Commune *</label>
              <input name="commune" required placeholder="Rennes" />
            </div>
            <div class="field">
              <label>Latitude *</label>
              <input name="consolidated_latitude" required placeholder="48.1173" />
            </div>
            <div class="field">
              <label>Longitude *</label>
              <input name="consolidated_longitude" required placeholder="-1.6778" />
            </div>
          </div>
        </fieldset>

        <fieldset>
          <legend>Caractéristiques</legend>
          <div class="grid-2">
            <div class="field">
              <label>Horaires *</label>
              <input name="horaires" required placeholder="24/7" />
            </div>
            <div class="field">
              <label>Puissance maximale (kW) *</label>
              <input name="puissance_nominale" required type="number" placeholder="22" />
            </div>
            <div class="field">
              <label>Nombre de points *</label>
              <input name="nbre_pdc" required type="number" placeholder="1" />
            </div>
            <div class="field">
              <label>Condition d'accès *</label>
              <select name="condition_acces" required>
                <option value="Accès libre">Accès libre</option>
                <option value="Accès réservé">Accès réservé</option>
              </select>
            </div>
            <div class="checkline">
              <label><input type="checkbox" name="gratuit" /> Service gratuit</label>
              <label><input type="checkbox" name="cable_t2_attache" /> Câble T2 attaché</label>
            </div>
          </div>
        </fieldset>

        <fieldset>
          <legend>Exploitation</legend>
          <div class="grid-2">
            <div class="field">
              <label>Mise en service *</label>
              <input name="date_mise_en_service" required type="date" />
            </div>
            <div class="field">
              <label>Tarification</label>
              <input name="tarification" placeholder="Gratuit / payant" />
            </div>
          </div>
        </fieldset>

        <div class="form-actions">
          <a class="btn" href="../index.php">Annuler</a>
          <button type="submit" class="btn btn-primary">+ Créer le point</button>
        </div>

      </form>
    </section>
  </main>

  <footer class="footer">
    FEUARDENT Emma / ZADOROZNYJ Lia — Groupe : CIN2 — 2026
  </footer>
</body>
</html>