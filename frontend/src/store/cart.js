import { defineStore } from 'pinia'
import { ref, computed } from 'vue'

export const useCartStore = defineStore('cart', () => {
  const items = ref([])

  const totalItems = computed(() => {
    return items.value.reduce((total, item) => total + item.quantity, 0)
  })

  const totalPrice = computed(() => {
    return items.value.reduce((total, item) => {
      if (item.price && typeof item.price === 'number') {
        return total + (item.price * item.quantity)
      }
      return total
    }, 0)
  })

  function addItem(product) {
    const existingItem = items.value.find(item => 
      item.id === product.id && 
      item.width === product.width && 
      item.height === product.height
    )

    if (existingItem) {
      existingItem.quantity += 1
    } else {
      items.value.push({
        ...product,
        quantity: 1,
        observations: ''
      })
    }

    saveCart()
  }

  function removeItem(itemId) {
    items.value = items.value.filter(item => item.id !== itemId)
    saveCart()
  }

  function updateQuantity(itemId, quantity) {
    const item = items.value.find(item => item.id === itemId)
    if (item) {
      if (quantity <= 0) {
        removeItem(itemId)
      } else {
        item.quantity = quantity
        saveCart()
      }
    }
  }

  function updateObservations(itemId, observations) {
    const item = items.value.find(item => item.id === itemId)
    if (item) {
      item.observations = observations
      saveCart()
    }
  }

  function clearCart() {
    items.value = []
    saveCart()
  }

  function saveCart() {
    localStorage.setItem('psikeCart', JSON.stringify(items.value))
  }

  function loadCart() {
    const saved = localStorage.getItem('psikeCart')
    if (saved) {
      try {
        items.value = JSON.parse(saved)
      } catch (e) {
        console.error('Error loading cart:', e)
        items.value = []
      }
    }
  }

  return {
    items,
    totalItems,
    totalPrice,
    addItem,
    removeItem,
    updateQuantity,
    updateObservations,
    clearCart,
    loadCart
  }
})
