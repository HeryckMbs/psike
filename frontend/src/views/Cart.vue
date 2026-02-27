<template>
  <div>
    <Header />
    
    <section class="cart-page">
      <div class="container">
        <h1 class="cart-page-title">Meu Carrinho</h1>
        
        <div class="cart-page-content">
          <div class="cart-items-container">
            <div class="cart-items" id="cartItems">
              <div v-if="cartStore.items.length === 0" class="cart-empty" id="cartEmpty">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <p>Seu carrinho está vazio</p>
                <router-link to="/produtos" class="btn-primary" style="margin-top: 1rem;">Ver Produtos</router-link>
              </div>
              
              <div v-for="item in cartStore.items" :key="item.id" class="cart-item" :data-product-id="item.id">
                <img :src="item.image" :alt="item.name" class="cart-item-image">
                <div class="cart-item-content">
                  <h4 class="cart-item-name">{{ item.name }}</h4>
                  <p class="cart-item-specs">{{ item.specs || '' }}</p>
                  
                  <div v-if="item.isTent && item.area" class="cart-item-dimensions">
                    <p class="cart-item-dimensions-text">
                      <strong>Área:</strong> {{ item.area.toFixed(2) }}m²
                    </p>
                  </div>
                  
                  <div v-if="item.price" class="cart-item-price">
                    <span class="cart-item-price-label">Preço unitário:</span>
                    <span class="cart-item-price-value">{{ formatPrice(item.price) }}</span>
                  </div>
                  
                  <div v-if="item.price" class="cart-item-price-total">
                    <span class="cart-item-price-label">Preço total:</span>
                    <span class="cart-item-price-value">{{ formatPrice(item.price * item.quantity) }}</span>
                  </div>
                  
                  <div class="cart-item-controls">
                    <div class="cart-item-quantity">
                      <button class="cart-quantity-btn" @click="cartStore.updateQuantity(item.id, item.quantity - 1)">-</button>
                      <span class="cart-quantity-value">{{ item.quantity }}</span>
                      <button class="cart-quantity-btn" @click="cartStore.updateQuantity(item.id, item.quantity + 1)">+</button>
                    </div>
                    <button class="cart-item-remove" @click="cartStore.removeItem(item.id)" aria-label="Remover item">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                      </svg>
                    </button>
                  </div>
                  
                  <div class="cart-item-observations">
                    <label :for="`observations-${item.id}`" class="cart-item-observations-label">
                      Personalizações desejadas para este pedido:
                    </label>
                    <textarea 
                      :id="`observations-${item.id}`"
                      class="cart-item-observations-field" 
                      placeholder="Cores desejadas, medidas personalizadas ou outras especificações..."
                      rows="4"
                      :value="item.observations"
                      @input="cartStore.updateObservations(item.id, $event.target.value)">
                    </textarea>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div v-if="cartStore.items.length > 0" class="cart-sidebar" id="cartSidebar">
            <div class="cart-summary-card">
              <h3 class="cart-summary-title">Resumo do Pedido</h3>
              
              <div class="cart-summary">
                <div class="cart-summary-row">
                  <span class="cart-summary-label">Total de itens:</span>
                  <span class="cart-summary-value" id="cartTotalItems">{{ cartStore.totalItems }}</span>
                </div>
                <div class="cart-summary-row">
                  <span class="cart-summary-label">Valor:</span>
                  <span class="cart-summary-value" id="cartTotalPrice">{{ formatPrice(cartStore.totalPrice) }}</span>
                </div>
              </div>

              <ShippingCalculator />
              
              <div v-if="shippingStore.selectedOption" class="cart-summary-row">
                <span class="cart-summary-label">Frete:</span>
                <span class="cart-summary-value">{{ formatPrice(shippingStore.selectedOption.price) }}</span>
              </div>
              
              <div class="cart-summary-row cart-summary-total">
                <span class="cart-summary-label">Total:</span>
                <span class="cart-summary-value">{{ formatPrice(cartStore.totalPrice + (shippingStore.selectedOption?.price || 0)) }}</span>
              </div>

              <div class="cart-shipping-info">
                <h4 class="cart-shipping-title">CONDIÇÕES</h4>
                <ul class="cart-shipping-list">
                  <li>Valor do frete varia com o peso e modelo</li>
                  <li>Consulte valores e condições</li>
                  <li>Valores das tendas sujeitos a alteração</li>
                </ul>
                <div class="cart-shipping-not-included">
                  <strong>NÃO INCLUSO:</strong> Cordas, estruturas de sustentação e mão de obra presencial (quando não contratada).
                </div>
              </div>

              <div class="cart-client-data">
                <h4 class="cart-client-title">Dados para Frete</h4>
                <p class="cart-client-subtitle">Por favor, preencha os dados abaixo para finalizar o pedido:</p>
                
                <div class="cart-form-group">
                  <label for="client-name" class="cart-form-label">
                    Nome Completo <span class="required">*</span>
                  </label>
                  <input 
                    type="text" 
                    id="client-name" 
                    class="cart-form-input" 
                    v-model="customerData.name"
                    placeholder="Digite seu nome completo" 
                    required>
                </div>
                
                <div class="cart-form-group">
                  <label for="client-cep" class="cart-form-label">
                    CEP <span class="required">*</span>
                  </label>
                  <input 
                    type="text" 
                    id="client-cep" 
                    class="cart-form-input" 
                    v-model="customerData.cep"
                    placeholder="00000-000" 
                    maxlength="9" 
                    @input="formatCEP"
                    @blur="fetchCEP"
                    required>
                </div>
                
                <div class="cart-form-group">
                  <label for="client-address" class="cart-form-label">
                    Endereço Completo <span class="required">*</span>
                  </label>
                  <textarea 
                    id="client-address" 
                    class="cart-form-textarea" 
                    v-model="customerData.address"
                    placeholder="Rua, número, complemento, bairro, cidade, estado" 
                    rows="3" 
                    required>
                  </textarea>
                </div>
                
                <div class="cart-form-group">
                  <label for="client-document" class="cart-form-label">
                    CPF ou CNPJ <span class="required">*</span>
                  </label>
                  <input 
                    type="text" 
                    id="client-document" 
                    class="cart-form-input" 
                    v-model="customerData.document"
                    placeholder="000.000.000-00 ou 00.000.000/0000-00" 
                    @input="formatDocument"
                    required>
                </div>
                
                <div class="cart-form-group">
                  <label for="client-phone" class="cart-form-label">
                    Telefone <span class="required">*</span>
                  </label>
                  <input 
                    type="text" 
                    id="client-phone" 
                    class="cart-form-input" 
                    v-model="customerData.phone"
                    placeholder="(00) 00000-0000" 
                    @input="formatPhone"
                    required>
                </div>
              </div>

              <button class="btn-whatsapp-cart" @click="sendOrder">
                <svg viewBox="0 0 24 24" fill="currentColor">
                  <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                </svg>
                Enviar Pedido via WhatsApp
              </button>

              <router-link to="/produtos" class="btn-secondary" style="margin-top: 1rem; text-align: center; display: block;">
                Continuar Comprando
              </router-link>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import Header from '../components/Header.vue'
