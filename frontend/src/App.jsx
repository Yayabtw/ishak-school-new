import { useState } from 'react'
import { GraduationCap, Users, BookOpen, ClipboardList, Database } from 'lucide-react'
import FunctionSelector from './components/FunctionSelector'
import DynamicForm from './components/DynamicForm'
import DataTable from './components/DataTable'
import Header from './components/Header'

// Configuration des fonctionnalités disponibles
const FUNCTIONS = {
  // ========== MENU PRINCIPAL ==========
  'teachers': {
    label: 'Enseignants',
    type: 'list',
    entity: 'teachers',
    icon: Users,
    description: 'Gérer les enseignants',
  },
  'students': {
    label: 'Étudiants', 
    type: 'list',
    entity: 'students',
    icon: GraduationCap,
    description: 'Gérer les étudiants',
  },
  'courses': {
    label: 'Cours',
    type: 'list', 
    entity: 'courses',
    icon: BookOpen,
    description: 'Gérer les cours',
  },
  'enrollments': {
    label: 'Inscriptions',
    type: 'list',
    entity: 'enrollments', 
    icon: ClipboardList,
    description: 'Gérer les inscriptions',
  },
  
  // ========== ACTIONS CRUD (cachées du menu) ==========
  'create-teacher': {
    label: 'Créer un enseignant',
    type: 'create',
    entity: 'teachers',
    icon: Users,
    description: 'Ajouter un nouvel enseignant',
  },
  'update-teacher': {
    label: 'Modifier un enseignant',
    type: 'update',
    entity: 'teachers',
    icon: Users,
    description: 'Mettre à jour un enseignant existant',
  },
  'delete-teacher': {
    label: 'Supprimer un enseignant',
    type: 'delete',
    entity: 'teachers',
    icon: Users,
    description: 'Supprimer un enseignant',
  },
  'create-student': {
    label: 'Créer un étudiant',
    type: 'create',
    entity: 'students',
    icon: GraduationCap,
    description: 'Ajouter un nouvel étudiant',
  },
  'update-student': {
    label: 'Modifier un étudiant',
    type: 'update',
    entity: 'students',
    icon: GraduationCap,
    description: 'Mettre à jour un étudiant existant',
  },
  'delete-student': {
    label: 'Supprimer un étudiant',
    type: 'delete',
    entity: 'students',
    icon: GraduationCap,
    description: 'Supprimer un étudiant',
  },
  'create-course': {
    label: 'Créer un cours',
    type: 'create',
    entity: 'courses',
    icon: BookOpen,
    description: 'Ajouter un nouveau cours',
  },
  'update-course': {
    label: 'Modifier un cours',
    type: 'update',
    entity: 'courses',
    icon: BookOpen,
    description: 'Mettre à jour un cours existant',
  },
  'delete-course': {
    label: 'Supprimer un cours',
    type: 'delete',
    entity: 'courses',
    icon: BookOpen,
    description: 'Supprimer un cours',
  },
  'create-enrollment': {
    label: 'Créer une inscription',
    type: 'create',
    entity: 'enrollments',
    icon: ClipboardList,
    description: 'Ajouter une nouvelle inscription',
  },
  'update-enrollment': {
    label: 'Modifier une inscription',
    type: 'update',
    entity: 'enrollments',
    icon: ClipboardList,
    description: 'Mettre à jour une inscription existante',
  },
  'delete-enrollment': {
    label: 'Supprimer une inscription',
    type: 'delete',
    entity: 'enrollments',
    icon: ClipboardList,
    description: 'Supprimer une inscription',
  },
}

