# Makefile pour Ishak'School
# Variables
COMPOSE_FILE = docker-compose.yaml
PROJECT_NAME = ishak-school

# Aide
.PHONY: help
help: ## Affiche cette aide
	@echo "🏫 Ishak'School - Commandes disponibles :"
	@echo ""
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-20s\033[0m %s\n", $$1, $$2}'

# Commandes Docker
.PHONY: build
build: ## Construire tous les conteneurs
	@echo "🔨 Construction des conteneurs..."
	docker-compose -f $(COMPOSE_FILE) build

.PHONY: up
up: ## Démarrer tous les services
	@echo "🚀 Démarrage des services..."
	docker-compose -f $(COMPOSE_FILE) up -d

.PHONY: up-build
up-build: ## Construire et démarrer tous les services
	@echo "🔨🚀 Construction et démarrage des services..."
	docker-compose -f $(COMPOSE_FILE) up -d --build

.PHONY: down
down: ## Arrêter tous les services
	@echo "🛑 Arrêt des services..."
	docker-compose -f $(COMPOSE_FILE) down

.PHONY: stop
stop: ## Arrêter tous les services (alias de down)
	@make down

.PHONY: restart
restart: ## Redémarrer tous les services
	@echo "🔄 Redémarrage des services..."
	@make down
	@make up

.PHONY: logs
logs: ## Afficher les logs de tous les services
	docker-compose -f $(COMPOSE_FILE) logs -f

.PHONY: logs-nginx
logs-nginx: ## Afficher les logs de Nginx
	docker-compose -f $(COMPOSE_FILE) logs -f nginx

.PHONY: logs-react
logs-react: ## Afficher les logs de React
	docker-compose -f $(COMPOSE_FILE) logs -f react

.PHONY: logs-symfony
logs-symfony: ## Afficher les logs de Symfony
	docker-compose -f $(COMPOSE_FILE) logs -f symfony

.PHONY: logs-db
logs-db: ## Afficher les logs de MariaDB
	docker-compose -f $(COMPOSE_FILE) logs -f mariadb

# Commandes utilitaires
.PHONY: status
status: ## Afficher le statut des conteneurs
	@echo "📊 Statut des conteneurs :"
	docker-compose -f $(COMPOSE_FILE) ps

.PHONY: clean
clean: ## Nettoyer les conteneurs, images et volumes
	@echo "🧹 Nettoyage..."
	docker-compose -f $(COMPOSE_FILE) down -v --rmi all --remove-orphans

.PHONY: clean-volumes
clean-volumes: ## Supprimer uniquement les volumes
	@echo "🗑️ Suppression des volumes..."
	docker-compose -f $(COMPOSE_FILE) down -v

.PHONY: shell-react
shell-react: ## Accéder au shell du conteneur React
	docker-compose -f $(COMPOSE_FILE) exec react sh

.PHONY: shell-symfony
shell-symfony: ## Accéder au shell du conteneur Symfony
	docker-compose -f $(COMPOSE_FILE) exec symfony bash

.PHONY: shell-db
shell-db: ## Accéder au shell de MariaDB
	docker-compose -f $(COMPOSE_FILE) exec mariadb mysql -u ishak_user -p ishak_db

# Commandes de développement
.PHONY: install-frontend
install-frontend: ## Installer les dépendances frontend
	@echo "📦 Installation des dépendances frontend..."
	docker-compose -f $(COMPOSE_FILE) exec react npm install

.PHONY: install-backend
install-backend: ## Installer les dépendances backend
	@echo "📦 Installation des dépendances backend..."
	docker-compose -f $(COMPOSE_FILE) exec symfony composer install

# 🧪 Commandes de tests
.PHONY: test
test: ## Lancer tous les tests
	@echo "🧪 Lancement de tous les tests..."
	@make test-frontend-unit
	@make test-backend-unit
	@echo "✅ Tous les tests terminés !"

.PHONY: test-frontend
test-frontend: ## Lancer les tests frontend
	@echo "🧪 Tests frontend..."
	docker-compose -f $(COMPOSE_FILE) exec react npm test

.PHONY: test-frontend-unit
test-frontend-unit: ## Tests unitaires frontend
	@echo "🧪 Tests unitaires frontend..."
	docker-compose -f $(COMPOSE_FILE) exec react npm run test

.PHONY: test-frontend-watch
test-frontend-watch: ## Tests frontend en mode watch
	@echo "👁️ Tests frontend en mode watch..."
	docker-compose -f $(COMPOSE_FILE) exec react npm run test:watch

.PHONY: test-frontend-coverage
test-frontend-coverage: ## Coverage des tests frontend
	@echo "📊 Coverage des tests frontend..."
	docker-compose -f $(COMPOSE_FILE) exec react npm run test:coverage

.PHONY: test-backend
test-backend: ## Lancer les tests backend
	@echo "🧪 Tests backend..."
	docker-compose -f $(COMPOSE_FILE) exec symfony php bin/phpunit

.PHONY: test-backend-unit
test-backend-unit: ## Tests unitaires backend
	@echo "🧪 Tests unitaires backend..."
	docker-compose -f $(COMPOSE_FILE) exec symfony vendor/bin/phpunit

.PHONY: test-backend-coverage
test-backend-coverage: ## Coverage des tests backend
	@echo "📊 Coverage des tests backend..."
	docker-compose -f $(COMPOSE_FILE) exec symfony vendor/bin/phpunit --coverage-html coverage

.PHONY: test-integration
test-integration: ## Tests d'intégration
	@echo "🔗 Tests d'intégration..."
	@echo "📋 Vérification de l'état des services..."
	@make status
	@echo "🌐 Test de connectivité Frontend..."
	@curl -f http://localhost || (echo "❌ Frontend inaccessible" && exit 1)
	@echo "✅ Frontend accessible"
	@echo "🔧 Test de connectivité API..."
	@curl -f http://localhost/api/teachers || (echo "❌ API inaccessible" && exit 1)
	@echo "✅ API accessible"
	@echo "✅ Tests d'intégration réussis !"

.PHONY: test-e2e
test-e2e: ## Tests end-to-end (nécessite Playwright)
	@echo "🎭 Tests end-to-end..."
	@if command -v npx >/dev/null 2>&1; then \
		npx playwright test tests/e2e/; \
	else \
		echo "⚠️ Playwright non installé. Installation..."; \
		npm install -g @playwright/test; \
		npx playwright install; \
		npx playwright test tests/e2e/; \
	fi

.PHONY: test-api
test-api: ## Tests API avec curl
	@echo "🔧 Tests API basiques..."
	@echo "📋 Test GET /api/teachers"
	@curl -f -s http://localhost/api/teachers | jq . || echo "❌ Erreur GET teachers"
	@echo "📋 Test GET /api/students"
	@curl -f -s http://localhost/api/students | jq . || echo "❌ Erreur GET students"
	@echo "📋 Test GET /api/courses"
	@curl -f -s http://localhost/api/courses | jq . || echo "❌ Erreur GET courses"
	@echo "✅ Tests API terminés"

.PHONY: test-lint
test-lint: ## Vérification du code (linting)
	@echo "🔍 Vérification du code..."
	@echo "📝 Lint frontend..."
	docker-compose -f $(COMPOSE_FILE) exec react npm run lint
	@echo "✅ Lint terminé"

.PHONY: test-security
test-security: ## Tests de sécurité basiques
	@echo "🔒 Tests de sécurité..."
	@echo "🔧 Vérification des headers de sécurité..."
	@curl -I http://localhost | grep -i "x-frame-options\|x-content-type-options\|x-xss-protection" || echo "⚠️ Headers de sécurité manquants"
	@echo "🔧 Test CORS..."
	@curl -H "Origin: http://evil.com" -I http://localhost/api/teachers | grep -i "access-control" || echo "⚠️ Configuration CORS à vérifier"
	@echo "✅ Tests de sécurité terminés"

# 🌱 Commandes de données
.PHONY: fixtures
fixtures: ## Charger les fixtures de données de test
	@echo "🌱 Chargement des fixtures de données..."
	docker-compose -f $(COMPOSE_FILE) exec symfony php bin/console doctrine:fixtures:load --no-interaction
	@echo "✅ Données de test chargées !"

.PHONY: migrate
migrate: ## Exécuter les migrations de base de données
	@echo "🗄️ Exécution des migrations..."
	docker-compose -f $(COMPOSE_FILE) exec symfony php bin/console doctrine:migrations:migrate --no-interaction

.PHONY: schema-update
schema-update: ## Mettre à jour le schéma de base de données
	@echo "🔄 Mise à jour du schéma..."
	docker-compose -f $(COMPOSE_FILE) exec symfony php bin/console doctrine:schema:update --force

.PHONY: init
init: build up install-frontend install-backend migrate fixtures ## Initialisation complète du projet
	@echo "✅ Projet initialisé avec succès !"
	@echo ""
	@echo "🌐 Services disponibles :"
	@echo "   - Application : http://localhost"
	@echo "   - React : http://localhost:3000"
	@echo "   - Symfony : http://localhost:8000"
	@echo "   - PhpMyAdmin : http://localhost:8080"
	@echo ""
	@echo "🧪 Tests disponibles :"
	@echo "   - make test                 # Tous les tests"
	@echo "   - make test-frontend        # Tests React"
	@echo "   - make test-backend         # Tests Symfony"
	@echo "   - make test-integration     # Tests d'intégration"
	@echo "   - make test-e2e            # Tests End-to-End"
	@echo ""

# Commande par défaut
.DEFAULT_GOAL := help 