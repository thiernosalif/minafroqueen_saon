# Min Afro Queen — site + administration

PHP 8.2 + MySQL, sans framework ni commande à lancer sur le serveur.

## Déploiement sur o2switch via GitHub

1. **Base de données** (cPanel → *Bases de données MySQL*) : créez une base, un utilisateur, et associez-les (tous les privilèges).
2. **Dépôt GitHub** : poussez ce dossier (`config.php` et `uploads/` sont ignorés par Git, c'est voulu).
3. **cPanel → Git™ Version Control → Créer** : collez l'URL du dépôt (HTTPS, ou clé SSH si privé), puis *Déployer HEAD commit*.
   - Avant, vérifiez dans `.cpanel.yml` la ligne `DEPLOYPATH=` : ce doit être le dossier racine de votre domaine.
4. **Créez `config.php`** sur le serveur (gestionnaire de fichiers cPanel, dans le dossier du site) en copiant `config.sample.php`, avec les identifiants MySQL de l'étape 1.
5. Ouvrez `https://votre-domaine/install.php` : créez le compte admin. **Supprimez ensuite `install.php`** du serveur.
6. Connectez-vous sur `https://votre-domaine/admin`, puis *Réglages* : logo, photo d'accueil, réseaux sociaux.

Mises à jour : `git push`, puis dans cPanel *Mettre à jour depuis le dépôt* → *Déployer*. Les photos (`uploads/`) et `config.php` ne sont jamais écrasés.

## Test en local
```
cp config.sample.php config.php   # puis mettre 'db_driver' => 'sqlite'
php -S localhost:8000 storage/devrouter.php
```

## Vidéos
Upload mp4 limité par PHP (`upload_max_filesize`, `post_max_size`) : augmentez-les dans cPanel → *MultiPHP INI Editor* (ex. 128M), ou collez un lien YouTube/Vimeo.
