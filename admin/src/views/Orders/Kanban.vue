<template>
  <div class="kanban-container">
    <h1 class="text-h4 mb-4 kanban-title">Kanban de Vendas</h1>
    
    <div class="kanban-scroll">
      <div class="kanban-columns">
        <div
          v-for="status in statuses"
          :key="status.id"
          class="kanban-column"
        >
          <v-card class="kanban-card">
            <v-card-title :style="{ backgroundColor: status.color, color: 'white' }">
              {{ status.name }}
              <v-spacer></v-spacer>
              <v-chip size="small" color="white" text-color="primary">
                {{ getOrdersByStatus(status.id).length }}
              </v-chip>
            </v-card-title>
            <v-card-text 
              class="kanban-card-content"
              :class="{ 'drag-over': draggedOrder && draggedOrder.status_id !== status.id }"
              @drop="handleDrop($event, status.id)"
              @dragover.prevent
              @dragenter.prevent
            >
              <div
                v-for="order in getOrdersByStatus(status.id)"
                :key="order.id"
                class="order-card mb-2"
                :class="{ dragging: draggedOrder && draggedOrder.id === order.id }"
                draggable="true"
                @dragstart="handleDragStart($event, order)"
                @dragend="handleDragEnd"
                @click="selectOrder(order)"
              >
                <div class="order-card-header">
                  <div class="text-subtitle-2 font-weight-bold">{{ order.order_number }}</div>
                  <div class="text-caption text-grey-lighten-1">{{ order.customer?.name }}</div>
                </div>
                
                <div class="order-items mt-3" v-if="order.items && order.items.length > 0">
                  <div 
                    v-for="item in order.items" 
                    :key="item.id"
                    class="order-item"
                  >
                    <v-img
                      :src="getProductImage(item)"
                      :alt="item.product_name"
                      width="40"
                      height="40"
                      cover
                      class="order-item-image"
                    >
                      <template v-slot:placeholder>
                        <v-row class="fill-height ma-0" align="center" justify="center">
                          <v-icon color="grey">mdi-image-off</v-icon>
                        </v-row>
                      </template>
                    </v-img>
                    <div class="order-item-info">
                      <div class="text-caption font-weight-medium">{{ item.product_name }}</div>
                      <div class="text-caption text-grey-lighten-1">
                        <span v-if="item.width && item.height">{{ item.width }}m × {{ item.height }}m</span>
                        <span v-if="item.quantity > 1"> × {{ item.quantity }}</span>
                      </div>
                    </div>
                  </div>
                </div>
                
                <div class="order-card-footer mt-3">
                  <div class="text-body-2 font-weight-bold">
                    {{ formatCurrency(order.total_amount) }}
                  </div>
                  <div class="text-caption text-grey-lighten-1" v-if="order.items">
                    {{ order.items.length }} {{ order.items.length === 1 ? 'item' : 'itens' }}
                  </div>
                </div>
              </div>
              <div v-if="getOrdersByStatus(status.id).length === 0" class="text-center text-caption text-grey mt-4">
                Nenhum pedido
              </div>
            </v-card-text>
          </v-card>
        </div>
      </div>
    </div>
    
    <v-dialog v-model="orderDialog" max-width="900" scrollable>
      <v-card v-if="selectedOrder">
        <v-card-title class="d-flex align-center">
          <div>
            <div class="text-h5">{{ selectedOrder.order_number }}</div>
            <div class="text-caption text-grey">
              Criado em {{ formatDate(selectedOrder.created_at) }}
            </div>
          </div>
          <v-spacer></v-spacer>
          <v-chip
            :color="selectedOrder.status?.color || 'grey'"
            text-color="white"
            size="large"
          >
            {{ selectedOrder.status?.name || 'Sem status' }}
          </v-chip>
        </v-card-title>
        
        <v-divider></v-divider>
        
        <v-tabs v-model="activeTab" color="primary" variant="text">
          <v-tab value="details">
            <v-icon start>mdi-information</v-icon>
            Detalhes
          </v-tab>
          <v-tab value="shipping">
            <v-icon start>mdi-truck-delivery</v-icon>
            Frete
          </v-tab>
        </v-tabs>
        
        <v-window v-model="activeTab">
          <v-window-item value="details">
            <v-card-text>
          <v-row>
            <!-- Informações do Cliente -->
            <v-col cols="12" md="6">
              <v-card variant="outlined" class="mb-4 order-info-card">
                <v-card-title class="text-subtitle-1">
                  <v-icon class="mr-2">mdi-account</v-icon>
                  Informações do Cliente
                </v-card-title>
                <v-card-text>
                  <div class="mb-2">
                    <strong>Nome:</strong> {{ selectedOrder.customer?.name }}
                  </div>
                  <div class="mb-2" v-if="selectedOrder.customer?.number">
                    <strong>Número:</strong> {{ selectedOrder.customer.number }}
                  </div>
                  <div class="mb-2" v-if="selectedOrder.customer?.document">
                    <strong>Documento:</strong> {{ selectedOrder.customer.document }}
                  </div>
                  <div class="mb-2" v-if="selectedOrder.customer?.email">
                    <strong>Email:</strong> {{ selectedOrder.customer.email }}
                  </div>
                  <div class="mb-2" v-if="selectedOrder.customer?.phone">
                    <strong>Telefone:</strong> {{ selectedOrder.customer.phone }}
                  </div>
                  <div class="mb-2" v-if="selectedOrder.customer?.address">
                    <strong>Endereço:</strong><br>
                    {{ selectedOrder.customer.address }}
                    <span v-if="selectedOrder.customer.city">
                      <br>{{ selectedOrder.customer.city }}
                      <span v-if="selectedOrder.customer.state">, {{ selectedOrder.customer.state }}</span>
                    </span>
                    <span v-if="selectedOrder.customer.cep">
                      <br>CEP: {{ selectedOrder.customer.cep }}
                    </span>
                  </div>
                </v-card-text>
              </v-card>
            </v-col>
            
            <!-- Resumo do Pedido -->
            <v-col cols="12" md="6">
              <v-card variant="outlined" class="mb-4 order-info-card">
                <v-card-title class="text-subtitle-1">
                  <v-icon class="mr-2">mdi-information</v-icon>
                  Resumo do Pedido
                </v-card-title>
                <v-card-text>
                  <div class="mb-3">
                    <div class="text-h6 text-primary mb-1">
                      {{ formatCurrency(selectedOrder.total_amount) }}
                    </div>
                    <v-chip
                      v-if="selectedOrder.custom_price"
                      color="orange"
                      size="small"
                      class="mb-2"
                    >
                      Preço Customizado
                    </v-chip>
                    <div v-if="selectedOrder.custom_price_value" class="text-caption text-grey">
                      Valor original: {{ formatCurrency(selectedOrder.custom_price_value) }}
                    </div>
                  </div>
                  
                  <div class="mb-2">
                    <strong>Total de Itens:</strong> {{ selectedOrder.items?.length || 0 }}
                  </div>
                  
                  <div class="mb-2" v-if="orderTotalWeight > 0">
                    <strong>Peso Total:</strong> {{ formatNumber(orderTotalWeight) }} kg
                  </div>
                  
                  <div class="mb-2" v-if="orderTotalVolume > 0">
                    <strong>Volume Total:</strong> {{ formatNumber(orderTotalVolume) }} m³
                  </div>
                  
                  <div class="mb-2" v-if="selectedOrder.completed_at">
                    <strong>Concluído em:</strong> {{ formatDate(selectedOrder.completed_at) }}
                  </div>
                  
                  <v-select
                    v-model="selectedStatusId"
                    :items="statuses"
                    item-title="name"
                    item-value="id"
                    label="Alterar Status"
                    variant="outlined"
                    density="compact"
                    @update:model-value="updateOrderStatus"
                  ></v-select>
                </v-card-text>
              </v-card>
            </v-col>
          </v-row>
          
          <!-- Itens do Pedido -->
          <v-card variant="outlined" class="mb-4 mt-2">
            <v-card-title class="text-subtitle-1">
              <v-icon class="mr-2">mdi-package-variant</v-icon>
              Itens do Pedido ({{ selectedOrder.items?.length || 0 }})
            </v-card-title>
            <v-card-text>
              <div v-if="!selectedOrder.items || selectedOrder.items.length === 0" class="text-center text-grey py-4">
                Nenhum item encontrado
              </div>
              <v-list v-else>
                <v-list-item
                  v-for="(item, index) in selectedOrder.items"
                  :key="item.id"
                  class="mb-4 pb-4"
                  :class="{ 'border-bottom': index < selectedOrder.items.length - 1 }"
                >
                  <template v-slot:prepend>
                    <v-avatar size="80" rounded>
                      <v-img
                        :src="getProductImage(item)"
                        :alt="item.product_name"
                        cover
                      >
                        <template v-slot:placeholder>
                          <v-row class="fill-height ma-0" align="center" justify="center">
                            <v-icon color="grey">mdi-image-off</v-icon>
                          </v-row>
                        </template>
                      </v-img>
                    </v-avatar>
                  </template>
                  
                  <v-list-item-title class="text-h6 mb-2">
                    {{ item.product_name }}
                  </v-list-item-title>
                  
                  <v-list-item-subtitle class="item-details">
                    <div class="d-flex flex-wrap gap-2 mb-2">
                      <v-chip
                      class="mx-2"
                        v-if="item.width && item.height"
                        size="small"
                        variant="outlined"
                        density="comfortable"
                      >
                        {{ item.width }}m × {{ item.height }}m
                      </v-chip>
                      <v-chip
                        v-if="item.area"
                        size="small"
                        variant="outlined"
                        density="comfortable"
                      >
                        {{ item.area }}m²
                      </v-chip>
                      <v-chip
                        v-if="item.quantity > 1"
                        size="small"
                        color="primary"
                        density="comfortable"
                      >
                        Qtd: {{ item.quantity }}
                      </v-chip>
                      <v-chip
                        v-if="item.tenda_code"
                        size="small"
                        variant="outlined"
                        density="comfortable"
                      >
                        Código: {{ item.tenda_code }}
                      </v-chip>
                    </div>
                    <div class="d-flex flex-wrap gap-2 mx-2" v-if="getItemWeight(item) > 0 || getItemVolume(item) > 0">
                      <v-chip
                        v-if="getItemWeight(item) > 0"
                        size="small"
                        color="secondary"
                        prepend-icon="mdi-weight-kilogram"
                        density="comfortable"
                        class="mr-2"
                      >
                        Peso: {{ formatNumber(getItemWeight(item) * (item.quantity || 1)) }} kg
                      </v-chip>
                      <v-chip
                        v-if="getItemVolume(item) > 0"
                        size="small"
                        color="info"
                        prepend-icon="mdi-cube-outline"
                        density="comfortable"
                      >
                        Volume: {{ formatNumber(getItemVolume(item) * (item.quantity || 1)) }} m³
                      </v-chip>
                    </div>
                  </v-list-item-subtitle>
                  
                  <template v-slot:append>
                    <div class="text-right">
                      <div class="text-body-2 text-grey">
                        {{ formatCurrency(item.unit_price) }}
                        <span v-if="item.quantity > 1"> × {{ item.quantity }}</span>
                      </div>
                      <div class="text-h6 text-primary font-weight-bold">
                        {{ formatCurrency(item.total_price) }}
                      </div>
                    </div>
                  </template>
                  
                  <v-list-item-subtitle v-if="item.observations" class="mt-3 pt-2 border-top">
                    <v-icon size="small" class="mr-1">mdi-note-text</v-icon>
                    <strong>Observações:</strong> {{ item.observations }}
                  </v-list-item-subtitle>
                </v-list-item>
              </v-list>
            </v-card-text>
          </v-card>
          
          <!-- Custos do Pedido -->
          <v-card variant="outlined" class="mb-4">
            <v-card-title class="text-subtitle-1 d-flex align-center">
              <v-icon class="mr-2">mdi-cash-multiple</v-icon>
              Custos do Pedido
              <v-spacer></v-spacer>
              <v-btn
                color="primary"
                size="small"
                @click="openCostDialog(null)"
              >
                <v-icon class="mr-1">mdi-plus</v-icon>
                Adicionar Custo
              </v-btn>
            </v-card-title>
            <v-card-text>
              <div v-if="!selectedOrder.costs || selectedOrder.costs.length === 0" class="text-center text-grey py-4">
                Nenhum custo cadastrado
              </div>
              <v-table v-else>
                <thead>
                  <tr>
                    <th>Custo</th>
                    <th>Tipo</th>
                    <th class="text-right">Valor</th>
                    <th class="text-center" style="width: 120px">Ações</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="cost in selectedOrder.costs" :key="cost.id">
                    <td>
                      <v-chip
                        v-if="cost.cost_type"
                        :color="cost.cost_type.type === 'receita' ? 'success' : 'error'"
                        size="small"
                      >
                        {{ cost.cost_type.name }}
                      </v-chip>
                      <span v-else class="text-grey">-</span>
                    </td>
                    <td>
                      <v-chip
                        v-if="cost.cost_type"
                        :color="cost.cost_type.type === 'receita' ? 'success' : 'error'"
                        size="small"
                        variant="outlined"
                      >
                        {{ cost.cost_type.type === 'receita' ? 'Receita' : 'Despesa' }}
                      </v-chip>
                    </td>
                    <td class="text-right font-weight-bold">{{ formatCurrency(cost.value) }}</td>
                    <td class="text-center">
                      <v-btn
                        icon="mdi-pencil"
                        size="small"
                        variant="text"
                        @click="openCostDialog(cost)"
                      ></v-btn>
                      <v-btn
                        icon="mdi-delete"
                        size="small"
                        variant="text"
                        color="error"
                        @click="confirmDeleteCost(cost)"
                      ></v-btn>
                    </td>
                  </tr>
                </tbody>
                <tfoot>
                  <tr>
                    <td colspan="2" class="font-weight-bold">Total de Custos:</td>
                    <td class="text-right font-weight-bold text-primary">
                      {{ formatCurrency(totalCosts) }}
                    </td>
                    <td></td>
                  </tr>
                  <tr>
                    <td colspan="2" class="font-weight-bold text-h6">Total Geral (Pedido + Custos):</td>
                    <td class="text-right font-weight-bold text-h6 text-primary">
                      {{ formatCurrency(totalWithCosts) }}
                    </td>
                    <td></td>
                  </tr>
                </tfoot>
              </v-table>
            </v-card-text>
          </v-card>
          
          <!-- Observações Gerais -->
          <v-card v-if="selectedOrder.notes" variant="outlined">
            <v-card-title class="text-subtitle-1">
              <v-icon class="mr-2">mdi-note-text-outline</v-icon>
              Observações Gerais
            </v-card-title>
            <v-card-text>
              {{ selectedOrder.notes }}
            </v-card-text>
          </v-card>
            </v-card-text>
          </v-window-item>
          
          <v-window-item value="shipping">
            <v-card-text>
              <ShippingTab :order="selectedOrder" />
            </v-card-text>
          </v-window-item>
        </v-window>
        
        <v-divider></v-divider>
        
        <v-card-actions>
          <v-btn
            color="primary"
            variant="text"
            @click="generatePdf"
            :loading="generatingPdf"
          >
            <v-icon class="mr-2">mdi-file-pdf-box</v-icon>
            Gerar PDF
          </v-btn>
          <v-spacer></v-spacer>
          <v-btn @click="orderDialog = false">Fechar</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
    
    <!-- Dialog de Custo -->
    <v-dialog v-model="costDialog" max-width="500">
      <v-card>
        <v-card-title>
          {{ editingCost ? 'Editar Custo' : 'Adicionar Custo' }}
        </v-card-title>
        <v-card-text>
          <v-form ref="costFormRef">
            <v-select
              v-model="costForm.cost_type_id"
              :items="costTypes"
              item-title="name"
              item-value="id"
              label="Tipo de Custo"
              :rules="[v => !!v || 'Tipo de custo é obrigatório']"
              required
              variant="outlined"
              density="compact"
              @update:model-value="onCostTypeChange"
            >
              <template v-slot:item="{ props, item }">
                <v-list-item v-bind="props">
                  <template v-slot:prepend>
                    <v-chip
                      :color="item.raw.type === 'receita' ? 'success' : 'error'"
                      size="small"
                      class="mr-2"
                    >
                      {{ item.raw.type === 'receita' ? 'Receita' : 'Despesa' }}
                    </v-chip>
                  </template>
                </v-list-item>
              </template>
            </v-select>
            <v-text-field
              v-model.number="costForm.value"
              label="Valor"
              type="number"
              step="0.01"
              min="0"
              :rules="[
                v => !!v || 'Valor é obrigatório',
                v => v >= 0 || 'Valor deve ser positivo'
              ]"
              required
              variant="outlined"
              density="compact"
              prefix="R$"
              class="mt-2"
            ></v-text-field>
          </v-form>
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn @click="costDialog = false">Cancelar</v-btn>
          <v-btn
            color="primary"
            @click="saveCost"
            :loading="savingCost"
          >
            Salvar
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
    
    <!-- Dialog de Confirmação de Exclusão -->
    <v-dialog v-model="deleteCostDialog" max-width="400">
      <v-card>
        <v-card-title>Confirmar Exclusão</v-card-title>
        <v-card-text>
          Tem certeza que deseja excluir o custo "{{ costToDelete?.name }}"?
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn @click="deleteCostDialog = false">Cancelar</v-btn>
          <v-btn
            color="error"
            @click="deleteCost"
            :loading="deletingCost"
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
import { calculateTentMetrics, formatNumber } from '../../utils/tentCalculations'
import ShippingTab from './ShippingTab.vue'

