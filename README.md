
# ControllerGenerate laravel

Saloum45/ControllerGenerate est un package Laravel qui facilite la génération dynamique de contrôleurs, de migrations et de routes dans une application Laravel.

### Comment ça marche 👉🏽👉🏽👉🏽👉🏽 😇NB😇: il faut d'abord créer les modèles avant d'installer le package, car le package se base sur les modèles pour la génération.
Installation : Ajoutez le package à votre projet Laravel via Composer en exécutant la commande suivante :
```http
composer require saloum45/controllergenerate
```
Configuration : Le service provider du package, PackageServiceProvider, s'enregistre automatiquement lors de l'installation. Ce provider va créer les commandes nécessaires dans le dossier app/Console/Commands.

Exécution des commandes :
#### Pour générer les contrôleurs, exécutez la commande suivante dans votre terminal :
```http
php artisan generate:controllers
```

#### Pour générer les routes à partir des contrôleurs, exécutez :

```http
php artisan generate:routes-from-controllers
```

#### Pour générer les migrations à partir des modèles, exécutez :
```http
php artisan generate:migrations-from-models
```

Résultat : Une fois les commandes exécutées, des contrôleurs avec des méthodes CRUD seront créés dans le dossier app/Http/Controllers, des migrations dans le dossier database/migrations et les routes correspondantes seront ajoutées à votre fichier de routes API
##  👈🏽Bon code👉🏽
# In English
# ControllerGenerate laravel

Saloum45/ControllerGenerate is a Laravel package that makes it easy to dynamically generate controllers, migrations and routes in a Laravel application.

### How it works 👉🏽👉🏽👉🏽👉🏽 😇NB😇: you must first create the models before installing the package, because the package is based on the models for generation.
Installation: Add the package to your Laravel project via Composer by running the following command:
```http
composer require saloum45/controllergenerate
```
Configuration: The package service provider, PackageServiceProvider, is automatically registered during installation. This provider will create the necessary commands in the app/Console/Commands folder.

Order execution:
#### To generate the controllers, run the following command in your terminal:
```http
php artisan generate:controllers
```

#### To generate routes from controllers, run:

```http
php artisan generate:routes-from-controllers
```

#### To generate migrations from templates, run:
```http
php artisan generate:migrations-from-models
```

Result: After the commands are executed, controllers with CRUD methods will be created in the app/Http/Controllers folder, migrations in the database/migrations folder and the corresponding routes will be added to your API routes file
## 👈🏽Good code👉🏽