<?php // Fichier `functions.php`

// Fonction de connexion qui renvoie un objet PDO connecté à la BDD
function connexion_bdd() {
    // Création d'une variable pour la connexion
    $bdd = null;
    // Adresse de connexion local pour l'exercice
    $host = '127.0.0.1';
    // Nom de notre base de données
    $db = 'recsystem';
    // Utilisateur et mot de passe par défaut
    $user = 'root';
    $pass = '';
    // On utilise la charset universel utf8
    $charset = 'utf8mb4';

    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";

    $options = [
        // Cette option permet d'arrêter l'exécution du script et renvoie immédiatement une exception.
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        // Cette option contrôle comment les données sont renvoyées après une requête dans la base.
        // Ici on demande à retourner un tableau indexé par le nom de la colonne.
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        // La préparation est une fonctionnalité de sécurité destinée à empêcher les pirates de tenter des attaques par script.
        // Lorsque l'on reçoit une donnée écrite par un client, on se méfie de ces données.
        // Ici, on laisse MySQL s'occuper de la préparation pour avoir un meilleur retour des erreurs.
        PDO::ATTR_EMULATE_PREPARES   => false
    ];

    // Le bloc try catch permet de récupérer les exception émise par notre tentative de connexion.
    try {
        // La variable BDD contient le nouvel objet PHP PDO qui contient une connexion à la base de donnée.
        $bdd = new PDO($dsn, $user, $pass, $options);
    } catch (Exception $e) {
        // On arrête immédiatement l'exécution du script et on retourne le message d'erreur.
        die('Erreur : '.$e->getMessage());
    }

    // Si tout c'est bien passé, la variable BDD contient une connexion.
    // Dans le cas contraire c'est la variable 'null' nulle qui est renvoyée.
    return $bdd;
}

// On cherche un client dans la base à partir de son pseudo
function trouver_utilisateur($login) {
    // On ouvre la connexion
    $db = connexion_bdd();
    // La requête SQL cherche toutes les informations d'un utilisateur avec le pseudo spécifié
    $sql = "SELECT * FROM users WHERE login = ?";
    // On prépare la requête pour des questions de sécurité
    $req = $db->prepare($sql);

    $req->execute([ $login ]);
    $user = $req->fetch();

    return $user;
}

