<template>
  <div>
    <div class="d-flex justify-space-between align-center mb-6">
      <h1 class="text-h4">Caixas Padrões</h1>
      <v-btn color="primary" prepend-icon="mdi-plus" @click="goToNewBox">
        Nova Caixa
      </v-btn>
    </div>
    
    <v-card>
      <v-data-table
        :headers="headers"
        :items="boxes"
        :loading="loading"
      >
        <template v-slot:item.dimensions="{ item }">
          {{ item.width }}cm × {{ item.height }}cm × {{ item.length }}cm
        </template>
        <template v-slot:item.weight="{ item }">
          {{ formatNumber(item.weight) }} kg
        </template>
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
            @click="editBox(item)"
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
          Tem certeza que deseja excluir a caixa "{{ boxToDelete?.name }}"?
          <v-alert
            v-if="boxToDelete?.products_count > 0"
            type="warning"
            variant="tonal"
            class="mt-4"
          >
            Esta caixa está sendo usada por {{ boxToDelete.products_count }} produto(s). Não é possível excluí-la.
          </v-alert>
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn text @click="deleteDialog = false">Cancelar</v-btn>
          <v-btn 
            color="error" 
            @click="deleteBox"
            :disabled="boxToDelete?.products_count > 0"
          >
            Excluir
          </v-btn>
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
const boxes = ref([])
const loading = ref(false)
const deleteDialog = ref(false)
const boxToDelete = ref(null)
const snackbar = ref(false)
const snackbarText = ref('')
const snackbarColor = ref('success')

const headers = [
  { title: 'ID', key: 'id' },
  { title: 'Nome', key: 'name' },
  { title: 'Dimensões', key: 'dimensions' },
  { title: 'Peso', key: 'weight' },
  { title: 'Status', key: 'active' },
  { title: 'Ações', key: 'actions', sortable: false }
]

function goToNewBox() {
  router.push({ name: 'BoxNew' })
}

function editBox(box) {
  router.push({ name: 'BoxEdit', params: { id: box.id } })
}

function confirmDelete(box) {
  boxToDelete.value = box
  deleteDialog.value = true
}

async function deleteBox() {
  if (!boxToDelete.value) return

  try {
    await api.delete(`/admin/boxes/${boxToDelete.value.id}`)
    showSnackbar('Caixa excluída com sucesso', 'success')
    deleteDialog.value = false
    boxToDelete.value = null
    await fetchBoxes()
  } catch (error) {
    console.error('Error deleting box:', error)
    const errorMessage = error.response?.data?.message || 'Erro ao excluir caixa'
    showSnackbar(errorMessage, 'error')
  }
}

function showSnackbar(text, color = 'success') {
  snackbarText.value = text
  snackbarColor.value = color
  snackbar.value = true
}

function formatNumber(value) {
  return new Intl.NumberFormat('pt-BR', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(value)
}

async function fetchBoxes() {
  loading.value = true
  try {
    const response = await api.get('/admin/boxes')
    boxes.value = response.data.data || response.data
  } catch (error) {
    console.error('Error fetching boxes:', error)
    showSnackbar('Erro ao carregar caixas', 'error')
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchBoxes()
})
</script>
