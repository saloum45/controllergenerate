
# ControllerGenerate Laravel

Saloum45/ControllerGenerate est un package Laravel qui facilite la génération dynamique de contrôleurs, migrations, routes et relations dans une application Laravel(API), via la doc **web** ou en **ligne de commande**.

#### Tuto complet :  
[![youtube](https://img.shields.io/badge/youtube-FF0000?style=for-the-badge&logo=youtube&logoColor=white)](https://www.youtube.com/watch?v=YJmBQQF3ODU)


---

## Installation

Ajoutez le package à votre projet Laravel via Composer :

```bash
composer require saloum45/controllergenerate
```

**Note** :  
Une fois installé le package crée automatiquement une [documentation Web](http://127.0.0.1:8000/generate/docs/) et les commandes nécessaires, que vous pouvez exécuter en suivant ces étapes.

### Règles 👉🏽😇
Tout repose sur les modèles et leurs attributs, donc c'est ce qu'on crée en premier. 
1. Pour  **les modèles**.  
   - Respectez le **PascalCase** pour le nom des modèles, ex : `EtudiantClasse`.

2. Pour les **clés étrangères**, respectez cette nomenclature :  
   - `id_nom_de_table`, exemple : `id_classe`.

3. Pour éviter de perdre du code par erreur, à chaque génération il n'y a que les migrations qui sont écrasées.

4. Le package gére les realtions (with) en fonction des clés étrangéres définies dans les modèles

**Documentation** :  
Vous pouvez accéder à la documentation intégrée et faire les générations via cette route.

```bash
http://127.0.0.1:8000/generate/docs/
```
si vous choisissez la doc web vous pouvez vous arrêter là.

---
**Ligne de commande** :  
Ou si vous préférez les lignes de commandes vous avez aussi le choix en suivant ces étapes ci-dessous


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
php artisan install:api
```

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

## Bon code 🧑🏽‍💻 Salem DEV 👈🏽 fait avec beaucoup de ❤️ et ☕️
