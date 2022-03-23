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
