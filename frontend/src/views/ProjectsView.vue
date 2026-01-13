<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { Modal } from 'bootstrap'
import { useAuthStore } from '@/stores/auth'
import CommentsModal from '@/components/CommentsModal.vue'
import Swal from 'sweetalert2' // <--- IMPORTAMOS SWEETALERT

const authStore = useAuthStore()
const projects = ref([])
const isLoading = ref(true)

// --- CONFIGURACIÓN DE NOTIFICACIONES (TOAST) ---
const Toast = Swal.mixin({
  toast: true,
  position: 'top-end',
  showConfirmButton: false,
  timer: 3000,
  timerProgressBar: true,
  didOpen: (toast) => {
    toast.addEventListener('mouseenter', Swal.stopTimer)
    toast.addEventListener('mouseleave', Swal.resumeTimer)
  }
})

// Estados para nuevos proyectos, edición, y comentarios
const newProjectForm = ref({ name: '', tasks: [] })
let createModalInstance = null
const editForm = ref({ id: null, type: '', name: '' }) 
let editModalInstance = null
const commentsModalRef = ref(null)
const currentModel = ref({ id: 0, type: '', title: '' })
const activeRealItem = ref(null)

// 1. CARGAR DATOS
const fetchProjects = async () => {
  isLoading.value = true
  try {
    const response = await axios.get('http://localhost:8000/api/projects')
    projects.value = response.data
  } catch (error) {
    if(error.response && error.response.status === 401) authStore.logout()
  } finally {
    isLoading.value = false
  }
}

// 2. CREAR PROYECTO
const openCreateModal = () => {
  createModalInstance = new Modal(document.getElementById('createProjectModal'))
  createModalInstance.show()
}
const addTaskField = () => { newProjectForm.value.tasks.push({ name: '', subtasks: [] }) }
const removeTaskField = (index) => { newProjectForm.value.tasks.splice(index, 1) }
const addSubtaskField = (taskIndex) => { newProjectForm.value.tasks[taskIndex].subtasks.push({ name: '' }) }
const removeSubtaskField = (taskIndex, subIndex) => { newProjectForm.value.tasks[taskIndex].subtasks.splice(subIndex, 1) }

const createProject = async () => {
  if (!newProjectForm.value.name) {
    Toast.fire({ icon: 'warning', title: 'El nombre es obligatorio' })
    return;
  }
  try {
    await axios.post('http://localhost:8000/api/projects', newProjectForm.value)
    newProjectForm.value = { name: '', tasks: [] }
    createModalInstance.hide()
    fetchProjects()
    Toast.fire({ icon: 'success', title: 'Proyecto creado correctamente' })
  } catch (error) {
    Swal.fire('Error', 'No se pudo crear el proyecto', 'error')
  }
}

// 3. EDITAR
const openEditModal = (item, type) => {
  editForm.value = { id: item.id, type: type, name: item.name }
  editModalInstance = new Modal(document.getElementById('editItemModal'))
  editModalInstance.show()
}

const saveEdit = async () => {
  if (!editForm.value.name) return;
  try {
    await axios.put(`http://localhost:8000/api/${editForm.value.type}/${editForm.value.id}`, {
      name: editForm.value.name
    })
    fetchProjects()
    editModalInstance.hide()
    Toast.fire({ icon: 'success', title: 'Nombre actualizado' })
  } catch (error) {
    Toast.fire({ icon: 'error', title: 'Error al actualizar' })
  }
}

// 4. CAMBIAR ESTATUS
const toggleStatus = async (item, type) => {
  const newStatus = !item.status
  try {
    item.status = newStatus
    await axios.put(`http://localhost:8000/api/${type}/${item.id}`, { status: newStatus })
  } catch (error) {
    item.status = !newStatus 
    Toast.fire({ icon: 'error', title: 'Error de conexión' })
  }
}

