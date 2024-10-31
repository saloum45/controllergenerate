
# ControllerGenerate

Saloum45/ControllerGenerate est un package Laravel qui facilite la génération dynamique de contrôleurs et de routes dans une application Laravel. Ce package automatise la création de contrôleurs CRUD (Create, Read, Update, Delete, Show) dans le dossier app/Models, et migrations dans le dossier database/migrations pour tous les modèles, ainsi que la génération de routes correspondantes, ce qui accélère considérablement le processus de développement et réduit la répétition du code.

# Fonctionnalité 
#### Génération automatique de contrôleurs : 
Crée des contrôleurs pour chaque modèle existant dans le dossier app/Models avec des méthodes CRUD prêtes à l'emploi.

#### Génération de routes : 
Crée automatiquement des routes pour les contrôleurs générés, facilitant ainsi l'accès aux fonctionnalités CRUD via une API RESTful.

#### Génération automatique de migrations : 
Crée des migrations pour chaque modèle existant dans le dossier app/Models avec les attributs et même les clés étrangères.

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