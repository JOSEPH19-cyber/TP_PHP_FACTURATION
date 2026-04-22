<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/fonctions-auth.php';

// Si déjà connecté, rediriger vers l'accueil
if (estConnecte()) 
{

    header('Location: ../index.php');

    exit();

}

$erreur = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') 
{

    $identifiant = trim($_POST['identifiant'] ?? '');
    $motDePasse = $_POST['mot_de_passe'] ?? '';
    
    if (empty($identifiant) || empty($motDePasse)) 
    {

        $erreur = 'Veuillez remplir tous les champs';

    } 
    else 
    {

        $utilisateur = verifierIdentifiants($identifiant, $motDePasse);
        
        if ($utilisateur) 
        {

            // Connexion réussie
            $_SESSION['user'] = 
            [
                'identifiant' => $utilisateur['identifiant'],
                'nom_complet' => $utilisateur['nom_complet'],
                'role' => $utilisateur['role']
            ];

            header('Location: ../index.php');

            exit();

        } 
        else 
        {

            $erreur = 'Identifiant ou mot de passe incorrect';

        }

    }

}

?>

<!DOCTYPE html>
<html lang="fr">
    <head>

        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>Connexion - Système de Facturation</title>

        <link rel="stylesheet" href="../assets/css/style.css">
        <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">

    </head>

    <body>

        <?php if ($erreur): ?>

            <div class="alert alert-error">
                <?php echo htmlspecialchars($erreur); ?>
            </div>

        <?php endif; ?>

        <div class="auth-container">

            <h1>Connexion</h1>

            <form action="" method="post">

                <div class="form-group">

                    <label for="identifiant">Identifiant :</label>
                    <input type="text" id="identifiant" name="identifiant" required>

                </div>

                <div class="form-group">

                    <label for="mot_de_passe">Mot de passe :</label>
                    <input type="password" id="mot_de_passe" name="mot_de_passe" required>

                </div>

                <button type="submit" name="submit">Se connecter</button>

            </form>

            <p>
                <small>Système de facturation - Supermarché</small>
            </p>

        </div>
        
    </body>

</html>