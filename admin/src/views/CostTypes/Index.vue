<template>
  <div>
    <v-card>
      <v-card-title class="d-flex align-center">
        <v-icon class="mr-2">mdi-cash-multiple</v-icon>
        Tipos de Custos
        <v-spacer></v-spacer>
        <v-btn
          color="primary"
          @click="openDialog(null)"
        >
          <v-icon class="mr-1">mdi-plus</v-icon>
          Novo Tipo de Custo
        </v-btn>
      </v-card-title>
      
      <v-card-text>
        <v-tabs v-model="tab" class="mb-4">
          <v-tab value="all">Todos</v-tab>
          <v-tab value="receita">Receitas</v-tab>
          <v-tab value="despesa">Despesas</v-tab>
        </v-tabs>
        
        <v-data-table
          :headers="headers"
          :items="filteredCostTypes"
          :loading="loading"
          item-key="id"
        >
          <template v-slot:item.type="{ item }">
            <v-chip
              :color="item.type === 'receita' ? 'success' : 'error'"
              size="small"
            >
              {{ item.type === 'receita' ? 'Receita' : 'Despesa' }}
            </v-chip>
          </template>
          
          <template v-slot:item.active="{ item }">
            <v-chip
              :color="item.active ? 'success' : 'grey'"
              size="small"
            >
              {{ item.active ? 'Ativo' : 'Inativo' }}
            </v-chip>
          </template>
          
          <template v-slot:item.actions="{ item }">
            <v-btn
              icon="mdi-pencil"
              size="small"
              variant="text"
              @click="openDialog(item)"
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
      </v-card-text>
    </v-card>
    
    <!-- Dialog de Criar/Editar -->
    <v-dialog v-model="dialog" max-width="600">
      <v-card>
        <v-card-title>
          {{ editingItem ? 'Editar Tipo de Custo' : 'Novo Tipo de Custo' }}
        </v-card-title>
        <v-card-text>
          <v-form ref="formRef">
            <v-text-field
              v-model="form.name"
              label="Nome"
              :rules="[v => !!v || 'Nome é obrigatório']"
              required
              variant="outlined"
              density="compact"
            ></v-text-field>
            
            <v-select
              v-model="form.type"
              :items="typeOptions"
              item-title="label"
              item-value="value"
              label="Tipo"
              :rules="[v => !!v || 'Tipo é obrigatório']"
              required
              variant="outlined"
              density="compact"
              class="mt-2"
            ></v-select>
            
            <v-textarea
              v-model="form.description"
              label="Descrição"
              variant="outlined"
              density="compact"
              rows="3"
              class="mt-2"
            ></v-textarea>
            
            <v-text-field
              v-model.number="form.order"
              label="Ordem"
              type="number"
              variant="outlined"
              density="compact"
              class="mt-2"
            ></v-text-field>
            
            <v-switch
              v-model="form.active"
              label="Ativo"
              color="primary"
              class="mt-2"
            ></v-switch>
          </v-form>
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn @click="dialog = false">Cancelar</v-btn>
          <v-btn
            color="primary"
            @click="save"
            :loading="saving"
          >
            Salvar
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
    
    <!-- Dialog de Confirmação de Exclusão -->
    <v-dialog v-model="deleteDialog" max-width="400">
      <v-card>
        <v-card-title>Confirmar Exclusão</v-card-title>
        <v-card-text>
          Tem certeza que deseja excluir o tipo de custo "{{ itemToDelete?.name }}"?
          <v-alert
            v-if="itemToDelete && itemToDelete.costs_count > 0"
            type="warning"
            class="mt-2"
          >
            Este tipo de custo possui {{ itemToDelete.costs_count }} custo(s) associado(s) e não pode ser excluído.
          </v-alert>
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn @click="deleteDialog = false">Cancelar</v-btn>
          <v-btn
            color="error"
            @click="deleteItem"
            :loading="deleting"
            :disabled="itemToDelete && itemToDelete.costs_count > 0"
          >
            Excluir
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '../../services/api'

const costTypes = ref([])
const loading = ref(false)
const tab = ref('all')
const dialog = ref(false)
const editingItem = ref(null)
const form = ref({
  name: '',
  type: 'despesa',
  description: '',
  active: true,
  order: 0
})
const formRef = ref(null)
const saving = ref(false)
const deleteDialog = ref(false)
const itemToDelete = ref(null)
const deleting = ref(false)

const headers = [
  { title: 'Nome', key: 'name' },
  { title: 'Tipo', key: 'type' },
  { title: 'Descrição', key: 'description' },
  { title: 'Ordem', key: 'order' },
  { title: 'Status', key: 'active' },
  { title: 'Ações', key: 'actions', sortable: false }
]

const typeOptions = [
  { label: 'Receita', value: 'receita' },
  { label: 'Despesa', value: 'despesa' }
]

const filteredCostTypes = computed(() => {
  if (tab.value === 'all') {
    return costTypes.value
  }
  return costTypes.value.filter(item => item.type === tab.value)
})

async function fetchData() {
  loading.value = true
  try {
    const response = await api.get('/admin/cost-types')
    // A API pode retornar response.data diretamente ou response.data.data
    if (Array.isArray(response.data)) {
      costTypes.value = response.data
    } else if (response.data.data && Array.isArray(response.data.data)) {
      costTypes.value = response.data.data
    } else {
      costTypes.value = []
    }
    console.log('Cost types loaded:', costTypes.value.length)
  } catch (error) {
    console.error('Error fetching cost types:', error)
    costTypes.value = []
  } finally {
    loading.value = false
  }
}

function openDialog(item) {
  editingItem.value = item
  if (item) {
    form.value = {
      name: item.name,
      type: item.type,
      description: item.description || '',
      active: item.active,
      order: item.order || 0
    }
  } else {
    form.value = {
      name: '',
      type: 'despesa',
      description: '',
      active: true,
      order: 0
    }
  }
  dialog.value = true
}

async function save() {
  if (!formRef.value) return
  
  const { valid } = await formRef.value.validate()
  if (!valid) return
  
  saving.value = true
  try {
    if (editingItem.value) {
      await api.put(`/admin/cost-types/${editingItem.value.id}`, form.value)
    } else {
      await api.post('/admin/cost-types', form.value)
    }
    
    dialog.value = false
    await fetchData()
  } catch (error) {
    console.error('Error saving cost type:', error)
    alert('Erro ao salvar tipo de custo. Tente novamente.')
  } finally {
    saving.value = false
  }
}

function confirmDelete(item) {
  itemToDelete.value = item
  deleteDialog.value = true
}

async function deleteItem() {
  if (!itemToDelete.value) return
  
  deleting.value = true
  try {
    await api.delete(`/admin/cost-types/${itemToDelete.value.id}`)
    deleteDialog.value = false
    itemToDelete.value = null
    await fetchData()
  } catch (error) {
    console.error('Error deleting cost type:', error)
    if (error.response?.status === 422) {
      alert(error.response.data.message || 'Não é possível excluir este tipo de custo.')
    } else {
      alert('Erro ao excluir tipo de custo. Tente novamente.')
    }
  } finally {
    deleting.value = false
  }
}

onMounted(() => {
  fetchData()
})
</script>

<style scoped>
</style>
