import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '../services/api'
import axios from 'axios'

export const useAuthStore = defineStore('auth', () => {
  const token = ref(localStorage.getItem('admin_token'))
  const user = ref(null)

  const isAuthenticated = computed(() => !!token.value)

  async function login(email, password) {
    try {
      // Autenticar via API Laravel usando Sanctum ou criar endpoint customizado
      // Por enquanto, vamos usar um endpoint customizado de login
      const response = await api.post('/login', {
        email: email,
        password: password
      })

      if (!response.data.access_token) {
        throw new Error('Token de acesso não recebido')
      }

      token.value = response.data.access_token
      localStorage.setItem('admin_token', token.value)
      
      // Set default authorization header
      api.defaults.headers.common['Authorization'] = `Bearer ${token.value}`

      // User data já vem na resposta do login
      if (response.data.user) {
        user.value = response.data.user
      } else {
        await fetchUser()
      }

      return true
    } catch (error) {
      console.error('Login error:', error)
      const errorMessage = error.response?.data?.message || error.message || 'Erro ao fazer login. Verifique suas credenciais.'
      throw new Error(errorMessage)
    }
  }

  async function fetchUser() {
    try {
      const response = await api.get('/user')
      user.value = response.data
    } catch (error) {
      console.error('Error fetching user:', error)
    }
  }

  function logout() {
    token.value = null
    user.value = null
    localStorage.removeItem('admin_token')
    delete api.defaults.headers.common['Authorization']
  }

  // Initialize auth header if token exists
  if (token.value) {
    api.defaults.headers.common['Authorization'] = `Bearer ${token.value}`
  }

  return {
    token,
    user,
    isAuthenticated,
    login,
    logout,
    fetchUser
  }
})
