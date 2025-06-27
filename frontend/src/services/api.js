import axios from 'axios'
import toast from 'react-hot-toast'

// Configuration de base d'Axios
const api = axios.create({
  baseURL: '/api',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
  timeout: 10000, // 10 secondes
})

// Intercepteur pour les requêtes
api.interceptors.request.use(
  (config) => {
    console.log(`🚀 ${config.method?.toUpperCase()} ${config.url}`)
    return config
  },
  (error) => {
    console.error('❌ Erreur de requête:', error)
    return Promise.reject(error)
  }
)

// Intercepteur pour les réponses
api.interceptors.response.use(
  (response) => {
    console.log(`✅ ${response.status} ${response.config.url}`)
    return response
  },
  (error) => {
    console.error('❌ Erreur de réponse:', error)
    
    // Gestion des erreurs globales
    if (error.response) {
      const { status, data } = error.response
      
      switch (status) {
        case 400:
          toast.error(data.message || 'Données invalides')
          break
        case 404:
          toast.error(data.message || 'Ressource non trouvée')
          break
        case 500:
          toast.error('Erreur serveur. Veuillez réessayer.')
          break
        default:
          toast.error(data.message || 'Une erreur est survenue')
      }
    } else if (error.request) {
      toast.error('Impossible de joindre le serveur')
    } else {
      toast.error('Erreur de configuration')
    }
    
    return Promise.reject(error)
  }
)

// ============ SERVICES TEACHERS ============
export const teachersApi = {
  // Récupérer tous les enseignants
  getAll: () => api.get('/teachers'),
  
  // Récupérer un enseignant par ID
  getById: (id) => api.get(`/teachers/${id}`),
  
  // Créer un enseignant
  create: (data) => api.post('/teachers', data),
  
  // Modifier un enseignant
  update: (id, data) => api.put(`/teachers/${id}`, data),
  
  // Supprimer un enseignant
  delete: (id) => api.delete(`/teachers/${id}`),
  
  // Récupérer les cours d'un enseignant
  getCourses: (id) => api.get(`/teachers/${id}/courses`),
}

// ============ SERVICES STUDENTS ============
export const studentsApi = {
  // Récupérer tous les étudiants
  getAll: () => api.get('/students'),
  
  // Récupérer un étudiant par ID
  getById: (id) => api.get(`/students/${id}`),
  
  // Créer un étudiant
  create: (data) => api.post('/students', data),
  
  // Modifier un étudiant
  update: (id, data) => api.put(`/students/${id}`, data),
  
  // Supprimer un étudiant
  delete: (id) => api.delete(`/students/${id}`),
  
  // Récupérer les inscriptions d'un étudiant
  getEnrollments: (id) => api.get(`/students/${id}/enrollments`),
}

// ============ SERVICES COURSES ============
export const coursesApi = {
  // Récupérer tous les cours
  getAll: () => api.get('/courses'),
  
  // Récupérer un cours par ID
  getById: (id) => api.get(`/courses/${id}`),
  
  // Créer un cours
  create: (data) => api.post('/courses', data),
  
  // Modifier un cours
  update: (id, data) => api.put(`/courses/${id}`, data),
  
  // Supprimer un cours
  delete: (id) => api.delete(`/courses/${id}`),
  
  // Récupérer les inscriptions d'un cours
  getEnrollments: (id) => api.get(`/courses/${id}/enrollments`),
}

// ============ SERVICES ENROLLMENTS ============
export const enrollmentsApi = {
  // Récupérer toutes les inscriptions
  getAll: () => api.get('/enrollments'),
  
  // Récupérer une inscription par ID
  getById: (id) => api.get(`/enrollments/${id}`),
  
  // Créer une inscription
  create: (data) => api.post('/enrollments', data),
  
  // Modifier une inscription
  update: (id, data) => api.put(`/enrollments/${id}`, data),
  
  // Supprimer une inscription
  delete: (id) => api.delete(`/enrollments/${id}`),
}

// ============ HELPERS ============
export const apiHelpers = {
  // Formater une date pour l'API
  formatDate: (date) => {
    if (!date) return null
    if (typeof date === 'string') return date
    return date.toISOString().split('T')[0]
  },
  
  // Extraire les données de la réponse API
  extractData: (response) => {
    return response.data?.data || response.data
  },
  
  // Extraire le message de succès
  extractMessage: (response) => {
    return response.data?.message || 'Opération réussie'
  },
  
  // Extraire les erreurs de validation
  extractErrors: (error) => {
    return error.response?.data?.errors || []
  },
  
  // Vérifier si la réponse est un succès
  isSuccess: (response) => {
    return response.data?.success !== false && response.status >= 200 && response.status < 300
  },
}

// Export par défaut
export default api 