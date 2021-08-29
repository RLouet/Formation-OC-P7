# Formation Développeur PHP / Symfony
## Projet 7 : API REST BileMo

### Introduction
Projet 7 de la formation **OpenClassrooms** [*Développeur d'application PHP / Symfony*](https://openclassrooms.com/fr/paths/59-developpeur-dapplication-php-symfony) :

#### Développez de A à Z le site communautaire SnowTricks

Vous pouvez voir la démo du projet [ici](https://snowtricks.romainlouet.fr/)

### Installation

#### Prérequis
*   Version minimum de PHP : 8.0
*   Git
*   Composer

#### Copie du projet
`git clone https://github.com/RLouet/Formation-OC-P7.git`

#### Installation des dépendances
`composer install --optimize-autoloader`

#### Configuration
##### .env
*   APP_ENV=dev
*   DATABASE_URL="mysql://db_user:db_password@127.0.0.1:3306/db_name?serverVersion=5.7"
##### Création de la bdd
    php bin/console doctrine:database:create
##### Création des tables
    php bin/console doctrine:schema:create
##### Création des données
    php bin/console doctrine:fixtures:load
#### Utilisation
contact@bilemo.com
admin
##### Routes
###### Get products list :
    /api/product
###### Get product details :
    /api/product/{id}
###### Get company's users list :
    /api/company/{company_id}/user
###### Get company's user details :
    /api/company/{company_id}/user/{user_id}

