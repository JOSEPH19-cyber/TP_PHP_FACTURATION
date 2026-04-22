<?php

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/fonctions-auth.php';
require_once __DIR__ . '/../../includes/fonctions-produits.php';
require_once __DIR__ . '/../../includes/fonctions-factures.php';

// Vérifier que l'utilisateur est connecté (Caissier, Manager ou Super Admin)
if (!estConnecte()) 
{

    header('Location: ../../auth/login.php');

    exit();

}

$user = getUtilisateurCourant();
$message = '';
$erreur = '';

// Initialiser le panier si inexistant
if (!isset($_SESSION['panier'])) 
{

    $_SESSION['panier'] = [];

}


if ($_SERVER['REQUEST_METHOD'] === 'POST') 
{

    // Ajout d'un produit scanné
    if (isset($_POST['code_barre']) && !isset($_POST['supprimer_ligne'])) 
    {

        $codeBarre = trim($_POST['code_barre']);
        $quantite = isset($_POST['quantite']) ? (int)$_POST['quantite'] : 1;
        
        if ($quantite <= 0) 
        {

            $erreur = 'La quantité doit être supérieure à 0';

        } 
        else 
        {

            $produit = getProduitByCodeBarre($codeBarre);
            
            if (!$produit) 
            {

                $erreur = 'Produit non trouvé. Veuillez demander au manager de l\'enregistrer.';

            } 
            else 
            {

                // Vérifier si le produit est déjà dans le panier
                $indexExistant = -1;

                foreach ($_SESSION['panier'] as $i => $item) 
                {

                    if ($item['code_barre'] === $codeBarre) 
                    {

                        $indexExistant = $i;
                        break;

                    }

                }
                
                if ($indexExistant !== -1) 
                {

                    // Produit déjà dans le panier
                    $quantiteActuelle = $_SESSION['panier'][$indexExistant]['quantite'];
                    $nouvelleQuantite = $quantiteActuelle + $quantite;
                    
                    // Vérifier le stock par rapport à la nouvelle quantité totale
                    if ($produit['quantite_stock'] < $nouvelleQuantite) 
                    {

                        $erreur = 'Stock insuffisant. Stock disponible : ' . $produit['quantite_stock'] . ', vous tentez d\'avoir ' . $nouvelleQuantite;

                    } 
                    else 
                    {

                        $_SESSION['panier'][$indexExistant]['quantite'] = $nouvelleQuantite;
                        $_SESSION['panier'][$indexExistant]['sous_total_ht'] = calculerSousTotalHT(
                            $_SESSION['panier'][$indexExistant]['prix_unitaire_ht'], 
                            $nouvelleQuantite
                        );

                        $message = 'Quantité augmentée pour "' . $produit['nom'] . '" (de ' . $quantiteActuelle . ' à ' . $nouvelleQuantite . ')';

                    }

                } 
                else 
                {

                    // Nouveau produit dans le panier
                    if ($produit['quantite_stock'] < $quantite) 
                    {

                        $erreur = 'Stock insuffisant. Stock disponible : ' . $produit['quantite_stock'];

                    } 
                    else 
                    {

                        $_SESSION['panier'][] = 
                        [

                            'code_barre' => $produit['code_barre'],
                            'nom' => $produit['nom'],
                            'prix_unitaire_ht' => $produit['prix_unitaire_ht'],
                            'quantite' => $quantite,
                            'sous_total_ht' => calculerSousTotalHT($produit['prix_unitaire_ht'], $quantite)

                        ];

                        $message = 'Produit "' . $produit['nom'] . '" ajouté au panier';

                    }

                }

            }

        }

    }
    
    // Suppression d'une ligne du panier
    if (isset($_POST['supprimer_ligne'])) 
    {

        $index = (int)$_POST['supprimer_ligne'];

        if (isset($_SESSION['panier'][$index])) 
        {

            unset($_SESSION['panier'][$index]);
            $_SESSION['panier'] = array_values($_SESSION['panier']);
            $message = 'Ligne supprimée';

        }
        
    }
    
    // Vider le panier
    if (isset($_POST['vider_panier'])) 
    {

        $_SESSION['panier'] = [];
        $message = 'Panier vidé';

    }
    
    // Valider la facture
    if (isset($_POST['valider_facture'])) 
    {

        if (empty($_SESSION['panier'])) 
        {

            $erreur = 'Le panier est vide';

        } 
        else 
        {

            $totalHT = calculerTotalHT($_SESSION['panier']);
            $tva = calculerTVA($totalHT);
            $totalTTC = calculerTotalTTC($totalHT);
            
            $facture = creerFacture($user['identifiant'], $_SESSION['panier'], $totalHT, $tva, $totalTTC);
            
            if ($facture) 
            {

                $_SESSION['panier'] = [];
                $_SESSION['derniere_facture'] = $facture;

                header('Location: afficher-facture.php?id=' . urlencode($facture['id_facture']));
                
                exit();

            } 
            else 
            {

                $erreur = 'Erreur lors de la création de la facture. Vérifiez les stocks.';

            }

        }

    }

}

