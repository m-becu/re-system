<?php // Par défaut, PHP va automatiquement chercher un fichier nommé `index.php` à la racine de notre projet, nous nous servirons donc de se fichier comme page d'accueil.
/** 
    Fonctionnalités à implémenter:
    - Consulter les produits du site même en mode invité (non-connecté)
    * Cliquer sur un lien d'inscription
    * Cliquer sur un lien de connexion
    - Si connecté, de cliquer sur un lien de déconnexion
    - Si connecté, de consulter ses recommandations

    La page doit également permettre à un administrateur du site de se connecter, et d'accèder aux formulaires CRUD pour les produits. 
*/
session_start();
require_once("./functions.php"); // On récupère les fonctions de notre fichier.
$connexion = connexion_bdd(); // Puis on récupère la connexion à la base.

// On regarde si une action à été entreprise
if (isset($_GET["action"])) {
    switch ($_GET["action"]) {
        case 'login':
            try {
                // Dans ce cas, une demande de connexion à été formulée
                // On vérifie que nous avons le couple login/motdepasse du client
                if (isset($_POST["username"]) && isset($_POST["password"])) {
                    // Si tel est le cas, nous pouvons alors faire une requête à la base pour connecter le client
                    // Essayons d'abord de trouver ce client dans la base:
                    $user = trouver_utilisateur($_POST["username"]);
                    if ($user) {
                        // Si on arrive ici, c'est que l'utilisateur existe
                        // On va donc maintenant vérifier son mot de passe
                        if (password_verify($_POST["password"], $user["passw"])) {
                            // On enregistre les informations dans la variable session de PHP
                            $_SESSION['admin'] = $user['admin'];
                            $_SESSION['login'] = $user['login'];
                            $_SESSION['id'] = $user['id'];
                
                            $user["passw"] = null;
                        }
                    }
                }
            } catch (Exception $e) {
                die('Erreur : '.$e->getMessage());
            }
            break;
        
        case 'register':
            try {
                // Dans ce cas, une demande d'inscription à été formulée
                // On vérifie qu'on a bien les données
                if (isset($_POST["username"]) && isset($_POST["password"]) && isset($_POST["password2"])) {
                    // On vérifie que le client à bien écrit deux fois le même mot de passe
                    if ($_POST["password"] === $_POST["password2"]) {
                        // On vérifie qu'aucun client n'éxiste avec ce pseudonyme dans la base
                        if (!trouver_utilisateur(isset($_POST["username"]))) {
                            // On hash le mot de passe et on sauvegarde le tout dans la base
                            $hash = password_hash($_POST["password"], PASSWORD_DEFAULT);
                            $sql = "INSERT INTO users (login, passw) VALUES (:login, :passw)";
                            $res = $connexion->prepare($sql)->execute(array(
                                'login' => $_POST['username'],
                                'passw' => $hash
                            ));
                            // Si tout c'est bien passé, on connecte le nouvel inscrit
                            if ($res) {
                                $user = trouver_utilisateur($_POST["username"]);

                                $_SESSION['admin'] = $user['admin'];
                                $_SESSION['login'] = $user['login'];
                                $_SESSION['id'] = $user['id'];

                                $user["passw"] = null;
                            }
                
                        }
                    }
                }
            } catch (Exception $e) {
                die('Erreur : '.$e->getMessage());
            }
        
        default:
            # code...
            break;
    }
}

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
        <fieldset>
            <legend> <a href="?show=login">Connexion</a> / <a href="?show=register">Inscription</a> </legend>
            <?php
            if (isset($_GET["show"]) && $_GET["show"] === "register") {
            ?>
                <!-- Formulaire d'inscription -->
                <form action="?action=register" method="post">
                <label for="iusername">Nom d'utilisateur</label>
                    <input id="iusername" name="username" type="text">
                    <label for="ipassword">Mot de passe</label>
                    <input id="ipassword" name="password" type="password">
                    <label for="ipassword2">Vérifiez le mot de passe</label>
                    <input id="ipassword2" name="password2" type="password">
                    <input type="submit" value="Envoyer">
                </form>
            <?php
            } else { ?>
                <!-- Formulaire de connexion -->
                <form action="?action=login" method="post">
                    <label for="iusername">Nom d'utilisateur</label>
                    <input id="iusername" name="username" type="text">
                    <label for="ipassword">Mot de passe</label>
                    <input id="ipassword" name="password" type="password">
                    <input type="submit" value="Envoyer">
                </form>
            <?php
            }
            ?>
        </fieldset>
        <p>Etat de la connexion: <b>
            <?php 
                if (isset($_SESSION['id'])) echo("Client");
                else if ($connexion) echo("BDD");
                else echo("Erreur");
            ?>
        </b></p>
    </body>
</html>