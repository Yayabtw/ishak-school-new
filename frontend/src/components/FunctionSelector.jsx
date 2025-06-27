import { ChevronDown } from 'lucide-react'
import { Fragment } from 'react'
import { Listbox, Transition } from '@headlessui/react'
import clsx from 'clsx'

const FunctionSelector = ({ functions, selectedFunction, onSelectFunction }) => {
  // Convertir les fonctions en liste simple
  const functionsList = Object.entries(functions).map(([key, func]) => ({
    key,
    ...func
  }))

  // Fonction sélectionnée
  const selectedFunctionObj = selectedFunction ? functions[selectedFunction] : null

  return (
    <div className="max-w-md mx-auto">
      <Listbox value={selectedFunction} onChange={onSelectFunction}>
        <div className="relative">
          <Listbox.Label className="block text-sm font-medium text-gray-700 mb-2">
            Sélectionnez une section
          </Listbox.Label>
          
          <Listbox.Button className="relative w-full bg-white border border-gray-300 rounded-lg pl-3 pr-10 py-3 text-left cursor-pointer focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent shadow-sm hover:border-gray-400 transition-colors duration-200">
            <span className="block truncate">
              {selectedFunctionObj ? (
                <div className="flex items-center">
                  <selectedFunctionObj.icon className="h-5 w-5 text-gray-500 mr-3" />
                  <span className="font-medium">{selectedFunctionObj.label}</span>
                </div>
              ) : (
                <span className="text-gray-500">Choisir une section...</span>
              )}
            </span>
            <span className="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
              <ChevronDown
                className="h-5 w-5 text-gray-400"
                aria-hidden="true"
              />
            </span>
          </Listbox.Button>

          <Transition
            as={Fragment}
            leave="transition ease-in duration-100"
            leaveFrom="opacity-100"
            leaveTo="opacity-0"
          >
            <Listbox.Options className="absolute z-10 mt-1 w-full bg-white shadow-lg max-h-96 rounded-lg py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm">
              {functionsList.map((func) => (
                <Listbox.Option
                  key={func.key}
                  className={({ active }) =>
                    clsx(
                      'relative cursor-pointer select-none py-3 pl-3 pr-9 transition-colors duration-150',
                      active ? 'bg-primary-50 text-primary-900' : 'text-gray-900'
                    )
                  }
                  value={func.key}
                >
                  {({ selected, active }) => (
                    <div className="flex items-center">
                      <func.icon
                        className={clsx(
                          'h-5 w-5 mr-3',
                          active ? 'text-primary-600' : 'text-gray-500'
                        )}
                      />
                      <div className="flex-1">
                        <span
                          className={clsx(
                            'block truncate',
                            selected ? 'font-semibold' : 'font-normal'
                          )}
                        >
                          {func.label}
                        </span>
                        <span className="block text-xs text-gray-500 mt-1">
                          {func.description}
                        </span>
                      </div>
                      
                      {selected && (
                        <span
                          className={clsx(
                            'absolute inset-y-0 right-0 flex items-center pr-3',
                            active ? 'text-primary-600' : 'text-primary-600'
                          )}
                        >
                          <svg className="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path
                              fillRule="evenodd"
                              d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                              clipRule="evenodd"
                            />
                          </svg>
                        </span>
                      )}
                    </div>
                  )}
                </Listbox.Option>
              ))}
            </Listbox.Options>
          </Transition>
        </div>
      </Listbox>
    </div>
  )
}

export default FunctionSelector 