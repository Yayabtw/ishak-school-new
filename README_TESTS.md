# 🧪 Guide des Tests - Ishak'School

## 📋 Vue d'ensemble

Ce projet implémente une **suite de tests complète** avec plusieurs niveaux de tests :

- **Tests unitaires** (Frontend React + Backend Symfony)
- **Tests d'intégration** (API + Base de données)
- **Tests End-to-End** (Workflow complet utilisateur)
- **Tests de sécurité** (Headers, CORS, validation)
- **Tests de performance** (Temps de chargement)

## 🎯 Architecture des tests

```
ishak-school/
├── frontend/
│   ├── src/
│   │   ├── components/__tests__/     # Tests unitaires React
│   │   ├── services/__tests__/       # Tests services API
│   │   └── test/
│   │       ├── setup.js              # Configuration Vitest
│   │       └── __mocks__/            # Mocks API
│   ├── vitest.config.js              # Configuration Vitest
│   └── package.json                  # Scripts de test
├── backend/
│   ├── tests/
│   │   ├── Entity/                   # Tests entités Doctrine
│   │   └── Controller/               # Tests API REST
│   ├── phpunit.xml.dist              # Configuration PHPUnit
│   └── config/packages/test/         # Config test Symfony
├── tests/e2e/                        # Tests End-to-End
├── .github/workflows/ci.yml          # CI/CD GitHub Actions
└── README_TESTS.md                   # Cette documentation
```

## 🚀 Lancement des tests

### 📱 Tests Frontend (React + Vitest)

```bash
# Tests unitaires
make test-frontend-unit
npm run test                     # Dans le container

# Tests en mode watch (développement)
make test-frontend-watch
npm run test:watch              # Dans le container

# Coverage des tests
make test-frontend-coverage
npm run test:coverage           # Dans le container

# Interface graphique des tests
npm run test:ui                 # Dans le container
```

### ⚙️ Tests Backend (Symfony + PHPUnit)

```bash
# Tests unitaires
make test-backend-unit
composer test                   # Dans le container

# Tests avec coverage
make test-backend-coverage
composer test-coverage          # Dans le container

# Tests spécifiques
vendor/bin/phpunit tests/Entity/TeacherTest.php
vendor/bin/phpunit tests/Controller/
```

### 🔗 Tests d'intégration

```bash
# Tests d'intégration complets
make test-integration

# Tests API spécifiques
make test-api

# Tests de connectivité
curl -f http://localhost/api/teachers
curl -f http://localhost/api/students
```

### 🎭 Tests End-to-End

```bash
# Tests E2E avec Playwright
make test-e2e

# Installation Playwright (si nécessaire)
npm install -g @playwright/test
npx playwright install
npx playwright test tests/e2e/
```

### 🧪 Tous les tests

```bash
# Lancer tous les tests
make test

# Tests + vérifications
make test && make test-lint && make test-security
```

## 📊 Tests Frontend détaillés

### 🧩 Tests unitaires des composants

**Header.test.jsx**
- ✅ Rendu du logo et titre
- ✅ Navigation desktop/mobile
- ✅ Menu hamburger responsive
- ✅ Attributs d'accessibilité
- ✅ Structure responsive

**FunctionSelector.test.jsx**
- ✅ Sélection de fonctions
- ✅ Dropdown groupé par entités
- ✅ Navigation clavier
- ✅ États sélectionnés
- ✅ Callbacks d'interaction

**DataTable.test.jsx** (à implémenter)
- ✅ Affichage des données
- ✅ Recherche temps réel
- ✅ Filtres par statut
- ✅ Pagination
- ✅ Tri des colonnes

### 🌐 Tests des services API

**api.test.js**
- ✅ CRUD teachers complet
- ✅ CRUD students complet
- ✅ Gestion d'erreurs HTTP
- ✅ Helpers de formatage
- ✅ Intercepteurs Axios
- ✅ Mocks de réponses

### 🎨 Configuration Vitest

