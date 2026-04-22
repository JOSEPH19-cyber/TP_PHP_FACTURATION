<?php

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/fonctions-auth.php';

// Vérifier que l'utilisateur est Super Admin
if (!estConnecte() || $_SESSION['user']['role'] !== 'Super Administrateur') 
{

    header('Location: ../index.php');

    exit('Accès non autorisé');

}

$utilisateurs = getUtilisateurs();

?>

<!DOCTYPE html>
<html lang="fr">
        
    <head>

        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>Gestion des comptes - Système de Facturation</title>

        <link rel="stylesheet" href="/../../assets/css/style.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
        <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">

    </head>

    <body>

        <div class="admin-container">

            <div class="admin-header">

                <h1><i class="fas fa-users" style="color: #17a2b8;"></i> Gestion des comptes</h1>

                <div>

                    <a href="ajouter-compte.php" class="btn-ajouter"><i class="fas fa-plus"></i> Ajouter un compte</a>
                    <a href="/../../index.php" class="btn-retour"><i class="fas fa-arrow-left"></i> Retour</a>

                </div>

            </div>

            <?php if (isset($_GET['message'])): ?>

                <div class="message-succes">
                    <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($_GET['message']); ?>
                </div>

            <?php endif; ?>
            
            <?php if (isset($_GET['erreur'])): ?>

                <div class="message-erreur">
                    <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($_GET['erreur']); ?>
                </div>

            <?php endif; ?>

            <h2 class="section-title"><i class="fas fa-list"></i> Liste des comptes</h2>
            
            <table class="table-comptes">

                <thead>

                    <tr>

                        <th><i class="fas fa-user"></i> Identifiant</th>
                        <th><i class="fas fa-id-card"></i> Nom complet</th>
                        <th><i class="fas fa-tag"></i> Rôle</th>
                        <th><i class="fas fa-calendar"></i> Date création</th>
                        <th><i class="fas fa-cog"></i> Action</th>

                    </tr>

                </thead>

                <tbody>

                    <?php foreach ($utilisateurs as $user): ?>

                        <tr>

                            <td><?php echo ucfirst(htmlspecialchars($user['identifiant'])); ?></td>
                            <td><?php echo htmlspecialchars($user['nom_complet']); ?></td>

                            <td>

                                <?php if ($user['role'] === 'Caissier'): ?>
                                    <span class="badge-caissier"><i class="fas fa-cash-register"></i> <?php echo htmlspecialchars($user['role']); ?></span>

                                <?php elseif ($user['role'] === 'Manager'): ?>
                                    <span class="badge-manager"><i class="fas fa-chalkboard-user"></i> <?php echo htmlspecialchars($user['role']); ?></span>
                
                                <?php else: ?>
                                    <span class="badge-super"><i class="fas fa-crown"></i> <?php echo htmlspecialchars($user['role']); ?></span>

                                <?php endif; ?>

                            </td>

                            <td><?php echo date('d/m/Y', strtotime($user['date_creation'])); ?></td>

                            <td>

                                <?php if ($user['identifiant'] !== $_SESSION['user']['identifiant']): ?>

                                    <a 
                                        href="supprimer-compte.php?identifiant=<?php echo urlencode($user['identifiant']); ?>" 
                                        class="btn-supprimer"
                                        onclick="return confirm('Supprimer cet utilisateur ?')">
                                            <i class="fas fa-trash-alt"></i> Supprimer
                                    </a>
                                <?php else: ?>

                                    <span style="color:#999;"><i class="fas fa-lock"></i> Compte actuel</span>

                                <?php endif; ?>
                            </td>

                        </tr>

                    <?php endforeach; ?>

                </tbody>

            </table>

        </div>

    </body>

</html>