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

$erreur = '';
$succes = '';
$codeBarre = $_GET['code_barre'] ?? '';
$produit = null;

// Récupérer le produit
if (!empty($codeBarre)) 
{

    $produit = getProduitByCodeBarre($codeBarre);

    if (!$produit) 
    {

        header('Location: liste.php?erreur=' . urlencode('Produit non trouvé'));
        exit();

    }

} else 
{

    header('Location: liste.php?erreur=' . urlencode('Aucun produit spécifié'));

    exit();

}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') 
{

    $nouvelleQuantite = trim($_POST['quantite'] ?? '');
    
    if (!is_numeric($nouvelleQuantite) || $nouvelleQuantite < 0) 
    {

        $erreur = 'La quantité doit être un nombre positif ou zéro';

    } else 
    {

        if (mettreAJourStock($codeBarre, $nouvelleQuantite)) 
        {

            header('Location: liste.php?message=' . urlencode('Stock du produit "' . $produit['nom'] . '" modifié avec succès'));

            exit();

        } 
        else 
        {

            $erreur = 'Erreur lors de la modification du stock';

        }

    }

}

?>

<!DOCTYPE html>
<html lang="fr">

    <head>

        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>Modifier le stock - Système de Facturation</title>

        <link rel="stylesheet" href="../../assets/css/style.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
        <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">

    </head>

    <body>

        <div class="edit-container">

            <a href="liste.php" class="btn-retour"><i class="fas fa-arrow-left"></i> Retour à la liste</a>
            
            <div class="card">

                <h2><i class="fas fa-edit"></i> Modifier le stock</h2>
                
                <?php if ($erreur): ?>
                    <div class="message-erreur"><?php echo htmlspecialchars($erreur); ?></div>
                <?php endif; ?>
                
                <div class="info-produit">

                    <p><strong><i class="fas fa-barcode"></i> Code-barres :</strong> <?php echo htmlspecialchars($produit['code_barre']); ?></p>
                    <p><strong><i class="fas fa-tag"></i> Produit :</strong> <?php echo htmlspecialchars($produit['nom']); ?></p>
                    <p><strong><i class="fas fa-money-bill-wave"></i> Prix HT :</strong> <?php echo number_format($produit['prix_unitaire_ht'], 0, ',', ' '); ?> CDF</p>
                    <p><strong><i class="fas fa-box"></i> Stock actuel :</strong> 
                        <span class="stock-actuel"><?php echo $produit['quantite_stock']; ?></span>
                        <?php if ($produit['quantite_stock'] < 10): ?>
                            <span class="stock-critique"><i class="fas fa-exclamation-triangle"></i> Stock faible !</span>
                        <?php endif; ?>
                    </p>

                </div>
                
                <form method="POST" action="">

                    <div class="form-group">

                        <label><i class="fas fa-boxes"></i> Nouvelle quantité :</label>
                        <input type="number" step="1" min="0" name="quantite" value="<?php echo $produit['quantite_stock']; ?>" required>
                        <small>Saisissez la nouvelle quantité en stock</small>

                    </div>
                    
                    <button type="submit" class="btn-modifier">
                        <i class="fas fa-save"></i> Mettre à jour
                    </button>

                </form>

            </div>

        </div>

    </body>
    
</html>