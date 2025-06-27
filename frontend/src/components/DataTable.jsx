import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query'
import { teachersApi, studentsApi, coursesApi, enrollmentsApi, apiHelpers } from '../services/api'
import { useState } from 'react'
import { 
  Eye, 
  Loader2, 
  AlertCircle, 
  RefreshCw,
  Search,
  Filter,
  Download,
  Users,
  GraduationCap,
  BookOpen,
  ClipboardList,
  Pencil,
  Trash2,
  Plus
} from 'lucide-react'
import toast from 'react-hot-toast'

// Configuration des APIs par entité
const API_CONFIG = {
  teachers: {
    api: teachersApi,
    icon: Users,
    title: 'Enseignants',
    singularTitle: 'enseignant',
  },
  students: {
    api: studentsApi,
    icon: GraduationCap,
    title: 'Étudiants',
    singularTitle: 'étudiant',
  },
  courses: {
    api: coursesApi,
    icon: BookOpen,
    title: 'Cours',
    singularTitle: 'cours',
  },
  enrollments: {
    api: enrollmentsApi,
    icon: ClipboardList,
    title: 'Inscriptions',
    singularTitle: 'inscription',
  },
}

// Configuration des colonnes par entité
const COLUMNS_CONFIG = {
  teachers: [
    { key: 'id', label: 'ID', width: 'w-16' },
    { key: 'firstName', label: 'Prénom', width: 'w-32' },
    { key: 'lastName', label: 'Nom', width: 'w-32' },
    { key: 'email', label: 'Email', width: 'w-48' },
    { key: 'phone', label: 'Téléphone', width: 'w-32' },
    { key: 'speciality', label: 'Spécialité', width: 'w-32' },
    { key: 'createdAt', label: 'Créé le', width: 'w-32', type: 'date' },
  ],
  students: [
    { key: 'id', label: 'ID', width: 'w-16' },
    { key: 'studentNumber', label: 'N° Étudiant', width: 'w-24' },
    { key: 'firstName', label: 'Prénom', width: 'w-32' },
    { key: 'lastName', label: 'Nom', width: 'w-32' },
    { key: 'email', label: 'Email', width: 'w-48' },
    { key: 'birthDate', label: 'Naissance', width: 'w-32', type: 'date' },
    { key: 'createdAt', label: 'Créé le', width: 'w-32', type: 'date' },
  ],
  courses: [
    { key: 'id', label: 'ID', width: 'w-16' },
    { key: 'code', label: 'Code', width: 'w-24' },
    { key: 'name', label: 'Nom du cours', width: 'w-48' },
    { key: 'credits', label: 'Crédits', width: 'w-20' },
    { key: 'semester', label: 'Semestre', width: 'w-24' },
    { key: 'year', label: 'Année', width: 'w-20' },
    { key: 'teacher', label: 'Enseignant', width: 'w-32', type: 'object', subKey: 'fullName' },
  ],
  enrollments: [
    { key: 'id', label: 'ID', width: 'w-16' },
    { key: 'studentData', label: 'Étudiant', width: 'w-32', type: 'object', subKey: 'fullName' },
    { key: 'courseData', label: 'Cours', width: 'w-32', type: 'object', subKey: 'name' },
    { key: 'status', label: 'Statut', width: 'w-24', type: 'badge' },
    { key: 'grade', label: 'Note', width: 'w-20', type: 'grade' },
    { key: 'enrollmentDate', label: 'Inscrit le', width: 'w-32', type: 'date' },
  ],
}

