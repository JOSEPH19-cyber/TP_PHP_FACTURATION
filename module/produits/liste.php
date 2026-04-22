<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/fonctions-auth.php';
require_once __DIR__ . '/../../includes/fonctions-produits.php';

// Vérifier que l'utilisateur est Manager ou Super Admin
if (!estConnecte() || !aUnDeCesRoles(['Manager', 'Super Administrateur'])) 
{

    header('Location: ../../index.php');
    exit('Accès non autorisé');

}

$produits = getProduits();

?>

<!DOCTYPE html>
<html lang="fr">

    <head>

        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>Liste des produits - Système de Facturation</title>

        <link rel="stylesheet" href="../../assets/css/style.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
        <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">

    </head>

    <body>

        <div class="produits-container">

            <div class="header">

                <h1><i class="fas fa-boxes"></i> Liste des produits</h1>

                <div>

                    <a href="enregistrer.php" class="btn-ajouter"><i class="fas fa-plus"></i> Nouveau produit</a>
                    <a href="../../index.php" class="btn-retour"><i class="fas fa-arrow-left"></i> Retour</a>

                </div>

            </div>

            <?php if (isset($_GET['message'])): ?>

                <div class="message-succes">
                    <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($_GET['message']); ?>
                </div>

            <?php endif; ?>

            <?php if (empty($produits)): ?>

                <div class="message-erreur">
                    <i class="fas fa-info-circle"></i> Aucun produit. Cliquez sur "Nouveau produit" pour en ajouter.
                </div>

            <?php else: ?>

                <table class="table-produits">

                    <thead>

                        <tr>

                            <th>Code-barres</th>
                            <th>Nom</th>
                            <th>Prix HT (CDF)</th>
                            <th>Stock</th>
                            <th>Date expiration</th>
                            <th>Actions</th>

                        </tr>

                    </thead>

                    <tbody>

                        <?php foreach ($produits as $produit): ?>

                            <?php 
                                $stockFaible = $produit['quantite_stock'] < 10;
                                $expire = estExpire($produit['date_expiration']);
                            ?>

                            <tr>

                                <td><?php echo htmlspecialchars($produit['code_barre']); ?></td>
                                <td><?php echo htmlspecialchars($produit['nom']); ?></td>
                                <td><?php echo number_format($produit['prix_unitaire_ht'], 0, ',', ' '); ?> CDF</td>

                                <td class="<?php echo $stockFaible ? 'stock-faible' : 'stock-normal'; ?>">
                                    
                                    <?php echo $produit['quantite_stock']; ?>

                                    <?php if ($stockFaible): ?>
                                        <i class="fas fa-exclamation-triangle"></i>
                                    <?php endif; ?>

                                </td>

                                <td>

                                    <?php if ($expire): ?>
                                        <span class="expire"><i class="fas fa-skull-crosswalk"></i> <?php echo date('d/m/Y', strtotime($produit['date_expiration'])); ?></span>
                                    <?php else: ?>
                                        <?php echo date('d/m/Y', strtotime($produit['date_expiration'])); ?>
                                    <?php endif; ?>

                                </td>

                                <td>

                                    <a href="edit_stock.php?code_barre=<?php echo urlencode($produit['code_barre']); ?>" class="btn-edit" title="Modifier le stock">
                                        <i class="fas fa-edit"></i>
                                    </a>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                    </tbody>

                </table>

            <?php endif; ?>

        </div>

    </body>

</html>