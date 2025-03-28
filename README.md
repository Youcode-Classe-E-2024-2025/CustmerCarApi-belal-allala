# CustomerCareAPI - API de Gestion de Tickets d'Assistance Client

[![Tests Status](https://github.com/Youcode-Classe-E-2024-2025/CustmerCarApi-belal-allala/actions/workflows/run-tests.yml/badge.svg)](https://github.com/Youcode-Classe-E-2024-2025/CustmerCarApi-belal-allala/actions)
[![License](https://img.shields.io/badge/License-MIT-blue.svg)](https://opensource.org/licenses/MIT)

## 📝 Description

CustomerCareAPI est une API RESTful Laravel pour la gestion de tickets d'assistance client. Elle permet de gérer les tickets, les réponses, la pagination et les filtres. L'API est documentée avec Swagger UI et testée avec PHPUnit.

**Fonctionnalités Principales :**

* CRUD Tickets et Réponses
* Pagination et Filtres pour les Tickets
* API RESTful avec Documentation Swagger
* Tests Unitaires et Fonctionnels (PHPUnit)
* Authentification (Laravel Sanctum)

## 🚀 Installation Rapide

1. **Cloner le dépôt :**

```bash
git clone https://github.com/Youcode-Classe-E-2024-2025/CustmerCarApi-belal-allala.git
cd CustomerCareAPI
```
2. **Installer Composer :**
```bash
 composer install
```
3. **Copier**
```bash
.env.example vers .env et configurer la base de données MySQL.
```
4. **Générer la clé :**
```bash
 php artisan key:generate
```
5. **Migrer la base de données :**
```bash
 php artisan migrate
```
6. **Générer la documentation Swagger :**
```bash
 php artisan l5-swagger:generate
```
7. **Démarrer le serveur :**
```bash
 php artisan serve
```
## 📚 Documentation API

La documentation interactive de l'API est disponible via Swagger UI à l'adresse :

[http://localhost:8000/api/documentation](http://localhost:8000/api/documentation)

**Endpoints Principaux :**

* `/api/tickets` : `GET` (Liste des tickets), `POST` (Créer un ticket)
* `/api/tickets/{id}` : `GET` (Afficher un ticket), `PUT` (Mettre à jour), `DELETE` (Supprimer) - Ticket spécifique par ID
* `/api/tickets/{ticket}/responses` : `GET` (Liste des réponses d'un ticket), `POST` (Ajouter une réponse à un ticket)
* `/api/responses/{id}` : `GET` (Afficher une réponse), `PUT` (Mettre à jour), `DELETE` (Supprimer) - Réponse spécifique par ID

## ✅ Tests

Pour exécuter la suite de tests unitaires et fonctionnels PHPUnit :

```bash
php artisan test
```

### 🛠 Tech
**Laravel, PHP, MySQL, Swagger UI, PHPUnit**

## 👨‍💻 Auteur
Belal Allala

