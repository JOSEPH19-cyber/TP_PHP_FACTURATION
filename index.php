<?php

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/fonctions-auth.php';

// Si pas connecté, rediriger vers login
if (!estConnecte()) 
{

    header('Location: auth/login.php');

    exit();

}

$user = $_SESSION['user'];
$role = $user['role'];
$dateJour = date('d/m/Y');

?>

<!DOCTYPE html>
<html lang="fr">

    <head>

        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>Tableau de bord - Système de Facturation</title>

        <link rel="stylesheet" href="assets/css/style.css">
        <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    </head>

    <body>

        <div class="dashboard-container">
            <!-- ENTÊTE -->

                <?php require_once 'includes/header.php' ?>

            <!-- MENU PRINCIPAL -->
            <div class="dashboard-content">

                <h2 class="section-title">Menu principal</h2>
                
                <div class="menu-cartes">

                    <!-- 1. Caisse : tous les rôles -->
                    <a href="module/facturation/nouvelle-facture.php" class="carte">

                        <div class="carte-icon"><i class="fas fa-shopping-cart"></i></div>
                        <h3>Caisse</h3>
                        <p>Scanner des produits et créer des factures</p>

                    </a>

                    <!-- 2. Historique factures : tous les rôles -->
                    <a href="module/facturation/historique.php" class="carte">

                        <div class="carte-icon"><i class="fas fa-file-invoice"></i></div>
                        <h3>Historique des factures</h3>
                        <p>Consulter toutes les factures</p>

                    </a>

                    <!-- 3. Gestion produits : Manager et Super Admin -->
                    <?php if (in_array($role, ['Manager', 'Super Administrateur'])): ?>

                        <a href="module/produits/liste.php" class="carte">

                            <div class="carte-icon"><i class="fas fa-boxes"></i></div>
                            <h3>Gestion des produits</h3>
                            <p>Ajouter, modifier des produits</p>

                        </a>

                    <?php endif; ?>

                    <!-- 4. Rapports : Manager et Super Admin -->
                    <?php if (in_array($role, ['Manager', 'Super Administrateur'])): ?>

                        <a href="rapports/rapport-journalier.php" class="carte">
                            <div class="carte-icon"><i class="fas fa-chart-line"></i></div>
                            <h3>Rapports</h3>
                            <p>Consulter les rapports journaliers/mensuels</p>
                        </a>

                    <?php endif; ?>

                    <!-- 5. Gestion utilisateurs : Super Admin uniquement -->
                    <?php if ($role === 'Super Administrateur'): ?>

                        <a href="module/admin/gestion-comptes.php" class="carte">

                            <div class="carte-icon"><i class="fas fa-users"></i></div>
                            <h3>Gestion des utilisateurs</h3>
                            <p>Créer et gérer les comptes</p>

                        </a>

                    <?php endif; ?>
                </div>
            </div>
        </div>
    </body>
</html>