// 5. BORRAR
const deleteItem = async (id, type) => {
  const result = await Swal.fire({
    title: '¿Estás seguro?',
    text: "No podrás revertir esta acción.",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#ef4444',
    cancelButtonColor: '#6b7280', 
    confirmButtonText: 'Sí, eliminar',
    cancelButtonText: 'Cancelar',
    reverseButtons: true
  })

  if (result.isConfirmed) {
    try {
      await axios.delete(`http://localhost:8000/api/${type}/${id}`)
      fetchProjects()
      Swal.fire(
        '¡Eliminado!',
        'El elemento ha sido borrado.',
        'success'
      )
    } catch (error) {
      Swal.fire('Error', 'No se pudo eliminar el elemento.', 'error')
    }
  }
}

// 6. COMENTARIOS
const openComments = (item, type) => {
  activeRealItem.value = item
  currentModel.value = { id: item.id, type: type, title: item.name }
  commentsModalRef.value.open(item.comments || [])
}

const handleCommentAdded = (newComment) => {
  if (activeRealItem.value) {
    if (!activeRealItem.value.comments) activeRealItem.value.comments = []
    activeRealItem.value.comments.push(newComment)
    if (activeRealItem.value.comments_count !== undefined) activeRealItem.value.comments_count++
    
    Toast.fire({ icon: 'success', title: 'Comentario agregado' })
  }
}

onMounted(() => { fetchProjects() })
</script>