$totalHT = calculerTotalHT($_SESSION['panier']);
$tva = calculerTVA($totalHT);
$totalTTC = calculerTotalTTC($totalHT);

?>

<!DOCTYPE html>
<html lang="fr">

    <head>

        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>Caisse - Nouvelle facture</title>

        <link rel="stylesheet" href="/../../assets/css/style.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
        <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
        <script src="https://unpkg.com/@zxing/library@0.18.6/umd/index.min.js"></script>

        <script src="/../../assets/js/scanner.js"></script>

    </head>

    <body>

        <div class="caisse-container">

            <div class="caisse-header">

                <h1><i class="fas fa-shopping-cart"></i> Caisse - Nouvelle facture</h1>

                <div>
                    <span class="badge-role"><i class="fas fa-user"></i> <?php echo ucfirst($_SESSION['user']['role'] ?? 'Utilisateur'); ?> : <?php echo htmlspecialchars($_SESSION['user_nom_complet'] ?? 'Connecté'); ?></span>                
                </div>

                <a href="../../index.php" class="btn-retour"><i class="fas fa-home"></i> Accueil</a>

            </div>



            <?php if ($message): ?>
                <div class="message-succes"><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>
            
            <?php if ($erreur): ?>
                <div class="message-erreur"><i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($erreur); ?></div>
            <?php endif; ?>

            <!-- Scanner -->

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

            <form method="POST" action="" style="margin-top: 15px;">

                <div class="form-row">

                    <div class="form-group">

                        <label><i class="fas fa-barcode"></i> Code-barres :</label>
                        <input type="text" name="code_barre" id="code_barre" placeholder="ou saisir manuellement" autocomplete="off">
                    </div>

                    <div class="form-group">

                        <label><i class="fas fa-boxes"></i> Quantité :</label>
                        <input type="number" name="quantite" id="quantite" value="1" min="1" style="width: 80px;">

                    </div>

                    <div class="form-group">

                        <button type="submit" class="btn-ajouter" style="margin-top: 25px;">
                            <i class="fas fa-plus"></i> Ajouter
                        </button>

                    </div>

                </div>

            </form>

            <!-- Panier -->
            <?php if (empty($_SESSION['panier'])): ?>

                <div class="message-erreur">
                    <i class="fas fa-info-circle"></i> Panier vide. Scannez un produit pour commencer.
                </div>

            <?php else: ?>

                <div style="overflow-x: auto;">

                    <table class="panier-table">

                        <thead>

                            <tr>

                                <th><i class="fas fa-tag"></i> Désignation</th>
                                <th><i class="fas fa-money-bill-wave"></i> Prix unit. HT (CDF)</th>
                                <th><i class="fas fa-boxes"></i> Quantité</th>
                                <th><i class="fas fa-calculator"></i> Sous-total HT (CDF)</th>
                                <th><i class="fas fa-trash"></i> Action</th>

                            </tr>

                        </thead>

                        <tbody>

                            <?php foreach ($_SESSION['panier'] as $index => $item): ?>

                                <tr>

                                    <td><?php echo htmlspecialchars($item['nom']); ?></td>
                                    <td><?php echo number_format($item['prix_unitaire_ht'], 0, ',', ' '); ?></td>
                                    <td><?php echo $item['quantite']; ?></td>
                                    <td><?php echo number_format($item['sous_total_ht'], 0, ',', ' '); ?></td>

                                    <td>

                                        <form method="POST" style="display:inline;">

                                            <button type="submit" name="supprimer_ligne" value="<?php echo $index; ?>" class="btn-supprimer-ligne" onclick="return confirm('Supprimer cette ligne ?')">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>

                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>
                    
                <div class="recap">

                    <p><strong>Total HT :</strong> <?php echo number_format($totalHT, 0, ',', ' '); ?> CDF</p>
                    <p><strong>TVA (<?php echo TVA * 100; ?>%) :</strong> <?php echo number_format($tva, 0, ',', ' '); ?> CDF</p>                    
                    <p><strong> Net à payer (TTC) :</strong> <?php echo number_format($totalTTC, 0, ',', ' '); ?> CDF</p>

                </div>
                    
                <div class="actions-caisse">

                    <form method="POST">
                        <button type="submit" name="vider_panier" class="btn-erreur" onclick="return confirm('Vider tout le panier ?')">
                            <i class="fas fa-trash-alt"></i> Vider le panier
                        </button>
                    </form>

                    <form method="POST">
                        <button type="submit" name="valider_facture" class="btn-valider">
                            <i class="fas fa-check-circle"></i> Valider la facture
                        </button>
                    </form>

                </div>

            <?php endif; ?>
        
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
                        document.querySelector('.scanner-container + form').submit();

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