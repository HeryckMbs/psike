import { defineStore } from 'pinia'
import { ref } from 'vue'
import { productsApi, categoriesApi } from '../services/api'

export const useProductsStore = defineStore('products', () => {
  const products = ref([])
  const categories = ref([])
  const loading = ref(false)
  const error = ref(null)

  async function fetchProducts(params = {}) {
    loading.value = true
    error.value = null
    try {
      const response = await productsApi.getAll(params)
      products.value = response.data.data || response.data
    } catch (e) {
      error.value = e.message
      console.error('Error fetching products:', e)
    } finally {
      loading.value = false
    }
  }

  async function fetchProduct(id) {
    loading.value = true
    error.value = null
    try {
      const response = await productsApi.getById(id)
      return response.data.data || response.data
    } catch (e) {
      error.value = e.message
      console.error('Error fetching product:', e)
      return null
    } finally {
      loading.value = false
    }
  }

  async function fetchCategories() {
    try {
      const response = await categoriesApi.getAll()
      categories.value = response.data.data || response.data
    } catch (e) {
      console.error('Error fetching categories:', e)
    }
  }

  return {
    products,
    categories,
    loading,
    error,
    fetchProducts,
    fetchProduct,
    fetchCategories
  }
})