import ShippingCalculator from '../components/ShippingCalculator.vue'
import { useCartStore } from '../store/cart'
import { useShippingStore } from '../store/shipping'
import { ordersApi } from '../services/api'

const cartStore = useCartStore()
const shippingStore = useShippingStore()

const customerData = ref({
  name: '',
  cep: '',
  address: '',
  document: '',
  phone: '',
  number: ''
})

function formatPrice(price) {
  if (!price || price === 0) return 'R$ 0,00'
  return new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL',
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(price)
}

function formatCEP(event) {
  let value = event.target.value.replace(/\D/g, '')
  if (value.length <= 8) {
    value = value.replace(/^(\d{5})(\d)/, '$1-$2')
    customerData.value.cep = value
  }
}

async function fetchCEP() {
  const cep = customerData.value.cep.replace(/\D/g, '')
  if (cep.length !== 8) return
  
  try {
    const response = await fetch(`https://viacep.com.br/ws/${cep}/json/`)
    const data = await response.json()
    
    if (data.erro) {
      return
    }
    
    const addressParts = []
    if (data.logradouro) addressParts.push(data.logradouro)
    if (data.bairro) addressParts.push(data.bairro)
    if (data.localidade) addressParts.push(data.localidade)
    if (data.uf) addressParts.push(data.uf)
    
    if (addressParts.length > 0) {
      customerData.value.address = addressParts.join(', ')
    }
  } catch (e) {
    console.error('Error fetching CEP:', e)
  }
}

function formatDocument(event) {
  let value = event.target.value.replace(/\D/g, '')
  
  if (value.length <= 11) {
    value = value.replace(/^(\d{3})(\d)/, '$1.$2')
    value = value.replace(/^(\d{3})\.(\d{3})(\d)/, '$1.$2.$3')
    value = value.replace(/\.(\d{3})(\d)/, '.$1-$2')
  } else {
    value = value.replace(/^(\d{2})(\d)/, '$1.$2')
    value = value.replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3')
    value = value.replace(/\.(\d{3})(\d)/, '.$1/$2')
    value = value.replace(/(\d{4})(\d)/, '$1-$2')
  }
  
  customerData.value.document = value
}

function formatPhone(event) {
  let value = event.target.value.replace(/\D/g, '')
  
  if (value.length <= 10) {
    value = value.replace(/^(\d{2})(\d)/, '($1) $2')
    value = value.replace(/(\d{4})(\d)/, '$1-$2')
  } else {
    value = value.replace(/^(\d{2})(\d)/, '($1) $2')
    value = value.replace(/(\d{5})(\d)/, '$1-$2')
  }
  
  customerData.value.phone = value
}

