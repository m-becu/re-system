<nav>
    <div class="left">
        <h1>Echo-Zik</h1>
        <p>Etat de la connexion: <b>
            <?php 
                if ($connexion) echo("OK");
                else echo("Erreur");
            ?>
        </b></p>
    </div>
    <div class="center">
        <?php if (isset($_SESSION["login"])) { ?>
            <h2>Bienvenue <?=$_SESSION["login"]?>!</h2>
        <?php } ?>
        <ul>
            <li><a href="/">Accueil</a></li>
            <?php // On affiche le lien que si le client à le droit de s'y rendre
                if (isset($_SESSION["id"]) && $_SESSION["admin"] === 1) { ?>
                    <li><a href="/admin">Administration</a></li>
                <?php
                }
            ?>
        </ul>
    </div>
    <div class="right">
        <?php
        // Si un client est connecté, il n'a plus besoin de le faire, on remplace donc les formulaires d'inscription/connexion par un lien de déconnexion grâce à une condition.
        if (isset($_SESSION["id"])) { ?>
            <a href="/?action=logout">Déconnexion</a>
        <?php
        } else { ?>
            <a href="?show=login">Connexion</a> / <a href="?show=register">Inscription</a>
            <?php
            if (isset($_GET["show"]) && $_GET["show"] === "register") {
            ?>
                <!-- Formulaire d'inscription -->
                <form action="?action=register" method="post">
                    <span>
                        <label for="iusername">Nom d'utilisateur</label>
                        <input id="iusername" name="username" type="text">
                    </span>
                    <span>
                        <label for="ipassword">Mot de passe</label>
                        <input id="ipassword" name="password" type="password">
                    </span>
                    <span>
                        <label for="ipassword2">Vérifiez le mot de passe</label>
                        <input id="ipassword2" name="password2" type="password">
                    </span>
                    <input type="submit" value="Envoyer">
                </form>
            <?php
            } else { ?>
                <!-- Formulaire de connexion -->
                <form action="?action=login" method="post">
                    <span>
                        <label for="iusername">Nom d'utilisateur</label>
                        <input id="iusername" name="username" type="text">
                    </span>
                    <span>
                        <label for="ipassword">Mot de passe</label>
                        <input id="ipassword" name="password" type="password">
                    </span>
                    <input type="submit" value="Envoyer">
                </form>
            <?php
            }
        } ?>
    </div>
</nav>