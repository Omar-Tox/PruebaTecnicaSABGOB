<script setup>
import { ref } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { RouterLink } from 'vue-router'
import Swal from 'sweetalert2'

const authStore = useAuthStore()
const email = ref('')
const password = ref('')
const isLoading = ref(false)

// Configuración de Toast (Notificación pequeña)
const Toast = Swal.mixin({
  toast: true,
  position: 'top-end',
  showConfirmButton: false,
  timer: 3000,
  timerProgressBar: true
})

const handleSubmit = async () => {
  isLoading.value = true
  try {
    await authStore.login(email.value, password.value)
    Toast.fire({ icon: 'success', title: '¡Bienvenido de nuevo!' })
  } catch (error) {
    // Alerta de error elegante
    Swal.fire({
      icon: 'error',
      title: 'Acceso Denegado',
      text: typeof error === 'string' ? error : 'Credenciales incorrectas.',
      confirmButtonColor: '#3b82f6'
    })
  } finally {
    isLoading.value = false
  }
}
</script>

<template>
  <div class="login-container d-flex align-items-center justify-content-center min-vh-100 px-3">
    
    <div class="card login-card border-0 shadow-lg rounded-4 overflow-hidden fade-in-up">
      <div class="card-body p-5">
        
        <div class="text-center mb-5">
          <div class="icon-bg mx-auto mb-3">
            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="text-primary">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
            </svg>
          </div>
          <h2 class="fw-bold text-dark">Iniciar Sesión</h2>
          <p class="text-muted">Ingresa a tu espacio de trabajo</p>
        </div>

        <form @submit.prevent="handleSubmit">
          <div class="mb-4">
            <label class="form-label text-uppercase fw-bold text-muted fs-7 ls-1">Correo Electrónico</label>
            <input 
              v-model="email" 
              type="email" 
              class="form-control form-control-lg bg-light border-0" 
              placeholder="nombre@ejemplo.com"
              required 
              autofocus
            >
          </div>

          <div class="mb-4">
            <div class="d-flex justify-content-between">
              <label class="form-label text-uppercase fw-bold text-muted fs-7 ls-1">Contraseña</label>
            </div>
            <input 
              v-model="password" 
              type="password" 
              class="form-control form-control-lg bg-light border-0" 
              placeholder="••••••••"
              required
            >
          </div>

          <button type="submit" class="btn btn-primary btn-lg w-100 rounded-3 fw-bold shadow-sm hover-elevate" :disabled="isLoading">
            <span v-if="isLoading" class="spinner-border spinner-border-sm me-2"></span>
            {{ isLoading ? 'Entrando...' : 'Ingresar' }}
          </button>
        </form>

        <div class="text-center mt-5 pt-3 border-top">
          <p class="text-muted mb-0">¿Aún no tienes cuenta?</p>
          <RouterLink to="/register" class="btn btn-link text-decoration-none fw-bold text-primary">
            Crear cuenta nueva
          </RouterLink>
        </div>

      </div>
    </div>

  </div>
</template>

<style scoped>
/* ESTILOS PREMIUM */
.login-container { background-color: #f4f6f8; }
.login-card { width: 100%; max-width: 450px; }

.icon-bg {
  width: 64px; height: 64px;
  background: #e0f2fe;
  border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
}

.fs-7 { font-size: 0.8rem; }
.ls-1 { letter-spacing: 0.05em; }

.hover-elevate { transition: transform 0.2s, box-shadow 0.2s; }
.hover-elevate:hover { transform: translateY(-2px); box-shadow: 0 10px 20px rgba(59, 130, 246, 0.2); }

/* Animación de entrada */
.fade-in-up { animation: fadeInUp 0.6s ease-out; }
@keyframes fadeInUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
</style>