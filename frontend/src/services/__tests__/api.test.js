import { describe, it, expect, vi, beforeEach } from 'vitest'
import axios from 'axios'
import { teachersApi, studentsApi, apiHelpers } from '../api'

// Mock d'Axios
vi.mock('axios')
const mockedAxios = vi.mocked(axios)

// Mock de react-hot-toast
vi.mock('react-hot-toast', () => ({
  default: {
    success: vi.fn(),
    error: vi.fn(),
  },
}))

describe('API Services', () => {
  beforeEach(() => {
    vi.clearAllMocks()
  })

  describe('TeachersApi', () => {
    it('should fetch all teachers successfully', async () => {
      const mockTeachers = [
        { id: 1, firstName: 'Jean', lastName: 'Dupont' },
        { id: 2, firstName: 'Marie', lastName: 'Martin' },
      ]
      
      mockedAxios.get.mockResolvedValueOnce({
        data: mockTeachers,
        status: 200,
      })

      const response = await teachersApi.getAll()
      
      expect(mockedAxios.get).toHaveBeenCalledWith('/teachers')
      expect(response.data).toEqual(mockTeachers)
    })

    it('should fetch teacher by id successfully', async () => {
      const mockTeacher = { id: 1, firstName: 'Jean', lastName: 'Dupont' }
      
      mockedAxios.get.mockResolvedValueOnce({
        data: mockTeacher,
        status: 200,
      })

      const response = await teachersApi.getById(1)
      
      expect(mockedAxios.get).toHaveBeenCalledWith('/teachers/1')
      expect(response.data).toEqual(mockTeacher)
    })

    it('should create teacher successfully', async () => {
      const teacherData = {
        firstName: 'Jean',
        lastName: 'Dupont',
        email: 'jean.dupont@example.com',
        speciality: 'Informatique'
      }
      
      const mockResponse = {
        data: { id: 1, ...teacherData },
        status: 201,
      }
      
      mockedAxios.post.mockResolvedValueOnce(mockResponse)

      const response = await teachersApi.create(teacherData)
      
      expect(mockedAxios.post).toHaveBeenCalledWith('/teachers', teacherData)
      expect(response.data.id).toBe(1)
      expect(response.data.firstName).toBe('Jean')
    })

    it('should update teacher successfully', async () => {
      const teacherData = {
        firstName: 'Jean Updated',
        lastName: 'Dupont',
        email: 'jean.updated@example.com',
        speciality: 'Informatique'
      }
      
      const mockResponse = {
        data: { id: 1, ...teacherData },
        status: 200,
      }
      
      mockedAxios.put.mockResolvedValueOnce(mockResponse)

      const response = await teachersApi.update(1, teacherData)
      
      expect(mockedAxios.put).toHaveBeenCalledWith('/teachers/1', teacherData)
      expect(response.data.firstName).toBe('Jean Updated')
    })

    it('should delete teacher successfully', async () => {
      const mockResponse = {
        data: { message: 'Teacher deleted successfully' },
        status: 200,
      }
      
      mockedAxios.delete.mockResolvedValueOnce(mockResponse)

      const response = await teachersApi.delete(1)
      
      expect(mockedAxios.delete).toHaveBeenCalledWith('/teachers/1')
      expect(response.data.message).toBe('Teacher deleted successfully')
    })

    it('should fetch teacher courses successfully', async () => {
      const mockCourses = [
        { id: 1, name: 'Informatique 101', code: 'INFO101' },
      ]
      
      mockedAxios.get.mockResolvedValueOnce({
        data: mockCourses,
        status: 200,
      })

      const response = await teachersApi.getCourses(1)
      
      expect(mockedAxios.get).toHaveBeenCalledWith('/teachers/1/courses')
      expect(response.data).toEqual(mockCourses)
    })
  })

  describe('StudentsApi', () => {
    it('should fetch all students successfully', async () => {
      const mockStudents = [
        { id: 1, firstName: 'Pierre', lastName: 'Dubois', studentNumber: 'STU001' },
      ]
      
      mockedAxios.get.mockResolvedValueOnce({
        data: mockStudents,
        status: 200,
      })

      const response = await studentsApi.getAll()
      
      expect(mockedAxios.get).toHaveBeenCalledWith('/students')
      expect(response.data).toEqual(mockStudents)
    })

    it('should create student with generated student number', async () => {
      const studentData = {
        firstName: 'Pierre',
        lastName: 'Dubois',
        email: 'pierre.dubois@student.example.com',
        birthDate: '2000-05-15'
      }
      
      const mockResponse = {
        data: { 
          id: 1, 
          ...studentData, 
          studentNumber: 'STU001' 
        },
        status: 201,
      }
      
      mockedAxios.post.mockResolvedValueOnce(mockResponse)

      const response = await studentsApi.create(studentData)
      
      expect(mockedAxios.post).toHaveBeenCalledWith('/students', studentData)
      expect(response.data.studentNumber).toBe('STU001')
    })

    it('should fetch student enrollments successfully', async () => {
      const mockEnrollments = [
        { 
          id: 1, 
          student: { id: 1, firstName: 'Pierre' },
          course: { id: 1, name: 'Informatique 101' },
          status: 'Active'
        },
      ]
      
      mockedAxios.get.mockResolvedValueOnce({
        data: mockEnrollments,
        status: 200,
      })

      const response = await studentsApi.getEnrollments(1)
      
      expect(mockedAxios.get).toHaveBeenCalledWith('/students/1/enrollments')
      expect(response.data).toEqual(mockEnrollments)
    })
  })

  describe('ApiHelpers', () => {
    it('should format date correctly', () => {
      const date = new Date('2024-01-15T10:30:00Z')
      const formattedDate = apiHelpers.formatDate(date)
      
      expect(formattedDate).toBe('2024-01-15')
    })

    it('should handle null date', () => {
      const formattedDate = apiHelpers.formatDate(null)
      
      expect(formattedDate).toBeNull()
    })

    it('should handle string date', () => {
      const dateString = '2024-01-15'
      const formattedDate = apiHelpers.formatDate(dateString)
      
      expect(formattedDate).toBe('2024-01-15')
    })

    it('should extract data from response', () => {
      const response1 = { data: { data: { id: 1, name: 'Test' } } }
      const response2 = { data: { id: 1, name: 'Test' } }
      
      expect(apiHelpers.extractData(response1)).toEqual({ id: 1, name: 'Test' })
      expect(apiHelpers.extractData(response2)).toEqual({ id: 1, name: 'Test' })
    })

    it('should extract message from response', () => {
      const response1 = { data: { message: 'Success message' } }
      const response2 = { data: {} }
      
      expect(apiHelpers.extractMessage(response1)).toBe('Success message')
      expect(apiHelpers.extractMessage(response2)).toBe('Opération réussie')
    })

    it('should extract errors from error response', () => {
      const error1 = { 
        response: { 
          data: { 
            errors: ['Email is required', 'Name is too short'] 
          } 
        } 
      }
      const error2 = { response: { data: {} } }
      
      expect(apiHelpers.extractErrors(error1)).toEqual(['Email is required', 'Name is too short'])
      expect(apiHelpers.extractErrors(error2)).toEqual([])
    })

    it('should check if response is successful', () => {
      const successResponse = { data: { success: true }, status: 200 }
      const errorResponse = { data: { success: false }, status: 400 }
      const validResponse = { data: {}, status: 201 }
      
      expect(apiHelpers.isSuccess(successResponse)).toBe(true)
      expect(apiHelpers.isSuccess(errorResponse)).toBe(false)
      expect(apiHelpers.isSuccess(validResponse)).toBe(true)
    })
  })

  describe('Error Handling', () => {
    it('should handle network errors', async () => {
      const networkError = new Error('Network Error')
      networkError.request = {}
      
      mockedAxios.get.mockRejectedValueOnce(networkError)

      try {
        await teachersApi.getAll()
      } catch (error) {
        expect(error.message).toBe('Network Error')
      }
    })

    it('should handle HTTP error responses', async () => {
      const httpError = {
        response: {
          status: 404,
          data: { message: 'Teacher not found' }
        }
      }
      
      mockedAxios.get.mockRejectedValueOnce(httpError)

      try {
        await teachersApi.getById(999)
      } catch (error) {
        expect(error.response.status).toBe(404)
        expect(error.response.data.message).toBe('Teacher not found')
      }
    })

    it('should handle validation errors', async () => {
      const validationError = {
        response: {
          status: 400,
          data: { 
            message: 'Validation failed',
            errors: {
              email: ['Email format is invalid'],
              firstName: ['First name is required']
            }
          }
        }
      }
      
      mockedAxios.post.mockRejectedValueOnce(validationError)

      try {
        await teachersApi.create({ lastName: 'Dupont' })
      } catch (error) {
        expect(error.response.status).toBe(400)
        expect(error.response.data.errors.email).toContain('Email format is invalid')
      }
    })
  })
}) 