<template>
  <div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-5 fade-in-up">
      <div>
        <h2 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2">
          <div class="icon-box bg-primary text-white">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" /></svg>
          </div>
          Mis Proyectos
        </h2>
        <p class="text-muted mb-0 ms-1">Gestiona tus tareas y colaboraciones</p>
      </div>
      <div class="d-flex gap-3">
        <button @click="openCreateModal" class="btn btn-primary btn-lg shadow-sm d-flex align-items-center gap-2">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
          Nuevo Proyecto
        </button>
        <button @click="authStore.logout()" class="btn btn-light text-danger btn-lg shadow-sm hover-danger">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
        </button>
      </div>
    </div>

    <div v-if="isLoading" class="text-center mt-5">
      <div class="spinner-border text-primary" role="status"></div>
    </div>

    <div v-else class="accordion fade-in-up" id="projectsAccordion">
      
      <div v-for="project in projects" :key="project.id" class="accordion-item project-card mb-4 border-0">
        
        <h2 class="accordion-header">
          <div class="d-flex align-items-center p-3 project-header">
            <button class="accordion-button collapsed fw-bold flex-grow-1 text-dark shadow-none bg-transparent fs-5" type="button" data-bs-toggle="collapse" :data-bs-target="'#collapse' + project.id">
              {{ project.name }}
            </button>
            
            <div class="d-flex align-items-center gap-3 ms-3">
              <span @click.stop="toggleStatus(project, 'projects')" 
                    class="badge-pill cursor-pointer" 
                    :class="project.status ? 'status-success' : 'status-pending'">
                <span class="dot"></span>
                {{ project.status ? 'Completado' : 'Pendiente' }}
              </span>

              <div class="action-buttons">
                <button @click.stop="openEditModal(project, 'projects')" class="btn-icon" title="Editar"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg></button>
                <button @click.stop="openComments(project, 'project')" class="btn-icon position-relative" title="Comentarios">
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" /></svg>
                  <span v-if="project.comments_count" class="badge-count">{{ project.comments_count }}</span>
                </button>
                <button @click.stop="deleteItem(project.id, 'projects')" class="btn-icon text-danger-hover" title="Borrar"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg></button>
              </div>
            </div>
          </div>
        </h2>

        <div :id="'collapse' + project.id" class="accordion-collapse collapse" data-bs-parent="#projectsAccordion">
          <div class="accordion-body pt-0 pb-4 px-4">
            <div class="tasks-container p-3 rounded-3">
              <h6 class="text-uppercase fw-bold text-muted mb-3 fs-7 ls-1">Tareas & Progreso</h6>

              <ul class="list-group list-group-flush">
                <li v-for="task in project.tasks" :key="task.id" class="list-group-item bg-transparent px-0 py-3 border-bottom-dashed">
                  
                  <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-3">
                      <div @click="toggleStatus(task, 'tasks')" class="check-circle cursor-pointer" :class="{'checked': task.status}">
                        <svg v-if="task.status" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                      </div>
                      <span class="fw-medium text-dark" :class="{'text-decoration-line-through text-muted': task.status}">{{ task.name }}</span>
                    </div>

                    <div class="action-buttons opacity-hover">
                      <button @click="openEditModal(task, 'tasks')" class="btn-icon btn-sm"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg></button>
                      <button @click="deleteItem(task.id, 'tasks')" class="btn-icon btn-sm text-danger-hover"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg></button>
                      <button @click="openComments(task, 'task')" class="btn-icon btn-sm text-primary-hover"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" /></svg></button>
                    </div>
                  </div>

                  <div v-if="task.subtasks && task.subtasks.length > 0" class="mt-2 ms-4 ps-3 border-start">
                    <div v-for="sub in task.subtasks" :key="sub.id" class="d-flex justify-content-between align-items-center py-1 group-hover-container">
                      <div class="d-flex align-items-center">
                         <input type="checkbox" class="form-check-input custom-checkbox me-2" :checked="!!sub.status" @change="toggleStatus(sub, 'subtasks')">
                         <span class="fs-7" :class="{'text-decoration-line-through text-muted': sub.status}">{{ sub.name }}</span>
                      </div>
                      <div class="d-flex gap-1 opacity-0 group-hover-visible">
                        <button @click="openEditModal(sub, 'subtasks')" class="btn-icon btn-xs"><svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg></button>
                        <button @click="deleteItem(sub.id, 'subtasks')" class="btn-icon btn-xs text-danger-hover"><svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg></button>
                        <button @click="openComments(sub, 'subtask')" class="btn-icon btn-xs text-primary-hover"><svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" /></svg></button>
                      </div>
                    </div>
                  </div>

                </li>
              </ul>
              
              <div v-if="project.tasks.length === 0" class="text-center py-4 text-muted">
                <small>Sin tareas activas</small>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="modal fade" id="createProjectModal" tabindex="-1" data-bs-backdrop="static">
       <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
          <div class="modal-header border-0 pb-0">
            <h5 class="modal-title fw-bold">🚀 Nuevo Proyecto</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div class="mb-4">
              <label class="form-label text-muted fw-bold fs-7">NOMBRE DEL PROYECTO</label>
              <input v-model="newProjectForm.name" class="form-control form-control-lg bg-light border-0" placeholder="Ej: Rediseño Web Corporativo">
            </div>
            <div class="d-flex justify-content-between align-items-center mb-3">
              <label class="form-label text-muted fw-bold fs-7 mb-0">TAREAS & SUBTAREAS</label>
              <button @click="addTaskField" class="btn btn-sm btn-soft-primary fw-bold">+ Añadir Tarea</button>
            </div>
            <div v-for="(task, tIndex) in newProjectForm.tasks" :key="tIndex" class="card mb-3 border border-dashed shadow-none">
              <div class="card-body py-3">
                <div class="d-flex gap-2 mb-2">
                   <input v-model="task.name" class="form-control form-control-sm border-0 fw-bold" placeholder="Nombre de la tarea...">
                   <button @click="removeTaskField(tIndex)" class="btn-icon text-danger"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg></button>
                </div>
                <div class="ms-3 ps-3 border-start">
                   <div v-for="(sub, sIndex) in task.subtasks" :key="sIndex" class="d-flex gap-2 mb-1">
                      <input v-model="sub.name" class="form-control form-control-sm bg-light border-0" placeholder="Subtarea...">
                      <button @click="removeSubtaskField(tIndex, sIndex)" class="btn-icon text-muted"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg></button>
                   </div>
                   <button @click="addSubtaskField(tIndex)" class="btn btn-link btn-sm text-decoration-none p-0 mt-1 text-muted fs-7">+ Subtarea</button>
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer border-0 pt-0">
            <button type="button" class="btn btn-light text-muted" data-bs-dismiss="modal">Cancelar</button>
            <button @click="createProject" class="btn btn-primary px-4 rounded-3">Crear Proyecto</button>
          </div>
        </div>
      </div>
    </div>

    <div class="modal fade" id="editItemModal" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
          <div class="modal-body p-4">
            <label class="form-label fw-bold text-muted fs-7">RENOMBRAR</label>
            <input v-model="editForm.name" @keyup.enter="saveEdit" class="form-control form-control-lg border-0 bg-light mb-3" autofocus>
            <div class="d-flex justify-content-end gap-2">
              <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cancelar</button>
              <button @click="saveEdit" class="btn btn-dark btn-sm px-3">Guardar</button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <CommentsModal ref="commentsModalRef" :modelId="currentModel.id" :modelType="currentModel.type" :title="currentModel.title" @comment-added="handleCommentAdded" />
  </div>
