<template>
  <div>
    <div class="d-flex justify-space-between align-center mb-6">
      <h1 class="text-h4">Produtos</h1>
      <v-btn color="primary" prepend-icon="mdi-plus" @click="goToNewProduct">
        Novo Produto
      </v-btn>
    </div>
    
    <v-card>
      <v-data-table
        :headers="headers"
        :items="products"
        :loading="loading"
      >
        <template v-slot:item.active="{ item }">
          <v-chip :color="item.active ? 'success' : 'error'" size="small">
            {{ item.active ? 'Ativo' : 'Inativo' }}
          </v-chip>
        </template>
        <template v-slot:item.actions="{ item }">
          <v-btn 
            icon="mdi-pencil" 
            size="small" 
            variant="text"
            @click="editProduct(item)"
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
          Tem certeza que deseja excluir o produto "{{ productToDelete?.name }}"?
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn text @click="deleteDialog = false">Cancelar</v-btn>
          <v-btn color="error" @click="deleteProduct">Excluir</v-btn>
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
const products = ref([])
const loading = ref(false)
const deleteDialog = ref(false)
const productToDelete = ref(null)
const snackbar = ref(false)
const snackbarText = ref('')
const snackbarColor = ref('success')

const headers = [
  { title: 'ID', key: 'id' },
  { title: 'Nome', key: 'name' },
  { title: 'Categoria', key: 'category.name' },
  { title: 'Dimensões', key: 'dimensions' },
  { title: 'Status', key: 'active' },
  { title: 'Ações', key: 'actions', sortable: false }
]

function goToNewProduct() {
  router.push({ name: 'ProductNew' })
}

function editProduct(product) {
  router.push({ name: 'ProductEdit', params: { id: product.id } })
}

function confirmDelete(product) {
  productToDelete.value = product
  deleteDialog.value = true
}

async function deleteProduct() {
  if (!productToDelete.value) return

  try {
    await api.delete(`/admin/products/${productToDelete.value.id}`)
    showSnackbar('Produto excluído com sucesso', 'success')
    deleteDialog.value = false
    productToDelete.value = null
    await fetchProducts()
  } catch (error) {
    console.error('Error deleting product:', error)
    showSnackbar('Erro ao excluir produto', 'error')
  }
}

function showSnackbar(text, color = 'success') {
  snackbarText.value = text
  snackbarColor.value = color
  snackbar.value = true
}

async function fetchProducts() {
  loading.value = true
  try {
    const response = await api.get('/admin/products')
    products.value = response.data.data || response.data
    products.value = products.value.map(p => ({
      ...p,
      dimensions: `${p.width || 0}m × ${p.height || 0}m`
    }))
  } catch (error) {
    console.error('Error fetching products:', error)
    showSnackbar('Erro ao carregar produtos', 'error')
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchProducts()
})
</script>
