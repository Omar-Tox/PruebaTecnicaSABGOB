<script setup>
import { ref } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { RouterLink } from 'vue-router'
import Swal from 'sweetalert2'

const authStore = useAuthStore()
const name = ref('')
const email = ref('')
const password = ref('')
const isLoading = ref(false)

const handleRegister = async () => {
  isLoading.value = true
  try {
    await authStore.register(name.value, email.value, password.value)
    
    // Éxito con SweetAlert
    Swal.fire({
      icon: 'success',
      title: '¡Cuenta Creada!',
      text: 'Bienvenido a bordo.',
      timer: 2000,
      showConfirmButton: false
    })
  } catch (error) {
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: typeof error === 'string' ? error : 'No se pudo completar el registro.',
      confirmButtonColor: '#ef4444'
    })
  } finally {
    isLoading.value = false
  }
}
</script>

<template>
  <div class="register-container d-flex align-items-center justify-content-center min-vh-100 px-3">
    
    <div class="card register-card border-0 shadow-lg rounded-4 overflow-hidden fade-in-up">
      <div class="card-body p-5">
        
        <div class="text-center mb-4">
          <div class="icon-bg mx-auto mb-3">
            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="text-success">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
            </svg>
          </div>
          <h2 class="fw-bold text-dark">Crear Cuenta</h2>
          <p class="text-muted">Únete y comienza a gestionar proyectos</p>
        </div>

        <form @submit.prevent="handleRegister">
          
          <div class="mb-3">
            <label class="form-label text-uppercase fw-bold text-muted fs-7 ls-1">Nombre Completo</label>
            <input 
              v-model="name" 
              type="text" 
              class="form-control form-control-lg bg-light border-0" 
              placeholder="Juan Pérez"
              required
            >
          </div>

          <div class="mb-3">
            <label class="form-label text-uppercase fw-bold text-muted fs-7 ls-1">Correo Electrónico</label>
            <input 
              v-model="email" 
              type="email" 
              class="form-control form-control-lg bg-light border-0" 
              placeholder="juan@ejemplo.com"
              required
            >
          </div>

          <div class="mb-4">
            <label class="form-label text-uppercase fw-bold text-muted fs-7 ls-1">Contraseña</label>
            <input 
              v-model="password" 
              type="password" 
              class="form-control form-control-lg bg-light border-0" 
              placeholder="Elige una contraseña segura"
              required
            >
          </div>

          <button type="submit" class="btn btn-success btn-lg w-100 rounded-3 fw-bold shadow-sm hover-elevate" :disabled="isLoading">
            <span v-if="isLoading" class="spinner-border spinner-border-sm me-2"></span>
            {{ isLoading ? 'Creando cuenta...' : 'Registrarme' }}
          </button>
        </form>

        <div class="text-center mt-4 pt-3 border-top">
          <p class="text-muted mb-0">¿Ya tienes una cuenta?</p>
          <RouterLink to="/" class="btn btn-link text-decoration-none fw-bold text-success">
            Iniciar Sesión
          </RouterLink>
        </div>

      </div>
    </div>

  </div>
</template>

<style scoped>
.register-container { background-color: #f4f6f8; }
.register-card { width: 100%; max-width: 450px; }

.icon-bg {
  width: 64px; height: 64px;
  background: #dcfce7; /* Verde suave */
  border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
}

.fs-7 { font-size: 0.8rem; }
.ls-1 { letter-spacing: 0.05em; }

.hover-elevate { transition: transform 0.2s, box-shadow 0.2s; }
.hover-elevate:hover { transform: translateY(-2px); box-shadow: 0 10px 20px rgba(22, 163, 74, 0.2); }

.fade-in-up { animation: fadeInUp 0.6s ease-out; }
@keyframes fadeInUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
</style>