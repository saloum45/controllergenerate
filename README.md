# ControllerGenerate Laravel

Saloum45/ControllerGenerate est un package Laravel qui facilite la génération dynamique de contrôleurs, migrations, routes et relations dans une application Laravel.

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
Le service provider du package `PackageServiceProvider` s’enregistre automatiquement et crée les commandes nécessaires dans le dossier `app/Console/Commands`.

---

## Commandes disponibles

### 1️⃣ Générer les contrôleurs

```bash
php artisan generate:controllers
```

- Crée des contrôleurs pour tous les modèles ou un modèle spécifique (php artisan generate:controllers NomModel).
- Chaque contrôleur contient des **méthodes CRUD**, un endpoint `getFormDetails` pour les clés étrangères, et les méthodes `login`/`logout` pour le modèle `User`.

---

### 2️⃣ Générer les routes

```bash
php artisan generate:routes
```

- Crée des routes API pour tous les contrôleurs ou pour un modèle précis (php artisan generate:routes NomModel).
- Ajoute automatiquement les routes `login` et `logout` pour `UserController`.
- Si un fichier de routes existe, les nouvelles routes sont ajoutées **à la fin** sans écraser le fichier existant.

---

### 3️⃣ Générer les migrations

```bash
php artisan generate:migrations
```

- Crée les migrations à partir des attributs `$fillable` des modèles.
- Les migrations sont générées dans le bon ordre en fonction des **dépendances des clés étrangères**.
- Si une migration existe déjà pour un modèle, elle **est écrasée** pour éviter les duplications.

---

### 4️⃣ Générer les relations entre modèles

```bash
php artisan generate:relations
```

- Analyse les attributs `$fillable` des modèles.
- Crée automatiquement les relations **belongsTo** et **hasMany**.
- Peut générer uniquement pour un modèle spécifique si un argument est fourni :

```bash
php artisan generate:relations NomModel
```

---

### 5️⃣ Commande ultime : tout générer

```bash
php artisan generate:all {model?}
```

- Regroupe toutes les commandes ci-dessus en une seule.
- Argument facultatif `{model}` :  
  - Si spécifié, ne génère que pour ce modèle.  
  - Sinon, génère pour tous les modèles.
- Exemple :

```bash
php artisan generate:all NomModel   # pour un modèle précis
php artisan generate:all             # pour tous les modèles
```

---

### 6️⃣ Exécuter les migrations et seeders

```bash
php artisan migrate
```

---

## Résultat attendu

- **Controllers** : créés dans `app/Http/Controllers` avec méthodes CRUD.  
- **Migrations** : créées dans `database/migrations` dans le bon ordre avec clés étrangères.  
- **Routes** : ajoutées à `routes/api.php`.  
- **Relations** : ajoutées automatiquement dans les modèles (belongsTo et hasMany).  
- Bonus : gestion spéciale pour le modèle `User` avec endpoints `login`/`logout` et tables `sessions` et `password_reset_tokens`.

---

## Bon code 👈🏽

---

# In English

# ControllerGenerate Laravel

**Saloum45/ControllerGenerate** is a Laravel package that makes it easy to dynamically generate **controllers**, **migrations**, **routes**, and **relations** in a Laravel application.

#### Complete tutorial:  
[![youtube](https://img.shields.io/badge/youtube-FF0000?style=for-the-badge&logo=youtube&logoColor=white)](https://www.youtube.com/watch?v=YJmBQQF3ODU)

---

### How it works 👉🏽😇

1. You must first **create the models**.  
   - Use **PascalCase** for model names, e.g., `StudentClass`.  
   - The package relies on models to generate controllers, migrations, routes, and relations.

2. For **foreign keys**, use the naming convention:  
   - `id_table_name`, e.g., `id_classe`.

---

### Installation

Add the package to your Laravel project via Composer:

```bash
composer require saloum45/controllergenerate
```

**Configuration**:  
The `PackageServiceProvider` is automatically registered and creates the necessary commands in `app/Console/Commands`.

---

### Available commands

#### 1️⃣ Generate controllers

```bash
php artisan generate:controllers
```

- Creates controllers for all models or a specific model.
- Each controller contains **CRUD methods**, a `getFormDetails` endpoint for foreign keys, and `login`/`logout` methods for the `User` model.

---

#### 2️⃣ Generate routes

```bash
php artisan generate:routes
```

- Creates API routes for all controllers or a specific model.
- Adds `login` and `logout` routes for `UserController`.
- If the routes file exists, new routes are **added at the end** without overwriting existing routes.

---

#### 3️⃣ Generate migrations

```bash
php artisan generate:migrations
```

- Creates migrations based on the `$fillable` attributes of models.
- Orders migrations according to **foreign key dependencies**.
- Existing migrations for a model are **overwritten** to avoid duplicates.

---

#### 4️⃣ Generate relations between models

```bash
php artisan generate:relations
```

- Analyzes `$fillable` attributes.
- Automatically creates **belongsTo** and **hasMany** relations.
- Can generate for a specific model only:

```bash
php artisan generate:relations ModelName
```

---

#### 5️⃣ Ultimate command: generate everything

```bash
php artisan generate:all {model?}
```

- Runs **Relations, Controllers, Routes, and Migrations** in one command.
- Optional `{model}` argument:  
  - Generate for a specific model if provided.  
  - Otherwise, generate for all models.
- Example:

```bash
php artisan generate:all ModelName   # for a specific model
php artisan generate:all             # for all models
```

---

#### 6️⃣ Run migrations and seeders

```bash
php artisan migrate
```

---

### Expected result

- **Controllers**: created in `app/Http/Controllers` with CRUD methods.  
- **Migrations**: created in `database/migrations` in correct order with foreign keys.  
- **Routes**: added to `routes/api.php`.  
- **Relations**: automatically added to models (belongsTo and hasMany).  
- Bonus: `User` model handled specially with `login`/`logout` endpoints and `sessions` & `password_reset_tokens` tables.

---

## Good code 👈🏽

