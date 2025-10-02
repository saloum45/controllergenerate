
# ControllerGenerate Laravel

Saloum45/ControllerGenerate est un package Laravel qui facilite la génération dynamique de contrôleurs, migrations, routes et relations dans une application Laravel(API).

#### Tuto complet :  
[![youtube](https://img.shields.io/badge/youtube-FF0000?style=for-the-badge&logo=youtube&logoColor=white)](https://www.youtube.com/watch?v=YJmBQQF3ODU)

---

## Comment ça marche 👉🏽😇

1. Il faut d’abord **créer les modèles**.  
   - Respectez le **PascalCase** pour le nom des modèles, ex : `EtudiantClasse`.  
   - Le package se base sur les modèles pour générer les contrôleurs, migrations, routes et relations.

2. Pour les **clés étrangères**, respectez cette nomenclature :  
   - `id_nom_de_table`, exemple : `id_classe`.

---

## Installation

Ajoutez le package à votre projet Laravel via Composer :

```bash
composer require saloum45/controllergenerate
```

**Configuration** :  
Une fois installé package crée automatiquement les commandes nécessaires, que vous pouvez exécuter en suivant ces étapes.

---

## Commandes disponibles

### 1️⃣ Générer les contrôleurs

```bash
php artisan generate:controllers
```

- Crée des contrôleurs pour tous les modèles.

```bash
php artisan generate:controllers NomModel
```

- Crée le contrôleur pour un modèle spécifique.

- #### Chaque contrôleur contient des **méthodes CRUD**, un endpoint `getFormDetails` pour les clés étrangères, et les méthodes `login`/`logout` pour le modèle `User`.

---

### 2️⃣ Générer les routes

```bash
php artisan generate:routes
```

- Crée des routes API pour tous les contrôleurs.

```bash
php artisan generate:routes NomModel
```
- Crée des routes API pour un contrôleur spécifique.

- #### Si un fichier de routes existe, les nouvelles routes sont ajoutées **à la fin** sans écraser le fichier existant.

---

### 3️⃣ Générer les migrations

```bash
php artisan generate:migrations
```

- Crée les migrations à partir des attributs `$fillable` des modèles.

```bash
php artisan generate:migrations NomModel
```

- Crée la migration d'un modèle spécifique.

- #### Les migrations sont générées dans le bon ordre en fonction des **dépendances des clés étrangères**. Si une migration existe déjà pour un modèle, elle **est écrasée** pour éviter les duplications.

---

### 4️⃣ Générer les relations (belongsTo et hasMany) entre modèles

```bash
php artisan generate:relations
```

- Crée les relations de tous les modéles.

```bash
php artisan generate:relations NomModel
```
- Crée les relations pour un modèle spécifique.

---

### 5️⃣ Commande ultime : tout générer

```bash
php artisan generate:all
```

- Regroupe toutes les commandes ci-dessus en une seule pour tous les modèles.

```bash
php artisan generate:all NomModel
```
- Regroupe toutes les commandes ci-dessus en une seule pour un modèle spécifique.

---

### 6️⃣ Exécuter les migrations

```bash
php artisan migrate
```

---

## Résultat attendu

- **Controllers** : créés dans `app/Http/Controllers` avec méthodes CRUD.  
- **Migrations** : créées dans `database/migrations` dans le bon ordre avec clés étrangères.  
- **Routes** : ajoutées à `routes/api.php`.  
- **Relations** : ajoutées automatiquement dans les modèles (belongsTo et hasMany).  
- Bonus : gestion spéciale pour le modèle `User` avec endpoints `login`/`logout` avec géneration du token.

---

## Bon code 🧑🏽‍💻 Salem DEV 👈🏽

---
# ControllerGenerate Laravel

**Saloum45/ControllerGenerate** is a Laravel package that makes it easy to dynamically generate **controllers**, **migrations**, **routes**, and **relations** in a Laravel (API) application.

#### Full tutorial:  
[![youtube](https://img.shields.io/badge/youtube-FF0000?style=for-the-badge&logo=youtube&logoColor=white)](https://www.youtube.com/watch?v=YJmBQQF3ODU)

---

## How it works 👉🏽😇

1. You must first **create the models**.  
   - Use **PascalCase** for model names, e.g., `StudentClass`.  
   - The package relies on models to generate controllers, migrations, routes, and relations.

2. For **foreign keys**, follow this naming convention:  
   - `id_table_name`, e.g., `id_class`.

---

## Installation

Add the package to your Laravel project via Composer:  

```bash
composer require saloum45/controllergenerate
```

**Configuration**:  
Once installed, the package automatically registers the necessary commands, which you can execute as shown below.

---

## Available Commands

### 1️⃣ Generate controllers

```bash
php artisan generate:controllers
```

- Creates controllers for all models.

```bash
php artisan generate:controllers ModelName
```

- Creates a controller for a specific model.

- #### Each controller contains **CRUD methods**, a `getFormDetails` endpoint for foreign keys, and `login`/`logout` methods for the `User` model.

---

### 2️⃣ Generate routes

```bash
php artisan generate:routes
```

- Creates API routes for all controllers.

```bash
php artisan generate:routes ModelName
```

- Creates API routes for a specific controller.

- #### If a routes file already exists, the new routes are **added at the end** without overwriting the existing file.

---

### 3️⃣ Generate migrations

```bash
php artisan generate:migrations
```

- Creates migrations based on the `$fillable` attributes of models.

```bash
php artisan generate:migrations ModelName
```

- Creates the migration for a specific model.

- #### Migrations are generated in the correct order based on **foreign key dependencies**. If a migration already exists for a model, it is **overwritten** to avoid duplicates.

---

### 4️⃣ Generate model relations (belongsTo and hasMany)

```bash
php artisan generate:relations
```

- Creates relations for all models.

```bash
php artisan generate:relations ModelName
```

- Creates relations for a specific model.

---

### 5️⃣ Ultimate command: generate everything

```bash
php artisan generate:all
```

- Runs all of the above commands for all models.

```bash
php artisan generate:all ModelName
```

- Runs all of the above commands for a specific model.

---

### 6️⃣ Run migrations

```bash
php artisan migrate
```

---

## Expected result

- **Controllers**: created in `app/Http/Controllers` with CRUD methods.  
- **Migrations**: created in `database/migrations` in the correct order with foreign keys.  
- **Routes**: added to `routes/api.php`.  
- **Relations**: automatically added to models (belongsTo and hasMany).  
- Bonus: special handling for the `User` model with `login`/`logout` endpoints and token generation.  

---

## Happy coding 🧑🏽‍💻 Salem DEV 👈🏽  
