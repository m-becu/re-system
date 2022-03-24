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

Une fois la fonction de connexion écrite, nous pouvons l'appeler en ajoutant les fonctions depuis n'importe qu'elle autre page de notre site.

### Connexion / Inscription client

Pour gérer la connexion client, on va se servir d'un formulaire HTML pour récupérer les infos du client via la méthode POST pour des raisons de sécurité. Une fois les données envoyées, nous utiliseront GET pour définir une "action" de connexion, qui sera détectée par notre page d'accueil. Ensuite on essaiera de conncecter le client avec ses informations.

Dans l'action de notre formulaire, on écrit "?action=login" ce qui a en réalité pour effet d'affecter la valeur 'login' à la variable $_GET["action"], cela nous permet de détecter la connexion.

Pour vérifier la connexion client, on va d'abord rechercher si son nom d'utilisateur existe. Puisque c'est quelque-chose que l'on va faire souvent, nous écrirons une autre fonction globale. Une fois le login récupéré, on va ensuite vérifier la correspondance du mot de passe. Si tout est en ordre, le client est maintenant connecté.

Pour l'inscription c'est plus ou moins la même chose, on va utiliser l'action 'register' cette fois-ci, on va récupérer cette action sur la page d'accueil et ajouter l'utilisateur dans la base. La différence est que cette fois-ci on doit vérifier que l'utilisateur n'est pas déjà dans la base, et connecter automatiquement celui qui réussi à s'inscrire.

Pour le moment le seul moyen de créer un compte administrateur est en venant modifier le contenu de la base de donnée directement, en changeant la valeur de la colonne 'admin' par 1. Nous créons quelques utilisateurs pour nos tests : Pierre (admin), Paul & Jacques.

### Ajout de produits

En tant qu'administrateur connecté, le client peut ajouter de nouvelles chansons dans la base. On utilise le même principe que pour l'inscription client et le même gestionnaire d'action de la page d'accueil. Pour faciliter la navigation nous allons également ajouter une barre de liens en haut de chaque page. Afin d'éviter de taper plusieurs fois le même code, nous utiliseront un composant à part qu'on importera dans toutes les pages.

### Navigation

La barre de navigation contiendra les données suivantes:

- Formulaire de connexion
- Formulaire d'inscription
- Outils d'administration
- Liens de navigation
- Etat de la connexion
- Bouton de déconnexion

## Système de recommandation

Pour réaliser notre système de recommandation, nous allons utiliser un système de 'favoris'. Le client, une fois connecté, aura la possibilité d'ajouter ses titres favoris. Nous utiliseront pour cela une table d'association client-titre. Grâce à cette table nous pourrons capter les genres musicaux les plus écoutés par notre client et lui proposer des titres similaires dans ses recommandations.

### Mise en favoris

La table 'likes' va référencer des couples client-titre en se servant de leurs identifiants uniques dans la base de données. Chaque fois qu'un client va mettre un titre en favoris, le système ajoutera un enregistrement dans cette table, correspondant à l'identifiant du client et celui du titre.

En détails, nous allons déclarer des clés étrangères sur la table 'likes' comme le recommande l'usage.
