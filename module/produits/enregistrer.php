<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/fonctions-auth.php';
require_once __DIR__ . '/../../includes/fonctions-produits.php';

// Vérifier que l'utilisateur est Manager ou Super Admin
if (!estConnecte() || !aUnDeCesRoles(['Manager', 'Super Administrateur'])) {
    header('Location: ../../index.php');
    exit('Accès non autorisé');
}

$erreur = '';
$succes = '';
$codeBarre = '';
$nom = '';
$prix = '';
$dateExpiration = '';
$quantite = '';

// Vérifier si un code-barres a été passé en paramètre (depuis le scan)
if (isset($_GET['code_barre'])) {
    $codeBarre = $_GET['code_barre'];
    $produitExistant = getProduitByCodeBarre($codeBarre);
    if ($produitExistant) {
        // Le produit existe déjà, rediriger vers edit_stock ou afficher message
        header('Location: liste.php?message=' . urlencode('Le produit ' . $codeBarre . ' existe déjà. Utilisez "Modifier le stock" pour changer la quantité.'));
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codeBarre = trim($_POST['code_barre'] ?? '');
    $nom = trim($_POST['nom'] ?? '');
    $prix = trim($_POST['prix'] ?? '');
    $dateExpiration = trim($_POST['date_expiration'] ?? '');
    $quantite = trim($_POST['quantite'] ?? '');
    
    // Validation
    if (empty($codeBarre) || empty($nom) || empty($prix) || empty($dateExpiration) || empty($quantite)) {
        $erreur = 'Tous les champs sont obligatoires';
    } elseif (!is_numeric($prix) || $prix <= 0) {
        $erreur = 'Le prix doit être un nombre positif';
    } elseif (!is_numeric($quantite) || $quantite < 0) {
        $erreur = 'La quantité doit être un nombre positif ou zéro';
    } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateExpiration)) {
        $erreur = 'Format de date invalide. Utilisez AAAA-MM-JJ';
    } elseif (strtotime($dateExpiration) < strtotime(date('Y-m-d'))) {
        $erreur = 'La date d\'expiration ne peut pas être dans le passé';
    } else {
        // Vérifier si le code-barres existe déjà
        $existant = getProduitByCodeBarre($codeBarre);
        if ($existant) {
            $erreur = 'Un produit avec ce code-barres existe déjà';
        } else {
            // Ajouter le produit
            if (ajouterProduit($codeBarre, $nom, $prix, $dateExpiration, $quantite)) {
                $succes = 'Produit ajouté avec succès';
                // Vider le formulaire
                $codeBarre = '';
                $nom = '';
                $prix = '';
                $dateExpiration = '';
                $quantite = '';
            } else {
                $erreur = 'Erreur lors de l\'ajout du produit';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">

    <head>

        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>Ajouter un produit - Système de Facturation</title>

        <link rel="stylesheet" href="../../assets/css/style.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
        <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">

    </head>

    <body>

        <div class="produits-container">

            <div class="header">

                <h1><i class="fas fa-plus-circle"></i> Ajouter un produit</h1>

                <div>
                    <a href="liste.php" class="btn-retour"><i class="fas fa-arrow-left"></i> Retour à la liste</a>
                </div>
                
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

            <div class="form-produit">
                <form method="POST" action="">
                    <div class="form-row">
                        <div class="form-group">
                            <label><i class="fas fa-barcode"></i> Code-barres :</label>
                            <input type="text" name="code_barre" value="<?php echo htmlspecialchars($codeBarre); ?>" required placeholder="ex: 3017620422003">
                            <small>Scannez le code-barres ou entrez-le manuellement</small>
                        </div>

                        <div class="form-group">
                            <label><i class="fas fa-tag"></i> Nom du produit :</label>
                            <input type="text" name="nom" value="<?php echo htmlspecialchars($nom); ?>" required placeholder="ex: Coca-Cola 1L">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label><i class="fas fa-money-bill-wave"></i> Prix unitaire HT (CDF) :</label>
                            <input type="number" step="1" name="prix" value="<?php echo htmlspecialchars($prix); ?>" required placeholder="ex: 1200">
                            <small>Prix en francs congolais (CDF)</small>
                        </div>

                        <div class="form-group">
                            <label><i class="fas fa-calendar-alt"></i> Date d'expiration :</label>
                            <input type="date" name="date_expiration" value="<?php echo htmlspecialchars($dateExpiration); ?>" required>
                            <small>Format: AAAA-MM-JJ</small>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label><i class="fas fa-box"></i> Quantité initiale en stock :</label>
                            <input type="number" step="1" min="0" name="quantite" value="<?php echo htmlspecialchars($quantite); ?>" required placeholder="ex: 50">
                        </div>
                    </div>

                    <button type="submit" class="btn-sauvegarder">
                        <i class="fas fa-save"></i> Enregistrer le produit
                    </button>
                </form>
            </div>
        </div>
    </body>
</html>