const DataTable = ({ entity, refreshKey, onEditItem, onCreateItem }) => {
  const [searchTerm, setSearchTerm] = useState('')
  const [filterStatus, setFilterStatus] = useState('')
  const queryClient = useQueryClient()
  
  const config = API_CONFIG[entity]
  const columns = COLUMNS_CONFIG[entity]
  
  // Query pour récupérer les données
  const {
    data: response,
    isLoading,
    error,
    refetch,
  } = useQuery({
    queryKey: [entity, refreshKey],
    queryFn: () => config.api.getAll(),
    select: (response) => apiHelpers.extractData(response),
    onError: (error) => {
      toast.error(`Erreur lors du chargement des ${config.title.toLowerCase()}`)
    },
  })

  const data = response || []

  // Mutation pour la suppression
  const deleteMutation = useMutation({
    mutationFn: (id) => config.api.delete(id),
    onSuccess: () => {
      queryClient.invalidateQueries([entity])
      toast.success(`${config.singularTitle.charAt(0).toUpperCase() + config.singularTitle.slice(1)} supprimé(e) avec succès !`)
    },
    onError: (error) => {
      toast.error(`Erreur lors de la suppression : ${error.response?.data?.message || error.message}`)
    }
  })

  // Gestion de la suppression
  const handleDelete = (item) => {
    const itemName = item.fullName || item.fullDisplay || 
                     `${item.firstName} ${item.lastName}` || 
                     item.name || `ID ${item.id}`
    
    if (window.confirm(`Êtes-vous sûr de vouloir supprimer "${itemName}" ?\n\nCette action est irréversible.`)) {
      deleteMutation.mutate(item.id)
    }
  }

  // Gestion de la modification
  const handleEdit = (item) => {
    if (onEditItem) {
      onEditItem(entity, item.id)
    }
  }

  // Gestion de la création
  const handleCreate = () => {
    if (onCreateItem) {
      onCreateItem(entity)
    }
  }

  // Filtrage des données
  const filteredData = data.filter((item) => {
    const matchesSearch = searchTerm === '' || 
      Object.values(item).some(value => 
        value && value.toString().toLowerCase().includes(searchTerm.toLowerCase())
      )
    
    const matchesFilter = filterStatus === '' || 
      (item.status && item.status.toLowerCase() === filterStatus.toLowerCase())
    
    return matchesSearch && matchesFilter
  })

  // Formatage des valeurs selon le type
  const formatValue = (value, column) => {
    if (!value && value !== 0) return '-'
    
    // Cas spécial pour la colonne teacher
    if (column.key === 'teacher' && value && typeof value === 'object') {
      return `${value.firstName || ''} ${value.lastName || ''}`.trim() || '-'
    }
    
    switch (column.type) {
      case 'date':
        return new Date(value).toLocaleDateString('fr-FR')
      
      case 'object':
        return value[column.subKey] || '-'
      
      case 'badge':
        return (
          <span className={`badge ${getBadgeClass(value)}`}>
            {value}
          </span>
        )
      
      case 'grade':
        return value ? `${value}/20` : '-'
      
      default:
        return value
    }
  }

  // Classes CSS pour les badges de statut
  const getBadgeClass = (status) => {
    switch (status?.toLowerCase()) {
      case 'actif':
        return 'badge-success'
      case 'terminé':
        return 'badge-info'
      case 'abandonné':
        return 'badge-danger'
      case 'en attente':
        return 'badge-warning'
      default:
        return 'badge-info'
    }
  }

  // Export des données au format CSV
  const handleExport = () => {
    try {
      if (!filteredData || filteredData.length === 0) {
        toast.error('Aucune donnée à exporter')
        return
      }

      // Création des en-têtes CSV
      const headers = columns.map(col => col.label).join(',')
      
      // Conversion des données en lignes CSV
      const rows = filteredData.map(item => {
        return columns.map(column => {
          let value = item[column.key]
          
          // Formatage spécial pour les types de données
          if (column.key === 'teacher' && value && typeof value === 'object') {
            value = `"${value.firstName || ''} ${value.lastName || ''}".trim()`
          } else if (column.type === 'date' && value) {
            value = new Date(value).toLocaleDateString('fr-FR')
          } else if (column.type === 'object' && value) {
            value = value[column.subKey] || ''
          } else if (value === null || value === undefined) {
            value = ''
          }
          
          // Échapper les virgules et guillemets dans les valeurs
          const stringValue = String(value)
          if (stringValue.includes(',') || stringValue.includes('"') || stringValue.includes('\n')) {
            return `"${stringValue.replace(/"/g, '""')}"`
          }
          
          return stringValue
        }).join(',')
      })

      // Assemblage du contenu CSV
      const csvContent = [headers, ...rows].join('\n')
      
      // Création et téléchargement du fichier
      const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' })
      const link = document.createElement('a')
      const url = URL.createObjectURL(blob)
      
      link.setAttribute('href', url)
      link.setAttribute('download', `${config.title.toLowerCase()}_${new Date().toISOString().split('T')[0]}.csv`)
      link.style.visibility = 'hidden'
      
      document.body.appendChild(link)
      link.click()
      document.body.removeChild(link)
      
      toast.success(`Export de ${filteredData.length} ${config.singularTitle}(s) réussi !`)
      
    } catch (error) {
      console.error('Erreur lors de l\'export:', error)
      toast.error('Erreur lors de l\'export des données')
    }
  }

  if (isLoading) {
    return (
      <div className="card">
        <div className="flex items-center justify-center py-12">
          <Loader2 className="h-8 w-8 animate-spin text-primary-600 mr-3" />
          <span className="text-gray-600">Chargement des {config.title.toLowerCase()}...</span>
        </div>
      </div>
    )
  }

  if (error) {
    return (
      <div className="card">
        <div className="text-center py-12">
          <AlertCircle className="h-12 w-12 text-red-500 mx-auto mb-4" />
          <h3 className="text-lg font-medium text-gray-900 mb-2">
            Erreur de chargement
          </h3>
          <p className="text-gray-600 mb-4">
            Impossible de charger les {config.title.toLowerCase()}
          </p>
          <button
            onClick={() => refetch()}
            className="btn-primary inline-flex items-center"
          >
            <RefreshCw className="h-4 w-4 mr-2" />
            Réessayer
          </button>
        </div>
      </div>
    )
  }

  return (
    <div className="card">
      {/* Header */}
      <div className="card-header">
        <div className="flex items-center justify-between">
          <div className="flex items-center">
            <config.icon className="h-6 w-6 text-primary-600 mr-3" />
            <div>
              <h2 className="card-title">{config.title}</h2>
              <p className="text-sm text-gray-600 mt-1">
                {filteredData.length} {config.singularTitle}(s) trouvé(s)
              </p>
            </div>
          </div>
          
          <div className="flex items-center space-x-2">
            <button
              onClick={handleCreate}
              className="btn-primary btn-sm inline-flex items-center"
              title={`Ajouter ${config.singularTitle}`}
            >
              <Plus className="h-4 w-4 mr-1" />
              Ajouter
            </button>
            <button
              onClick={() => refetch()}
              className="btn-secondary btn-sm inline-flex items-center"
              title="Actualiser"
            >
              <RefreshCw className="h-4 w-4" />
            </button>
            <button
              onClick={handleExport}
              className="btn-secondary btn-sm inline-flex items-center"
              title="Exporter"
            >
              <Download className="h-4 w-4" />
            </button>
          </div>
        </div>
      </div>

      {/* Filtres */}
      <div className="mb-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        {/* Recherche */}
        <div className="relative">
          <Search className="h-5 w-5 absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400" />
          <input
            type="text"
            placeholder="Rechercher..."
            value={searchTerm}
            onChange={(e) => setSearchTerm(e.target.value)}
            className="form-input pl-10"
          />
        </div>

        {/* Filtre par statut (pour les inscriptions) */}
        {entity === 'enrollments' && (
          <div className="relative">
            <Filter className="h-5 w-5 absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400" />
            <select
              value={filterStatus}
              onChange={(e) => setFilterStatus(e.target.value)}
              className="form-select pl-10"
            >
              <option value="">Tous les statuts</option>
              <option value="Actif">Actif</option>
              <option value="Terminé">Terminé</option>
              <option value="Abandonné">Abandonné</option>
              <option value="En attente">En attente</option>
            </select>
          </div>
        )}
      </div>

      {/* Tableau */}
      {filteredData.length === 0 ? (
        <div className="text-center py-12">
          <config.icon className="h-12 w-12 text-gray-400 mx-auto mb-4" />
          <h3 className="text-lg font-medium text-gray-900 mb-2">
            Aucun {config.singularTitle} trouvé
          </h3>
          <p className="text-gray-600">
            {searchTerm || filterStatus 
              ? 'Essayez de modifier vos critères de recherche.' 
              : `Il n'y a pas encore de ${config.title.toLowerCase()} dans la base de données.`
            }
          </p>
        </div>
      ) : (
        <div className="overflow-hidden shadow ring-1 ring-black ring-opacity-5 rounded-lg">
          <div className="overflow-x-auto">
            <table className="table">
              <thead>
                <tr>
                  {columns.map((column) => (
                    <th key={column.key} className={column.width}>
                      {column.label}
                    </th>
                  ))}
                  <th className="w-24">Actions</th>
                </tr>
              </thead>
              <tbody>
                {filteredData.map((item) => (
                  <tr key={item.id}>
                    {columns.map((column) => (
                      <td key={column.key} className={column.width}>
                        {formatValue(item[column.key], column)}
                      </td>
                    ))}
                    <td>
                      <div className="flex items-center space-x-1">
                        <button
                          className="text-yellow-600 hover:text-yellow-800 p-1"
                          title="Modifier"
                          onClick={() => handleEdit(item)}
                        >
                          <Pencil className="h-4 w-4" />
                        </button>
                        <button
                          className="text-red-600 hover:text-red-800 p-1"
                          title="Supprimer"
                          onClick={() => handleDelete(item)}
                          disabled={deleteMutation.isPending}
                        >
                          <Trash2 className="h-4 w-4" />
                        </button>
                      </div>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </div>
      )}
    </div>
  )
}

export default DataTable 