async function sendOrder() {
  // Validar campos
  if (!customerData.value.name || !customerData.value.cep || !customerData.value.address || !customerData.value.document || !customerData.value.phone) {
    alert('Por favor, preencha todos os campos obrigatórios.')
    return
  }
  
  // Verificar se frete foi selecionado (opcional, mas avisar se não foi)
  if (!shippingStore.selectedOption) {
    const proceed = confirm('Nenhum frete foi selecionado. Deseja continuar sem frete?')
    if (!proceed) {
      return
    }
  }
  
  // Criar pedido na API
  try {
    // Preparar dados do frete ANTES de criar o objeto orderData
    const shippingData = shippingStore.selectedOption ? {
      id: shippingStore.selectedOption.id || String(shippingStore.selectedOption.id) || '',
      price: Number(shippingStore.selectedOption.price) || 0,
      delivery_time: Number(shippingStore.selectedOption.delivery_time) || null,
      name: String(shippingStore.selectedOption.name || ''),
      company: String(shippingStore.selectedOption.company || shippingStore.selectedOption.name || ''),
      melhor_envio_service_id: shippingStore.selectedOption.melhor_envio_service_id ? Number(shippingStore.selectedOption.melhor_envio_service_id) : null
    } : null
    
    // Log para debug
    console.log('Frete selecionado:', shippingData)
    console.log('ShippingStore selectedOption:', shippingStore.selectedOption)
    
    const orderData = {
      customer: customerData.value,
      items: cartStore.items.map(item => ({
        product_id: item.product_id || null,
        product_name: item.name,
        width: item.width || null,
        height: item.height || null,
        area: item.area || null,
        quantity: item.quantity,
        unit_price: item.price || 0,
        total_price: (item.price || 0) * item.quantity,
        observations: item.observations || ''
      })),
      custom_price: false,
      notes: '',
      selected_shipping: shippingData
    }
    
    // Log do orderData completo
    console.log('OrderData sendo enviado:', JSON.stringify(orderData, null, 2))
    
    // Salvar dados do cliente ANTES de enviar (garantir que não sejam sobrescritos)
    const clientDataForWhatsApp = {
      name: String(customerData.value.name || '').trim(),
      phone: String(customerData.value.phone || '').trim(),
      cep: String(customerData.value.cep || '').trim(),
      address: String(customerData.value.address || '').trim(),
      document: String(customerData.value.document || '').trim()
    }
    
    const response = await ordersApi.create(orderData)
    
    // Verificar se o frete foi salvo
    if (response.data && response.data.shipping) {
      console.log('✅ Frete salvo com sucesso:', response.data.shipping)
    } else if (shippingData) {
      console.warn('⚠️ Frete foi enviado mas não foi retornado na resposta')
      console.warn('Shipping data enviado:', shippingData)
      console.warn('Response data:', response.data)
    } else {
      console.log('ℹ️ Nenhum frete foi selecionado')
    }
    
    // Gerar mensagem WhatsApp usando os dados salvos (não da resposta da API)
    const WHATSAPP_NUMBER = '556282198202'
    let message = '*PEDIDO - PSIKÉ DELOUN ARTS*\n\n'
    
    cartStore.items.forEach((item, index) => {
      message += `*${index + 1}. ${item.name}*\n`
      message += `Qtd: ${item.quantity}x\n\n`
      
      if (item.image) {
        const imageUrl = item.image.startsWith('http') ? item.image : window.location.origin + item.image
        message += `*Imagem:*\n${imageUrl}\n\n`
      }
      
      if (item.area) {
        message += `Área: ${item.area.toFixed(2)}m²\n`
      }
      
      if (item.price) {
        message += `Preço unitário: ${formatPrice(item.price)}\n`
        message += `Preço total: ${formatPrice(item.price * item.quantity)}\n\n`
      }
      
      if (item.observations) {
        message += `*Observações:*\n${item.observations}\n\n`
      }
    })
    
    message += '━━━━━━━━━━━━━━━━━━━━\n\n'
    message += `*TOTAL: ${formatPrice(cartStore.totalPrice)}*\n\n`
    message += `Total de itens: ${cartStore.totalItems}\n\n`
    message += '━━━━━━━━━━━━━━━━━━━━\n\n'
    message += '*DADOS PARA FRETE:*\n\n'
    message += `*Nome Completo:*\n${clientDataForWhatsApp.name}\n\n`
    message += `*Telefone:*\n${clientDataForWhatsApp.phone}\n\n`
    message += `*CEP:*\n${clientDataForWhatsApp.cep}\n\n`
    message += `*Endereço Completo:*\n${clientDataForWhatsApp.address}\n\n`
    message += `*CPF ou CNPJ:*\n${clientDataForWhatsApp.document}\n\n`
    message += 'Aguardo retorno.'
    
    const whatsappUrl = `https://wa.me/${WHATSAPP_NUMBER}?text=${encodeURIComponent(message)}`
    window.open(whatsappUrl, '_blank')
    
    // Limpar carrinho e dados do cliente APÓS enviar com sucesso
    cartStore.clearCart()
    shippingStore.clearShipping()
    customerData.value = { name: '', cep: '', address: '', document: '', phone: '', number: '' }
    
  } catch (error) {
    console.error('Error creating order:', error)
    alert('Erro ao criar pedido. Tente novamente.')
  }
}

onMounted(() => {
  cartStore.loadCart()
})
</script>
