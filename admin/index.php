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
$sql = $connexion->query("SELECT id, title, artist, genre FROM `songs`");

$songs = $sql->fetchAll();

// Tout comme le gestionnaire d'actions ou d'erreurs, le gestionnaire d'infos nous permet d'afficher des messages informatifs au client, notamment lorsque l'on envoie notre nouveau titre dans la base.
if (isset($_GET["info"])) {
    switch ($_GET["info"]) {
        case 'success':
            ?>
            <div class="infos success">
                <p>Ajout réalisé avec succès!</p>
            </div>
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

        case 'modify':
            if (isset($_POST["title"]) && isset($_POST["artist"])) {
                $sql = "UPDATE songs SET title = :title, artist = :artist, album = :album, genre = :genre WHERE `songs`.`id` = :id;";
                $img = validation_image('album');

                $connexion->prepare($sql)->execute(array(
                    'title'  => $_POST['title'],
                    'artist' => $_POST['artist'],
                    'genre'  => $_POST['genre'],
                    'album'  => "images/".$img,
                ));
                header("Location: /admin?info=success");
            }
            break;
        
        case 'delete':
            if (isset($_GET["id"])) {
                $sql = "DELETE FROM `songs` WHERE `songs`.`id` = :id";
                $connexion->prepare($sql)->execute(array(
                    'id' => $_GET["id"],
                ));
            }
            header("Location: /admin");
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
        <div class="admin">
            <div class="add">
                <fieldset>
                    <legend>Formulaire d'édition d'un morceau</legend>
                    <?php if (isset($_GET["action"]) && isset($_GET["id"])) { 
                        // On retire 1 au nombre identifiant le titre car les tableaux PHP sont indexés à partir de 0
                        // Et les titres de la base sont indexés à partir de 1
                        // Donc la chanson d'id 3 est à la position 2 du tableau.
                        $modify = $songs[$_GET["id"]-1];
                    ?>
                    <form action="?action=modify" method="post" enctype="multipart/form-data">
                        <span>
                            <label for="ititle">Titre</label>
                            <input type="text" name="title" id="ititle" value="<?=$modify['title']?>">
                            <label for="iartist">Artiste</label>
                            <input type="text" name="artist" id="iartist" value="<?=$modify['artist']?>">
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
                        <input type="submit" value="Modifier">
                        <br>
                        <a href="/admin">Annuler la modification</a>
                    </form>
                    <?php } else { ?>
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
                    <?php } ?>
                </fieldset>
            </div>
            <div class="list">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Titre</th>
                            <th>Artiste</th>
                            <th>Genre</th>
                            <th>Modifier</th>
                            <th>Supprimer</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Ici pas de vérifications, à tout instant on affichera tous les titres sur la page d'accueil.
                        foreach ($songs as $i => $song) { ?>
                        <tr>
                            <td><?=$song['id']?></td>
                            <td><?=$song['title']?></td>
                            <td><?=$song['artist']?></td>
                            <td><?=$song['genre']?></td>
                            <td><a href="?action=modify&id=<?=$song['id']?>">📝</a></td>
                            <td><a href="?action=delete&id=<?=$song['id']?>">❌</a></td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>