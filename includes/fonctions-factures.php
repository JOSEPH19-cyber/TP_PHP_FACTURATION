<?php

require_once __DIR__ . '/fonctions-produits.php';

// Chemin du fichier JSON des factures
if (!defined('CHEMIN_DATA')) 
{

    define('CHEMIN_DATA', __DIR__ . '/../data/');

}

define('FICHIER_FACTURES', CHEMIN_DATA . 'factures.json');

function getFactures() 
{

    if (!file_exists(FICHIER_FACTURES)) 
    {

        return [];

    }
    
    $contenu = file_get_contents(FICHIER_FACTURES);

    return json_decode($contenu, true) ?? [];

}

function sauvegarderFactures($factures) 
{

    return file_put_contents(FICHIER_FACTURES, json_encode($factures, JSON_PRETTY_PRINT));

}

function genererNumeroFacture()
{
    
    $factures = getFactures();
    $date = date('Ymd');
    $compteur = 1;
    
    // Trouver le dernier compteur pour aujourd'hui
    foreach ($factures as $facture) 
    {

        if (strpos($facture['id_facture'], "FAC-{$date}-") === 0) 
        {

            $num = (int)substr($facture['id_facture'], -3);

            if ($num >= $compteur) 
            {

                $compteur = $num + 1;

            }

        }

    }
    
    return "FAC-{$date}-" . str_pad($compteur, 3, '0', STR_PAD_LEFT);

}


function calculerSousTotalHT($prixHT, $quantite) 
{

    return $prixHT * $quantite;

}


function calculerTotalHT($articles) 
{

    $total = 0;

    foreach ($articles as $article) 
    {

        $total += $article['sous_total_ht'];

    }

    return $total;

}


function calculerTVA($totalHT) 
{

    return $totalHT * TVA;

}


function calculerTotalTTC($totalHT) 
{

    $tva = calculerTVA($totalHT);
    return $totalHT + $tva;

}

function creerFacture($caissier, $articles, $totalHT, $tva, $totalTTC) 
{

    // Vérifier que le stock est suffisant avant de créer la facture
    foreach ($articles as $article) 
    {

        $produit = getProduitByCodeBarre($article['code_barre']);

        if (!$produit || $produit['quantite_stock'] < $article['quantite']) 
        {

            return false;

        }

    }
    
    // Créer la facture 
    $facture = 
    [

        'id_facture' => genererNumeroFacture(),
        'date' => date('Y-m-d'),
        'heure' => date('H:i:s'),
        'caissier' => $caissier,
        'articles' => $articles,
        'total_ht' => $totalHT,
        'tva' => $tva,
        'total_ttc' => $totalTTC

    ];
    
    // Sauvegarder la facture
    $factures = getFactures();
    $factures[] = $facture;

    if (!sauvegarderFactures($factures)) 
    {

        return false;

    }
    
    // Mettre à jour le stock
    foreach ($articles as $article) 
    {

        $produit = getProduitByCodeBarre($article['code_barre']);
        $nouveauStock = $produit['quantite_stock'] - $article['quantite'];

        mettreAJourStock($article['code_barre'], $nouveauStock);

    }
    
    return $facture;
}


function getFactureById($idFacture) 
{

    $factures = getFactures();

    foreach ($factures as $facture) 
    {

        if ($facture['id_facture'] === $idFacture) 
        {

            return $facture;

        }

    }

    return null;

}

function getFacturesByCaissier($caissier) 
{

    $factures = getFactures();
    $resultats = [];
    
    foreach ($factures as $facture) {
        if ($facture['caissier'] === $caissier) {
            $resultats[] = $facture;
        }
    }
    
    return $resultats;

}


function getFacturesByDate($date) 
{

    $factures = getFactures();
    $resultats = [];
    
    foreach ($factures as $facture) 
    {

        if ($facture['date'] === $date) 
        {

            $resultats[] = $facture;

        }

    }
    
    return $resultats;

}


function getChiffreAffaires($dateDebut, $dateFin) 
{

    $factures = getFactures();
    $totalHT = 0;
    $totalTTC = 0;
    $nombreFactures = 0;
    
    foreach ($factures as $facture) 
    {

        if ($facture['date'] >= $dateDebut && $facture['date'] <= $dateFin) 
        {

            $totalHT += $facture['total_ht'];
            $totalTTC += $facture['total_ttc'];
            $nombreFactures++;

        }

    }
    
    return 
    [

        'total_ht' => $totalHT,
        'total_ttc' => $totalTTC,
        'tva' => $totalTTC - $totalHT,
        'nombre_factures' => $nombreFactures

    ];

}


function getFacturesByDateRange($dateDebut, $dateFin) 
{

    $factures = getFactures();
    $resultats = [];
    
    foreach ($factures as $facture) 
    {

        if ($facture['date'] >= $dateDebut && $facture['date'] <= $dateFin) 
        {

            $resultats[] = $facture;

        }

    }
    
    return $resultats;
    
}