function App() {
  const [selectedFunction, setSelectedFunction] = useState('')
  const [refreshKey, setRefreshKey] = useState(0)
  const [editingItem, setEditingItem] = useState(null)
  
  // Fonction pour rafraîchir les données après une opération
  const handleOperationSuccess = () => {
    setRefreshKey(prev => prev + 1)
    setEditingItem(null) // Reset l'item en cours de modification
  }

  // Fonction pour gérer l'annulation et retour à la liste
  const handleCancel = () => {
    const entityMap = {
      'teacher': 'teachers',
      'student': 'students',
      'course': 'courses', 
      'enrollment': 'enrollments'
    }
    
    if (currentFunction?.entity) {
      setSelectedFunction(entityMap[currentFunction.entity] || currentFunction.entity)
    }
    setEditingItem(null)
  }
  
  // Fonction pour gérer la modification depuis le tableau
  const handleEditItem = (entity, itemId) => {
    setEditingItem({ entity, id: itemId })
    // Convertir le nom de l'entité au singulier pour la fonction update
    const entityMap = {
      'teachers': 'teacher',
      'students': 'student', 
      'courses': 'course',
      'enrollments': 'enrollment'
    }
    setSelectedFunction(`update-${entityMap[entity]}`)
  }

  // Fonction pour gérer la création depuis le tableau
  const handleCreateItem = (entity) => {
    setEditingItem(null) // Reset l'item en cours de modification
    // Convertir le nom de l'entité au singulier pour la fonction create
    const entityMap = {
      'teachers': 'teacher',
      'students': 'student', 
      'courses': 'course',
      'enrollments': 'enrollment'
    }
    setSelectedFunction(`create-${entityMap[entity]}`)
  }
  
  // Récupérer la configuration de la fonction sélectionnée
  const currentFunction = FUNCTIONS[selectedFunction]
  
  return (
    <div className="min-h-screen bg-gray-50">
      {/* Header */}
      <Header />
      
      {/* Contenu principal */}
      <main className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {/* Introduction */}
        <div className="text-center mb-8">
          <div className="flex items-center justify-center mb-4">
            <Database className="h-8 w-8 text-primary-600 mr-3" />
            <h1 className="text-3xl font-bold text-gray-900">
              Ishak'School
            </h1>
          </div>
          <p className="text-lg text-gray-600 max-w-2xl mx-auto">
            Plateforme moderne pour gérer les enseignants, étudiants, cours et inscriptions.
            Sélectionnez une section ci-dessous pour consulter, modifier, supprimer ou ajouter des éléments.
          </p>
        </div>
        
        {/* Sélecteur de fonction */}
        <div className="mb-8">
          <FunctionSelector
            functions={Object.fromEntries(
              Object.entries(FUNCTIONS).filter(([key, _]) => 
                ['teachers', 'students', 'courses', 'enrollments'].includes(key)
              )
            )}
            selectedFunction={selectedFunction}
            onSelectFunction={setSelectedFunction}
          />
        </div>
        
        {/* Contenu dynamique */}
        {currentFunction && (
          <div className="space-y-8">
            {/* Formulaire dynamique pour create, update, delete */}
            {['create', 'update', 'delete'].includes(currentFunction.type) && (
              <div className="animate-slide-up">
                <DynamicForm
                  functionConfig={currentFunction}
                  onSuccess={handleOperationSuccess}
                  onCancel={handleCancel}
                  prefilledId={editingItem?.id}
                />
              </div>
            )}
            
            {/* Tableau de données pour list */}
            {currentFunction.type === 'list' && (
              <div className="animate-slide-up">
                <DataTable
                  entity={currentFunction.entity}
                  refreshKey={refreshKey}
                  onEditItem={handleEditItem}
                  onCreateItem={handleCreateItem}
                />
              </div>
            )}

          </div>
        )}
        
        {/* Message de bienvenue si aucune section sélectionnée */}
        {!selectedFunction && (
          <div className="text-center py-16">
            <div className="max-w-md mx-auto">
              <Database className="h-16 w-16 text-gray-400 mx-auto mb-4" />
              <h3 className="text-lg font-medium text-gray-900 mb-2">
                Aucune section sélectionnée
              </h3>
              <p className="text-gray-600">
                Choisissez une section dans la liste déroulante ci-dessus pour consulter
                et gérer vos données scolaires.
              </p>
            </div>
          </div>
        )}
      </main>
      
      {/* Footer */}
      <footer className="bg-white border-t border-gray-200 mt-16">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
          <div className="text-center text-gray-500 text-sm">
            <p>
              © 2025 Ishak'School - Plateforme de gestion scolaire
            </p>
            <p className="mt-1">
              Développé par la Ishak'Team
            </p>
          </div>
        </div>
      </footer>
    </div>
  )
}

export default App 