# re-system

Site web dynamique de démonstration d'un système de recommandation produit.

## Création d'un site web dynamique

Pour démontrer un système de recommandations de produits, nous allons mettre en place un site web dynamique en utilisant PHP. L'idée est de simuler un site proposant des produits, le client pourra alors s'inscrire, se connecter, choisir ses produits préférés et le système lui proposera alors un carousel de propositions recommandées.

Dans un premier temps, nous construirons les fonctionnalitées basique : connection à la BDD, système CRUD pour les produits et inscription/connexion du client.

Le système CRUD est un acronyme technique : CREATE - READ - UPDATE - DELETE, les quatres actions de base que l'on doit pouvoir effectuer en tant qu'administrateur d'une base de données (Ajouter, Lire, Mettre à jour et Supprimer un ou des produits).

Dans un second temps, nous programmerons un système de recommandation capable de nous renvoyer une liste de produits adéquats selon le client que nous afficherons sur sa page d'accueil.

### Page d'accueil

Création de la page d'accueil `index.php`. Cette page doit permettre au client de :

- Consulter les produits du site même en mode invité (non-connecté)
- Cliquer sur un lien d'inscription
- Cliquer sur un lien de connexion
- Si connecté, de cliquer sur un lien de déconnexion
- Si connecté, de consulter ses recommandations

La page doit également permettre à un administrateur du site de se connecter, et d'accèder aux formulaires CRUD pour les produits.

### Création de la base de données

Nous utiliserons l'outil fourni par WAMP: PHP-MyAdmin pour la gestion de la base de donnée. Nous créons donc la base MYSQL (recsystem) pour cet exercice nous ne mettons aucun mot de passe et gardons l'utilisateur de connexion par défaut.

Nous ajoutons ensuite la table des utilisateurs (users), qui contiendra les informations suivantes :

- (id) Identifiant unique
- (username) Nom d'utilisateur
- (password) Mot de passe*

note*: Les recommandations de sécurité vis-à-vis des mots de passes dans les bases de donnée nous impose de les "hacher" avant de les inscrire dans la base, plus d'infos ici: [https://culture-informatique.net/cest-quoi-hachage/]

Nous ajoutons également la table des produits (products). Comme nous avons choisi le thème de la musique, cette table contiendra les informations suivantes :

- (id) Identifiant unique
- (title) Titre du morceau
- (artist) Artiste
- (album) Image de la pochette de l'album
- (genre) Style musical (pour les recommandations)

### Connexion avec la base

Pour se connecter à la base de donnée, nous allons programmer une fonction qui sera très souvent réutilisée. L'idéal serait donc d'écrire notre fonction dans un fichier spécifique, que l'on pourrait appeler n'importe où sur notre site.

Pour se faire nous allons créer un fichier `functions.php`. Plus tard nous y ajouterons toutes les autres fonctions susceptibles d'être réutilisées un peu partout.
<<<<<<< HEAD

Une fois la fonction de connexion écrite, nous pouvons l'appeler en ajoutant les fonctions depuis n'importe qu'elle autre page de notre site.
=======
>>>>>>> 93154e1ddd89739afc386f2a45fef4ec52abe1a4
