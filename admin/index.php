<?php
// Utilisation des sessions
session_start();
// Cette page est réservée au administrateur, si aucun client n'est connecté ou que le client connecté n'est pas un administrateur alors on redirige immédiatement le client vers la page d'accueil.
if (!isset($_SESSION["admin"]) && !isset($_SESSION["id"])) header("Location: /"); // Aucun client connecté
if ($_SESSION["admin"] !== 1) header("Location: /"); // Client pas un admin
// Autrement, on récupère les fonctions.
require_once("../functions.php");
// Et on se connecte à la base.
$connexion = connexion_bdd();
// Tout comme le gestionnaire d'actions ou d'erreurs, le gestionnaire d'infos nous permet d'afficher des messages informatifs au client, notamment lorsque l'on envoie notre nouveau titre dans la base.
if (isset($_GET["info"])) {
    switch ($_GET["info"]) {
        case 'success':
            ?>
            <p>Ajout réalisé avec succès!</p>
            <?php
            break;
        
        default:
            # code...
            break;
    }
}

if (isset($_GET["action"])) {
    switch ($_GET["action"]) {
        // Si on réussi à ajouter le nouveau titre, on relocalise pour informer l'utilisateur.
        case 'add':
            if (isset($_POST["title"]) && isset($_POST["artist"])) {
                // var_dump($_FILES);
                $sql = "INSERT INTO `songs` (title, artist, album, genre) VALUES (:title, :artist, :album, :genre)";
                $img = validation_image('album');

                $connexion->prepare($sql)->execute(array(
                    'title'  => $_POST['title'],
                    'artist' => $_POST['artist'],
                    'genre'  => $_POST['genre'],
                    'album'  => "images/".$img,
                ));
            }
            header("Location: /admin?info=success");
            break;
        
        default:
            // Par défaut on relocalise vers une URL propre.
            header("Location: /admin");
            break;
    }
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../styles/global.css">
    <title>Administration</title>
</head>
<body>
    <?php include_once("../components/navigation.php") ?>
    <div class="main">
        <h2>Page d'administration</h2>
        <fieldset>
            <legend>Formulaire d'ajout d'un morceau</legend>
            <form action="?action=add" method="post" enctype="multipart/form-data">
                <span>
                    <label for="ititle">Titre</label>
                    <input type="text" name="title" id="ititle">
                    <label for="iartist">Artiste</label>
                    <input type="text" name="artist" id="iartist">
                </span>
                <label for="ialbum">Pochette de l'album</label>
                <input type="file" name="album" id="ialbum">
                <label for="igenre">Genre</label>
                <select name="genre" id="igenre">
                    <option value="null">Sélectionnez un genre</option>
                    <option value="rock">Rock</option>
                    <option value="pop">Pop</option>
                    <option value="funk">Funk</option>
                    <option value="electro">Electro</option>
                    <option value="disco">Disco</option>
                    <option value="rap">Rap</option>
                </select>
                <input type="submit" value="Envoyer">
            </form>
        </fieldset>
    </div>
</body>
</html>