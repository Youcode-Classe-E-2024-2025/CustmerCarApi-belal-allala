# CustomerCareAPI - API de Gestion de Tickets d'Assistance Client
## 📝 Description Rapide

CustomerCareAPI est une API RESTful développée avec Laravel pour gérer les tickets d'assistance client. Elle permet de créer, lire, mettre à jour et supprimer des tickets, ainsi que d'ajouter des réponses. L'API inclut une documentation Swagger et des tests automatisés.

**Fonctionnalités Principales :**

* Gestion des Tickets (CRUD)
* Système de Réponses
* Pagination et Filtres pour les Tickets
* API RESTful Documentée (Swagger UI)
* Tests Unitaires et Fonctionnels

## 🚀 Installation Facile

### Cloner le dépôt :
```bash
git clone https://github.com/Youcode-Classe-E-2024-2025/CustmerCarApi-belal-allala.git
cd CustomerCareAPI
```

### Installer Composer :
```bash
composer install
```

### Copier `.env.example` vers `.env` et configurer la base de données MySQL.

### Générer la clé :
```bash
php artisan key:generate
```

### Migrer la base de données :
```bash
php artisan migrate
```

### Générer la documentation Swagger :
```bash
php artisan l5-swagger:generate
```

### Démarrer le serveur :
```bash
# php artisan serve
```
