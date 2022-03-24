<?php // Fichier `functions.php`

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

        if (!isset($_FILES[$name]['error']) || is_array($_FILES[$name]['error'])) {
            throw new RuntimeException('Paramètres invalides.');
        }

        switch ($_FILES[$name]['error']) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_NO_FILE:
                throw new RuntimeException('Aucun fichier envoyé.');
            case UPLOAD_ERR_INI_SIZE:
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
        
        // https://github.com/Glavin001/atom-beautify/issues/1108#issuecomment-272012827
        $filename = @tempnam('../images', '');
        unlink($filename);
        
        $data = explode("\\", $filename);
        $gename = explode(".", end($data))[0];

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
        
        /*
            if (!move_uploaded_file(
                $_FILES[$name]['tmp_name'],
                sprintf('../images/%s.%s',
                    $_FILES[$name]['tmp_name'],
                    $ext
                )
            )) {
                throw new RuntimeException('Impossible de déplacer le fichier');
            }
        */

    } catch (RuntimeException $e) {
        header("Location: ../?error=".$e->getMessage());
    }
}

function ajouter_favori($user_id, $song_id) {
    $connexion = connexion_bdd();

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

?>
