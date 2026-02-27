import { defineStore } from 'pinia'
import { ref, watch } from 'vue'
import api from '../services/api'

export const useShippingStore = defineStore('shipping', () => {
  // Carregar selectedOption do localStorage se existir
  const loadSelectedOption = () => {
    try {
      const saved = localStorage.getItem('psikeShippingSelected')
      if (saved) {
        return JSON.parse(saved)
      }
    } catch (e) {
      console.error('Erro ao carregar frete selecionado:', e)
    }
    return null
  }

  const options = ref([])
  const selectedOption = ref(loadSelectedOption())
  const loading = ref(false)
  const error = ref(null)

  // Salvar selectedOption no localStorage sempre que mudar
  watch(selectedOption, (newValue) => {
    if (newValue) {
      try {
        localStorage.setItem('psikeShippingSelected', JSON.stringify(newValue))
      } catch (e) {
        console.error('Erro ao salvar frete selecionado:', e)
      }
    } else {
      localStorage.removeItem('psikeShippingSelected')
    }
  }, { deep: true })

  async function calculateShipping(items, destinationCep) {
    loading.value = true
    error.value = null
    
    try {
      const response = await api.post('/shipping/calculate', {
        destination_cep: destinationCep,
        items: items.map(item => ({
          width: item.width || null,
          height: item.height || null,
          quantity: item.quantity || 1,
        }))
      })
      
      if (response.data.success) {
        options.value = response.data.options
        
        // Se havia um frete selecionado salvo, tentar encontrar nas novas opções
        if (selectedOption.value && selectedOption.value.id) {
          const foundOption = response.data.options.find(opt => opt.id === selectedOption.value.id)
          if (foundOption) {
            // Atualizar o selectedOption com os dados atualizados
            selectedOption.value = foundOption
          } else {
            // Se não encontrou, limpar a seleção
            selectedOption.value = null
          }
        }
        
        return response.data.options
      } else {
        throw new Error(response.data.message || 'Erro ao calcular frete')
      }
    } catch (err) {
      error.value = err.response?.data?.message || err.message || 'Erro ao calcular frete'
      throw err
    } finally {
      loading.value = false
    }
  }

  function selectShippingOption(option) {
    selectedOption.value = option
    // O watch já salva automaticamente no localStorage
  }

  function clearShipping() {
    options.value = []
    selectedOption.value = null
    error.value = null
    localStorage.removeItem('psikeShippingSelected')
  }

  return {
    options,
    selectedOption,
    loading,
    error,
    calculateShipping,
    selectShippingOption,
    clearShipping
  }
})
