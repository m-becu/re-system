<?php // Par défaut, PHP va automatiquement chercher un fichier nommé `index.php` à la racine de notre projet, nous nous servirons donc de se fichier comme page d'accueil.
/** 
    Fonctionnalités à implémenter:
    - Consulter les produits du site même en mode invité (non-connecté)
    * Cliquer sur un lien d'inscription
    * Cliquer sur un lien de connexion
    * Si connecté, de cliquer sur un lien de déconnexion
    - Si connecté, de consulter ses recommandations

    La page doit également permettre à un administrateur du site de se connecter, et d'accèder aux formulaires CRUD pour les produits. 
*/
session_start();
require_once("./functions.php"); // On récupère les fonctions de notre fichier.

$connexion = connexion_bdd(); // Puis on récupère la connexion à la base.
$sql = $connexion->query("SELECT * FROM `songs`");

// Gestion des erreurs
if (isset($_GET["error"])) {
    switch ($_GET['error']) {
        case 'form_type':
            $error = "Typage formulaire invalide.";
            break;

        case 'image_ext':
            $error = "Extension de fichier invalide.\nImage non sauvée.";
            break;
        
        default:
            $error = $_GET['error'];
            break;
    }
    ?>
    <span id="errors">
        <p>Une erreur est survenue : <?=$error?></p>
    </span>
    <?php
}

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
                header("Location: /");

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
                header("Location: /");

            } catch (Exception $e) {
                die('Erreur : '.$e->getMessage());
            }
            break;
        
        case 'logout':
            session_destroy();
            $_SESSION = array();
            header("Location: /");
            break;
        
        case 'like':
            if (isset($_GET["id"]) && isset($_SESSION["id"])) {
                ajouter_favori( (int)$_SESSION["id"], (int)$_GET["id"] );
                header("Location: /");
            }
            break;
        
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
        <link rel="stylesheet" href="styles/global.css">
        <title>Accueil</title>
    </head>
    <body>
        <?php 
            include_once("components/navigation.php"); 
            if (isset($_SESSION["id"])) {

                $recoms = generer_recommandations($_SESSION["id"]);
                if (count($recoms) > 0) { ?>
                    <div class="recoms songs">
                        <h2>Vous pourriez aimer</h2>
                        <ul> <?php
                            foreach ($recoms as $i => $recom) {
                                ?>
                                <li>
                                    <a href="?action=like&id=<?=$recom['id']?>">
                                        <img src="../<?=$recom['album']?>" alt="pochette">
                                        <b>💖</b>
                                    </a>
                                    <h3><?=$recom['title']?></h3>
                                    <h4><?=$recom['artist']?></h4>
                                </li>
                            <?php } ?>
                        </ul>
                    </div>
                <?php
                }

                $favs = recuperer_favoris($_SESSION["id"]);
                if (count($favs) > 1) { ?>
                    <div class="favs songs">
                        <h2>Vos favoris</h2>
                        <ul> <?php
                            foreach ($favs as $i => $fav) { 
                                if ($i !== 0) { ?>
                                <li>
                                    <a href="?action=like&id=<?=$fav['id']?>">
                                        <img src="../<?=$fav['album']?>" alt="pochette">
                                        <b>💖</b>
                                    </a>
                                    <h3><?=$fav['title']?></h3>
                                    <h4><?=$fav['artist']?></h4>
                                </li>
                            <?php }
                            }
                        ?>
                        </ul>
                    </div>
                <?php
                }
            }
        ?>
        <div class="songs">
            <h2>Tous les titres</h2>
            <ul> <?php
                while ($songs = $sql->fetch()) {
                    $id = $songs['id'];
                    $title = $songs['title'];
                    $artist = $songs['artist'];
                    $album = $songs['album'];
                    $genre = $songs['genre'];
                ?>
                <li>
                    <a href="?action=like&id=<?=$id?>">
                        <img src="../<?=$album?>" alt="pochette">
                        <b>💖</b>
                    </a>
                    <h3><?=$title?></h3>
                    <h4><?=$artist?></h4>
                </li>
                <?php } ?>
            </ul>
        </div>
    </body>
</html>