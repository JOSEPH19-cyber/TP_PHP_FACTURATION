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
$codeBarre = '';
$nom = '';
$prix = '';
$dateExpiration = '';
$quantite = '';

if (isset($_GET['code_barre'])) 
{

    $codeBarre = $_GET['code_barre'];

}

if ($_SERVER['REQUEST_METHOD'] === 'POST') 
{

    $codeBarre = trim($_POST['code_barre'] ?? '');
    $nom = trim($_POST['nom'] ?? '');
    $prix = trim($_POST['prix'] ?? '');
    $dateExpiration = trim($_POST['date_expiration'] ?? '');
    $quantite = trim($_POST['quantite'] ?? '');
    
    if (empty($codeBarre) || empty($nom) || empty($prix) || empty($dateExpiration) || empty($quantite)) 
    {

        $erreur = 'Tous les champs sont obligatoires';

    } elseif (!is_numeric($prix) || $prix <= 0) 
    {

        $erreur = 'Le prix doit être un nombre positif';

    } 
    elseif (!is_numeric($quantite) || $quantite < 0) 
    {

        $erreur = 'La quantité doit être un nombre positif ou zéro';

    } elseif (strtotime($dateExpiration) < strtotime(date('Y-m-d'))) 
    {

        $erreur = 'La date d\'expiration ne peut pas être dans le passé';
    
    } else 
    {

        $existant = getProduitByCodeBarre($codeBarre);

        if ($existant) 
        {

            $erreur = 'Un produit avec ce code-barres existe déjà';

        } else 
        {

            if (ajouterProduit($codeBarre, $nom, $prix, $dateExpiration, $quantite)) 
            {

                header('Location: liste.php?message=' . urlencode('Produit "' . $nom . '" ajouté avec succès'));

                exit();

            } 
            else 
            {

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
        <script src="https://unpkg.com/@zxing/library@0.18.6/umd/index.min.js"></script>

        <script src="../../assets/js/scanner.js"></script>

    </head>

    <body>

        <div class="produits-container">

            <div class="header">

                <h1><i class="fas fa-plus-circle"></i> Ajouter un produit</h1>
                <a href="liste.php" class="btn-retour"><i class="fas fa-arrow-left"></i> Retour</a>

            </div>

            <?php if ($erreur): ?>
                <div class="message-erreur"><?php echo htmlspecialchars($erreur); ?></div>
            <?php endif; ?>

        <div class="scanner-container">

            <button type="button" id="btn-scanner" class="btn-scanner">
                <i class="fas fa-camera"></i> Scanner un code-barres
            </button>

            <div id="scanner-video" style="display:none; margin-top:20px;">

                <div id="scanner-viewport" class="scanner-viewport"></div>

                <button type="button" id="btn-arreter" class="btn-arreter">
                    <i class="fas fa-stop"></i> Arrêter
                </button>

            </div>
            
        </div>

            <div class="form-produit">

                <form method="POST" action="">

                    <div class="form-row">

                        <div class="form-group">

                            <label>Code-barres :</label>
                            <input type="text" name="code_barre" id="code_barre" value="<?php echo htmlspecialchars($codeBarre); ?>" required>

                        </div>

                        <div class="form-group">

                            <label>Nom du produit :</label>
                            <input type="text" name="nom" value="<?php echo htmlspecialchars($nom); ?>" required>

                        </div>

                    </div>

                    <div class="form-row">

                        <div class="form-group">

                            <label>Prix HT (CDF) :</label>
                            <input type="number" step="1" min="0" name="prix" value="<?php echo htmlspecialchars($prix); ?>" required>

                        </div>

                        <div class="form-group">

                            <label>Date expiration :</label>
                            <input type="date" name="date_expiration" value="<?php echo htmlspecialchars($dateExpiration); ?>" required>

                        </div>

                    </div>

                    <div class="form-row">

                        <div class="form-group">

                            <label>Quantité stock :</label>
                            <input type="number" step="1" min="0" name="quantite" value="<?php echo htmlspecialchars($quantite); ?>" required>

                        </div>

                    </div>

                    <button type="submit" class="btn-sauvegarder">Enregistrer</button>

                </form>

            </div>

        </div>

        <script>
            
            const btnScanner = document.getElementById('btn-scanner');
            const scannerVideo = document.getElementById('scanner-video');
            const btnArreter = document.getElementById('btn-arreter');
            const inputCodeBarre = document.getElementById('code_barre');

            btnScanner.addEventListener('click', function() 
            {

                scannerVideo.style.display = 'block';
                btnScanner.style.display = 'none';
                
                demarrerScannerZXing(function(code, erreur) 
                {

                    if (erreur) 
                    {

                        alert(erreur);

                    } 
                    else if (code) 
                    {

                        inputCodeBarre.value = code;

                    }

                    scannerVideo.style.display = 'none';
                    btnScanner.style.display = 'inline-block';
                });

            });
            
            btnArreter.addEventListener('click', function() 
            {

                arreterScannerZXing();
                scannerVideo.style.display = 'none';
                btnScanner.style.display = 'inline-block';

            });

        </script>

    </body>

</html>