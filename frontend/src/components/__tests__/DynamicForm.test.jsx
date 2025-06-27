import { describe, it, expect, vi, beforeEach } from 'vitest'
import { render, screen, fireEvent, waitFor } from '@testing-library/react'
import { QueryClient, QueryClientProvider } from '@tanstack/react-query'
import DynamicForm from '../DynamicForm'
import { mockTeachersApi, mockStudentsApi } from '../../test/__mocks__/api'

// Mock react-hot-toast
vi.mock('react-hot-toast', () => ({
  default: {
    success: vi.fn(),
    error: vi.fn(),
  },
}))

// Mock des services API
vi.mock('../../services/api', () => ({
  teachersApi: mockTeachersApi,
  studentsApi: mockStudentsApi,
  coursesApi: {
    getAll: vi.fn(() => Promise.resolve({ data: [] })),
    getById: vi.fn(),
    create: vi.fn(),
    update: vi.fn(),
    delete: vi.fn(),
  },
  enrollmentsApi: {
    getAll: vi.fn(() => Promise.resolve({ data: [] })),
    getById: vi.fn(),
    create: vi.fn(),
    update: vi.fn(),
    delete: vi.fn(),
  },
}))

const createTestQueryClient = () => new QueryClient({
  defaultOptions: {
    queries: { retry: false },
    mutations: { retry: false },
  },
})

const renderWithQueryClient = (component) => {
  const queryClient = createTestQueryClient()
  return render(
    <QueryClientProvider client={queryClient}>
      {component}
    </QueryClientProvider>
  )
}

