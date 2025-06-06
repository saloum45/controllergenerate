
# ControllerGenerate laravel

Saloum45/ControllerGenerate est un package Laravel qui facilite la génération dynamique de contrôleurs, de migrations et de routes dans une application Laravel.
#### tuto au complet : [![youtube](https://img.shields.io/badge/youtube-FF0000?style=for-the-badge&logo=youtube&logoColor=white)](https://www.youtube.com/watch?v=YJmBQQF3ODU)
### Comment ça marche 👉🏽👉🏽👉🏽👉🏽 😇NB😇: il faut d'abord créer les modèles(Il faut respecter le PacalCase ex: EtudiantClasse) avant d'installer le package, car le package se base sur les modèles pour la génération.

### Contrainte 👉🏽👉🏽👉🏽👉🏽 😇NB😇: pour les clés étrangères il faut respecter cette nomenclature : id_nom_de_table exemple : id_classe.
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

#### Pour générer les routes, exécutez :

```http
php artisan install:api
```

```http
php artisan generate:routes
```

#### Pour générer les migrations, exécutez :
```http
php artisan generate:migrations
```

#### Pour générer les relations entre modeles, exécutez :
```http
php artisan generate:relations
```

#### Pour migrer et seeder, exécutez :
```http
php artisan migrate
```

Résultat : Une fois les commandes exécutées, des contrôleurs avec des méthodes CRUD seront créés dans le dossier app/Http/Controllers, des migrations dans le dossier database/migrations et les routes correspondantes seront ajoutées à votre fichier de routes API, pour bonus même les relations entre models sont gérées ...
##  👈🏽Bon code👉🏽
# In English
# ControllerGenerate laravel

Saloum45/ControllerGenerate is a Laravel package that makes it easy to dynamically generate controllers, migrations and routes in a Laravel application.
#### complete tuto : [![youtube](https://img.shields.io/badge/youtube-FF0000?style=for-the-badge&logo=youtube&logoColor=white)](https://www.youtube.com/watch?v=YJmBQQF3ODU)

### How it works 👉🏽👉🏽👉🏽👉🏽 😇NB😇: you must first create the models(You must respect the PacalCase ex: StudentClass) before installing the package, because the package is based on the models for generation.

### Constraints 👉🏽👉🏽👉🏽👉🏽 😇NB😇: for foreign keys you must respect this naming : id_table_name example : id_classe.
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

#### To generate routes, run:

```http
php artisan install:api
```

```http
php artisan generate:routes
```

#### To generate migrations, run:
```http
php artisan generate:migrations
```

#### To generate relations between models, run:
```http
php artisan generate:relations
```

#### to migrer and seed, run :
```http
php artisan migrate 
```


Result: After the commands are executed, controllers with CRUD methods will be created in the app/Http/Controllers folder, migrations in the database/migrations folder and the corresponding routes will be added to your API routes file, even the models relations are okay ...
## 👈🏽Good code👉🏽