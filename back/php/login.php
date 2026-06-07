<?php

// -------------------------------------------------------
// INITIALISATION
// session_start() appelé avant tout l'HTML
// -------------------------------------------------------
session_start() ;
require_once('API/constantes.php') ;


// -------------------------------------------------------
// TRAITEMENT DU FORMULAIRE
// Vérifie les identifiants et démarre la session admin
// password_verify() compare le mot de passe saisi avec le hash stocké dans constantes.php
// -------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = isset($_POST['login']) ? $_POST['login'] : '' ;
    $mdp = isset($_POST['mdp']) ? $_POST['mdp'] : '' ;

    if ($login === ADMIN_LOGIN && password_verify($mdp, ADMIN_MDP)) {
        $_SESSION['admin'] = true ;
        header('Location: /back/index.php') ;
        exit ;
    }
    else {
        $erreur = 'Identifiants incorrects.' ;
    }
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
    <link rel="stylesheet" href="../css/style-back.css" />
</head>
<body style="justify-content: center; background-image: url('https://images.unsplash.com/photo-1593941707882-a5bba14938c7?w=1400&q=80'); background-size: cover; background-position: center; position: relative;">

    <div style="position: absolute; inset: 0; background: linear-gradient(135deg, rgba(124,90,245,0.85) 0%, rgba(30,27,46,0.9) 100%); z-index: 0;"></div>

    <main class="container-xl px-4 d-flex justify-content-center align-items-center flex-grow-1" style="position: relative; z-index: 1;">
        <section class="bc-card p-5" style="width:100%; max-width:420px; box-shadow: 0 10px 30px rgba(0,0,0,0.2);">
            
            <div class="text-center mb-4">
                <img src="../../ressources/img/logo.jpeg" alt="Logo Elivolt" class="brand-logo"/>
                <h2 class="page-title mb-1">Espace Administrateur</h2>
                <p class="section-subtitle">Accès sécurisé EliVolt</p>
            </div>

            <?php if (!empty($erreur)) : ?>
                <div class="alert alert-danger text-center" style="font-size:13px;"><?= htmlspecialchars($erreur) ?></div>
            <?php endif ; ?>

            <form method="POST" action="login.php" class="form-grid" style="gap: 1.25rem;">
                <div class="field">
                    <label>Identifiant</label>
                    <input type="text" name="login" required placeholder="Saisir l'identifiant..." class="filter-input" />
                </div>
                <div class="field">
                    <label>Mot de passe</label>
                    <input type="password" name="mdp" required placeholder="••••••••" class="filter-input" />
                </div>
                <div class="form-actions mt-3 flex-column">
                    <button type="submit" class="btn-prim w-100 justify-content-center py-2" style="font-size:14px;">Se connecter</button>
                    <a class="btn-sec w-100 justify-content-center py-2" href="/" style="font-size:14px;">Retour au site public</a>
                </div>
            </form>
        </section>
    </main>

</body>
</html>