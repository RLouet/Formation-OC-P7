# Formation Développeur PHP / Symfony

## Projet 7 : API REST BileMo

### Introduction
Projet 7 de la formation **OpenClassrooms** [*Développeur d'application PHP / Symfony*](https://openclassrooms.com/fr/paths/59-developpeur-dapplication-php-symfony) :

#### Créez un web service exposant une API

Vous pouvez voir la démo du projet [ici](https://bilemo.romainlouet.fr/)

### Installation

#### Prérequis
*   Version minimum de PHP : 8.0
*   Git
*   Composer

#### Copie du projet
`git clone https://github.com/RLouet/Formation-OC-P7.git .`

#### Installation des dépendances
`composer install --optimize-autoloader`

#### Configuration

##### Configuration du .env
Modifier le fichier .env avec vos informations et passer le projet en dev.
* Application en dev

  `APP_ENV=dev`

* Configuration de la base de données

  `DATABASE_URL="mysql://db_user:db_password@127.0.0.1:3306/db_name?serverVersion=5.7"`

##### Création de la bdd
`php bin/console doctrine:database:create`

*(Ou création de la base manuellement)*

##### Création des tables
`php bin/console doctrine:schema:create`

##### Création des données
    php bin/console doctrine:fixtures:load

##### Génération des clés SSL
    php bin/console lexik:jwt:generate-keypair

##### Repasser l'application en prod
Dans le fichier .env :
`APP_ENV=dev`

#### Utilisation
Par défaut, le compte administrateur est le suivant :

    email : contact@bilemo.com
    identifiant : Admin
    mot de passe : admin

Les comptes utilisateurs sont configurés ainsi :

    email : phonekingsuser1@phonekings.com
    identifiant : PhoneKingsUser1
    mot de passe : user

##### Utilisation en local
*Prérequis : symfony installé*
> https://symfony.com/download
*   Démarrer le serveur local :

`symfony server:start`
*   La documentation est accessible à l'adresse <localhost:8000>

##### Utilisation en production
* Vérifier l'environnement de l'application dans le .env :

  `APP_ENV=prod`

* Améliorer les performances du .env :

  `composer dump-env prod`

* Mettre à jour les dépendances pour environment :

  `composer install --no-dev --optimize-autoloader`

* Vidage du cache :

  `APP_ENV=prod APP_DEBUG=0 php bin/console cache:clear`

* Configurer le domaine pour qu'il pointe vers vers le dossier */public*

##### Utiliser l'API
L'API comporte une documentation, qui permet également de s'authentifier de l'utiliser.
* Rendez vous sur l'url de l'application, suivi de /api/doc
* Dans la section "Authentication", ouvrir la partie "Get Authentication Token"
* Cliquer sur "Try it out"
* Renseigner un username et un password valide et executer la requète
* Copier le token renvoyé dans la réponse*
* Cliquer sur "Authorize", en haut à droite de la page pour vous authentifier avec le token généré.

##### Ajouter un administrateur
* Créer un nouvel utilisateur
* Dans la table user de la base de données, modifier la colonne 'roles' de l'utilisateur à passer administrateur en `["ROLE_ADMIN"]`
