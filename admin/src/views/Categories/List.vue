<template>
  <div>
    <div class="d-flex justify-space-between align-center mb-6">
      <h1 class="text-h4">Categorias</h1>
      <v-btn color="primary" prepend-icon="mdi-plus" @click="goToNewCategory">
        Nova Categoria
      </v-btn>
    </div>
    
    <v-card>
      <v-data-table
        :headers="headers"
        :items="categories"
        :loading="loading"
      >
        <template v-slot:item.active="{ item }">
          <v-chip :color="item.active ? 'success' : 'error'" size="small">
            {{ item.active ? 'Ativa' : 'Inativa' }}
          </v-chip>
        </template>
        <template v-slot:item.actions="{ item }">
          <v-btn 
            icon="mdi-pencil" 
            size="small" 
            variant="text"
            @click="editCategory(item)"
          ></v-btn>
          <v-btn 
            icon="mdi-delete" 
            size="small" 
            variant="text"
            color="error"
            @click="confirmDelete(item)"
          ></v-btn>
        </template>
      </v-data-table>
    </v-card>

    <v-dialog v-model="deleteDialog" max-width="400">
      <v-card>
        <v-card-title>Confirmar Exclusão</v-card-title>
        <v-card-text>
          Tem certeza que deseja excluir a categoria "{{ categoryToDelete?.name }}"?
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn text @click="deleteDialog = false">Cancelar</v-btn>
          <v-btn color="error" @click="deleteCategory">Excluir</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <v-snackbar v-model="snackbar" :color="snackbarColor" timeout="3000">
      {{ snackbarText }}
    </v-snackbar>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '../../services/api'

const router = useRouter()
const categories = ref([])
const loading = ref(false)
const deleteDialog = ref(false)
const categoryToDelete = ref(null)
const snackbar = ref(false)
const snackbarText = ref('')
const snackbarColor = ref('success')

const headers = [
  { title: 'ID', key: 'id' },
  { title: 'Nome', key: 'name' },
  { title: 'Slug', key: 'slug' },
  { title: 'Ordem', key: 'order' },
  { title: 'Status', key: 'active' },
  { title: 'Ações', key: 'actions', sortable: false }
]

function goToNewCategory() {
  router.push({ name: 'CategoryNew' })
}

function editCategory(category) {
  router.push({ name: 'CategoryEdit', params: { id: category.id } })
}

function confirmDelete(category) {
  categoryToDelete.value = category
  deleteDialog.value = true
}

async function deleteCategory() {
  if (!categoryToDelete.value) return

  try {
    await api.delete(`/admin/categories/${categoryToDelete.value.id}`)
    showSnackbar('Categoria excluída com sucesso', 'success')
    deleteDialog.value = false
    categoryToDelete.value = null
    await fetchCategories()
  } catch (error) {
    console.error('Error deleting category:', error)
    showSnackbar('Erro ao excluir categoria', 'error')
  }
}

function showSnackbar(text, color = 'success') {
  snackbarText.value = text
  snackbarColor.value = color
  snackbar.value = true
}

async function fetchCategories() {
  loading.value = true
  try {
    const response = await api.get('/admin/categories')
    categories.value = response.data.data || response.data
  } catch (error) {
    console.error('Error fetching categories:', error)
    showSnackbar('Erro ao carregar categorias', 'error')
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchCategories()
})
</script>
