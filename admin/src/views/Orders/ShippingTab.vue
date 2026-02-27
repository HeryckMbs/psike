<template>
  <div v-if="order">
    <v-alert
      v-if="!order.customer?.cep"
      type="warning"
      variant="tonal"
      class="mb-4"
    >
      CEP do cliente não informado. Não é possível calcular frete.
    </v-alert>

    <div v-else>
      <!-- Informações do Pedido para Frete -->
      <v-card variant="outlined" class="mb-4">
        <v-card-title class="text-subtitle-1">
          <v-icon class="mr-2">mdi-package-variant</v-icon>
          Dimensões das Caixas
        </v-card-title>
        <v-card-text>
          <div v-if="boxDimensions.length === 0" class="text-center text-grey py-4">
            Nenhum produto com dimensões válidas
          </div>
          <v-list v-else>
            <v-list-item
              v-for="(box, index) in boxDimensions"
              :key="index"
              class="mb-2"
            >
              <template v-slot:prepend>
                <v-avatar color="primary" size="40">
                  <v-icon color="white">mdi-package-variant</v-icon>
                </v-avatar>
              </template>
              <v-list-item-title>
                <div class="d-flex align-center">
                  <span v-if="box.name">{{ box.name }}</span>
                  <span v-else>Caixa {{ index + 1 }}</span>
                  <span v-if="box.quantity > 1" class="text-caption text-grey ml-2">
                    ({{ box.quantity }}x)
                  </span>
                  <v-chip
                    v-if="box.missingBox"
                    size="small"
                    color="error"
                    class="ml-2"
                  >
                    Sem caixa
                  </v-chip>
                </div>
              </v-list-item-title>
              <v-list-item-subtitle>
                <div v-if="box.missingBox" class="text-error mt-1">
                  <strong>Produto:</strong> {{ box.productName }}<br>
                  <strong>Atenção:</strong> Este produto não possui caixa padrão associada. É necessário associar uma caixa ao produto antes de calcular o frete.
                </div>
                <div v-else class="d-flex flex-wrap gap-2 mt-1">
                  <v-chip class="mr-2" size="small" variant="outlined">
                    Largura: {{ box.width }}cm
                  </v-chip>
                  <v-chip class="mr-2" size="small" variant="outlined">
                    Altura: {{ box.height }}cm
                  </v-chip>
                  <v-chip class="mr-2" size="small" variant="outlined">
                    Comprimento: {{ box.length }}cm
                  </v-chip>
                  <v-chip size="small" color="secondary" variant="outlined">
                    Peso: {{ formatNumber(box.weight) }}kg
                  </v-chip>
                </div>
              </v-list-item-subtitle>
            </v-list-item>
          </v-list>
        </v-card-text>
      </v-card>

      <!-- Cálculo de Frete -->
      <v-card variant="outlined" class="mb-4">
        <v-card-title class="text-subtitle-1">
          <v-icon class="mr-2">mdi-truck-delivery</v-icon>
          Opções de Frete
        </v-card-title>
        <v-card-text>
          <div class="mb-4">
            <div class="text-caption text-grey mb-2">
              <strong>Origem:</strong> Goiânia/GO (CEP: 74000-000)<br>
              <strong>Destino:</strong> {{ order.customer?.cep }}
            </div>
            <v-btn
              color="primary"
              @click="calculateShipping"
              :loading="loadingShipping"
              :disabled="!order.customer?.cep || hasMissingBoxes"
            >
              <v-icon start>mdi-calculator</v-icon>
              Calcular Frete
            </v-btn>
            <v-alert
              v-if="hasMissingBoxes"
              type="error"
              variant="tonal"
              class="mt-2"
            >
              Alguns produtos não possuem caixa padrão associada. É necessário associar caixas aos produtos antes de calcular o frete.
            </v-alert>
          </div>

          <v-alert
            v-if="shippingError"
            type="error"
            variant="tonal"
            class="mb-4"
            closable
            @click:close="shippingError = null"
          >
            {{ shippingError }}
          </v-alert>

          <div v-if="shippingOptions.length > 0">
            <v-list>
              <v-list-item
                v-for="option in shippingOptions"
                :key="option.id"
                :class="{ 'selected-shipping': selectedShipping?.id === option.id }"
                class="mb-2 shipping-option-item"
                @click="selectShipping(option)"
              >
                <template v-slot:prepend>
                  <v-radio
                    :model-value="selectedShipping?.id === option.id"
                    @click.stop="selectShipping(option)"
                  ></v-radio>
                </template>
                <v-list-item-title class="d-flex align-center justify-space-between">
                  <div>
                    <strong>{{ option.name }}</strong>
                    <v-chip
                      size="small"
                      variant="text"
                      class="ml-2"
                    >
                      {{ option.company }}
                    </v-chip>
                  </div>
                  <div class="text-h6 text-primary ml-4">
                    {{ formatCurrency(option.price) }}
                  </div>
                </v-list-item-title>
                <v-list-item-subtitle>
                  <div class="d-flex align-center gap-2 mt-1">
                    <v-icon size="small">mdi-clock-outline</v-icon>
                    <span>{{ option.delivery_time }} dias úteis</span>
                    <span class="text-grey">({{ option.delivery_range }})</span>
                  </div>
                </v-list-item-subtitle>
              </v-list-item>
            </v-list>

            <v-btn
              v-if="selectedShipping"
              color="success"
              class="mt-4"
              @click="saveShipping"
              :loading="savingShipping"
              block
            >
              <v-icon start>mdi-content-save</v-icon>
              Salvar Frete Selecionado
            </v-btn>
          </div>

          <div v-else-if="!loadingShipping" class="text-center text-grey py-8">
            <v-icon size="48" color="grey">mdi-truck-delivery-outline</v-icon>
            <div class="mt-2">Clique em "Calcular Frete" para ver as opções</div>
          </div>
        </v-card-text>
      </v-card>

      <!-- Frete Atual -->
      <v-card v-if="order.shipping" variant="outlined">
        <v-card-title class="text-subtitle-1">
          <v-icon class="mr-2">mdi-information</v-icon>
          Frete Atual
        </v-card-title>
        <v-card-text>
          <div class="mb-2">
            <strong>Transportadora:</strong> {{ order.shipping.carrier }}
          </div>
          <div class="mb-2">
            <strong>Serviço:</strong> {{ order.shipping.selected_service_name }}
          </div>
          <div class="mb-2">
            <strong>Valor:</strong> {{ formatCurrency(order.shipping.cost) }}
          </div>
          <div class="mb-2" v-if="order.shipping.tracking_code">
            <strong>Código de Rastreamento:</strong> {{ order.shipping.tracking_code }}
          </div>
          <div class="mb-2">
            <strong>Status:</strong>
            <v-chip
              :color="getShippingStatusColor(order.shipping.status)"
              size="small"
              class="ml-2"
            >
              {{ getShippingStatusLabel(order.shipping.status) }}
            </v-chip>
          </div>
        </v-card-text>
      </v-card>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import api from '../../services/api'

