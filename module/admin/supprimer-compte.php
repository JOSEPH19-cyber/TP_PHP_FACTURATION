<?php

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/fonctions-auth.php';

// Vérifier que l'utilisateur est Super Admin
if (!estConnecte() || $_SESSION['user']['role'] !== 'Super Administrateur') 
{

    header('Location: ../../index.php');

    exit('Accès non autorisé');

}

$identifiant = $_GET['identifiant'] ?? '';
$message = '';
$erreur = '';

// Vérifier qu'un identifiant a été fourni
if (empty($identifiant)) 
{

    header('Location: gestion-comptes.php?erreur=' . urlencode('Aucun utilisateur spécifié'));

    exit();

}

// Empêcher la suppression de son propre compte
if ($identifiant === $_SESSION['user']['identifiant']) 
{

    header('Location: gestion-comptes.php?erreur=' . urlencode('Vous ne pouvez pas supprimer votre propre compte'));

    exit();

}

// Récupérer tous les utilisateurs
$utilisateurs = getUtilisateurs();
$trouve = false;
$nouvelleListe = [];


foreach ($utilisateurs as $user) 
{

    if ($user['identifiant'] === $identifiant) 
    {

        $trouve = true;
        $nomSupprime = $user['nom_complet'];
        continue;
    }

    $nouvelleListe[] = $user;

}

if (!$trouve) 
{

    header('Location: gestion-comptes.php?erreur=' . urlencode('Utilisateur non trouvé'));
    exit();

}

// Sauvegarder la nouvelle liste
sauvegarderUtilisateurs($nouvelleListe);

// Rediriger avec message de succès
header('Location: gestion-comptes.php?message=' . urlencode('Utilisateur "' . $nomSupprime . '" supprimé avec succès'));

exit();

?>