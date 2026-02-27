<template>
  <div>
    <div class="d-flex justify-space-between align-center mb-6">
      <h1 class="text-h4">Blog</h1>
      <v-btn color="primary" prepend-icon="mdi-plus">Nova Postagem</v-btn>
    </div>
    
    <v-card>
      <v-data-table
        :headers="headers"
        :items="posts"
        :loading="loading"
      >
        <template v-slot:item.type="{ item }">
          <v-chip :color="item.type === 'news' ? 'primary' : 'secondary'" size="small">
            {{ item.type === 'news' ? 'Notícia' : 'Evento' }}
          </v-chip>
        </template>
        <template v-slot:item.published_at="{ item }">
          {{ item.published_at ? formatDate(item.published_at) : 'Não publicado' }}
        </template>
        <template v-slot:item.actions="{ item }">
          <v-btn icon="mdi-pencil" size="small" @click="editPost(item)"></v-btn>
          <v-btn icon="mdi-delete" size="small" @click="deletePost(item)"></v-btn>
        </template>
      </v-data-table>
    </v-card>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '../../services/api'

const posts = ref([])
const loading = ref(false)

const headers = [
  { title: 'Título', key: 'title' },
  { title: 'Tipo', key: 'type' },
  { title: 'Publicado em', key: 'published_at' },
  { title: 'Ações', key: 'actions', sortable: false }
]

function formatDate(date) {
  return new Date(date).toLocaleDateString('pt-BR')
}

function editPost(post) {
  // Implementar edição
  console.log('Edit post:', post)
}

function deletePost(post) {
  // Implementar exclusão
  console.log('Delete post:', post)
}

async function fetchPosts() {
  loading.value = true
  try {
    const response = await api.get('/admin/posts')
    posts.value = response.data.data || response.data
  } catch (error) {
    console.error('Error fetching posts:', error)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchPosts()
})
</script>