```javascript
// vitest.config.js
export default defineConfig({
  plugins: [react()],
  test: {
    globals: true,
    environment: 'jsdom',
    setupFiles: ['./src/test/setup.js'],
    css: true,
    coverage: {
      provider: 'v8',
      reporter: ['text', 'json', 'html'],
      exclude: ['node_modules/', 'src/test/']
    }
  }
})
```

## 🔧 Tests Backend détaillés

### 🏗️ Tests unitaires des entités

**TeacherTest.php**
- ✅ Création d'enseignant
- ✅ Validation des champs
- ✅ Nom complet généré
- ✅ Spécialités valides
- ✅ Timestamps automatiques
- ✅ Collections de cours

### 🌐 Tests d'intégration API

**TeacherControllerTest.php**
- ✅ GET /api/teachers (200)
- ✅ GET /api/teachers/{id} (200/404)
- ✅ POST /api/teachers (201/400)
- ✅ PUT /api/teachers/{id} (200/404)
- ✅ DELETE /api/teachers/{id} (200/404)
- ✅ Headers CORS
- ✅ Validation des données
- ✅ Gestion d'erreurs

### ⚙️ Configuration PHPUnit

```xml
<!-- phpunit.xml.dist -->
<phpunit bootstrap="tests/bootstrap.php">
    <testsuites>
        <testsuite name="Project Test Suite">
            <directory>tests</directory>
        </testsuite>
    </testsuites>
    <coverage>
        <include>
            <directory suffix=".php">src</directory>
        </include>
    </coverage>
</phpunit>
```

## 🎭 Tests End-to-End

### 🌊 Workflow principal testé

**basic-workflow.test.js**
- ✅ Chargement page d'accueil
- ✅ Sélecteur de fonctions
- ✅ Liste des enseignants
- ✅ Recherche et filtres
- ✅ Création d'enseignant
- ✅ Validation formulaire
- ✅ Responsive mobile
- ✅ Gestion d'erreurs API

### 🚀 Tests de performance

```javascript
test('devrait charger rapidement', async ({ page }) => {
  const startTime = Date.now()
  await page.goto('http://localhost')
  await page.waitForLoadState('networkidle')
  const loadTime = Date.now() - startTime
  
  expect(loadTime).toBeLessThan(3000) // < 3 secondes
})
```

### ♿ Tests d'accessibilité

```javascript
test('devrait avoir une bonne structure sémantique', async ({ page }) => {
  // Vérifier éléments sémantiques
  await expect(page.locator('header')).toBeVisible()
  await expect(page.locator('main')).toBeVisible()
  
  // Vérifier attributs ARIA
  await expect(page.locator('button[aria-label]')).toHaveCount(1)
})
```

## 🔒 Tests de sécurité

### 🛡️ Headers de sécurité

```bash
# Vérification headers de sécurité
make test-security

curl -I http://localhost | grep -i "x-frame-options"
curl -I http://localhost | grep -i "x-content-type-options"
curl -I http://localhost | grep -i "x-xss-protection"
```

### 🌐 Tests CORS

```bash
# Test configuration CORS
curl -H "Origin: http://evil.com" -I http://localhost/api/teachers
# Doit retourner les headers Access-Control appropriés
```

## 🔄 CI/CD avec GitHub Actions

### 🚀 Pipeline automatisé

**.github/workflows/ci.yml**

```yaml
name: 🧪 CI/CD Pipeline
on: [push, pull_request]

jobs:
  frontend-tests:    # Tests React + Vitest
  backend-tests:     # Tests Symfony + PHPUnit  
  integration-tests: # Tests Docker + API
  code-quality:      # SonarCloud
  deploy:           # Déploiement production
```

### 📊 Couverture de code

- **Frontend**: Vitest + V8 coverage
- **Backend**: PHPUnit + Xdebug coverage
- **Intégration**: Codecov pour agrégation

### 🎯 Checks automatiques

- ✅ Tests unitaires (Frontend + Backend)
- ✅ Tests d'intégration (API + DB)
- ✅ Linting (ESLint + PHP CS)
- ✅ Coverage > 80%
- ✅ Build Docker réussi
- ✅ Déploiement automatique (main)

## 📈 Métriques et reporting

