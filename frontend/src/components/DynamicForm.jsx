import { useState, useEffect } from 'react'
import { useForm } from 'react-hook-form'
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { Save, Trash2, Loader2, AlertTriangle, Search, Calendar, User, Book, GraduationCap } from 'lucide-react'
import toast from 'react-hot-toast'
import { teachersApi, studentsApi, coursesApi, enrollmentsApi } from '../services/api'

// Configuration des champs par entité
const ENTITY_CONFIGS = {
  teachers: {
    name: 'enseignant',
    icon: User,
    fields: [
      { name: 'firstName', label: 'Prénom', type: 'text', required: true },
      { name: 'lastName', label: 'Nom', type: 'text', required: true },
      { name: 'email', label: 'Email', type: 'email', required: true },
      { name: 'phone', label: 'Téléphone', type: 'tel', required: false },
      { 
        name: 'speciality', 
        label: 'Spécialité', 
        type: 'select', 
        required: true,
        options: [
          'Turc',
          'Informatique',
          'Mathématiques',
          'Physique',
          'Chimie',
          'Biologie',
          'Histoire',
          'Géographie',
          'Français',
          'Anglais',
          'Espagnol',
          'Philosophie',
          'Économie'
        ]
      }
    ]
  },
  students: {
    name: 'étudiant',
    icon: GraduationCap,
    fields: [
      { name: 'firstName', label: 'Prénom', type: 'text', required: true },
      { name: 'lastName', label: 'Nom', type: 'text', required: true },
      { name: 'email', label: 'Email', type: 'email', required: true },
      { name: 'phone', label: 'Téléphone', type: 'tel', required: false },
      { name: 'birthDate', label: 'Date de naissance', type: 'date', required: true },
      { name: 'address', label: 'Adresse', type: 'textarea', required: false }
    ]
  },
  courses: {
    name: 'cours',
    icon: Book,
    fields: [
      { name: 'name', label: 'Nom du cours', type: 'text', required: true },
      { name: 'code', label: 'Code du cours', type: 'text', required: true },
      { name: 'description', label: 'Description', type: 'textarea', required: false },
      { name: 'credits', label: 'Crédits', type: 'number', required: true, min: 1, max: 12 },
      { name: 'maxCapacity', label: 'Capacité maximale', type: 'number', required: true, min: 1, max: 200 },
      { 
        name: 'semester', 
        label: 'Semestre', 
        type: 'select', 
        required: true,
        options: ['Automne', 'Hiver', 'Printemps', 'Été']
      },
      { name: 'year', label: 'Année', type: 'number', required: true, min: 2020, max: 2030 },
      { name: 'teacherId', label: 'Enseignant', type: 'select-async', required: true, entity: 'teachers' }
    ]
  },
  enrollments: {
    name: 'inscription',
    icon: Calendar,
    fields: [
      { name: 'studentId', label: 'Étudiant', type: 'select-async', required: true, entity: 'students' },
      { name: 'courseId', label: 'Cours', type: 'select-async', required: true, entity: 'courses' },
      { 
        name: 'status', 
        label: 'Statut', 
        type: 'select', 
        required: true,
        options: ['Actif', 'Terminé', 'Abandonné', 'En attente']
      },
      { name: 'grade', label: 'Note (optionnel)', type: 'number', required: false, min: 0, max: 20, step: 0.5 },
      { name: 'notes', label: 'Notes (optionnel)', type: 'textarea', required: false },
      { name: 'enrollmentDate', label: 'Date d\'inscription', type: 'date', required: true }
    ]
  }
}

// APIs par entité
const ENTITY_APIS = {
  teachers: teachersApi,
  students: studentsApi,
  courses: coursesApi,
  enrollments: enrollmentsApi
}

