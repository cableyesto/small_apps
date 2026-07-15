# Small Apps - Contacts List

<b>Warning</b><br />
Ne pas utiliser en production. Uniquement dans un environnement de développement.<br />
<b>Warning</b>

## Dépendances
1) Windows
 - Utilisation de WSL 2, Ubuntu
 - Docker Desktop
 - HeidiSQL
 - Navigateur web

2) WSL 2, Ubuntu
 - git
 - Docker
 - DDEV

## Démarrage du projet
``` bash
$ git clone https://github.com/cableyesto/small_apps.git
$ cd contacts_list
$ ddev describe
$ ddev start
$ ddev exec composer install
```

### Connexion DB
#### Option 1
Pour se connecter à la base de données MySQL.
Récupérer le port accessible de la BDD via Docker Desktop

    Containers > ddev-contacts-list > db 

    Port xxxxx:3306

Via l'interface de HeidiSQL<br />
Type de réseau: MySQL<br />
Bibliothèque: libmysql-8.4.0<br />
IP de l'hôte: 127.0.0.1<br />
Port: xxxxx<br />
Utilisateur: db<br />
Mot de passe: db<br />

#### Option 2
```bash
$ ddev ssh
$ mysql
> SHOW TABLES;
```


### Charger la base de données
Utiliser le fichier `script/dump.sql`


## Accès à l'application

Se rendre sur l'URL `https://contacts-list.ddev.site/`
