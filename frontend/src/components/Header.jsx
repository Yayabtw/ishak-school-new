import { GraduationCap, Menu, X } from 'lucide-react'
import { useState } from 'react'

const Header = () => {
  const [isMenuOpen, setIsMenuOpen] = useState(false)

  return (
    <header className="bg-white shadow-sm border-b border-gray-200">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="flex justify-between items-center h-16">
          {/* Logo et titre */}
          <div className="flex items-center">
            <div className="flex-shrink-0 flex items-center">
              <img
                src="../../assets/images/LogoIshak.svg"
                alt="Ishak'School Logo"
                className="h-12 w-12"
              />
              <span className="ml-2 text-xl font-bold text-gray-900 hidden sm:block">
                'School
              </span>
            </div>
          </div>

          {/* Bouton menu mobile */}
          <div className="md:hidden">
            <button
              onClick={() => setIsMenuOpen(!isMenuOpen)}
              className="text-gray-500 hover:text-gray-900 focus:outline-none focus:text-gray-900 p-2"
              aria-label="Toggle menu"
            >
              {isMenuOpen ? (
                <X className="h-6 w-6" />
              ) : (
                <Menu className="h-6 w-6" />
              )}
            </button>
          </div>
        </div>

        {/* Menu mobile */}
        {isMenuOpen && (
          <div className="md:hidden border-t border-gray-200 pt-4 pb-3">
            <div className="space-y-1">
              <a
                href="#"
                className="block px-3 py-2 text-base font-medium text-gray-900 hover:text-primary-600 hover:bg-gray-50 rounded-md transition-colors duration-200"
              >
                Tableau de bord
              </a>
              <a
                href="#"
                className="block px-3 py-2 text-base font-medium text-gray-500 hover:text-primary-600 hover:bg-gray-50 rounded-md transition-colors duration-200"
              >
                Statistiques
              </a>
              <a
                href="#"
                className="block px-3 py-2 text-base font-medium text-gray-500 hover:text-primary-600 hover:bg-gray-50 rounded-md transition-colors duration-200"
              >
                Paramètres
              </a>
            </div>
          </div>
        )}
      </div>
    </header>
  )
}

export default Header 