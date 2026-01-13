import { ref } from 'vue'
import { defineStore } from 'pinia'
import axios from 'axios'
import router from '@/router'

export const useAuthStore = defineStore('auth', () => {
  // Estado: Leemos el token del navegador si ya existe
  const token = ref(localStorage.getItem('token') || null)
  const user = ref(null)
  
  if (token.value) {
    axios.defaults.headers.common['Authorization'] = `Bearer ${token.value}`
}

  // Acción: Iniciar Sesión
  async function login(email, password) {
    try {
      // 1. Pedir token a Laravel
      const response = await axios.post('http://localhost:8000/api/login', {
        email,
        password
      })

      // 2. Guardar token en el estado y en localStorage
      token.value = response.data.token
      localStorage.setItem('token', token.value)
      
      // 3. Configurar Axios para que use este token en futuras peticiones
      axios.defaults.headers.common['Authorization'] = `Bearer ${token.value}`

      // 4. Redirigir al Dashboard (lo crearemos luego)
      router.push('/projects')
      
      return true
    } catch (error) {
      console.error(error)
      throw error.response?.data?.message || 'Error al iniciar sesión'
    }
  }

  async function register(name, email, password) {
    try {
      const response = await axios.post('http://localhost:8000/api/register', {
        name,
        email,
        password
      })
      
      // Auto-login después de registrarse
      token.value = response.data.token
      localStorage.setItem('token', token.value)
      axios.defaults.headers.common['Authorization'] = `Bearer ${token.value}`
      
      user.value = response.data.user // Si tu API devuelve el usuario
      router.push('/projects')
    } catch (error) {
      throw error.response?.data?.message || 'Error en el registro'
    }
  }


  // Acción: Cerrar Sesión
  function logout() {
    token.value = null
    user.value = null
    localStorage.removeItem('token')
    delete axios.defaults.headers.common['Authorization']
    router.push('/')
  }

  return { token, user, login, logout, register }
})