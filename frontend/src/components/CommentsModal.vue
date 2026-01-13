<script setup>
import { ref } from 'vue'
import axios from 'axios'
import { Modal } from 'bootstrap'

const props = defineProps(['modelId', 'modelType', 'title'])
const emit = defineEmits(['close', 'comment-added']) 

const comments = ref([])
const newComment = ref('')
let bsModal = null

const sendComment = async () => {
  if(!newComment.value) return;
  try {
    const res = await axios.post('http://localhost:8000/api/comments', {
      body: newComment.value,
      commentable_id: props.modelId,
      commentable_type: props.modelType
    })
    
    emit('comment-added', res.data)
    
    newComment.value = ''
  } catch (error) {
    alert("Error al comentar")
  }
}

const open = (existingComments) => {
  // Aseguramos que sea un array, si viene null lo convertimos a []
  comments.value = existingComments || [] 
  bsModal = new Modal(document.getElementById('commentsModal'))
  bsModal.show()
}

defineExpose({ open })
</script>

<template>
  <div class="modal fade" id="commentsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Comentarios: {{ title }}</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <ul class="list-group mb-3">
            <li v-for="c in comments" :key="c.id" class="list-group-item bg-light">
              <small class="fw-bold text-primary">Usuario ID {{ c.user_id }}:</small>
              <p class="mb-0">{{ c.body }}</p>
            </li>
            <li v-if="comments.length === 0" class="list-group-item text-muted">Sin comentarios aún.</li>
          </ul>

          <div class="input-group">
            <input v-model="newComment" type="text" class="form-control" placeholder="Escribe algo...">
            <button @click="sendComment" class="btn btn-primary">Enviar</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>