// Cette fonction gère la validation des images envoyées lorsque l'on ajoute un titre.
// C'est à dire qu'on vérifie que l'image n'est pas trop grosse, qu'elle ne contient pas d'extensions étranges...
function validation_image($name) {
    try {
        /**
         * J'ai eu pas mal de difficultées pour faire fonctionner
         * la sauvegarde d'image, ce script fonctionne bien et gère les erreurs.
         * 
         * Il y a encore beaucoup de failles de sécurité qui ne sont
         * pas comblée ici, voir commentaires :
         * https://www.php.net/manual/en/features.file-upload.php
         */

        // Lorsqu'on utilise l'input de type "file" en HTML, PHP récupère l'image envoyée dans la variable $_FILES, ce qui nous permet de la valider ici.
        // Si on arrive pas à accèder aux variables 'error' du fichier c'est que celui-ci est invalide, car cette variable est toujours mise en place, même si l'erreur qu'elle contient n'en est pas une.
        if (!isset($_FILES[$name]['error']) || is_array($_FILES[$name]['error'])) {
            throw new RuntimeException('Paramètres invalides.');
        }

        switch ($_FILES[$name]['error']) {
            // Comme expliqué plus haut, la variable erreur peut aussi contenir l'information que tout vas bien
            // C'est le cas juste ici, on dans le cas où erreur vaut UPLOAD_ERR_OK, alors on peut continuer.
            // Les autres cas sont des exemple des erreurs les plus fréquentes, que l'on va se charger de traiter
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_NO_FILE:
                throw new RuntimeException('Aucun fichier envoyé.');
            case UPLOAD_ERR_INI_SIZE:
                // L'erreur UPLOAD_ERR_INI_SIZE mérite qu'on l'explique car très peu fréquente:
                // Elle ne prend sont sens que lorsque la variable POST_MAX_SIZE (taille maximale d'une requête POST) est plus grande que la variable UPLOAD_MAX_FILESIZE (taille maximale d'un upload unique de fichier par ex).
            case UPLOAD_ERR_FORM_SIZE:
                throw new RuntimeException('Limite de taille de fichier dépassée.');
            default:
                throw new RuntimeException('Erreurs inconnues.');
                break;
        }

        // Test taille de fichier une seconde fois
        // Potentiellement inutile
        
        if ($_FILES[$name]['size'] > 1000000) {
            throw new RuntimeException('Limite de taille de fichier dépassée.');
        }

        // Ne jamais faire confiance à la valeur 'MIME' d'un fichier
        // On vérifie le type nous-même
        // Cette vérification est totalement insuffisante car on peux facilement tricher.

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        if (false === $ext = array_search(
            $finfo->file($_FILES[$name]['tmp_name']),
            array(
                'jpg' => 'image/jpeg',
                'png' => 'image/png',
                'gif' => 'image/gif',
            ),
            true
        )) {
            throw new RuntimeException('Format de fichier invalide');
        }

        // Ne jamais faire confiance au nom du fichier
        // Il faut soit le valider, soit générer un nom en se 
        // servant des données binaires de l'image.
        
        // Ici on génère un identifiant unique grâce à tempnam, qui est précédée d'un '@' pour supprimer les 'notices' que cette fonction renvoie (des erreurs qui n'en sont pas et c'est très moche sur le site).
        // https://github.com/Glavin001/atom-beautify/issues/1108#issuecomment-272012827
        $filename = @tempnam('../images', ''); // Générer un identifiant unique dans ce répertoire
        unlink($filename); // Supprimer le fichier du disque
        
        $data = explode("\\", $filename); // On découpe la chaîne de caractère à chaque '\'
        $gename = explode(".", end($data))[0]; // On découpe la chaîne à chaque '.'
        // Ce qui nous donne le nom du fichier, débarassé du chemin et de l'extension .tmp
        // On déplace alors notre image dans le bon dossier, et on change son nom pour notre identifiant aleatoire
        if (!move_uploaded_file(
            $_FILES[$name]['tmp_name'],
            sprintf('../images/%s.%s',
                $gename,
                $ext
            )
        )) {
            throw new RuntimeException('Impossible de déplacer le fichier');
        }

        return "".$gename.".".$ext;

    } catch (RuntimeException $e) {
        header("Location: ../?error=".$e->getMessage());
    }
}

function ajouter_favori($user_id, $song_id) {
    $connexion = connexion_bdd();

    // Lorsque l'on effectue une requête SQL, on prend toujours soin de la 'préparer' et de passer toutes les variables venant du client dans un filtre de sécurité. Cela évite notamment les attaques par injection SQL.
    $sql = "SELECT * FROM likes WHERE user_id = :userid AND song_id = :songid";
    $req = $connexion->prepare($sql);
    $req->execute(array(
        'userid' => $user_id,
        'songid' => $song_id,
    ));
    
    $res = null;
    // On récupère les données renvoyées par la requête preparée.
    // Si le tableau que l'on a récupéré n'est pas vide, c'est que le client à déjà liké ce titre.
    foreach ($req as $row) { $res = $row; }

    if ($res) {
        // Ce client à déjà ajouté ce titre dans ses favoris, on le retire.
        $sql = "DELETE FROM likes WHERE user_id = :userid AND song_id = :songid";
        $req = $connexion->prepare($sql)->execute(array(
            'userid' => $user_id,
            'songid' => $song_id,
        ));

    } else {
        // Le client n'avait pas encore mis ce titre en favori, on l'ajoute donc.
        $sql = "INSERT INTO likes (user_id, song_id) VALUES (:userid, :songid)";
        $connexion->prepare($sql)->execute(array(
            'userid' => $user_id,
            'songid' => $song_id,
        ));
    }
}

