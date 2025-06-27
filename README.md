# 🏫 Ishak'School

Une plateforme web moderne pour la gestion scolaire, développée avec React, Symfony, MariaDB et conteneurisée avec Docker.

## 🚀 Technologies utilisées

- **Frontend** : React avec Vite
- **Backend** : Symfony (API REST)
- **Base de données** : MariaDB
- **Reverse Proxy** : Nginx
- **Conteneurisation** : Docker & Docker Compose
- **Gestionnaire BDD** : PhpMyAdmin

## 📁 Structure du projet

```
ishak-school/
├── docker-compose.yaml          # Configuration des services Docker
├── Makefile                     # Commandes simplifiées
├── README.md                    # Documentation
├── frontend/                    # Application React
│   ├── Dockerfile
│   └── ...
├── backend/                     # API Symfony
│   └── ...
├── nginx/                       # Configuration Nginx
│   ├── nginx.conf
│   └── conf.d/default.conf
└── database/                    # Scripts d'initialisation
    └── init/01-init.sql
```

## 🛠️ Installation

### Prérequis

- Docker
- Docker Compose
- Make (optionnel mais recommandé)

### Installation rapide

```bash
# Cloner le projet
git clone <url-du-repo>
cd ishak-school

# Initialiser le projet (construire, démarrer et installer les dépendances)
make init
```

### Installation manuelle

```bash
# Construire les conteneurs
docker-compose build

# Démarrer les services
docker-compose up -d

# Installer les dépendances frontend
docker-compose exec react npm install

# Installer les dépendances backend
docker-compose exec symfony composer install
```

## 🌐 Accès aux services

| Service | URL | Description |
|---------|-----|-------------|
| **Application principale** | http://localhost | Interface utilisateur via Nginx |
| **Frontend React** | http://localhost:3000 | Interface React (développement) |
| **API Symfony** | http://localhost:8000 | API REST backend |
| **PhpMyAdmin** | http://localhost:8080 | Gestionnaire de base de données |
| **Base de données** | localhost:3306 | MariaDB (accès direct) |

### Identifiants par défaut

**Base de données :**
- Utilisateur : `ishak_user`
- Mot de passe : `ishak_password`
- Base : `ishak_db`

**PhpMyAdmin :**
- Utilisateur : `root`
- Mot de passe : `root_password`

## 📋 Commandes disponibles

### Avec Make (recommandé)

```bash
make help           # Afficher toutes les commandes disponibles
make build          # Construire les conteneurs
make up             # Démarrer les services
make down           # Arrêter les services
make restart        # Redémarrer les services
make logs           # Afficher les logs
make status         # Statut des conteneurs
make clean          # Nettoyer complètement
```

### Docker Compose

```bash
docker-compose up -d        # Démarrer
docker-compose down         # Arrêter
docker-compose logs -f      # Logs
docker-compose ps           # Statut
```

## 🧪 Tests

```bash
# Tests frontend
make test-frontend
# ou
docker-compose exec react npm test

# Tests backend
make test-backend
# ou
docker-compose exec symfony php bin/phpunit
```

## 🔧 Développement

### Accéder aux conteneurs

```bash
# Shell React
make shell-react

# Shell Symfony
make shell-symfony

# Shell MariaDB
make shell-db
```

### Hot-reload

- **React** : Hot-reload automatique activé
- **Symfony** : Modifications automatiquement détectées

## 📊 Entités de base

Le projet gère 4 entités principales :

1. **Students** (Élèves)
2. **Teachers** (Enseignants)
3. **Courses** (Cours)
4. **Enrollments** (Inscriptions)

## 🤝 Équipe

- **Backend** : Yanis, Fredy, Elyas
- **Frontend** : Tümay, Ilias

## 📝 Roadmap

- [ ] ✅ Environnement Docker complet
- [ ] Backend Symfony avec entités
- [ ] Frontend React avec interface dynamique
- [ ] Tests unitaires et d'intégration
- [ ] CI/CD avec GitHub Actions

## 🚨 Dépannage

### Les conteneurs ne démarrent pas
```bash
make clean
make build
make up
```

### Problème de permissions
```bash
sudo chown -R $USER:$USER .
```

### Logs d'erreurs
```bash
make logs           # Tous les logs
make logs-nginx     # Logs Nginx
make logs-symfony   # Logs Symfony
```

## 📄 Licence

Projet scolaire - Tous droits réservés. 