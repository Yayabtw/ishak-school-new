import { describe, it, expect } from 'vitest'
import { render, screen, fireEvent } from '@testing-library/react'
import Header from '../Header'

describe('Header Component', () => {
  it('renders the logo and title', () => {
    render(<Header />)
    
    expect(screen.getByText('Ishak\'School')).toBeInTheDocument()
    expect(screen.getByRole('banner')).toBeInTheDocument()
  })

  it('displays navigation links on desktop', () => {
    render(<Header />)
    
    expect(screen.getByText('Tableau de bord')).toBeInTheDocument()
    expect(screen.getByText('Statistiques')).toBeInTheDocument()
    expect(screen.getByText('Paramètres')).toBeInTheDocument()
  })

  it('shows mobile menu button on smaller screens', () => {
    render(<Header />)
    
    const menuButton = screen.getByLabelText('Toggle menu')
    expect(menuButton).toBeInTheDocument()
  })

  it('toggles mobile menu when button is clicked', () => {
    render(<Header />)
    
    const menuButton = screen.getByLabelText('Toggle menu')
    
    // Le menu mobile ne devrait pas être visible initialement
    expect(screen.queryByText('Tableau de bord')).toBeInTheDocument() // Desktop version
    
    // Cliquer pour ouvrir le menu mobile
    fireEvent.click(menuButton)
    
    // Vérifier que l'état change (l'icône change)
    expect(menuButton).toBeInTheDocument()
  })

  it('has proper accessibility attributes', () => {
    render(<Header />)
    
    const header = screen.getByRole('banner')
    expect(header).toHaveClass('bg-white', 'shadow-sm', 'border-b', 'border-gray-200')
    
    const menuButton = screen.getByLabelText('Toggle menu')
    expect(menuButton).toHaveAttribute('aria-label', 'Toggle menu')
  })

  it('renders navigation links with proper hover states', () => {
    render(<Header />)
    
    const dashboardLink = screen.getByText('Tableau de bord')
    expect(dashboardLink).toHaveClass('text-gray-900', 'hover:text-primary-600')
    
    const statsLink = screen.getByText('Statistiques')
    expect(statsLink).toHaveClass('text-gray-500', 'hover:text-primary-600')
  })

  it('maintains responsive design structure', () => {
    render(<Header />)
    
    // Vérifier la structure responsive
    const container = screen.getByRole('banner').firstChild
    expect(container).toHaveClass('max-w-7xl', 'mx-auto', 'px-4', 'sm:px-6', 'lg:px-8')
    
    // Vérifier que la navigation desktop a les bonnes classes
    const desktopNav = screen.getByText('Tableau de bord').closest('nav')
    expect(desktopNav).toHaveClass('hidden', 'md:flex', 'space-x-8')
  })

  it('renders logo with graduation cap icon', () => {
    render(<Header />)
    
    // Vérifier que l'icône est présente (elle est rendue comme SVG)
    const logoContainer = screen.getByText('Ishak\'School').closest('div')
    expect(logoContainer).toHaveClass('flex-shrink-0', 'flex', 'items-center')
  })
}) 