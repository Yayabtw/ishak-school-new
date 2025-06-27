import { vi } from 'vitest'

// Mock data
export const mockTeachers = [
  {
    id: 1,
    firstName: 'Jean',
    lastName: 'Dupont',
    email: 'jean.dupont@example.com',
    phone: '+33123456789',
    speciality: 'Informatique',
    fullName: 'Jean Dupont',
    createdAt: '2024-01-01T00:00:00Z',
    updatedAt: '2024-01-01T00:00:00Z',
  },
  {
    id: 2,
    firstName: 'Marie',
    lastName: 'Martin',
    email: 'marie.martin@example.com',
    phone: '+33987654321',
    speciality: 'Mathématiques',
    fullName: 'Marie Martin',
    createdAt: '2024-01-02T00:00:00Z',
    updatedAt: '2024-01-02T00:00:00Z',
  },
]

export const mockStudents = [
  {
    id: 1,
    firstName: 'Pierre',
    lastName: 'Dubois',
    email: 'pierre.dubois@student.example.com',
    phone: '+33555666777',
    birthDate: '2000-05-15',
    address: '123 Rue de la Paix, 75001 Paris',
    studentNumber: 'STU001',
    fullName: 'Pierre Dubois',
    createdAt: '2024-01-01T00:00:00Z',
    updatedAt: '2024-01-01T00:00:00Z',
  },
]

export const mockCourses = [
  {
    id: 1,
    name: 'Introduction à la Programmation',
    code: 'INFO101',
    description: 'Cours d\'introduction aux concepts de base de la programmation',
    credits: 6,
    maxCapacity: 30,
    semester: 'Automne',
    year: 2024,
    teacher: mockTeachers[0],
    fullDisplay: 'INFO101 - Introduction à la Programmation',
    createdAt: '2024-01-01T00:00:00Z',
    updatedAt: '2024-01-01T00:00:00Z',
  },
]

export const mockEnrollments = [
  {
    id: 1,
    student: mockStudents[0],
    course: mockCourses[0],
    status: 'Active',
    grade: 15.5,
    notes: 'Bon travail',
    enrollmentDate: '2024-01-15',
    createdAt: '2024-01-15T00:00:00Z',
    updatedAt: '2024-01-15T00:00:00Z',
  },
]

// API Response mocks
const createSuccessResponse = (data) => ({
  data: { data },
  status: 200,
  statusText: 'OK',
  headers: {},
})

const createErrorResponse = (status = 400, message = 'Error') => ({
  response: {
    data: { message },
    status,
    statusText: 'Error',
  },
})

// Mock functions
export const mockTeachersApi = {
  getAll: vi.fn(() => Promise.resolve(createSuccessResponse(mockTeachers))),
  getById: vi.fn((id) => {
    const teacher = mockTeachers.find(t => t.id === parseInt(id))
    return teacher 
      ? Promise.resolve(createSuccessResponse(teacher))
      : Promise.reject(createErrorResponse(404, 'Teacher not found'))
  }),
  create: vi.fn((data) => {
    const newTeacher = {
      id: Date.now(),
      ...data,
      fullName: `${data.firstName} ${data.lastName}`,
      createdAt: new Date().toISOString(),
      updatedAt: new Date().toISOString(),
    }
    return Promise.resolve(createSuccessResponse(newTeacher))
  }),
  update: vi.fn((id, data) => {
    const teacher = mockTeachers.find(t => t.id === parseInt(id))
    if (!teacher) {
      return Promise.reject(createErrorResponse(404, 'Teacher not found'))
    }
    const updatedTeacher = {
      ...teacher,
      ...data,
      fullName: `${data.firstName} ${data.lastName}`,
      updatedAt: new Date().toISOString(),
    }
    return Promise.resolve(createSuccessResponse(updatedTeacher))
  }),
  delete: vi.fn((id) => {
    const teacher = mockTeachers.find(t => t.id === parseInt(id))
    return teacher
      ? Promise.resolve(createSuccessResponse({ message: 'Teacher deleted successfully' }))
      : Promise.reject(createErrorResponse(404, 'Teacher not found'))
  }),
  getCourses: vi.fn(() => Promise.resolve(createSuccessResponse(mockCourses))),
}

export const mockStudentsApi = {
  getAll: vi.fn(() => Promise.resolve(createSuccessResponse(mockStudents))),
  getById: vi.fn((id) => {
    const student = mockStudents.find(s => s.id === parseInt(id))
    return student 
      ? Promise.resolve(createSuccessResponse(student))
      : Promise.reject(createErrorResponse(404, 'Student not found'))
  }),
  create: vi.fn((data) => {
    const newStudent = {
      id: Date.now(),
      ...data,
      studentNumber: `STU${String(Date.now()).slice(-3)}`,
      fullName: `${data.firstName} ${data.lastName}`,
      createdAt: new Date().toISOString(),
      updatedAt: new Date().toISOString(),
    }
    return Promise.resolve(createSuccessResponse(newStudent))
  }),
  update: vi.fn((id, data) => Promise.resolve(createSuccessResponse({ ...data, id }))),
  delete: vi.fn(() => Promise.resolve(createSuccessResponse({ message: 'Student deleted' }))),
  getEnrollments: vi.fn(() => Promise.resolve(createSuccessResponse(mockEnrollments))),
}

export const mockCoursesApi = {
  getAll: vi.fn(() => Promise.resolve(createSuccessResponse(mockCourses))),
  getById: vi.fn((id) => {
    const course = mockCourses.find(c => c.id === parseInt(id))
    return course 
      ? Promise.resolve(createSuccessResponse(course))
      : Promise.reject(createErrorResponse(404, 'Course not found'))
  }),
  create: vi.fn((data) => Promise.resolve(createSuccessResponse({ ...data, id: Date.now() }))),
  update: vi.fn((id, data) => Promise.resolve(createSuccessResponse({ ...data, id }))),
  delete: vi.fn(() => Promise.resolve(createSuccessResponse({ message: 'Course deleted' }))),
}

export const mockEnrollmentsApi = {
  getAll: vi.fn(() => Promise.resolve(createSuccessResponse(mockEnrollments))),
  getById: vi.fn((id) => {
    const enrollment = mockEnrollments.find(e => e.id === parseInt(id))
    return enrollment 
      ? Promise.resolve(createSuccessResponse(enrollment))
      : Promise.reject(createErrorResponse(404, 'Enrollment not found'))
  }),
  create: vi.fn((data) => Promise.resolve(createSuccessResponse({ ...data, id: Date.now() }))),
  update: vi.fn((id, data) => Promise.resolve(createSuccessResponse({ ...data, id }))),
  delete: vi.fn(() => Promise.resolve(createSuccessResponse({ message: 'Enrollment deleted' }))),
}

export const mockApiHelpers = {
  formatDate: vi.fn((date) => {
    if (!date) return null
    if (typeof date === 'string') return date
    return date.toISOString().split('T')[0]
  }),
  extractData: vi.fn((response) => response.data?.data || response.data),
  extractMessage: vi.fn((response) => response.data?.message || 'Operation successful'),
  extractErrors: vi.fn((error) => error.response?.data?.errors || []),
  isSuccess: vi.fn((response) => response.status >= 200 && response.status < 300),
} 