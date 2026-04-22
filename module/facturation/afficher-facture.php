<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/fonctions-auth.php';
require_once __DIR__ . '/../../includes/fonctions-factures.php';

// Vérifier que l'utilisateur est connecté
if (!estConnecte()) 
{

    header('Location: ../../auth/login.php');
    exit();

}

$idFacture = $_GET['id'] ?? '';

if (empty($idFacture)) 
{

    header('Location: nouvelle-facture.php?erreur=' . urlencode('Aucune facture spécifiée'));
    exit();

}

$facture = getFactureById($idFacture);

if (!$facture) 
{

    header('Location: nouvelle-facture.php?erreur=' . urlencode('Facture non trouvée'));
    exit();

}

// Récupérer le nom du caissier
$utilisateurs = getUtilisateurs();
$nomCaissier = $facture['caissier'] ?? 'ECKOMELA ELONGA';

foreach ($utilisateurs as $u) 
{

    if ($u['identifiant'] === $facture['caissier']) 
    {

        $nomCaissier = $u['nom_complet'] ?? $facture['caissier'];
        break;

    }

}

?>

<!DOCTYPE html>
<html lang="fr">
    <head>

        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>Facture - <?php echo htmlspecialchars($facture['id_facture']); ?></title>

        <link rel="stylesheet" href="../../assets/css/style.css">

        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
        <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">

    </head>

    <body>

        <div class="facture-container">

            <div class="facture-card">
                
                <div class="facture-header">

                    <h1><i class="fas fa-receipt"></i> FACTURE</h1>
                    <p><?php echo htmlspecialchars($facture['id_facture']); ?></p>

                </div>
                
                <div class="facture-infos">

                    <div>

                        <p><strong><i class="fas fa-calendar"></i> Date :</strong> <?php echo date('d/m/Y', strtotime($facture['date'])); ?></p>
                        <p><strong><i class="fas fa-clock"></i> Heure :</strong> <?php echo $facture['heure']; ?></p>

                    </div>

                    <div> 

                        <p><strong><i class="fas fa-user"></i> Caissier :</strong> <?php echo htmlspecialchars($nomCaissier ?? 'ECKOMELA ELONGA'); ?></p>
                        <p><strong><i class="fas fa-id-card"></i> ID :</strong> <?php echo htmlspecialchars($facture['caissier'] ?? 'caissier'); ?></p>

                    </div>

                </div>
                
                <table class="facture-table">

                    <thead>

                        <tr>

                            <th>Désignation</th>
                            <th>Prix unit. HT (CDF)</th>
                            <th>Qté</th>
                            <th>Sous-total HT (CDF)</th>

                        </tr>

                    </thead>

                    <tbody>

                        <?php foreach ($facture['articles'] as $article): ?>

                            <tr>

                                <td><?php echo htmlspecialchars($article['nom']); ?></td>
                                <td><?php echo number_format($article['prix_unitaire_ht'], 0, ',', ' '); ?></td>
                                <td><?php echo $article['quantite']; ?></td>
                                <td><?php echo number_format($article['sous_total_ht'], 0, ',', ' '); ?></td>

                            </tr>

                        <?php endforeach; ?>

                    </tbody>

                </table>
                
                <div class="facture-totaux">

                    <p><strong>Total HT :</strong> <?php echo number_format($facture['total_ht'], 0, ',', ' '); ?> CDF</p>
                    <p><strong>TVA (<?php echo TVA; ?>%) :</strong> <?php echo number_format($facture['tva'], 0, ',', ' '); ?> CDF</p>
                    <p class="total"><strong>Net à payer (TTC) :</strong> <?php echo number_format($facture['total_ttc'], 0, ',', ' '); ?> CDF</p>

                </div>
                
                <div class="actions-facture">

                    <a href="nouvelle-facture.php" class="btn-nouvelle">
                        <i class="fas fa-plus"></i> Nouvelle facture
                    </a>

                    <a href="../../index.php" class="btn-retour">
                        <i class="fas fa-home"></i> Accueil
                    </a>

                </div>
                
            </div>

        </div>

    </body>

</html>