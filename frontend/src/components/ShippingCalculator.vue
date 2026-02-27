<template>
  <div class="shipping-calculator">
    <h4 class="shipping-calculator-title">Calcular Frete</h4>
    
    <div class="shipping-calculator-form">
      <div class="shipping-cep-input">
        <label for="shipping-cep" class="shipping-label">
          CEP de Destino
        </label>
        <div class="shipping-cep-wrapper">
          <input
            id="shipping-cep"
            v-model="cep"
            type="text"
            class="shipping-input"
            placeholder="00000-000"
            maxlength="9"
            @input="formatCEP"
            :disabled="shippingStore.loading"
          />
          <button
            class="shipping-calculate-btn"
            @click="handleCalculate"
            :disabled="!isCepValid || shippingStore.loading"
          >
            {{ shippingStore.loading ? 'Calculando...' : 'Calcular' }}
          </button>
        </div>
        <p v-if="shippingStore.error" class="shipping-error">
          {{ shippingStore.error }}
        </p>
      </div>
    </div>

    <div v-if="shippingStore.options.length > 0" class="shipping-options">
      <h5 class="shipping-options-title">Opções de Frete:</h5>
      <div class="shipping-options-list">
        <div
          v-for="option in shippingStore.options"
          :key="option.id"
          class="shipping-option"
          :class="{ 'shipping-option-selected': shippingStore.selectedOption?.id === option.id }"
          @click="selectOption(option)"
        >
          <div class="shipping-option-header">
            <input
              type="radio"
              :id="`shipping-${option.id}`"
              :value="option.id"
              :checked="shippingStore.selectedOption?.id === option.id"
              @change="selectOption(option)"
              class="shipping-option-radio"
            />
            <label :for="`shipping-${option.id}`" class="shipping-option-label">
              <strong>{{ option.name }}</strong>
              <span class="shipping-option-company">{{ option.company }}</span>
            </label>
          </div>
          <div class="shipping-option-details">
            <div class="shipping-option-price">
              {{ formatPrice(option.price) }}
            </div>
            <div class="shipping-option-delivery">
              <span class="shipping-option-time">{{ option.delivery_time }} dias úteis</span>
              <span class="shipping-option-range">{{ option.delivery_range }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useShippingStore } from '../store/shipping'
import { useCartStore } from '../store/cart'

const shippingStore = useShippingStore()
const cartStore = useCartStore()

const cep = ref('')

// Carregar CEP do cliente se já tiver sido preenchido
onMounted(() => {
  // Se já houver um frete selecionado, tentar manter
  if (shippingStore.selectedOption && shippingStore.options.length === 0) {
    // Se tem frete selecionado mas não tem opções, limpar a seleção
    // (as opções precisam ser recalculadas)
    shippingStore.selectedOption = null
  }
})

// Observar mudanças no selectedOption para manter sincronizado
watch(() => shippingStore.selectedOption, (newOption) => {
  if (newOption && shippingStore.options.length > 0) {
    // Verificar se a opção selecionada ainda existe nas opções disponíveis
    const exists = shippingStore.options.some(opt => opt.id === newOption.id)
    if (!exists) {
      // Se não existe mais, limpar seleção
      shippingStore.selectedOption = null
    }
  }
})

const isCepValid = computed(() => {
  const cepDigits = cep.value.replace(/\D/g, '')
  return cepDigits.length === 8
})

function formatCEP(event) {
  let value = event.target.value.replace(/\D/g, '')
  if (value.length > 8) {
    value = value.substring(0, 8)
  }
  if (value.length > 5) {
    value = value.substring(0, 5) + '-' + value.substring(5)
  }
  cep.value = value
}

async function handleCalculate() {
  if (!isCepValid.value || cartStore.items.length === 0) {
    return
  }
  
  try {
    await shippingStore.calculateShipping(cartStore.items, cep.value)
  } catch (err) {
    console.error('Erro ao calcular frete:', err)
  }
}

function selectOption(option) {
  shippingStore.selectShippingOption(option)
}

function formatPrice(price) {
  return new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL',
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(price)
}
</script>

<style scoped>
.shipping-calculator {
  background: #1e1e1e;
  border-radius: 8px;
  padding: 1.5rem;
  margin-bottom: 1.5rem;
}

.shipping-calculator-title {
  font-size: 1.25rem;
  font-weight: 600;
  margin-bottom: 1rem;
  color: var(--color-text-primary);
}

.shipping-calculator-form {
  margin-bottom: 1rem;
}

.shipping-label {
  display: block;
  margin-bottom: 0.5rem;
  color: var(--color-text-secondary);
  font-size: 0.9rem;
}

.shipping-cep-wrapper {
  display: flex;
  gap: 0.75rem;
}

.shipping-input {
  flex: 1;
  padding: 0.75rem;
  background: #2a2a2a;
  border: 1px solid #333;
  border-radius: 4px;
  color: var(--color-text-primary);
  font-size: 1rem;
  transition: border-color 0.2s;
}

.shipping-input:focus {
  outline: none;
  border-color: var(--color-accent-primary);
}

.shipping-input:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.shipping-calculate-btn {
  padding: 0.75rem 1.5rem;
  background: var(--color-accent-primary);
  color: #000;
  border: none;
  border-radius: 4px;
  font-weight: 600;
  cursor: pointer;
  transition: opacity 0.2s;
  white-space: nowrap;
}

.shipping-calculate-btn:hover:not(:disabled) {
  opacity: 0.9;
}

.shipping-calculate-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.shipping-error {
  margin-top: 0.5rem;
  color: #e74c3c;
  font-size: 0.875rem;
}

.shipping-options {
  margin-top: 1.5rem;
}

.shipping-options-title {
  font-size: 1rem;
  font-weight: 600;
  margin-bottom: 1rem;
  color: var(--color-text-primary);
}

.shipping-options-list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.shipping-option {
  background: #2a2a2a;
  border: 2px solid #333;
  border-radius: 6px;
  padding: 1rem;
  cursor: pointer;
  transition: all 0.2s;
}

.shipping-option:hover {
  border-color: #444;
  background: #2f2f2f;
}

.shipping-option-selected {
  border-color: var(--color-accent-primary);
  background: rgba(127, 255, 0, 0.05);
}

.shipping-option-header {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin-bottom: 0.75rem;
}

.shipping-option-radio {
  width: 18px;
  height: 18px;
  cursor: pointer;
}

.shipping-option-label {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
  cursor: pointer;
}

.shipping-option-label strong {
  color: var(--color-text-primary);
  font-size: 1rem;
}

.shipping-option-company {
  color: var(--color-text-secondary);
  font-size: 0.875rem;
}

.shipping-option-details {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 0.5rem;
}

.shipping-option-price {
  font-size: 1.25rem;
  font-weight: 700;
  color: var(--color-accent-primary);
}

.shipping-option-delivery {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 0.25rem;
}

.shipping-option-time {
  color: var(--color-text-primary);
  font-size: 0.9rem;
  font-weight: 500;
}

.shipping-option-range {
  color: var(--color-text-secondary);
  font-size: 0.8rem;
}

@media (max-width: 768px) {
  .shipping-cep-wrapper {
    flex-direction: column;
  }
  
  .shipping-calculate-btn {
    width: 100%;
  }
  
  .shipping-option-details {
    flex-direction: column;
    align-items: flex-start;
    gap: 0.5rem;
  }
  
  .shipping-option-delivery {
    align-items: flex-start;
  }
}
</style>
