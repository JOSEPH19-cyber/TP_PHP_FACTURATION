<?php

function getUtilisateurs() 
{

    $fichier = __DIR__ . '/../data/utilisateurs.json';

    if (!file_exists($fichier)) 
    {
        return [];
    }

    $contenu = file_get_contents($fichier);

    return json_decode($contenu, true) ?: [];

}


function getUtilisateurCourant() 
{

    if (!estConnecte()) 
    {

        return null;

    }
    
    $identifiant = $_SESSION['user_identifiant'] ?? null;
    
    if (!$identifiant) 
    {

        return null;

    }
    
    $utilisateurs = getUtilisateurs();
    
    foreach ($utilisateurs as $utilisateur) 
    {

        if ($utilisateur['identifiant'] === $identifiant) 
        {

            return $utilisateur;

        }
        
    }
    
    return null;
}

function sauvegarderUtilisateurs($utilisateurs)
{
    
    $fichier = __DIR__ . '/../data/utilisateurs.json';

    file_put_contents($fichier, json_encode($utilisateurs, JSON_PRETTY_PRINT));

}

function verifierIdentifiants($identifiant, $motDePasse) 
{
    $utilisateurs = getUtilisateurs();
    
    foreach ($utilisateurs as $utilisateur) 
    {

        if ($utilisateur['identifiant'] === $identifiant && $utilisateur['actif'] === true) 
        {

            if (password_verify($motDePasse, $utilisateur['mot_de_passe'])) 
            {

                return $utilisateur;

            }

        }

    }

    return false;

}

function estConnecte()
{

    return isset($_SESSION['user']);

}


function aRole($role) 
{

    if (!estConnecte()) 
    {

        return false;

    }
    
    return $_SESSION['user']['role'] === $role;
}


function aUnDeCesRoles($roles) 
{

    if (!estConnecte()) 
    {

        return false;

    }

    return in_array($_SESSION['user']['role'], $roles);

}

?>