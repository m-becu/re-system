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

    $req->execute([ $login, ]);
    $user = $req->fetch();

    return $user;
}

?>
