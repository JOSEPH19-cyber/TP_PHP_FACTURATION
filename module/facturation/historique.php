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

$user = getUtilisateurCourant();
$message = '';
$erreur = '';

// Filtres
$dateFiltre = $_GET['date'] ?? '';
$caissierFiltre = $_GET['caissier'] ?? '';

// Récupérer les factures
$factures = getFactures();

// Appliquer les filtres
if (!empty($dateFiltre)) 
{

    $factures = array_filter($factures, function($facture) use ($dateFiltre) 
    {

        return $facture['date'] === $dateFiltre;

    });

}

if (!empty($caissierFiltre)) 
{

    $factures = array_filter($factures, function($facture) use ($caissierFiltre) 
    {

        return $facture['caissier'] === $caissierFiltre;

    });

}

// Trier par date décroissante
usort($factures, function($a, $b) 
{

    return strtotime($b['date'] . ' ' . $b['heure']) - strtotime($a['date'] . ' ' . $a['heure']);

});

// Récupérer la liste des caissiers pour le filtre
$utilisateurs = getUtilisateurs();

$caissiers = array_filter($utilisateurs, function($u) 
{

    return $u['role'] === 'caissier';

});

?>

<!DOCTYPE html>
<html lang="fr">

    <head>

        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>Historique des factures</title>
        
        <link rel="stylesheet" href="../../assets/css/style.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
        <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">

    </head>

    <body>

        <div class="produits-container">

            <div class="header">

                <h1><i class="fas fa-history"></i> Historique des factures</h1>

                <div>

                    <span class="badge-role"><i class="fas fa-user"></i> <?php echo ucfirst($_SESSION['user']['role'] ?? 'Utilisateur'); ?></span>
                    <a href="nouvelle-facture.php" class="btn-ajouter"><i class="fas fa-plus"></i> Nouvelle facture</a>
                    <a href="../../index.php" class="btn-retour"><i class="fas fa-home"></i> Accueil</a>

                </div>

            </div>

            <?php if ($message): ?>
                <div class="message-succes"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>
            
            <?php if ($erreur): ?>
                <div class="message-erreur"><?php echo htmlspecialchars($erreur); ?></div>
            <?php endif; ?>

            <!-- Filtres -->
            <div class="filtres-container">

                <form method="GET" action="" class="filtres-form">

                    <div class="form-row">

                        <div class="form-group">
                            
                            <label><i class="fas fa-calendar"></i> Date</label>
                            <input type="date" name="date" value="<?php echo htmlspecialchars($dateFiltre); ?>">

                        </div>

                        <div class="form-group">

                            <label><i class="fas fa-user"></i> Caissier</label>

                            <select name="caissier">

                                <option value="">Tous</option>

                                <?php foreach ($caissiers as $caissier): ?>

                                    <option value="<?php echo htmlspecialchars($caissier['identifiant']); ?>" 

                                        <?php echo $caissierFiltre === $caissier['identifiant'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($caissier['nom_complet']); ?>

                                    </option>

                                <?php endforeach; ?>

                            </select>

                        </div>

                        <div class="form-group">

                            <button type="submit" class="btn-filtrer"><i class="fas fa-filter"></i> Filtrer</button>
                            <a href="historique.php" class="btn-reinitialiser"><i class="fas fa-undo"></i> Réinitialiser</a>

                        </div>

                    </div>

                </form>

            </div>

            <!-- Liste des factures -->
            <?php if (empty($factures)): ?>

                <div class="message-erreur">
                    <i class="fas fa-info-circle"></i> Aucune facture trouvée.
                </div>

            <?php else: ?>

                <div style="overflow-x: auto;">

                    <table class="table-factures">

                        <thead>

                            <tr>

                                <th><i class="fas fa-hashtag"></i> N° Facture</th>
                                <th><i class="fas fa-calendar"></i> Date</th>
                                <th><i class="fas fa-clock"></i> Heure</th>
                                <th><i class="fas fa-user"></i> Caissier</th>
                                <th><i class="fas fa-chart-line"></i> Total HT (CDF)</th>
                                <th><i class="fas fa-percent"></i> TVA (CDF)</th>
                                <th><i class="fas fa-check-circle"></i> Total TTC (CDF)</th>
                                <th><i class="fas fa-eye"></i> Action</th>

                            </tr>

                        </thead>

                        <tbody>

                            <?php foreach ($factures as $facture): ?>

                                <?php
                                    // Récupérer le nom du caissier
                                    $nomCaissier = 'ECKOMELA ELONGA';

                                    foreach ($utilisateurs as $u) 
                                    {

                                        if ($u['identifiant'] === $facture['caissier']) 
                                        {

                                            $nomCaissier = $u['nom_complet'];
                                            break;

                                        }

                                    }

                                ?>
                                <tr>

                                    <td><?php echo htmlspecialchars($facture['id_facture']); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($facture['date'])); ?></td>
                                    <td><?php echo $facture['heure']; ?></td>
                                    <td><?php echo htmlspecialchars($nomCaissier); ?></td>
                                    <td><?php echo number_format($facture['total_ht'], 0, ',', ' '); ?></td>
                                    <td><?php echo number_format($facture['tva'], 0, ',', ' '); ?></td>
                                    <td><?php echo number_format($facture['total_ttc'], 0, ',', ' '); ?></td>

                                    <td>
                                        <a href="afficher-facture.php?id=<?php echo urlencode($facture['id_facture']); ?>" class="btn-detail" title="Voir la facture">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>
                
                <!-- Statistiques -->
                <?php 

                    $totalVentes = array_sum(array_column($factures, 'total_ttc'));
                    $nbFactures = count($factures);

                ?>

                <div class="statistiques">

                    <div class="stat-card">

                        <i class="fas fa-receipt"></i>
                        <div class="stat-info">

                            <span class="stat-valeur"><?php echo $nbFactures; ?></span>
                            <span class="stat-label">Factures</span>

                        </div>

                    </div>

                    <div class="stat-card">

                        <i class="fas fa-money-bill-wave"></i>

                        <div class="stat-info">

                            <span class="stat-valeur"><?php echo number_format($totalVentes, 0, ',', ' '); ?> CDF</span>
                            <span class="stat-label">Chiffre d'affaires</span>

                        </div>

                    </div>

                </div>

            <?php endif; ?>

        </div>

    </body>

</html>