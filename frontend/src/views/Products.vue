<template>
  <div>
    <CartButton />
    <Header />
    
    <section class="catalogo" id="catalogo">
      <div class="container">
        <h2 class="section-title">Nossas Tendas</h2>
        <p class="section-subtitle">
          Soluções personalizadas para transformar qualquer espaço em uma experiência memorável
        </p>
        
        <div v-if="loading" class="loading">Carregando produtos...</div>
        <div v-else-if="error" class="error">{{ error }}</div>
        <div v-else id="catalogGrid">
          <div v-for="category in groupedProducts" :key="category.slug" class="catalog-section" :data-type="category.slug">
            <h3 class="catalog-section-title">{{ category.name }}</h3>
            <div class="catalog-section-grid">
              <ProductCard 
                v-for="product in category.products" 
                :key="product.id"
                :product="product"
              />
            </div>
          </div>
        </div>
      </div>
    </section>

    <Footer />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import Header from '../components/Header.vue'
import Footer from '../components/Footer.vue'
import CartButton from '../components/CartButton.vue'
import ProductCard from '../components/ProductCard.vue'
import { useProductsStore } from '../store/products'

const productsStore = useProductsStore()
const loading = computed(() => productsStore.loading)
const error = computed(() => productsStore.error)

const groupedProducts = computed(() => {
  const groups = {}
  
  productsStore.products.forEach(product => {
    const categorySlug = product.category?.slug || 'outros'
    const categoryName = product.category?.name || 'Outros'
    
    if (!groups[categorySlug]) {
      groups[categorySlug] = {
        slug: categorySlug,
        name: categoryName,
        products: []
      }
    }
    
    groups[categorySlug].products.push(product)
  })
  
  // Ordenar categorias
  const order = { 'quadradas': 1, 'retangular': 2, 'mandala': 3 }
  return Object.values(groups).sort((a, b) => {
    return (order[a.slug] || 999) - (order[b.slug] || 999)
  })
})

onMounted(async () => {
  await productsStore.fetchCategories()
  await productsStore.fetchProducts()
})
</script>
