<?php

function getProduits() 
{

    $fichier = __DIR__ . '/../data/produits.json';

    if (!file_exists($fichier)) 
    {

        return [];

    }

    $contenu = file_get_contents($fichier);

    return json_decode($contenu, true) ?: [];

}


function sauvegarderProduits($produits) 
{

    $fichier = __DIR__ . '/../data/produits.json';

    file_put_contents($fichier, json_encode($produits, JSON_PRETTY_PRINT));

}


function getProduitByCodeBarre($codeBarre) 
{

    $produits = getProduits();

    foreach ($produits as $produit) 
    {

        if ($produit['code_barre'] === $codeBarre) 
        {

            return $produit;

        }

    }

    return null;

}


function ajouterProduit($codeBarre, $nom, $prixHT, $dateExpiration, $quantite) 
{

    $produits = getProduits();
    
    // Vérifier si le code-barres existe déjà
    if (getProduitByCodeBarre($codeBarre)) 
    {

        return false;

    }
    
    $nouveauProduit = 
    [

        "code_barre" => $codeBarre,
        "nom" => ucfirst($nom),
        "prix_unitaire_ht" => (float)$prixHT,
        "date_expiration" => $dateExpiration,
        "quantite_stock" => (int)$quantite,
        "date_enregistrement" => date("Y-m-d")

    ];
    
    $produits[] = $nouveauProduit;

    sauvegarderProduits($produits);

    return true;

}


function mettreAJourStock($codeBarre, $nouvelleQuantite) 
{

    $produits = getProduits();

    foreach ($produits as $key => $produit) 
    {

        if ($produit['code_barre'] === $codeBarre) 
        {

            $produits[$key]['quantite_stock'] = (int)$nouvelleQuantite;

            sauvegarderProduits($produits);

            return true;

        }

    }

    return false;

}


function estExpire($dateExpiration) 
{

    return strtotime($dateExpiration) < strtotime(date('Y-m-d'));

}


function decrementerStock($codeBarre, $quantiteVendue) 
{

    $produits = getProduits();

    foreach ($produits as $key => $produit) 
    {

        if ($produit['code_barre'] === $codeBarre) 
        {

            $nouveauStock = $produit['quantite_stock'] - $quantiteVendue;

            if ($nouveauStock >= 0) 
            {

                $produits[$key]['quantite_stock'] = $nouveauStock;

                sauvegarderProduits($produits);

                return true;

            }

            return false;

        }

    }

    return false;
    
}

?>