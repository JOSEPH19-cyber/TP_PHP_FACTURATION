<?php

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/fonctions-auth.php';

// Vérifier que l'utilisateur est Super Admin
if (!estConnecte() || $_SESSION['user']['role'] !== 'Super Administrateur') 
{

    header('Location: ../../index.php');
    exit('Accès non autorisé');

}

$erreur = '';
$succes = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') 
{

    $identifiant = trim($_POST['identifiant'] ?? '');
    $motDePasse = $_POST['mot_de_passe'] ?? '';
    $confirmeMotDePasse = $_POST['confirme_mot_de_passe'] ?? '';
    $role = $_POST['role'] ?? '';
    $nomComplet = $_POST['nom_complet'] ?? '';
    
    // Validation des champs
    if (empty($identifiant) || empty($motDePasse) || empty($role) || empty($nomComplet)) 
    {

        $erreur = 'Tous les champs sont obligatoires';

    } 
    elseif ($motDePasse !== $confirmeMotDePasse) 
    {

        $erreur = 'Les mots de passe ne correspondent pas';

    } elseif (strlen($motDePasse) < 4) 
    {

        $erreur = 'Le mot de passe doit contenir au moins 4 caractères';

    } 
    else 
    {

        $utilisateurs = getUtilisateurs();
        
        // Vérifier si l'identifiant existe déjà
        $existe = false;
        foreach ($utilisateurs as $u) 
        {

            if ($u['identifiant'] === $identifiant) 
            {

                $existe = true;
                break;

            }

        }
        
        if ($existe) 
        {

            $erreur = "L'identifiant '$identifiant' existe déjà";

        } else 
        {
            // Hacher le mot de passe
            $hash = password_hash($motDePasse, PASSWORD_DEFAULT);
            
            $nouvelUtilisateur = 
            [
                "identifiant" => $identifiant,
                "mot_de_passe" => $hash,
                "role" => $role,
                "nom_complet" => strtoupper($nomComplet),
                "date_creation" => date("Y-m-d"),
                "actif" => true
            ];
            
            $utilisateurs[] = $nouvelUtilisateur;
            sauvegarderUtilisateurs($utilisateurs);
            
            $succes = "Utilisateur '$identifiant' créé avec succès";
            
            // Vider le formulaire
            $_POST = [];

        }

    }

}

?>

<!DOCTYPE html>
<html lang="fr">

    <head>

        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>Ajouter un compte - Système de Facturation</title>

        <link rel="stylesheet" href="../../assets/css/style.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
        <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">

    </head>

    <body>

        <div class="admin-container">

            <div class="admin-header">

                <h1><i class="fas fa-user-plus" style="color: #28a745;"></i> Ajouter un compte</h1>
                <a href="gestion-comptes.php" class="btn-retour"><i class="fas fa-arrow-left"></i> Retour</a>

            </div>

            <?php if ($succes): ?>

                <div class="message-succes">
                    <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($succes); ?>
                </div>

            <?php endif; ?>
            
            <?php if ($erreur): ?>

                <div class="message-erreur">
                    <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($erreur); ?>
                </div>

            <?php endif; ?>

            <div class="form-ajout">

                <form method="POST" action="">

                    <div class="form-row">

                        <div class="form-group">

                            <label><i class="fas fa-user"></i> Identifiant :</label>
                            <input type="text" name="identifiant" value="<?php echo htmlspecialchars($_POST['identifiant'] ?? ''); ?>" required >
                            <small>Utilisé pour la connexion</small>

                        </div>

                        <div class="form-group">

                            <label><i class="fas fa-id-card"></i> Nom complet :</label>
                            <input type="text" name="nom_complet" value="<?php echo htmlspecialchars($_POST['nom_complet'] ?? ''); ?>" required >

                        </div>

                    </div>

                    <div class="form-row">

                        <div class="form-group">

                            <label><i class="fas fa-lock"></i> Mot de passe :</label>
                            <input type="password" name="mot_de_passe" required>
                            <small>Minimum 4 caractères</small>

                        </div>

                        <div class="form-group">

                            <label><i class="fas fa-lock"></i> Confirmer le mot de passe :</label>
                            <input type="password" name="confirme_mot_de_passe" required>

                        </div>

                    </div>

                    <div class="form-row">

                        <div class="form-group">

                            <label><i class="fas fa-tag"></i> Rôle :</label>

                            <select name="role" required>

                                <option value="" disabled>-- Sélectionner un rôle --</option>
                                <option value="Caissier" <?php echo (($_POST['role'] ?? '') === 'Caissier') ? 'selected' : ''; ?>>Caissier</option>
                                <option value="Manager" <?php echo (($_POST['role'] ?? '') === 'Manager') ? 'selected' : ''; ?>>Manager</option>

                            </select>

                            <small>Caissier: vente uniquement | Manager: vente + gestion produits + rapports</small>

                        </div>

                    </div>

                    <button type="submit" class="btn-ajouter">
                        <i class="fas fa-save"></i> Créer le compte
                    </button>

                </form>

            </div>

        </div>

    </body>

</html>