describe('DynamicForm Component', () => {
  beforeEach(() => {
    vi.clearAllMocks()
  })

  describe('Teacher Creation Form', () => {
    const teacherCreateConfig = {
      entity: 'teachers',
      type: 'create'
    }

    it('renders teacher creation form correctly', () => {
      renderWithQueryClient(
        <DynamicForm functionConfig={teacherCreateConfig} />
      )

      expect(screen.getByText('Créer un enseignant')).toBeInTheDocument()
      expect(screen.getByLabelText(/Prénom/)).toBeInTheDocument()
      expect(screen.getByLabelText(/Nom/)).toBeInTheDocument()
      expect(screen.getByLabelText(/Email/)).toBeInTheDocument()
      expect(screen.getByLabelText(/Téléphone/)).toBeInTheDocument()
      expect(screen.getByLabelText(/Spécialité/)).toBeInTheDocument()
    })

    it('shows required field indicators', () => {
      renderWithQueryClient(
        <DynamicForm functionConfig={teacherCreateConfig} />
      )

      // Vérifier les astérisques rouges pour les champs requis
      const requiredIndicators = screen.getAllByText('*')
      expect(requiredIndicators).toHaveLength(4) // firstName, lastName, email, speciality
    })

    it('displays speciality options correctly', () => {
      renderWithQueryClient(
        <DynamicForm functionConfig={teacherCreateConfig} />
      )

      const specialitySelect = screen.getByLabelText(/Spécialité/)
      expect(specialitySelect).toBeInTheDocument()

      // Vérifier quelques options
      fireEvent.click(specialitySelect)
      expect(screen.getByDisplayValue('')).toBeInTheDocument() // Option par défaut
    })

    it('validates required fields on submit', async () => {
      renderWithQueryClient(
        <DynamicForm functionConfig={teacherCreateConfig} />
      )

      const submitButton = screen.getByText('Créer')
      fireEvent.click(submitButton)

      await waitFor(() => {
        expect(screen.getAllByText('Ce champ est requis')).toHaveLength(4)
      })
    })

    it('validates email format', async () => {
      renderWithQueryClient(
        <DynamicForm functionConfig={teacherCreateConfig} />
      )

      const emailInput = screen.getByLabelText(/Email/)
      fireEvent.change(emailInput, { target: { value: 'invalid-email' } })

      const submitButton = screen.getByText('Créer')
      fireEvent.click(submitButton)

      await waitFor(() => {
        expect(screen.getByText('Format email invalide')).toBeInTheDocument()
      })
    })

    it('submits form with valid data', async () => {
      const onSuccess = vi.fn()
      renderWithQueryClient(
        <DynamicForm functionConfig={teacherCreateConfig} onSuccess={onSuccess} />
      )

      // Remplir le formulaire
      fireEvent.change(screen.getByLabelText(/Prénom/), {
        target: { value: 'Jean' }
      })
      fireEvent.change(screen.getByLabelText(/Nom/), {
        target: { value: 'Dupont' }
      })
      fireEvent.change(screen.getByLabelText(/Email/), {
        target: { value: 'jean.dupont@example.com' }
      })
      fireEvent.change(screen.getByLabelText(/Spécialité/), {
        target: { value: 'Informatique' }
      })

      const submitButton = screen.getByText('Créer')
      fireEvent.click(submitButton)

      await waitFor(() => {
        expect(mockTeachersApi.create).toHaveBeenCalledWith({
          firstName: 'Jean',
          lastName: 'Dupont',
          email: 'jean.dupont@example.com',
          speciality: 'Informatique'
        })
      })
    })

    it('resets form when reset button is clicked', () => {
      renderWithQueryClient(
        <DynamicForm functionConfig={teacherCreateConfig} />
      )

      // Remplir un champ
      const firstNameInput = screen.getByLabelText(/Prénom/)
      fireEvent.change(firstNameInput, { target: { value: 'Test' } })
      expect(firstNameInput.value).toBe('Test')

      // Cliquer sur Réinitialiser
      const resetButton = screen.getByText('Réinitialiser')
      fireEvent.click(resetButton)

      // Vérifier que le champ est vide
      expect(firstNameInput.value).toBe('')
    })
  })

  describe('Student Creation Form', () => {
    const studentCreateConfig = {
      entity: 'students',
      type: 'create'
    }

    it('renders student creation form with date field', () => {
      renderWithQueryClient(
        <DynamicForm functionConfig={studentCreateConfig} />
      )

      expect(screen.getByText('Créer un étudiant')).toBeInTheDocument()
      expect(screen.getByLabelText(/Date de naissance/)).toBeInTheDocument()
      expect(screen.getByLabelText(/Adresse/)).toBeInTheDocument()
    })

    it('renders textarea for address field', () => {
      renderWithQueryClient(
        <DynamicForm functionConfig={studentCreateConfig} />
      )

      const addressField = screen.getByLabelText(/Adresse/)
      expect(addressField.tagName).toBe('TEXTAREA')
    })

    it('validates birth date field', async () => {
      renderWithQueryClient(
        <DynamicForm functionConfig={studentCreateConfig} />
      )

      const submitButton = screen.getByText('Créer')
      fireEvent.click(submitButton)

      await waitFor(() => {
        // Birth date est requis
        expect(screen.getAllByText('Ce champ est requis')).toContain(
          expect.any(Object)
        )
      })
    })
  })

  describe('Update Form', () => {
    const teacherUpdateConfig = {
      entity: 'teachers',
      type: 'update'
    }

    it('shows ID selector for update mode', () => {
      renderWithQueryClient(
        <DynamicForm functionConfig={teacherUpdateConfig} />
      )

      expect(screen.getByText('Modifier un enseignant')).toBeInTheDocument()
      expect(screen.getByPlaceholderText('Entrez l\'ID...')).toBeInTheDocument()
      expect(screen.getByText('Sélectionner un élément')).toBeInTheDocument()
    })

    it('loads existing data when ID is entered', async () => {
      renderWithQueryClient(
        <DynamicForm functionConfig={teacherUpdateConfig} />
      )

      const idInput = screen.getByPlaceholderText('Entrez l\'ID...')
      fireEvent.change(idInput, { target: { value: '1' } })

      await waitFor(() => {
        expect(mockTeachersApi.getById).toHaveBeenCalledWith('1')
      })
    })

    it('shows loading state when fetching data', async () => {
      renderWithQueryClient(
        <DynamicForm functionConfig={teacherUpdateConfig} />
      )

      const idInput = screen.getByPlaceholderText('Entrez l\'ID...')
      fireEvent.change(idInput, { target: { value: '1' } })

      expect(screen.getByText('Chargement des données...')).toBeInTheDocument()
    })
  })

  describe('Delete Form', () => {
    const teacherDeleteConfig = {
      entity: 'teachers',
      type: 'delete'
    }

    it('shows confirmation interface for delete mode', () => {
      renderWithQueryClient(
        <DynamicForm functionConfig={teacherDeleteConfig} />
      )

      expect(screen.getByText('Supprimer un enseignant')).toBeInTheDocument()
      expect(screen.getByPlaceholderText('Entrez l\'ID...')).toBeInTheDocument()
    })

    it('shows danger confirmation when item is loaded', async () => {
      // Mock successful API response
      mockTeachersApi.getById.mockResolvedValueOnce({
        data: {
          id: 1,
          firstName: 'Jean',
          lastName: 'Dupont',
          email: 'jean.dupont@example.com'
        }
      })

      renderWithQueryClient(
        <DynamicForm functionConfig={teacherDeleteConfig} />
      )

      const idInput = screen.getByPlaceholderText('Entrez l\'ID...')
      fireEvent.change(idInput, { target: { value: '1' } })

      await waitFor(() => {
        expect(screen.getByText('Confirmer la suppression')).toBeInTheDocument()
        expect(screen.getByText(/irréversible/)).toBeInTheDocument()
        expect(screen.getByText('Supprimer définitivement')).toBeInTheDocument()
      })
    })

    it('calls delete API when confirmed', async () => {
      // Mock successful API response
      mockTeachersApi.getById.mockResolvedValueOnce({
        data: {
          id: 1,
          firstName: 'Jean',
          lastName: 'Dupont',
          email: 'jean.dupont@example.com'
        }
      })

      renderWithQueryClient(
        <DynamicForm functionConfig={teacherDeleteConfig} />
      )

      const idInput = screen.getByPlaceholderText('Entrez l\'ID...')
      fireEvent.change(idInput, { target: { value: '1' } })

      await waitFor(() => {
        const deleteButton = screen.getByText('Supprimer définitivement')
        fireEvent.click(deleteButton)
      })

      await waitFor(() => {
        expect(mockTeachersApi.delete).toHaveBeenCalledWith('1')
      })
    })

    it('allows canceling delete operation', async () => {
      // Mock successful API response
      mockTeachersApi.getById.mockResolvedValueOnce({
        data: {
          id: 1,
          firstName: 'Jean',
          lastName: 'Dupont'
        }
      })

      renderWithQueryClient(
        <DynamicForm functionConfig={teacherDeleteConfig} />
      )

      const idInput = screen.getByPlaceholderText('Entrez l\'ID...')
      fireEvent.change(idInput, { target: { value: '1' } })

      await waitFor(() => {
        const cancelButton = screen.getByText('Annuler')
        fireEvent.click(cancelButton)
      })

      // Vérifier que l'ID est réinitialisé
      expect(idInput.value).toBe('')
    })
  })

  describe('Form Icons and Styling', () => {
    it('displays correct icons for each entity', () => {
      const configs = [
        { entity: 'teachers', type: 'create' },
        { entity: 'students', type: 'create' },
        { entity: 'courses', type: 'create' },
        { entity: 'enrollments', type: 'create' },
      ]

      configs.forEach(config => {
        const { unmount } = renderWithQueryClient(
          <DynamicForm functionConfig={config} />
        )
        
        // Vérifier la présence de l'icône (rendue comme SVG)
        const iconContainer = document.querySelector('.bg-primary-100')
        expect(iconContainer).toBeInTheDocument()
        
        unmount()
      })
    })

    it('shows loading spinner when submitting', async () => {
      renderWithQueryClient(
        <DynamicForm functionConfig={{ entity: 'teachers', type: 'create' }} />
      )

      // Remplir le formulaire minimalement
      fireEvent.change(screen.getByLabelText(/Prénom/), {
        target: { value: 'Test' }
      })
      fireEvent.change(screen.getByLabelText(/Nom/), {
        target: { value: 'User' }
      })
      fireEvent.change(screen.getByLabelText(/Email/), {
        target: { value: 'test@example.com' }
      })
      fireEvent.change(screen.getByLabelText(/Spécialité/), {
        target: { value: 'Informatique' }
      })

      const submitButton = screen.getByText('Créer')
      fireEvent.click(submitButton)

      // Le bouton devrait montrer un loading state
      expect(submitButton).toBeDisabled()
    })
  })

  describe('Form Validation Edge Cases', () => {
    it('handles numeric field validation for courses', () => {
      const courseConfig = { entity: 'courses', type: 'create' }
      
      renderWithQueryClient(
        <DynamicForm functionConfig={courseConfig} />
      )

      // Vérifier les champs numériques
      expect(screen.getByLabelText(/Crédits/)).toHaveAttribute('type', 'number')
      expect(screen.getByLabelText(/Capacité maximale/)).toHaveAttribute('type', 'number')
      expect(screen.getByLabelText(/Année/)).toHaveAttribute('type', 'number')
    })

    it('cleans up empty optional fields before submission', async () => {
      const onSuccess = vi.fn()
      renderWithQueryClient(
        <DynamicForm 
          functionConfig={{ entity: 'teachers', type: 'create' }} 
          onSuccess={onSuccess}
        />
      )

      // Remplir seulement les champs requis
      fireEvent.change(screen.getByLabelText(/Prénom/), {
        target: { value: 'Jean' }
      })
      fireEvent.change(screen.getByLabelText(/Nom/), {
        target: { value: 'Dupont' }
      })
      fireEvent.change(screen.getByLabelText(/Email/), {
        target: { value: 'jean@example.com' }
      })
      fireEvent.change(screen.getByLabelText(/Spécialité/), {
        target: { value: 'Informatique' }
      })

      // Laisser le téléphone vide
      const phoneInput = screen.getByLabelText(/Téléphone/)
      fireEvent.change(phoneInput, { target: { value: '' } })

      const submitButton = screen.getByText('Créer')
      fireEvent.click(submitButton)

      await waitFor(() => {
        expect(mockTeachersApi.create).toHaveBeenCalledWith({
          firstName: 'Jean',
          lastName: 'Dupont',
          email: 'jean@example.com',
          speciality: 'Informatique'
          // phone ne devrait pas être présent car vide
        })
      })
    })
  })
}) 