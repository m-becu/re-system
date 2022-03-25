<?php
session_start();
if (!isset($_SESSION["admin"]) && !isset($_SESSION["id"])) header("Location: /");
if ($_SESSION["admin"] !== 1) header("Location: /");

require_once("../functions.php");
$connexion = connexion_bdd();

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
            # code...
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