
# ControllerGenerate

Saloum45/ControllerGenerate est un package Laravel qui facilite la génération dynamique de contrôleurs, de migrations et de routes dans une application Laravel.

### Comment ça marche 👉🏽👉🏽👉🏽👉🏽 😇NB😇: il faut d'abord créer les modèles avant d'installer le package 
Installation : Ajoutez le package à votre projet Laravel via Composer en exécutant la commande suivante :
```http
composer require saloum45/controllergenerate
```
Configuration : Le service provider du package, PackageServiceProvider, s'enregistre automatiquement lors de l'installation. Ce provider va créer les commandes nécessaires dans le dossier app/Console/Commands si elles n'existent pas déjà.

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