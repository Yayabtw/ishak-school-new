# 🧱 Backend Symfony - Ishak'School

Documentation technique complète du backend API REST pour la plateforme Ishak'School.

## 📋 Vue d'ensemble

API REST développée avec **Symfony 6.3** qui gère les opérations CRUD sur 4 entités principales :
- **Teachers** (Enseignants)
- **Students** (Étudiants)  
- **Courses** (Cours)
- **Enrollments** (Inscriptions)

## 🏗️ Architecture

### Entités et Relations

```
Teacher (1) ←→ (N) Course (1) ←→ (N) Enrollment (N) ←→ (1) Student
```

#### **Teacher** (Enseignant)
- `id` : Identifiant unique
- `firstName` : Prénom (obligatoire, 2-100 caractères)
- `lastName` : Nom (obligatoire, 2-100 caractères)
- `email` : Email (obligatoire, unique, format email)
- `phone` : Téléphone (optionnel, format validé)
- `speciality` : Spécialité (optionnel)
- `createdAt/updatedAt` : Horodatage automatique
- **Relations** : OneToMany vers Course

#### **Student** (Étudiant)
- `id` : Identifiant unique
- `firstName` : Prénom (obligatoire, 2-100 caractères)
- `lastName` : Nom (obligatoire, 2-100 caractères)
- `email` : Email (obligatoire, unique, format email)
- `phone` : Téléphone (optionnel, format validé)
- `birthDate` : Date de naissance (optionnel, < aujourd'hui)
- `address` : Adresse (optionnel)
- `studentNumber` : Numéro étudiant (auto-généré)
- `createdAt/updatedAt` : Horodatage automatique
- **Relations** : OneToMany vers Enrollment

#### **Course** (Cours)
- `id` : Identifiant unique
- `name` : Nom du cours (obligatoire, 3-200 caractères)
- `code` : Code cours (obligatoire, unique, format: ABC123)
- `description` : Description (optionnel)
- `credits` : Nombre de crédits (obligatoire, 1-10)
- `maxCapacity` : Capacité maximum (optionnel, > 0)
- `semester` : Semestre (Automne/Hiver/Printemps/Été)
- `year` : Année (2020-2030)
- `teacher_id` : Enseignant assigné (obligatoire)
- `createdAt/updatedAt` : Horodatage automatique
- **Relations** : ManyToOne vers Teacher, OneToMany vers Enrollment

#### **Enrollment** (Inscription)
- `id` : Identifiant unique
- `student_id` : Étudiant inscrit (obligatoire)
- `course_id` : Cours suivi (obligatoire)
- `enrollmentDate` : Date d'inscription (auto)
- `status` : Statut (Active/Completed/Dropped/Pending)
- `grade` : Note sur 20 (optionnel, 0-20)
- `notes` : Notes complémentaires (optionnel)
- `createdAt/updatedAt` : Horodatage automatique
- **Relations** : ManyToOne vers Student et Course
- **Contrainte** : Unique(student_id, course_id)

## 🛣️ Routes API

### **Teachers** (`/api/teachers`)

| Méthode | Route | Description |
|---------|-------|-------------|
| `GET` | `/api/teachers` | Liste tous les enseignants |
| `GET` | `/api/teachers/{id}` | Détail d'un enseignant |
| `POST` | `/api/teachers` | Créer un enseignant |
| `PUT` | `/api/teachers/{id}` | Modifier un enseignant |
| `DELETE` | `/api/teachers/{id}` | Supprimer un enseignant |
| `GET` | `/api/teachers/{id}/courses` | Cours de l'enseignant |

### **Students** (`/api/students`)

| Méthode | Route | Description |
|---------|-------|-------------|
| `GET` | `/api/students` | Liste tous les étudiants |
| `GET` | `/api/students/{id}` | Détail d'un étudiant |
| `POST` | `/api/students` | Créer un étudiant |
| `PUT` | `/api/students/{id}` | Modifier un étudiant |
| `DELETE` | `/api/students/{id}` | Supprimer un étudiant |
| `GET` | `/api/students/{id}/enrollments` | Inscriptions de l'étudiant |

### **Courses** (`/api/courses`)

| Méthode | Route | Description |
|---------|-------|-------------|
| `GET` | `/api/courses` | Liste tous les cours |
| `GET` | `/api/courses/{id}` | Détail d'un cours |
| `POST` | `/api/courses` | Créer un cours |
| `PUT` | `/api/courses/{id}` | Modifier un cours |
| `DELETE` | `/api/courses/{id}` | Supprimer un cours |
| `GET` | `/api/courses/{id}/enrollments` | Inscriptions du cours |

### **Enrollments** (`/api/enrollments`)

| Méthode | Route | Description |
|---------|-------|-------------|
| `GET` | `/api/enrollments` | Liste toutes les inscriptions |
| `GET` | `/api/enrollments/{id}` | Détail d'une inscription |
| `POST` | `/api/enrollments` | Créer une inscription |
| `PUT` | `/api/enrollments/{id}` | Modifier une inscription |
| `DELETE` | `/api/enrollments/{id}` | Supprimer une inscription |

## 📝 Format des Réponses API

### Réponse de succès
```json
{
  "success": true,
  "data": { ... },
  "message": "Action réalisée avec succès",
  "count": 10
}
```

### Réponse d'erreur
```json
{
  "success": false,
  "message": "Description de l'erreur",
  "errors": ["Détail erreur 1", "Détail erreur 2"],
  "error": "Exception technique (en dev)"
}
```

## 🔄 Validation et Contraintes

### Validation automatique
- **Champs obligatoires** : Validation `@Assert\NotBlank`
- **Formats email** : Validation `@Assert\Email`
- **Longueurs** : Validation `@Assert\Length`
- **Plages de valeurs** : Validation `@Assert\Range`
- **Expressions régulières** : Validation `@Assert\Regex`
- **Choix limités** : Validation `@Assert\Choice`

### Contraintes métier
- Email unique par entité
- Code cours unique
- Une seule inscription par étudiant/cours
- Suppression protégée (enseignant avec cours)

## 🎯 Fonctionnalités Avancées

### Méthodes utilitaires
- `Teacher::getFullName()` : Nom complet
- `Student::getAge()` : Calcul automatique de l'âge
- `Course::isFull()` : Vérification capacité
- `Enrollment::getMention()` : Mention selon la note
- `Enrollment::isPassed()` : Validation du cours

### Sérialisation
- **Groups** : Contrôle des données exposées
- `entity:read` : Données en lecture
- `entity:write` : Données en écriture

### Gestion d'erreurs
- **Try/catch** global sur tous les endpoints
- **Messages d'erreur** explicites en français
- **Codes HTTP** appropriés
- **Validation** avant persistance

## 🌱 Fixtures (Données de test)

### AppFixtures.php
- **5 enseignants** avec spécialités variées
- **15 étudiants** avec données complètes
- **10 cours** dans différentes matières
- **30 inscriptions** avec statuts et notes variés

### Commandes utiles
```bash
# Charger les fixtures
make shell-symfony
php bin/console doctrine:fixtures:load --no-interaction

# Créer une migration
php bin/console make:migration

# Exécuter les migrations
php bin/console doctrine:migrations:migrate
```

## 🔧 Configuration

### Base de données
- **Driver** : MySQL/MariaDB
- **Version** : 10.9
- **Charset** : utf8mb4_unicode_ci
- **URL** : Configurée via variables d'environnement

### CORS
- **Origins** : Autorisé pour tous (*)
- **Methods** : GET, POST, PUT, DELETE, OPTIONS
- **Headers** : Content-Type, Authorization, X-Requested-With

### Sérialisation
- **Format** : JSON
- **Groups** : Contrôle précis des données
- **Circular references** : Gérées par les groups

## ⚡ Optimisations

### Performance
- **Lazy loading** : Chargement à la demande
- **Serialization groups** : Évite les références circulaires
- **Validation** : Une seule passe par entité
- **Auto-timestamping** : Gestion automatique des dates

### Sécurité
- **Validation** : Tous les inputs validés
- **Échappement** : Protection XSS automatique
- **Erreurs** : Pas d'exposition de détails techniques en prod

## 🧪 Tests

### Structure de tests
```bash
tests/
├── Entity/          # Tests unitaires des entités
├── Controller/      # Tests d'intégration des API
└── Repository/      # Tests des requêtes
```

### Commandes de test
```bash
# Tests complets
make test-backend

# Tests spécifiques
php bin/phpunit tests/Controller/TeacherControllerTest.php
```

## 📦 Dépendances principales

```json
{
  "doctrine/doctrine-bundle": "^2.10",
  "doctrine/orm": "^2.16", 
  "symfony/validator": "6.3.*",
  "symfony/serializer": "6.3.*",
  "nelmio/cors-bundle": "^2.3",
  "doctrine/doctrine-fixtures-bundle": "^3.4",
  "fakerphp/faker": "^1.23"
}
```

## 🚀 Prochaines étapes

1. ✅ **Entités** : Créées et validées
2. ✅ **Contrôleurs** : API REST complète  
3. ✅ **Fixtures** : Données de test
4. ⏳ **Tests unitaires** : PHPUnit
5. ⏳ **Frontend React** : Interface utilisateur
6. ⏳ **CI/CD** : GitHub Actions

---

**Créé pour Ishak'School - Équipe Backend : Yanis, Fredy, Elyas** 🎓 