export default function DynamicForm({ functionConfig, onSuccess, onCancel, prefilledId }) {
  const [selectedId, setSelectedId] = useState(prefilledId || '')
  const [existingData, setExistingData] = useState(null)
  const queryClient = useQueryClient()
  
  const { entity, type } = functionConfig
  const config = ENTITY_CONFIGS[entity]
  const api = ENTITY_APIS[entity]
  const EntityIcon = config.icon

  // Effect pour mettre à jour selectedId quand prefilledId change
  useEffect(() => {
    if (prefilledId) {
      setSelectedId(prefilledId.toString())
    }
  }, [prefilledId])

  // Form setup avec react-hook-form
  const { 
    register, 
    handleSubmit, 
    formState: { errors, isSubmitting }, 
    reset, 
    setValue
  } = useForm()

  // Query pour récupérer les données existantes (update/delete)
  const { data: itemData, isLoading: loadingItem, error: itemError } = useQuery({
    queryKey: [entity, selectedId],
    queryFn: () => api.getById(selectedId),
    enabled: !!selectedId && ['update', 'delete'].includes(type)
  })

  // Effect pour traiter les données récupérées
  useEffect(() => {
    console.log('🔄 DynamicForm useEffect triggered', { itemData, type, selectedId })
    
    if (itemData) {
      console.log('📦 Raw itemData:', itemData)
      const item = itemData.data?.data || itemData.data
      console.log('📋 Extracted item:', item)
      
      setExistingData(item)
      
      if (type === 'update' && item) {
        console.log('✏️ Filling form for update with:', item)
        // Pré-remplir le formulaire avec les données existantes
        Object.keys(item).forEach(key => {
          let value = item[key]
          
          if (key === 'birthDate' || key === 'enrollmentDate') {
            // Formater les dates pour l'input date
            value = item[key]?.split('T')[0] || item[key]
          } else if (key === 'teacher' && item[key]?.id) {
            setValue('teacherId', item[key].id)
            return
          } else if (key === 'studentData' && item[key]?.id) {
            setValue('studentId', item[key].id)
            return
          } else if (key === 'courseData' && item[key]?.id) {
            setValue('courseId', item[key].id)
            return
          }
          
          console.log(`🎯 Setting ${key} = ${value}`)
          setValue(key, value)
        })
      }
    } else if (selectedId && !loadingItem) {
      console.log('❌ No itemData but selectedId exists and not loading')
    }
  }, [itemData, type, setValue, selectedId, loadingItem])

  // Queries pour les select async (teachers, students, courses)
  const { data: teachersData } = useQuery({
    queryKey: ['teachers'],
    queryFn: teachersApi.getAll,
    enabled: config.fields.some(field => field.entity === 'teachers')
  })

  const { data: studentsData } = useQuery({
    queryKey: ['students'],
    queryFn: studentsApi.getAll,
    enabled: config.fields.some(field => field.entity === 'students')
  })

  const { data: coursesData } = useQuery({
    queryKey: ['courses'],
    queryFn: coursesApi.getAll,
    enabled: config.fields.some(field => field.entity === 'courses')
  })

  // Mutations pour CRUD
  const createMutation = useMutation({
    mutationFn: api.create,
    onSuccess: () => {
      queryClient.invalidateQueries([entity])
      toast.success(`${config.name.charAt(0).toUpperCase() + config.name.slice(1)} créé(e) avec succès !`)
      reset()
      onSuccess?.()
    },
    onError: (error) => {
      toast.error(`Erreur lors de la création : ${error.response?.data?.message || error.message}`)
    }
  })

  const updateMutation = useMutation({
    mutationFn: (data) => api.update(selectedId, data),
    onSuccess: () => {
      queryClient.invalidateQueries([entity])
      toast.success(`${config.name.charAt(0).toUpperCase() + config.name.slice(1)} modifié(e) avec succès !`)
      onSuccess?.()
    },
    onError: (error) => {
      toast.error(`Erreur lors de la modification : ${error.response?.data?.message || error.message}`)
    }
  })

  const deleteMutation = useMutation({
    mutationFn: () => api.delete(selectedId),
    onSuccess: () => {
      queryClient.invalidateQueries([entity])
      toast.success(`${config.name.charAt(0).toUpperCase() + config.name.slice(1)} supprimé(e) avec succès !`)
      setSelectedId('')
      onSuccess?.()
    },
    onError: (error) => {
      toast.error(`Erreur lors de la suppression : ${error.response?.data?.message || error.message}`)
    }
  })

  // Handler pour soumission du formulaire
  const onSubmit = (data) => {
    // Nettoyage des données
    const cleanData = { ...data }
    
    // Supprimer les champs vides pour les champs optionnels
    Object.keys(cleanData).forEach(key => {
      if (cleanData[key] === '' || cleanData[key] === null) {
        delete cleanData[key]
      }
    })

    // Conversion des nombres
    if (cleanData.credits) cleanData.credits = parseInt(cleanData.credits)
    if (cleanData.maxCapacity) cleanData.maxCapacity = parseInt(cleanData.maxCapacity)
    if (cleanData.year) cleanData.year = parseInt(cleanData.year)
    if (cleanData.grade) cleanData.grade = parseFloat(cleanData.grade)
    if (cleanData.teacherId) cleanData.teacherId = parseInt(cleanData.teacherId)
    if (cleanData.studentId) cleanData.studentId = parseInt(cleanData.studentId)
    if (cleanData.courseId) cleanData.courseId = parseInt(cleanData.courseId)

    if (type === 'create') {
      createMutation.mutate(cleanData)
    } else if (type === 'update') {
      updateMutation.mutate(cleanData)
    } else if (type === 'delete') {
      deleteMutation.mutate()
    }
  }

  // Helper pour rendre un champ
  const renderField = (field) => {
    const fieldError = errors[field.name]
    
    if (field.type === 'select') {
      return (
        <div key={field.name} className="space-y-2">
          <label className="form-label">
            {field.label}
            {field.required && <span className="text-red-500 ml-1">*</span>}
          </label>
          <select
            {...register(field.name, { 
              required: field.required ? 'Ce champ est requis' : false 
            })}
            className={`form-select ${fieldError ? 'border-red-500' : ''}`}
          >
            <option value="">Sélectionner...</option>
            {field.options.map(option => (
              <option key={option} value={option}>{option}</option>
            ))}
          </select>
          {fieldError && (
            <p className="text-red-500 text-sm">{fieldError.message}</p>
          )}
        </div>
      )
    }

    if (field.type === 'select-async') {
      let options = []
      if (field.entity === 'teachers' && teachersData) {
        options = teachersData.data?.data || teachersData.data || []
      } else if (field.entity === 'students' && studentsData) {
        options = studentsData.data?.data || studentsData.data || []
      } else if (field.entity === 'courses' && coursesData) {
        options = coursesData.data?.data || coursesData.data || []
      }

      return (
        <div key={field.name} className="space-y-2">
          <label className="form-label">
            {field.label}
            {field.required && <span className="text-red-500 ml-1">*</span>}
          </label>
          <select
            {...register(field.name, { 
              required: field.required ? 'Ce champ est requis' : false 
            })}
            className={`form-select ${fieldError ? 'border-red-500' : ''}`}
          >
            <option value="">Sélectionner...</option>
            {options.map(option => (
              <option key={option.id} value={option.id}>
                {
                  field.entity === 'courses' 
                    ? `${option.code} - ${option.name}` 
                    : (option.fullName || option.fullDisplay || `${option.firstName} ${option.lastName}` || option.name)
                }
              </option>
            ))}
          </select>
          {fieldError && (
            <p className="text-red-500 text-sm">{fieldError.message}</p>
          )}
        </div>
      )
    }

    if (field.type === 'textarea') {
      return (
        <div key={field.name} className="space-y-2">
          <label className="form-label">
            {field.label}
            {field.required && <span className="text-red-500 ml-1">*</span>}
          </label>
          <textarea
            {...register(field.name, { 
              required: field.required ? 'Ce champ est requis' : false 
            })}
            className={`form-textarea ${fieldError ? 'border-red-500' : ''}`}
            rows={3}
            placeholder={`Entrez ${field.label.toLowerCase()}...`}
          />
          {fieldError && (
            <p className="text-red-500 text-sm">{fieldError.message}</p>
          )}
        </div>
      )
    }

    // Input standard
    return (
      <div key={field.name} className="space-y-2">
        <label className="form-label">
          {field.label}
          {field.required && <span className="text-red-500 ml-1">*</span>}
        </label>
        <input
          type={field.type}
          {...register(field.name, { 
            required: field.required ? 'Ce champ est requis' : false,
            min: field.min ? { value: field.min, message: `Minimum ${field.min}` } : undefined,
            max: field.max ? { value: field.max, message: `Maximum ${field.max}` } : undefined,
            pattern: field.type === 'email' ? {
              value: /^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}$/i,
              message: 'Format email invalide'
            } : undefined
          })}
          step={field.step}
          className={`form-input ${fieldError ? 'border-red-500' : ''}`}
          placeholder={`Entrez ${field.label.toLowerCase()}...`}
        />
        {fieldError && (
          <p className="text-red-500 text-sm">{fieldError.message}</p>
        )}
      </div>
    )
  }

  const isLoading = isSubmitting || createMutation.isPending || updateMutation.isPending || deleteMutation.isPending

  return (
    <div className="max-w-4xl mx-auto">
      <div className="card">
        <div className="card-header">
          <div className="flex items-center space-x-3">
            <div className="p-2 bg-primary-100 rounded-lg">
              <EntityIcon className="h-6 w-6 text-primary-600" />
            </div>
            <div>
              <h2 className="card-title">
                {type === 'create' && `Créer un ${config.name}`}
                {type === 'update' && `Modifier un ${config.name}`}
                {type === 'delete' && `Supprimer un ${config.name}`}
              </h2>
              <p className="text-sm text-gray-500">
                {type === 'create' && `Ajouter un nouveau ${config.name} au système`}
                {type === 'update' && `Modifier les informations d'un ${config.name} existant`}
                {type === 'delete' && `Supprimer définitivement un ${config.name}`}
              </p>
            </div>
          </div>
        </div>


        {/* Formulaire */}
        {(type === 'create' || existingData) && (
          <form onSubmit={handleSubmit(onSubmit)} className="space-y-6">
            {type === 'delete' ? (
              // Interface de suppression
              <div className="space-y-6">
                <div className="p-6 bg-red-50 border border-red-200 rounded-lg">
                  <div className="flex items-start space-x-3">
                    <AlertTriangle className="h-6 w-6 text-red-600 mt-1" />
                    <div>
                      <h3 className="text-lg font-medium text-red-800">
                        Confirmer la suppression
                      </h3>
                      <p className="text-red-700 mt-1">
                        Vous êtes sur le point de supprimer définitivement ce {config.name}.
                        Cette action est <strong>irréversible</strong>.
                      </p>
                      <div className="mt-4 p-3 bg-white border border-red-200 rounded">
                        <p className="font-medium text-gray-900">
                          {existingData?.fullName || existingData?.fullDisplay || 
                           `${existingData?.firstName} ${existingData?.lastName}` || 
                           existingData?.name || `ID ${existingData?.id}`}
                        </p>
                        {existingData?.email && (
                          <p className="text-sm text-gray-600">{existingData.email}</p>
                        )}
                      </div>
                    </div>
                  </div>
                </div>
                
                <div className="flex justify-end space-x-4">
                  <button
                    type="button"
                    onClick={() => {
                      setSelectedId('')
                      onCancel?.()
                    }}
                    className="btn-secondary"
                  >
                    Annuler
                  </button>
                  <button
                    type="submit"
                    disabled={isLoading}
                    className="btn-danger inline-flex items-center"
                  >
                    {isLoading ? (
                      <Loader2 className="h-4 w-4 mr-2 animate-spin" />
                    ) : (
                      <Trash2 className="h-4 w-4 mr-2" />
                    )}
                    Supprimer définitivement
                  </button>
                </div>
              </div>
            ) : (
              // Interface de création/modification
              <div className="space-y-6">
                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                  {config.fields.map(renderField)}
                </div>

                <div className="flex justify-end space-x-4 pt-6 border-t">
                  {type === 'create' && (
                    <button
                      type="button"
                      onClick={() => reset()}
                      className="btn-secondary"
                    >
                      Réinitialiser
                    </button>
                  )}
                  <button
                    type="button"
                    onClick={() => {
                      if (type === 'create') {
                        // Pour la création, retourner à la liste
                        onCancel?.()
                      } else {
                        // Pour la modification, annuler et reset
                        reset()
                        setSelectedId('')
                        setExistingData(null)
                      }
                    }}
                    className="btn-secondary"
                  >
                    {type === 'create' ? 'Retour à la liste' : 'Annuler'}
                  </button>
                  <button
                    type="submit"
                    disabled={isLoading}
                    className="btn-primary inline-flex items-center"
                  >
                    {isLoading ? (
                      <Loader2 className="h-4 w-4 mr-2 animate-spin" />
                    ) : (
                      <Save className="h-4 w-4 mr-2" />
                    )}
                    {type === 'create' ? 'Créer' : 'Enregistrer les modifications'}
                  </button>
                </div>
              </div>
            )}
          </form>
        )}

        {/* Message d'instruction pour update/delete */}
        {['update', 'delete'].includes(type) && !selectedId && (
          <div className="text-center py-12">
            <Search className="h-12 w-12 text-gray-400 mx-auto mb-4" />
            <h3 className="text-lg font-medium text-gray-900 mb-2">
              Sélectionner un élément
            </h3>
            <p className="text-gray-500">
              Entrez l'ID de l'élément que vous souhaitez {type === 'update' ? 'modifier' : 'supprimer'}
            </p>
          </div>
        )}
      </div>
    </div>
  )
} 