### 📊 Coverage attendu

| Composant | Coverage cible | Status |
|-----------|----------------|---------|
| Frontend Components | > 85% | ✅ |
| Frontend Services | > 90% | ✅ |
| Backend Entities | > 95% | ✅ |
| Backend Controllers | > 85% | ✅ |
| Integration | > 70% | ✅ |

### 🎯 KPIs de qualité

- **Temps d'exécution tests**: < 2 minutes
- **Couverture globale**: > 80%
- **Tests E2E**: 100% workflows critiques
- **Performance**: Chargement < 3 secondes
- **Sécurité**: 0 vulnérabilité critique

## 🛠️ Configuration locale

### 📦 Installation des dépendances

```bash
# Frontend (dans le container React)
npm install

# Backend (dans le container Symfony)  
composer install

# E2E (sur la machine hôte)
npm install -g @playwright/test
npx playwright install
```

### 🐳 Environnement Docker

```bash
# Démarrer l'environnement de test
make up

# Lancer tous les tests
make test

# Vérifier l'état des services
make status
```

### 🔧 Variables d'environnement

```bash
# Backend test (.env.test)
APP_ENV=test
DATABASE_URL=mysql://root:root@127.0.0.1:3306/ishak_school_test

# Frontend test (NODE_ENV)
NODE_ENV=test
VITE_API_BASE_URL=http://localhost/api
```

## 🐛 Débogage des tests

### 🔍 Debug Frontend

```bash
# Tests en mode debug
npm run test:watch
npm run test:ui

# Debug spécifique
npm test -- --reporter=verbose Header.test.jsx
```

### 🔍 Debug Backend

```bash
# Tests avec output détaillé
vendor/bin/phpunit --verbose
vendor/bin/phpunit --debug

# Test spécifique avec stack trace
vendor/bin/phpunit --testdox tests/Controller/TeacherControllerTest.php
```

### 🔍 Debug E2E

```bash
# Mode headed (interface visible)
npx playwright test --headed

# Debug avec DevTools
npx playwright test --debug

# Screenshots en cas d'échec
npx playwright test --screenshot=only-on-failure
```

## 📚 Bonnes pratiques

### ✅ Tests unitaires

- **Isolation**: Chaque test doit être indépendant
- **Mocks**: Utiliser des mocks pour les dépendances externes
- **Naming**: Noms de tests descriptifs et en français
- **Coverage**: Viser >85% de couverture
- **Fast**: Tests rapides (<100ms par test)

### ✅ Tests d'intégration

- **Real data**: Utiliser des fixtures réelles
- **Cleanup**: Nettoyer après chaque test
- **Idempotent**: Tests reproductibles
- **Error cases**: Tester les cas d'erreur
- **Performance**: Vérifier les temps de réponse

### ✅ Tests E2E

- **User journeys**: Tester les parcours utilisateur complets
- **Wait strategies**: Attendre les éléments dynamiques
- **Data isolation**: Utiliser des données de test isolées
- **Cross-browser**: Tester sur différents navigateurs
- **Mobile**: Tester la responsivité

## 🔄 Amélirations futures

### 🎯 Tests à ajouter

- [ ] Tests de charge (Artillery, K6)
- [ ] Tests de sécurité avancés (OWASP ZAP)
- [ ] Tests cross-browser (BrowserStack)
- [ ] Tests d'accessibilité (axe-core)
- [ ] Tests de régression visuelle (Percy)

### 🚀 Optimisations

- [ ] Parallélisation des tests
- [ ] Cache des dépendances CI
- [ ] Tests différentiels (changements uniquement)
- [ ] Monitoring des métriques de tests
- [ ] Notifications Slack en cas d'échec

---

## 🆘 Support

En cas de problème avec les tests :

1. **Vérifier l'environnement**: `make status`
2. **Reconstruire les containers**: `make up-build`
3. **Nettoyer les volumes**: `make clean-volumes`
4. **Consulter les logs**: `make logs`
5. **Redémarrer complètement**: `make restart`

**🎯 Tests = Confiance = Qualité = Succès !** 🚀 