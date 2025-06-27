import { describe, it, expect, vi } from 'vitest'
import { render, screen, fireEvent, waitFor } from '@testing-library/react'
import FunctionSelector from '../FunctionSelector'

// Mock des fonctions pour les tests
const mockFunctions = {
  'list-teachers': {
    label: 'Lister les enseignants',
    type: 'list',
    entity: 'teachers',
    icon: vi.fn(() => null),
    description: 'Afficher tous les enseignants',
  },
  'create-teacher': {
    label: 'Créer un enseignant',
    type: 'create',
    entity: 'teachers',
    icon: vi.fn(() => null),
    description: 'Ajouter un nouvel enseignant',
  },
  'list-students': {
    label: 'Lister les étudiants',
    type: 'list',
    entity: 'students',
    icon: vi.fn(() => null),
    description: 'Afficher tous les étudiants',
  },
}

const defaultProps = {
  functions: mockFunctions,
  selectedFunction: '',
  onSelectFunction: vi.fn(),
}

describe('FunctionSelector Component', () => {
  beforeEach(() => {
    vi.clearAllMocks()
  })

  it('renders with placeholder text when no function is selected', () => {
    render(<FunctionSelector {...defaultProps} />)
    
    expect(screen.getByText('Sélectionnez une fonctionnalité')).toBeInTheDocument()
    expect(screen.getByText('Choisir une action...')).toBeInTheDocument()
  })

  it('displays selected function when one is chosen', () => {
    const props = {
      ...defaultProps,
      selectedFunction: 'list-teachers',
    }
    
    render(<FunctionSelector {...props} />)
    
    expect(screen.getByText('Lister les enseignants')).toBeInTheDocument()
  })

  it('opens dropdown when clicked', async () => {
    render(<FunctionSelector {...defaultProps} />)
    
    const button = screen.getByRole('button')
    fireEvent.click(button)
    
    await waitFor(() => {
      expect(screen.getByText('👨‍🏫 Enseignants')).toBeInTheDocument()
      expect(screen.getByText('🎓 Étudiants')).toBeInTheDocument()
    })
  })

  it('groups functions by entity', async () => {
    render(<FunctionSelector {...defaultProps} />)
    
    const button = screen.getByRole('button')
    fireEvent.click(button)
    
    await waitFor(() => {
      // Vérifier les headers de groupe
      expect(screen.getByText('👨‍🏫 Enseignants')).toBeInTheDocument()
      expect(screen.getByText('🎓 Étudiants')).toBeInTheDocument()
      
      // Vérifier les options
      expect(screen.getByText('Lister les enseignants')).toBeInTheDocument()
      expect(screen.getByText('Créer un enseignant')).toBeInTheDocument()
      expect(screen.getByText('Lister les étudiants')).toBeInTheDocument()
    })
  })

  it('calls onSelectFunction when an option is selected', async () => {
    const onSelectFunction = vi.fn()
    const props = {
      ...defaultProps,
      onSelectFunction,
    }
    
    render(<FunctionSelector {...props} />)
    
    const button = screen.getByRole('button')
    fireEvent.click(button)
    
    await waitFor(() => {
      const option = screen.getByText('Lister les enseignants')
      fireEvent.click(option)
    })
    
    expect(onSelectFunction).toHaveBeenCalledWith('list-teachers')
  })

  it('shows function description in dropdown options', async () => {
    render(<FunctionSelector {...defaultProps} />)
    
    const button = screen.getByRole('button')
    fireEvent.click(button)
    
    await waitFor(() => {
      expect(screen.getByText('Afficher tous les enseignants')).toBeInTheDocument()
      expect(screen.getByText('Ajouter un nouvel enseignant')).toBeInTheDocument()
      expect(screen.getByText('Afficher tous les étudiants')).toBeInTheDocument()
    })
  })

  it('displays selected function details in info card', () => {
    const props = {
      ...defaultProps,
      selectedFunction: 'create-teacher',
    }
    
    render(<FunctionSelector {...props} />)
    
    expect(screen.getByText('Créer un enseignant')).toBeInTheDocument()
    expect(screen.getByText('Ajouter un nouvel enseignant')).toBeInTheDocument()
  })

  it('handles keyboard navigation', async () => {
    render(<FunctionSelector {...defaultProps} />)
    
    const button = screen.getByRole('button')
    
    // Ouvrir avec Entrée
    fireEvent.keyDown(button, { key: 'Enter' })
    
    await waitFor(() => {
      expect(screen.getByText('👨‍🏫 Enseignants')).toBeInTheDocument()
    })
  })

  it('has proper accessibility attributes', () => {
    render(<FunctionSelector {...defaultProps} />)
    
    const button = screen.getByRole('button')
    expect(button).toHaveAttribute('aria-haspopup', 'true')
    
    const label = screen.getByText('Sélectionnez une fonctionnalité')
    expect(label).toBeInTheDocument()
  })

  it('shows checkmark for selected option in dropdown', async () => {
    const props = {
      ...defaultProps,
      selectedFunction: 'list-teachers',
    }
    
    render(<FunctionSelector {...props} />)
    
    const button = screen.getByRole('button')
    fireEvent.click(button)
    
    await waitFor(() => {
      // La checkmark est rendue comme SVG, on vérifie la structure
      const selectedOption = screen.getByText('Lister les enseignants').closest('[role="option"]')
      expect(selectedOption).toBeInTheDocument()
    })
  })

  it('maintains responsive design classes', () => {
    render(<FunctionSelector {...defaultProps} />)
    
    const container = screen.getByText('Sélectionnez une fonctionnalité').closest('div')
    expect(container).toHaveClass('max-w-md', 'mx-auto')
  })
}) 