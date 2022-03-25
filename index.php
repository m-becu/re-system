<?php // Par défaut, PHP va automatiquement chercher un fichier nommé `index.php` à la racine de notre projet, nous nous servirons donc de se fichier comme page d'accueil.
/** 
    * Fonctionnalités à implémenter:
    * Consulter les produits du site même en mode invité (non-connecté)
    * Cliquer sur un lien d'inscription
    * Cliquer sur un lien de connexion
    * Si connecté, de cliquer sur un lien de déconnexion
    * Si connecté, de consulter ses recommandations

    * La page doit également permettre à un administrateur du site de se connecter, et d'ajouter des produits.
*/

// On utilisera les session sur notre site pour la connexion utilisateur.
// On utilise donc cette fonction pour spécifier cela à PHP.
session_start();
require_once("./functions.php"); // On récupère les fonctions de notre fichier.

$connexion = connexion_bdd(); // Puis on récupère la connexion à la base.
$sql = $connexion->query("SELECT * FROM `songs`"); // Ici on fait directement une requête à la base pour obtenir les titres et les afficher à l'accueil.

// Gestion des erreurs
// Cela nous permet de rediriger le client vers la page principale et d'y afficher un message personnalisé pour les erreurs.
if (isset($_GET["error"])) {
    switch ($_GET['error']) {
        // Les erreurs présentées ici sont liées à la fonction "validation_image" qui peut retourner un certains nombre d'erreurs.
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
                // Enfin on redirige le client vers une URL propre pour éviter de refaire une demande de connexion par simple refraichissement de page.
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
                                // On place les infos utilisateurs dans la variable session, ce qui nous permet ensuite d'utiliser les infos clients partout sur notre site, tant que la session est active.
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
            // On détruit la session et on vide toutes les valeurs de la variable session.
            // Puis on renvoie le client à la page d'accueil, le client est déconnecté.
            session_destroy();
            $_SESSION = array(); // array() utilisé ici pour dire "tableau vide"
            header("Location: /");
            break;
        
        case 'like':
            if (isset($_GET["id"]) && isset($_SESSION["id"])) {
                // Ici on vérifie simplement que l'utilisateur est connecté pour lui faire aimer un titre.
                ajouter_favori( (int)$_SESSION["id"], (int)$_GET["id"] );
                header("Location: /");
            }
            break;
        
        default:
            // Par défaut, on nettoie l'URL de toutes les variables $_GET qui peuvent s'y trouver.
            header("Location: /");
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
        <!-- Importation d'une feuille de style pour améliorer le contenu du site -->
        <link rel="stylesheet" href="styles/global.css">
        <title>Accueil</title>
    </head>
    <body>
        <?php 
            // On vient chercher le composant de navigation ici
            include_once("components/navigation.php"); 

            if (isset($_SESSION["id"])) {
                // On vérifie qu'un utilisateur est connecté avant de chercher les recommandations
                $recoms = generer_recommandations($_SESSION["id"]);
                // La fonction ci-dessus renvoyant un tableau de chansons, on va vérifier que celui-ci n'est pas vide avant d'afficher quoi que ce soit.
                if (count($recoms) > 0) { ?>
                    <div class="recoms songs">
                        <h2>Vous pourriez aimer</h2>
                        <ul> <?php
                            // Pour chaque chanson dans le tableau de recommandation, on affiche les pochettes:
                            foreach ($recoms as $i => $recom) {
                                ?>
                                <li>
                                    <a href="?action=like&id=<?=$recom['id']?>">
                                        <img src="../<?=$recom['album']?>" alt="pochette">
                                    </a>
                                    <h3><?=$recom['title']?></h3>
                                    <h4><?=$recom['artist']?></h4>
                                </li>
                            <?php } ?>
                        </ul>
                    </div>
                <?php
                }
                // Même chose ici pour les favoris. Pour une raison que j'ignore ce tableau contient toujours une entrée vide qui monte sa taille à un minimum de 1, on vérifie donc que la taille est >1 pour s'assurer que le tableau est bien rempli.
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
                // Ici pas de vérifications, à tout instant on affichera tous les titres sur la page d'accueil.
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
                    </a>
                    <h3><?=$title?></h3>
                    <h4><?=$artist?></h4>
                </li>
                <?php } ?>
            </ul>
        </div>
    </body>
</html>