

---

# 🍽️ Restaurant Booking API – Symfony 6

API RESTful développée avec **Symfony 6** permettant la gestion de **restaurants**, **menus**, **plats (foods)**, **catégories**, **réservations (bookings)** et **utilisateurs**, avec un système d’authentification sécurisé basé sur **token unique**.

Le projet inclut :

* Un CRUD complet pour chaque entité métier
* Une gestion fine des droits via les rôles (`ROLE_USER`, `ROLE_ADMIN`)
* Des règles métier encapsulées dans des **services Symfony**
* Une documentation interactive via **Swagger / NelmioApiDocBundle**

---

## 🚀 Fonctionnalités

### 🔑 Authentification & sécurité

* Inscription (`/api/registration`) → Génération d’un **token unique**
* Connexion (`/api/login`) → Nouveau token généré à chaque login
* Déconnexion (`/api/logout`) → Invalidation du token
* Gestion des rôles :

  * `ROLE_USER` : réservation & gestion de son compte
  * `ROLE_ADMIN` : gestion complète (CRUD + utilisateurs)

### 👤 Utilisateurs

* Création d’un utilisateur
* Suppression d’un compte :

  * Un utilisateur peut supprimer **son propre compte**
  * Un admin peut supprimer **n’importe quel compte**
* Suppression sécurisée (les réservations liées sont supprimées en cascade)

### 📚 CRUD complet

* **Restaurant** : création, lecture, modification, suppression
* **Menu**
* **Food (plat)**
* **Category**
* **Picture**
* **Booking (réservation)**

### ⚙️ Services métier

* **BookingService**

  * Vérifie que le restaurant est ouvert au créneau demandé
  * Vérifie que le nombre d’invités ≤ `maxGuest`
  * Crée une réservation valide (UUID, date, heure, utilisateur)
* **UserService**

  * Supprime un utilisateur ainsi que ses réservations liées
  * Vérifie les rôles : seul `ROLE_ADMIN` peut supprimer n’importe quel compte

### 📖 Documentation API

* Accessible via Swagger UI :
  👉 [http://127.0.0.1:8000/api/doc](http://127.0.0.1:8000/api/doc)

---

## 🛠️ Installation

### 1. Cloner le projet

```bash
git clone https://github.com/Belka-stack/symfony-restaurant-api.git
cd symfony-restaurant-api.git
```

### 2. Installer les dépendances

```bash
composer install
```

### 3. Configurer l’environnement

```bash
cp .env .env.local
```

➡️ Configurez votre base de données MySQL dans `.env.local`

### 4. Créer la base de données

```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

### 5. Charger les données de test (fixtures)

```bash
php bin/console doctrine:fixtures:load
```

### 6. Lancer le serveur

```bash
symfony serve
```

---

## 📖 Exemples d’utilisation

### 🔑 Inscription

```http
POST /api/registration
Content-Type: application/json

{
  "email": "user@example.com",
  "password": "password",
  "firstName": "John",
  "lastName": "Doe"
}
```

Réponse :

```json
{
  "user": "user@example.com",
  "apiToken": "abc123...",
  "roles": ["ROLE_USER"]
}
```

---

### 📅 Créer une réservation

```http
POST /api/booking
X-AUTH-TOKEN: votretoken
Content-Type: application/json

{
  "guestNumber": 4,
  "orderDate": "2025-08-20",
  "orderHour": "20:00:00",
  "allergy": "gluten",
  "restaurant": 3
}
```

Réponse :

```json
{
  "id": 12,
  "uuid": "d4a6c3a8-9b7e-4f8c-82a5-82b8afefc123",
  "guestNumber": 4,
  "orderDate": "2025-08-20",
  "orderHour": "20:00:00",
  "allergy": "gluten",
  "restaurant": { "id": 3, "name": "Chez Luigi" },
  "user": { "id": 5, "email": "user@example.com" }
}
```

---

### 👤 Supprimer un utilisateur

```http
DELETE /api/account/delete/{id}
X-AUTH-TOKEN: votretoken
```

* ✅ Un utilisateur peut supprimer **son propre compte**
* ✅ Un admin peut supprimer **n’importe quel compte**
* ❌ Un simple utilisateur ne peut pas supprimer celui d’un autre

---

## 👨‍💻 Stack technique

* **Symfony 6**
* **Doctrine ORM (MySQL)**
* **PHP 8.2**
* **NelmioApiDocBundle** (Swagger UI)
* **Composant Serializer**
* **Security + Token personnalisé**

---

## 📌 Notes pour le jury

* ✅ Les contrôleurs ne contiennent que l’orchestration → la logique métier est externalisée dans des **services**
* ✅ Gestion stricte des droits : distinction claire entre `ROLE_USER` et `ROLE_ADMIN`
* ✅ Problèmes de sérialisation résolus avec `#[Groups()]`
* ✅ Gestion des cascades pour éviter les erreurs SQL lors de suppressions
* ✅ Projet pensé pour être extensible : ajout futur d’API mobile ou front React possible

---

✍️ Projet développé par **Belka Bakouche**
📌 Soutenance – API Symfony RESTful avec gestion des réservations et sécurité

---