const props = defineProps({
  order: {
    type: Object,
    required: true
  }
})

const loadingShipping = ref(false)
const shippingOptions = ref([])
const selectedShipping = ref(null)
const shippingError = ref(null)
const savingShipping = ref(false)

const boxDimensions = computed(() => {
  if (!props.order?.items) return []
  
  const boxes = []
  props.order.items.forEach(item => {
    // Buscar caixa do produto
    const box = item.product?.box
    
    if (box) {
      boxes.push({
        width: box.width,
        height: box.height,
        length: box.length,
        weight: box.weight,
        name: box.name,
        quantity: item.quantity || 1,
        item: item,
        productName: item.product_name
      })
    } else {
      // Fallback: mostrar aviso se não tiver caixa
      boxes.push({
        width: null,
        height: null,
        length: null,
        weight: null,
        name: null,
        quantity: item.quantity || 1,
        item: item,
        productName: item.product_name,
        missingBox: true
      })
    }
  })
  
  return boxes
})

const hasMissingBoxes = computed(() => {
  return boxDimensions.value.some(box => box.missingBox)
})

async function calculateShipping() {
  if (!props.order?.customer?.cep) {
    shippingError.value = 'CEP do cliente não informado'
    return
  }

  loadingShipping.value = true
  shippingError.value = null
  shippingOptions.value = []
  selectedShipping.value = null

  try {
    const response = await api.post('/shipping/calculate', {
      order_id: props.order.id,
      destination_cep: props.order.customer.cep
    })

    if (response.data.success) {
      shippingOptions.value = response.data.options || []
      if (shippingOptions.value.length === 0) {
        shippingError.value = 'Nenhuma opção de frete disponível'
      } else {
        // Se já existe um shipping salvo, selecionar automaticamente a opção correspondente
        if (props.order.shipping) {
          const savedShipping = props.order.shipping
          // Tentar encontrar a opção que corresponde ao shipping salvo
          const matchingOption = shippingOptions.value.find(option => {
            // Comparar por melhor_envio_service_id se disponível
            if (savedShipping.melhor_envio_service_id && option.melhor_envio_service_id) {
              return option.melhor_envio_service_id === savedShipping.melhor_envio_service_id
            }
            // Comparar por nome do serviço e transportadora
            if (savedShipping.selected_service_name && option.name) {
              return option.name === savedShipping.selected_service_name &&
                     option.company === savedShipping.carrier
            }
            // Comparar apenas por transportadora e preço (último recurso)
            return option.company === savedShipping.carrier &&
                   Math.abs(option.price - savedShipping.cost) < 0.01
          })
          
          if (matchingOption) {
            selectedShipping.value = matchingOption
          }
        }
      }
    } else {
      shippingError.value = response.data.message || 'Erro ao calcular frete'
    }
  } catch (error) {
    shippingError.value = error.response?.data?.message || error.message || 'Erro ao calcular frete'
    console.error('Erro ao calcular frete:', error)
  } finally {
    loadingShipping.value = false
  }
}