const statuses = ref([])
const orders = ref([])
const orderDialog = ref(false)
const selectedOrder = ref(null)
const selectedStatusId = ref(null)
const draggedOrder = ref(null)
const generatingPdf = ref(false)
const activeTab = ref('details')

// Cost management
const costDialog = ref(false)
const editingCost = ref(null)
const costForm = ref({
  cost_type_id: null,
  value: 0
})
const costFormRef = ref(null)
const savingCost = ref(false)
const deleteCostDialog = ref(false)
const costToDelete = ref(null)
const deletingCost = ref(false)
const costTypes = ref([])

function formatCurrency(value) {
  return new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL'
  }).format(value)
}

function formatDate(dateString) {
  if (!dateString) return ''
  const date = new Date(dateString)
  return new Intl.DateTimeFormat('pt-BR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  }).format(date)
}

function getProductImage(item) {
  if (!item.product) {
    return ''
  }
  
  // Função auxiliar para formatar o caminho da imagem
  const formatImagePath = (path) => {
    if (!path) return ''
    
    // Se já é uma URL completa, retornar como está
    if (path.startsWith('http')) {
      return path
    }
    
    // Se começa com /assets, usar caminho absoluto
    // As imagens são servidas pelo backend via rota /assets/images
    if (path.startsWith('/assets') || path.startsWith('assets/')) {
      const cleanPath = path.startsWith('/') ? path : `/${path}`
      // Garantir que o caminho começa com /assets/images
      if (!cleanPath.startsWith('/assets/images')) {
        // Se começa com assets/, adicionar /
        if (cleanPath.startsWith('assets/')) {
          return `/${cleanPath}`
        }
        // Se não, assumir que precisa de /assets/images
        return `/assets/images/${cleanPath.replace(/^\/?assets\/images?\//, '')}`
      }
      return cleanPath
    }
    
    // Se começa com /storage, retornar como está
    if (path.startsWith('/storage')) {
      return path
    }
    
    // Se começa com /, retornar como está
    if (path.startsWith('/')) {
      return path
    }
    
    // Caso contrário, adicionar /storage/
    return `/storage/${path}`
  }
  
  // Tentar pegar a imagem principal do relacionamento mainImage
  if (item.product.main_image && item.product.main_image.path) {
    return formatImagePath(item.product.main_image.path)
  }
  
  // Tentar pegar a primeira imagem do array (que tem is_main = true)
  if (item.product.images && item.product.images.length > 0) {
    // Procurar pela imagem principal primeiro
    const mainImg = item.product.images.find(img => img.is_main)
    if (mainImg && mainImg.path) {
      return formatImagePath(mainImg.path)
    }
    
    // Se não encontrar, pegar a primeira
    const firstImage = item.product.images[0]
    return formatImagePath(firstImage.path || firstImage.image || '')
  }
  
  return ''
}

// Calcular peso de um item individual
function getItemWeight(item) {
  if (!item.width || !item.height || item.width <= 0 || item.height <= 0) {
    return 0
  }
  const metrics = calculateTentMetrics(item.height, item.width) // height = comprimento, width = largura
  return metrics.peso || 0
}

// Calcular volume de um item individual
function getItemVolume(item) {
  if (!item.width || !item.height || item.width <= 0 || item.height <= 0) {
    return 0
  }
  const metrics = calculateTentMetrics(item.height, item.width) // height = comprimento, width = largura
  return metrics.volume || 0
}

function getOrdersByStatus(statusId) {
  return orders.value.filter(o => o.status_id === statusId)
}

async function selectOrder(order) {
  selectedOrder.value = order
  selectedStatusId.value = order.status_id
  
  // Garantir que costs seja um array
  if (!selectedOrder.value.costs) {
    selectedOrder.value.costs = []
  }
  
  // Sempre recarregar o pedido completo para garantir que todos os dados estejam atualizados
  // especialmente o shipping que pode ter sido atualizado
  try {
    const response = await api.get(`/admin/orders/${order.id}`)
    selectedOrder.value = response.data
    selectedStatusId.value = response.data.status_id
    // Garantir que costs seja um array
    if (!selectedOrder.value.costs) {
      selectedOrder.value.costs = []
    }
  } catch (error) {
    console.error('Error fetching order details:', error)
  }
  
  orderDialog.value = true
}

async function generatePdf() {
  if (!selectedOrder.value) return
  
  generatingPdf.value = true
  try {
    const response = await api.get(`/admin/orders/${selectedOrder.value.id}/pdf`, {
      responseType: 'blob'
    })
    
    // Criar link para abrir em nova aba
    const url = window.URL.createObjectURL(new Blob([response.data], { type: 'application/pdf' }))
    window.open(url, '_blank')
    
    // Limpar URL após um tempo
    setTimeout(() => {
      window.URL.revokeObjectURL(url)
    }, 1000)
  } catch (error) {
    console.error('Error generating PDF:', error)
    alert('Erro ao gerar PDF. Verifique se o serviço está configurado.')
  } finally {
    generatingPdf.value = false
  }
}

// Computed properties for costs
const totalCosts = computed(() => {
  if (!selectedOrder.value || !selectedOrder.value.costs) return 0
  return selectedOrder.value.costs.reduce((sum, cost) => sum + parseFloat(cost.value || 0), 0)
})

const totalWithCosts = computed(() => {
  if (!selectedOrder.value) return 0
  return parseFloat(selectedOrder.value.total_amount || 0) + totalCosts.value
})

// Calcular peso e volume total do pedido
const orderTotalWeight = computed(() => {
  if (!selectedOrder.value || !selectedOrder.value.items) return 0
  
  return selectedOrder.value.items.reduce((total, item) => {
    // Se o item tem width e height, calcular o peso
    if (item.width && item.height && item.width > 0 && item.height > 0) {
      const metrics = calculateTentMetrics(item.height, item.width) // height = comprimento, width = largura
      const itemWeight = metrics.peso || 0
      const quantity = item.quantity || 1
      return total + (itemWeight * quantity)
    }
    return total
  }, 0)
})

const orderTotalVolume = computed(() => {
  if (!selectedOrder.value || !selectedOrder.value.items) return 0
  
  return selectedOrder.value.items.reduce((total, item) => {
    // Se o item tem width e height, calcular o volume
    if (item.width && item.height && item.width > 0 && item.height > 0) {
      const metrics = calculateTentMetrics(item.height, item.width) // height = comprimento, width = largura
      const itemVolume = metrics.volume || 0
      const quantity = item.quantity || 1
      return total + (itemVolume * quantity)
    }
    return total
  }, 0)
})

// Cost management functions
async function openCostDialog(cost) {
  editingCost.value = cost
  if (cost) {
    costForm.value = {
      cost_type_id: cost.cost_type_id || null,
      value: parseFloat(cost.value)
    }
  } else {
    costForm.value = {
      cost_type_id: null,
      value: 0
    }
  }
  
  // Carregar tipos de custos se ainda não foram carregados
  if (costTypes.value.length === 0) {
    await fetchCostTypes()
  }
  
  costDialog.value = true
}

function onCostTypeChange(costTypeId) {
  // Não precisa fazer nada, apenas selecionar o tipo
}

async function fetchCostTypes() {
  try {
    const response = await api.get('/admin/cost-types')
    costTypes.value = response.data
  } catch (error) {
    console.error('Error fetching cost types:', error)
  }
}

async function saveCost() {
  if (!costFormRef.value) return
  
  const { valid } = await costFormRef.value.validate()
  if (!valid) return
  
  if (!selectedOrder.value) return
  
  savingCost.value = true
  try {
    if (editingCost.value) {
      // Update
      const response = await api.put(
        `/admin/orders/${selectedOrder.value.id}/costs/${editingCost.value.id}`,
        costForm.value
      )
      
      // Atualizar no array local
      const costIndex = selectedOrder.value.costs.findIndex(c => c.id === editingCost.value.id)
      if (costIndex !== -1) {
        selectedOrder.value.costs[costIndex] = response.data
        // Forçar reatividade
        selectedOrder.value.costs = [...selectedOrder.value.costs]
      }
    } else {
      // Create
      const response = await api.post(
        `/admin/orders/${selectedOrder.value.id}/costs`,
        costForm.value
      )
      
      // Adicionar ao array local
      if (!selectedOrder.value.costs) {
        selectedOrder.value.costs = []
      }
      selectedOrder.value.costs.push(response.data)
      selectedOrder.value.costs = [...selectedOrder.value.costs]
    }
    
    costDialog.value = false
  } catch (error) {
    console.error('Error saving cost:', error)
    alert('Erro ao salvar custo. Tente novamente.')
  } finally {
    savingCost.value = false
  }
}

function confirmDeleteCost(cost) {
  costToDelete.value = cost
  deleteCostDialog.value = true
}

async function deleteCost() {
  if (!selectedOrder.value || !costToDelete.value) return
  
  deletingCost.value = true
  try {
    await api.delete(
      `/admin/orders/${selectedOrder.value.id}/costs/${costToDelete.value.id}`
    )
    
    // Remover do array local
    selectedOrder.value.costs = selectedOrder.value.costs.filter(
      c => c.id !== costToDelete.value.id
    )
    
    deleteCostDialog.value = false
    costToDelete.value = null
  } catch (error) {
    console.error('Error deleting cost:', error)
    alert('Erro ao excluir custo. Tente novamente.')
  } finally {
    deletingCost.value = false
  }
}

async function updateOrderStatus() {
  if (!selectedOrder.value || !selectedStatusId.value) return
  
  const orderId = selectedOrder.value.id
  const oldStatusId = selectedOrder.value.status_id
  
  try {
    await api.put(`/admin/orders/${orderId}/status`, {
      status_id: selectedStatusId.value
    })
    
    // Atualizar o pedido selecionado
    selectedOrder.value = {
      ...selectedOrder.value,
      status_id: selectedStatusId.value
    }
    
    // Atualizar no array de pedidos
    const orderIndex = orders.value.findIndex(o => o.id === orderId)
    if (orderIndex !== -1) {
      orders.value[orderIndex] = {
        ...orders.value[orderIndex],
        status_id: selectedStatusId.value
      }
      // Forçar atualização do Vue
      orders.value = [...orders.value]
    }
  } catch (error) {
    console.error('Error updating order status:', error)
    // Reverter se houver erro
    selectedStatusId.value = oldStatusId
  }
}

function handleDragStart(event, order) {
  draggedOrder.value = order
  event.dataTransfer.effectAllowed = 'move'
  event.dataTransfer.setData('text/plain', order.id.toString())
  event.currentTarget.style.opacity = '0.5'
}

function handleDragEnd(event) {
  event.currentTarget.style.opacity = '1'
  draggedOrder.value = null
}

async function handleDrop(event, targetStatusId) {
  event.preventDefault()
  
  if (!draggedOrder.value) return
  
  // Não fazer nada se o status for o mesmo
  if (draggedOrder.value.status_id === targetStatusId) {
    draggedOrder.value = null
    return
  }
  
  const orderId = draggedOrder.value.id
  const oldStatusId = draggedOrder.value.status_id
  
  try {
    await api.put(`/admin/orders/${orderId}/status`, {
      status_id: targetStatusId
    })
    
    // Atualizar o status do pedido localmente usando Vue.set ou reatribuição
    const orderIndex = orders.value.findIndex(o => o.id === orderId)
    if (orderIndex !== -1) {
      // Criar um novo objeto para garantir reatividade
      orders.value[orderIndex] = {
        ...orders.value[orderIndex],
        status_id: targetStatusId
      }
    }
    
    // Se o pedido selecionado foi movido, atualizar também
    if (selectedOrder.value && selectedOrder.value.id === orderId) {
      selectedOrder.value = {
        ...selectedOrder.value,
        status_id: targetStatusId
      }
      selectedStatusId.value = targetStatusId
    }
    
    // Forçar atualização do Vue
    orders.value = [...orders.value]
  } catch (error) {
    console.error('Error updating order status:', error)
    // Reverter visualmente se houver erro
    const orderIndex = orders.value.findIndex(o => o.id === orderId)
    if (orderIndex !== -1) {
      orders.value[orderIndex] = {
        ...orders.value[orderIndex],
        status_id: oldStatusId
      }
      orders.value = [...orders.value]
    }
  } finally {
    draggedOrder.value = null
  }
}

async function fetchData() {
  try {
    const [statusesRes, ordersRes] = await Promise.all([
      api.get('/admin/kanban/statuses'),
      api.get('/admin/kanban/orders')
    ])
    
    statuses.value = statusesRes.data.data || statusesRes.data
    // O endpoint retorna um array de pedidos
    if (Array.isArray(ordersRes.data)) {
      orders.value = ordersRes.data
    } else if (ordersRes.data.data && Array.isArray(ordersRes.data.data)) {
      orders.value = ordersRes.data.data
    } else {
      orders.value = []
    }
  } catch (error) {
    console.error('Error fetching data:', error)
  }
}

onMounted(() => {
  fetchData()
  
  // Listener para atualizar pedido quando frete for salvo
  window.addEventListener('order-updated', async (event) => {
    const orderId = event.detail?.orderId
    const updatedOrder = event.detail?.order
    
    if (orderId && selectedOrder.value?.id === orderId) {
      // Se o pedido atualizado foi passado no evento, usar ele
      if (updatedOrder) {
        selectedOrder.value = updatedOrder
        selectedStatusId.value = updatedOrder.status_id
      } else {
        // Caso contrário, recarregar do servidor
        try {
          const response = await api.get(`/admin/orders/${orderId}`)
          selectedOrder.value = response.data
          selectedStatusId.value = response.data.status_id
        } catch (error) {
          console.error('Error reloading order:', error)
        }
      }
      
      // Atualizar também na lista de pedidos
      const orderIndex = orders.value.findIndex(o => o.id === orderId)
      if (orderIndex !== -1) {
        if (updatedOrder) {
          orders.value[orderIndex] = updatedOrder
        } else {
          try {
            const response = await api.get(`/admin/orders/${orderId}`)
            orders.value[orderIndex] = response.data
          } catch (error) {
            console.error('Error reloading order in list:', error)
          }
        }
        // Forçar atualização do Vue
        orders.value = [...orders.value]
      }
    }
  })
})
</script>

<style scoped>
.kanban-container {
  display: flex;
  flex-direction: column;
  width: calc(100% + 48px); /* Compensa o padding do v-container (24px de cada lado) */
  max-width: calc(100% + 48px);
  height: calc(100vh - 64px); /* Altura total menos o header */
  overflow: hidden;
  margin: 0 -24px; /* Compensa o padding do v-container */
  padding: 0;
}

.kanban-title {
  padding: 0 24px; /* Adiciona padding ao título */
}

.kanban-scroll {
  flex: 1;
  width: 100%;
  overflow-x: auto;
  overflow-y: hidden;
  padding-bottom: 16px;
}

.kanban-columns {
  display: flex;
  gap: 16px;
  min-width: 100%;
  width: 100%;
  height: 100%;
  padding: 0 24px; /* Padding lateral para não colar nas bordas */
}

.kanban-column {
  flex: 0 0 320px; /* Largura fixa para cada coluna */
  min-width: 320px;
  height: 100%;
  display: flex;
  flex-direction: column;
}

.kanban-card {
  height: 100%;
  display: flex;
  flex-direction: column;
}

.kanban-card-content {
  flex: 1;
  overflow-y: auto;
  overflow-x: hidden;
  padding: 16px !important;
  min-height: 100px;
  transition: background-color 0.2s;
}

.kanban-card-content.drag-over {
  background-color: rgba(66, 66, 66, 0.1);
  border: 2px dashed #424242;
  border-radius: 4px;
}

.order-card {
  padding: 12px;
  background-color: #424242;
  color: white;
  border-radius: 4px;
  cursor: move;
  transition: all 0.2s;
  user-select: none;
}

.order-card:hover {
  background-color: #616161;
  transform: translateY(-2px);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
}

.order-card:active {
  cursor: grabbing;
}

.order-card-header {
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  padding-bottom: 8px;
  margin-bottom: 8px;
}

.order-items {
  display: flex;
  flex-direction: column;
  gap: 8px;
  max-height: 200px;
  overflow-y: auto;
}

.order-item {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 6px;
  background-color: rgba(0, 0, 0, 0.2);
  border-radius: 4px;
}

.order-item-image {
  flex-shrink: 0;
  border-radius: 4px;
  overflow: hidden;
}

.order-item-info {
  flex: 1;
  min-width: 0;
}

.order-item-info .text-caption {
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.order-card-footer {
  border-top: 1px solid rgba(255, 255, 255, 0.1);
  padding-top: 8px;
  margin-top: 8px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.order-card.dragging {
  opacity: 0.5;
}

.border-bottom {
  border-bottom: 1px solid rgba(255, 255, 255, 0.12);
  padding-bottom: 16px;
}

.order-info-card {
  height: 100%;
  display: flex;
  flex-direction: column;
}

.order-info-card .v-card-text {
  flex: 1;
  display: flex;
  flex-direction: column;
}

.item-details {
  margin-top: 8px;
}

.border-top {
  border-top: 1px solid rgba(255, 255, 255, 0.12);
}
</style>
