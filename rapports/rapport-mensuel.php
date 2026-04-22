<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/fonctions-auth.php';
require_once __DIR__ . '/../includes/fonctions-factures.php';

// Vérifier que l'utilisateur est Manager ou Super Admin
if (!estConnecte() || !aUnDeCesRoles(['Manager', 'Super Administrateur'])) 
{

    header('Location: ../index.php');
    exit('Accès non autorisé');

}



$mois = $_GET['mois'] ?? date('Y-m');
$dateDebut = $mois . '-01';
$dateFin = date('Y-m-t', strtotime($dateDebut));
$factures = getFacturesByDateRange($dateDebut, $dateFin);
$stats = getChiffreAffaires($dateDebut, $dateFin);


// Récupérer la map des noms des caissiers
$tousUtilisateurs = getUtilisateurs();
$mapCaissiers = [];
foreach ($tousUtilisateurs as $u) 
{

    $mapCaissiers[$u['identifiant']] = $u['nom_complet'];

}

// Regrouper par jour
$ventesParJour = [];

foreach ($factures as $facture) 
{

    $jour = $facture['date'];

    if (!isset($ventesParJour[$jour])) 
    {

        $ventesParJour[$jour] = 0;

    }

    $ventesParJour[$jour] += $facture['total_ttc'];

}

?>

<!DOCTYPE html>
<html lang="fr">

    <head>

        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>Rapport mensuel - <?php echo date('F Y', strtotime($dateDebut)); ?></title>

        <link rel="stylesheet" href="../assets/css/style.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    </head>

    <body>

        <div class="rapport-container">

            <div class="rapport-header">

                <h1><i class="fas fa-calendar-alt"></i> Rapport mensuel</h1>

                <div>

                    <span class="badge-role"><?php echo ucfirst($_SESSION['user']['role'] ?? 'Manager'); ?></span>
                    <a href="rapport-journalier.php" class="btn-journalier"><i class="fas fa-calendar-day"></i> Voir rapport journalier</a>
                    <a href="../index.php" class="btn-retour"><i class="fas fa-home"></i> Accueil</a>

                </div>

            </div>

            <form method="GET" class="rapport-form">

                <div class="form-group">

                    <label><i class="fas fa-calendar"></i> Mois :</label>
                    <input type="month" name="mois" value="<?php echo $mois; ?>">

                </div>

                <button type="submit" class="btn-filtrer"><i class="fas fa-eye"></i> Voir</button>

            </form>

            <div class="stats-cartes">

                <div class="stat-carte">

                    <i class="fas fa-receipt"></i>

                    <div class="stat-info">

                        <span class="stat-valeur"><?php echo $stats['nombre_factures']; ?></span>
                        <span class="stat-label">Factures</span>

                    </div>

                </div>

                <div class="stat-carte">

                    <i class="fas fa-chart-line"></i>

                    <div class="stat-info">

                        <span class="stat-valeur"><?php echo number_format($stats['total_ht'], 0, ',', ' '); ?> CDF</span>
                        <span class="stat-label">CA HT</span>

                    </div>

                </div>

                <div class="stat-carte">

                    <i class="fas fa-percent"></i>

                    <div class="stat-info">

                        <span class="stat-valeur"><?php echo number_format($stats['tva'], 0, ',', ' '); ?> CDF</span>
                        <span class="stat-label">TVA</span>

                    </div>

                </div>

                <div class="stat-carte">

                    <i class="fas fa-money-bill-wave"></i>

                    <div class="stat-info">

                        <span class="stat-valeur"><?php echo number_format($stats['total_ttc'], 0, ',', ' '); ?> CDF</span>
                        <span class="stat-label">CA TTC</span>

                    </div>

                </div>

            </div>

            <?php if (!empty($factures)): ?>

                <div class="table-container">

                    <h3>Liste des factures du mois</h3>

                    <table class="rapport-table">

                        <thead>

                            <tr>

                                <th>N° Facture</th>
                                <th>Date</th>
                                <th>Heure</th>
                                <th>Caissier</th>
                                <th>Total TTC</th>

                            </tr>

                        </thead>

                        <tbody>

                            <?php foreach ($factures as $facture): ?>

                                <tr>

                                    <td><?php echo htmlspecialchars($facture['id_facture']); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($facture['date'])); ?></td>
                                    <td><?php echo $facture['heure']; ?></td>

                                    <td>

                                        <?php 
                                            $nomCaissier = $mapCaissiers[$facture['caissier']] ?? $facture['caissier'] ?? 'Inconnu';
                                            echo htmlspecialchars($nomCaissier); 
                                        ?>

                                    </td>

                                    <td><?php echo number_format($facture['total_ttc'], 0, ',', ' '); ?> CDF</td>

                                </tr>

                            <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>

                <div class="table-container">

                    <h3>Ventes par jour</h3>

                    <table class="rapport-table">

                        <thead>

                            <tr>

                                <th>Date</th>
                                <th>Chiffre d'affaires (TTC)</th>

                            </tr>

                        </thead>

                        <tbody>

                            <?php foreach ($ventesParJour as $jour => $total): ?>

                                <tr>

                                    <td><?php echo date('d/m/Y', strtotime($jour)); ?></td>
                                    <td><?php echo number_format($total, 0, ',', ' '); ?> CDF</td>

                                </tr>

                            <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>

            <?php else: ?>
                <div class="message-erreur">Aucune facture trouvée pour ce mois.</div>
            <?php endif; ?>

            <div class="rapport-actions">
                <button onclick="window.print()" class="btn-imprimer"><i class="fas fa-print"></i> Imprimer</button>
            </div>

        </div>

    </body>

</html>