function selectShipping(option) {
  selectedShipping.value = option
}

async function saveShipping() {
  if (!selectedShipping.value) return

  savingShipping.value = true

  try {
    // Criar/atualizar shipping via API
    const response = await api.post(`/admin/orders/${props.order.id}/shipping`, {
      shipping_option: selectedShipping.value
    })

    if (response.data.success) {
      // Recarregar o pedido para pegar o shipping atualizado
      try {
        const orderResponse = await api.get(`/admin/orders/${props.order.id}`)
        // Atualizar o order no componente pai via evento
        window.dispatchEvent(new CustomEvent('order-updated', { 
          detail: { orderId: props.order.id, order: orderResponse.data } 
        }))
        
        // Atualizar o shipping localmente se possível
        if (orderResponse.data.shipping) {
          props.order.shipping = orderResponse.data.shipping
        }
      } catch (err) {
        console.error('Erro ao recarregar pedido:', err)
      }
      
      // Manter a seleção para mostrar que foi salvo
      // Não limpar selectedShipping e shippingOptions para que o usuário veja o que foi salvo
      
      // Mostrar mensagem de sucesso
      shippingError.value = null
      alert('Frete salvo com sucesso!')
    }
  } catch (error) {
    shippingError.value = error.response?.data?.message || error.message || 'Erro ao salvar frete'
    console.error('Erro ao salvar frete:', error)
  } finally {
    savingShipping.value = false
  }
}

function formatCurrency(value) {
  return new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL'
  }).format(value)
}

function formatNumber(value) {
  return new Intl.NumberFormat('pt-BR', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(value)
}

function getShippingStatusColor(status) {
  const colors = {
    'pending': 'grey',
    'shipped': 'blue',
    'delivered': 'green',
    'returned': 'red'
  }
  return colors[status] || 'grey'
}

function getShippingStatusLabel(status) {
  const labels = {
    'pending': 'Pendente',
    'shipped': 'Enviado',
    'delivered': 'Entregue',
    'returned': 'Devolvido'
  }
  return labels[status] || status
}

// Verificar se já existe shipping salvo quando o componente carregar
watch(() => props.order?.shipping, (newShipping) => {
  // Se já existe shipping salvo e temos opções calculadas, selecionar automaticamente
  if (newShipping && shippingOptions.value.length > 0 && !selectedShipping.value) {
    const matchingOption = shippingOptions.value.find(option => {
      // Comparar por melhor_envio_service_id se disponível
      if (newShipping.melhor_envio_service_id && option.melhor_envio_service_id) {
        return option.melhor_envio_service_id === newShipping.melhor_envio_service_id
      }
      // Comparar por nome do serviço e transportadora
      if (newShipping.selected_service_name && option.name) {
        return option.name === newShipping.selected_service_name &&
               option.company === newShipping.carrier
      }
      // Comparar apenas por transportadora e preço (último recurso)
      return option.company === newShipping.carrier &&
             Math.abs(option.price - newShipping.cost) < 0.01
    })
    
    if (matchingOption) {
      selectedShipping.value = matchingOption
    }
  }
}, { immediate: true })
</script>

<style scoped>
.shipping-option-item {
  border: 2px solid transparent;
  border-radius: 8px;
  transition: all 0.2s;
  cursor: pointer;
}

.shipping-option-item:hover {
  background-color: rgba(var(--v-theme-primary), 0.1);
  border-color: rgba(var(--v-theme-primary), 0.3);
}

.selected-shipping {
  background-color: rgba(var(--v-theme-primary), 0.15);
  border-color: rgb(var(--v-theme-primary));
}
</style>