function recuperer_favoris($user_id) {
    $connexion = connexion_bdd();
    // Ici la liaison de tables dans notre requête nous permet d'afficher tous les titres likés par notre utilisateur. Cette requête n'est bien sûr pas possible sans notre tables 'likes' qui référence tous les couples titre/client qui existent.
    $sql = "SELECT songs.* FROM songs INNER JOIN likes ON songs.id = likes.song_id JOIN users ON users.id = :userid";
    $req = $connexion->prepare($sql);
    $req->execute(array(
        'userid' => $user_id,
    ));
    
    $res[] = null;
    // On récupère les données renvoyées par la requête preparée.
    // Si le tableau que l'on a récupéré n'est pas vide, c'est que le client à déjà liké ce titre.
    foreach ($req as $row) { $res[] = $row; }
    return $res;
}

function artistes_favoris($user_id) {
    $connexion = connexion_bdd();

    // Récupérer les 3 artistes favoris
    $sql = "SELECT songs.artist, COUNT(songs.artist) AS `value_occurrence` FROM songs INNER JOIN likes ON songs.id = likes.song_id JOIN users ON users.id = :userid GROUP BY songs.artist ORDER BY `value_occurrence` DESC LIMIT 3";
    $req = $connexion->prepare($sql);
    $req->execute(array(
        'userid' => $user_id,
    ));

    $fav_artistes = [];
    foreach ($req as $row) { $fav_artistes[] = $row; }

    return $fav_artistes;
}

function genres_favoris($user_id) {
    $connexion = connexion_bdd();

    // Récupérer les 3 genres favoris
    $sql = "SELECT songs.genre, COUNT(songs.genre) AS `value_occurrence` FROM songs INNER JOIN likes ON songs.id = likes.song_id JOIN users ON users.id = :userid GROUP BY songs.genre ORDER BY `value_occurrence` DESC LIMIT 3";
    $req = $connexion->prepare($sql);
    $req->execute(array(
        'userid' => $user_id,
    ));

    $fav_genres = [];
    foreach ($req as $row) { $fav_genres[] = $row; }

    return $fav_genres;
}

function generer_recommandations($user_id) {

    $favs = recuperer_favoris($user_id);

    $meilleur_artiste = artistes_favoris($user_id);
    $meilleur_genre = genres_favoris($user_id);

    // echo("ARTISTES & GENRES");
    // var_dump($meilleur_artiste);
    // var_dump($meilleur_genre);
    
    $connexion = connexion_bdd();
    $titres_recommandes = [];

    // Les variables "$meilleur_" ne retournent pas toujours 3 éléments, ainsi, on va utiliser la taille minimale de ces deux variables (ex. si on a 3 artistes mais seulement 2 genres, on utilisera 2 pour nos calculs).
    // Ce chiffre nous donne alors le nombre de recommandations différentes que l'on peut générer avec notre algorithme: avec notre exemple on va générer 2 recommandations c'est à dire que notre boucle for va tourner deux fois et ajouter à chaque fois une recommandation dans notre tableau "$titres_recommandes".
    for ($i=0; $i < min(count($meilleur_artiste), count($meilleur_genre)); $i++) { 

        $sql = "SELECT * FROM songs LEFT OUTER JOIN likes ON songs.id = likes.song_id WHERE (songs.artist = :artiste OR songs.genre = :genre) AND likes.user_id IS NULL LIMIT 1";
        $req = $connexion->prepare($sql);

        $req->execute(array(
            'artiste' => $meilleur_artiste[$i]['artist'],
            'genre'   => $meilleur_genre[$i]['genre'],
        ));

        foreach ($req as $row) { $titres_recommandes[] = $row; }
        // var_dump($titres_recommandes);

    }

    return $titres_recommandes;
}

?>
