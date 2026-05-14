INSTRUCTIONS DE DÉPLOIEMENT LOCAL
=================================

1. PRÉREQUIS
   - Serveur Web local (Apache recommandé)
   - PHP 7.4 ou supérieur (avec les extensions suivantes activées) :
   - Navigateur récent avec support de la caméra (pour le scan de codes-barres via ZXingJS)
   - Connexion HTTPS requise pour la caméra (ou utilisation de localhost)

2. INSTALLATION

   a. Télécharger le projet
      - Clôner l'intégralité du dossier du projet (TP_PHP_FACTURATION) dans la racine de votre serveur local à partir du lien github fournit à la fin du fichier:
           * WAMP : C:\wamp64\www\
           * XAMPP : C:\xampp\htdocs\
           * Linux : /var/www/html/

   b. Renommer le dossier (optionnel mais recommandé)
      - Par exemple : "facturation" pour simplifier l’URL.

   c. Vérifier l’arborescence du projet
      Le projet doit contenir les dossiers suivants :
        config/ auth/ modules/ data/ includes/ assets/ rapports/


3. ACCÈS À L’APPLICATION

   Une fois le serveur démarré (Apache), accédez à l’application via :
   http://localhost/facturation/auth/login.php

   Si vous n’avez pas renommé le dossier, utilisez :
   http://localhost/TP_PHP_FACTURATION-MAIN/auth/login.php

4. COMPTES DE TEST (MOTS DE PASSE HACHÉS)

   Les mots de passe sont hachés avec password_hash() comme spécifié dans le rapport. Voici les comptes préchargés :

   Identifiant : admin
   Mot de passe : password
   Rôle : Super Administrateur
   Droits : accès total (gestion utilisateurs, produits, factures, rapports)

   Identifiant : manager
   Mot de passe : password
   Rôle : Manager
   Droits : gestion des produits, facturation, rapports

   Identifiant : caissier
   Mot de passe : password
   Rôle : caissier
   Droits : uniquement facturation (création de factures)

6. UTILISATION DE BASE

   - Après connexion, le tableau de bord s’affiche.
   - Menu disponible selon votre rôle :
        * Caissier : Nouvelle facture, Historique
        * Manager : + Gestion produits, Rapports
        * Super Admin : + Gestion utilisateurs

   - Pour scanner un code-barres :
        * Allez dans "Nouvelle facture"
        * Cliquez sur "Activer caméra"
        * Placez le code-barres devant la caméra (ZXingJS détecte automatiquement)

7. DÉPANNAGE (PROBLÈMES FRÉQUENTS)

   a. La caméra ne s’active pas ou ne scanne pas
      - Utilisez localhost ou HTTPS (les navigateurs récents bloquent la caméra en HTTP simple)
      - Vérifiez que vous avez autorisé l’accès à la caméra dans le navigateur

   b. Erreur "include_once() : failed to open stream"
      - Vérifiez que les chemins d’inclusion utilisent bien la constante RACINE définie dans config/config.php
      - Assurez-vous que le fichier config.php est bien présent dans le dossier config/

   c. Les modifications ne sont pas sauvegardées
      - Vérifiez les droits en écriture sur le dossier data/ (voir section 3)

   d. Erreur "JSON_ERROR_SYNTAX"
      - Un fichier JSON est peut-être corrompu. Supprimez-le et redémarrez (les fichiers vides seront recréés automatiquement)

8. NOTES COMPLÉMENTAIRES

   - Pas de base de données MySQL requise (tout est en JSON)
   - Le système utilise uniquement PHP procédural (pas de framework)
   - La lecture des codes-barres est assurée par ZXingJS (intégré dans assets/js/scanner.js)
   - La TVA est fixée à 18% conformément au cahier des charges

9. LIEN UTILE

   Dépôt GitHub complet : https://github.com/JOSEPH19-cyber/TP_PHP_FACTURATION

=================================
Document rédigé selon l’architecture décrite dans le rapport LaTeX.