</template>

<style scoped>
.fs-7 { font-size: 0.85rem; }
.ls-1 { letter-spacing: 0.05em; }
.cursor-pointer { cursor: pointer; }
.fade-in-up { animation: fadeInUp 0.5s ease-out; }
@keyframes fadeInUp { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

.icon-box { width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; }

.project-card { 
  background: #fff; 
  border-radius: 16px; 
  box-shadow: 0 4px 20px rgba(0,0,0,0.03); 
  transition: transform 0.2s, box-shadow 0.2s;
  overflow: hidden;
}
.project-card:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(0,0,0,0.06); }
.project-header { background: #fff; }

.badge-pill { 
  padding: 6px 12px; 
  border-radius: 20px; 
  font-size: 0.75rem; 
  font-weight: 600; 
  display: flex; 
  align-items: center; 
  gap: 6px;
}
.status-success { background: #dcfce7; color: #166534; }
.status-pending { background: #fef9c3; color: #854d0e; }
.dot { width: 6px; height: 6px; border-radius: 50%; background: currentColor; }
.badge-count { 
  position: absolute; top: -5px; right: -5px; 
  background: #ef4444; color: white; 
  font-size: 0.6rem; padding: 2px 5px; border-radius: 10px; 
}

.btn-icon { 
  border: none; background: transparent; color: #9ca3af; 
  width: 32px; height: 32px; border-radius: 8px; 
  display: flex; align-items: center; justify-content: center; 
  transition: all 0.2s;
}
.btn-icon:hover { background: #f3f4f6; color: #374151; }
.text-danger-hover:hover { color: #ef4444; background: #fee2e2; }
.text-primary-hover:hover { color: #3b82f6; background: #dbeafe; }

.btn-soft-primary { background: #e0f2fe; color: #0284c7; border: none; }
.btn-soft-primary:hover { background: #bae6fd; }

.tasks-container { background: #f8fafc; }
.border-bottom-dashed { border-bottom: 1px dashed #e2e8f0; }
.border-bottom-dashed:last-child { border-bottom: none; }

.check-circle { 
  width: 20px; height: 20px; 
  border: 2px solid #cbd5e1; border-radius: 50%; 
  display: flex; align-items: center; justify-content: center; 
  color: white; transition: all 0.2s;
}
.check-circle.checked { background: #10b981; border-color: #10b981; }
.check-circle svg { width: 12px; }

.group-hover-container:hover .group-hover-visible { opacity: 1 !important; }
.opacity-0 { opacity: 0; transition: opacity 0.2s; }
.opacity-hover { opacity: 0; transition: opacity 0.2s; }
.list-group-item:hover .opacity-hover { opacity: 1; }

.custom-checkbox { width: 16px; height: 16px; border-radius: 4px; }
</style>