<?php // Par défaut, PHP va automatiquement chercher un fichier nommé `index.php` à la racine de notre projet, nous nous servirons donc de se fichier comme page d'accueil.
/** 
    Fonctionnalités à implémenter:
    - Consulter les produits du site même en mode invité (non-connecté)
    - Cliquer sur un lien d'inscription
    - Cliquer sur un lien de connexion
    - Si connecté, de cliquer sur un lien de déconnexion
    - Si connecté, de consulter ses recommandations

    La page doit également permettre à un administrateur du site de se connecter, et d'accèder aux formulaires CRUD pour les produits. 
*/

require_once("./functions.php"); // On récupère les fonctions de notre fichier.
$connexion = connexion_bdd(); // Puis on récupère la connexion à la base.

?>
<!DOCTYPE html>
<html lang="fr">
    <head>
        <!-- On utiliser le charset universel pour afficher tous types de caractères -->
        <meta charset="UTF-8">
        <!-- Balise de compatibilité Microsoft Edge et Internet Explorer -->
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <!--
            Pour comprendre l'utilité de cette ligne: 
            https://www.pierre-giraud.com/html-css-apprendre-coder-cours/meta-viewport/
        -->
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Accueil</title>
    </head>
    <body>
        <!-- Ce code affichera en gras le mot "Connectée" si la connexion à la base est réussie. -->
        <p>Etat de la base: <b>
            <?php 
                if ($connexion) echo("Connectée");
            ?>
        </b></p>
    </body>
</html>