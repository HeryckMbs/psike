<template>
  <div>
    <CartButton />
    <Header />
    
    <section class="product-page" v-if="product">
      <div class="container">
        <div class="product-page-content">
          <div class="product-header-top">
            <div class="product-breadcrumb">
              <router-link to="/">Início</router-link> / 
              <router-link to="/produtos">Produtos</router-link> / 
              <span>Tendas Tensionadas</span>
            </div>
            <h1 class="product-page-title">{{ productTitle }}</h1>
          </div>

          <div class="product-row-1">
            <div class="product-gallery">
              <div class="product-main-image" :class="imageOrientation">
                <img :src="currentImage" :alt="product.name" id="mainProductImage">
                <button v-if="hasMultipleImages" 
                        class="carousel-nav carousel-prev" 
                        @click="previousImage" 
                        aria-label="Imagem anterior">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="15 18 9 12 15 6"></polyline>
                  </svg>
                </button>
                <button v-if="hasMultipleImages" 
                        class="carousel-nav carousel-next" 
                        @click="nextImage" 
                        aria-label="Próxima imagem">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="9 18 15 12 9 6"></polyline>
                  </svg>
                </button>
                <div v-if="hasMultipleImages" class="carousel-indicators">
                  <button 
                    v-for="(img, index) in allImages" 
                    :key="index"
                    class="carousel-indicator" 
                    :class="{ active: currentImageIndex === index }"
                    @click="goToImage(index)"
                    :aria-label="`Ir para imagem ${index + 1}`">
                  </button>
                </div>
              </div>
            </div>

            <div class="product-dimensions-large" data-product-type="tent">
              <div class="dimensions-header">
                <h3 class="dimensions-title-large">Informe as Dimensões</h3>
                <p class="dimensions-subtitle-large">
                  {{ product.can_calculate_price ? 'R$ 22,00 por m²' : 'Preço fixo conforme modelo' }}
                </p>
              </div>
              
              <p class="product-page-description">
                Tendas personalizadas sob medida, ideais para eventos e instalações fixas.
              </p>
              
              <div class="dimensions-inputs-large">
                <div class="dimension-input-group-large">
                  <label for="tent-height-large" class="dimension-label-large">Comprimento (m)</label>
                  <div class="dimension-input-wrapper">
                    <input 
                      type="number" 
                      id="tent-height-large" 
                      class="dimension-input-large" 
                      v-model.number="height"
                      :disabled="!product.can_calculate_price"
                      min="0" 
                      step="0.1" 
                      placeholder="Ex: 5.0"
                      @input="calculatePrice">
                    <button v-if="product.can_calculate_price" 
                            type="button" 
                            class="dimension-input-btn dimension-input-btn-up" 
                            @click="incrementDimension('height')">↑</button>
                    <button v-if="product.can_calculate_price" 
                            type="button" 
                            class="dimension-input-btn dimension-input-btn-down" 
                            @click="decrementDimension('height')">↓</button>
                  </div>
                </div>
                <div class="dimension-input-group-large">
                  <label for="tent-width-large" class="dimension-label-large">Largura (m)</label>
                  <div class="dimension-input-wrapper">
                    <input 
                      type="number" 
                      id="tent-width-large" 
                      class="dimension-input-large" 
                      v-model.number="width"
                      :disabled="!product.can_calculate_price"
                      min="0" 
                      step="0.1" 
                      placeholder="Ex: 3.0"
                      @input="calculatePrice">
                    <button v-if="product.can_calculate_price" 
                            type="button" 
                            class="dimension-input-btn dimension-input-btn-up" 
                            @click="incrementDimension('width')">↑</button>
                    <button v-if="product.can_calculate_price" 
                            type="button" 
                            class="dimension-input-btn dimension-input-btn-down" 
                            @click="decrementDimension('width')">↓</button>
                  </div>
                </div>
                <p v-if="!product.can_calculate_price" class="mandala-info-message">
                  💡 Se você deseja medidas diferentes deste modelo, informe nas observações do carrinho.
                </p>
              </div>
              
              <div class="price-display-large" id="tent-price-display-large">
                <span class="price-label-large">Preço Total:</span>
                <span class="price-value-large" id="tent-price-value-large">{{ formattedPrice }}</span>
              </div>
              
              <div class="product-actions-large">
                <button 
                  class="btn-primary-large" 
                  :disabled="!canAddToCart"
                  @click="addToCart">
                  Adicionar ao Carrinho
                </button>
                <router-link to="/produtos" class="product-link-secondary">Ver Outros Produtos</router-link>
              </div>
            </div>
          </div>

          <div class="product-info-full">
            <div class="product-specs-grid">
              <div class="spec-card">
                <h3 class="shipping-title">ESPECIFICAÇÕES</h3>
                <ul class="shipping-list">
                  <li>Envio para todo o Brasil</li>
                  <li>Suplex - gramatura 280</li>
                  <li>Dimensões e cores personalizadas</li>
                  <li>Materiais de alta durabilidade</li>
                  <li>Acabamento profissional</li>
                </ul>
              </div>
              
              <div class="spec-card">
                <h3 class="shipping-title">FORMAS DE PAGAMENTO</h3>
                <ul class="shipping-list">
                  <li>PIX</li>
                  <li>Cartão de crédito</li>
                  <li>Cartão de débito</li>
                </ul>
              </div>
              
              <div class="spec-card">
                <h3 class="shipping-title">PRAZO</h3>
                <ul class="shipping-list">
                  <li>Produção: 8 a 10 dias após confirmação</li>
                  <li>Prazo de entrega: conforme localidade e modalidade de frete escolhida</li>
                  <li>Consulte prazo específico para seu projeto</li>
                </ul>
              </div>
              
              <div class="spec-card">
                <h3 class="shipping-title">FRETES E CONDIÇÕES</h3>
                <ul class="shipping-list">
                  <li>Frete não incluso</li>
                  <li>Valor do frete varia com o peso e modelo</li>
                  <li>Consulte valores e condições</li>
                  <li>Valores das tendas sujeitos a alteração</li>
                  <li><strong>Obs:</strong> O valor do produto é cobrado separado do valor do frete.</li>
                </ul>
              </div>
              
              <div class="spec-card">
                <h4 class="shipping-not-included-title">INCLUSO</h4>
                <ul class="shipping-list">
                  <li>Tenda finalizada pronta para instalação</li>
                  <li>Manual de montagem</li>
                  <li>Suporte a distância para montagem</li>
                </ul>
              </div>
              
              <div class="spec-card">
                <h4 class="shipping-not-included-title">NÃO INCLUSO</h4>
                <ul class="shipping-list">
                  <li>Cordas</li>
                  <li>Frete</li>
                  <li>Estruturas de montagem (postes de sustentação)</li>
                  <li>Montagem presencial (quando não contratada)</li>
                </ul>
              </div>
              
              <div class="spec-card spec-card-full">
                <h3 class="shipping-title">OBSERVAÇÕES</h3>
                <ul class="shipping-list">
                  <li>Imagens ilustram o produto real</li>
                  <li>Projetos fora do catálogo: orçamento sob consulta</li>
                  <li>Confirmação com antecedência é essencial (produção sujeita à fila)</li>
                </ul>
              </div>
            </div>
          </div>

          <!-- Produtos Sugeridos -->
          <div class="related-products-section" v-if="suggestedProducts.length > 0">
            <h2 class="related-products-title">Você também pode gostar</h2>
            <div class="related-products-grid">
              <article 
                v-for="suggestedProduct in suggestedProducts" 
                :key="suggestedProduct.id"
                class="related-product-card">
                <router-link :to="`/tenda/${suggestedProduct.id}?w=${suggestedProduct.width}&h=${suggestedProduct.height}`">
                  <div class="related-product-image">
                    <img 
                      :src="getProductImage(suggestedProduct)" 
                      :alt="`Tenda ${suggestedProduct.width}m x ${suggestedProduct.height}m`" 
                      loading="lazy">
                  </div>
                  <div class="related-product-content">
                    <h3 class="related-product-title">
                      Tenda {{ suggestedProduct.width }}m × {{ suggestedProduct.height }}m 
                      ({{ getProductArea(suggestedProduct) }}m²)
                    </h3>
                  </div>
                </router-link>
              </article>
            </div>
          </div>
        </div>
      </div>
    </section>

    <Footer />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import Header from '../components/Header.vue'
import Footer from '../components/Footer.vue'
import CartButton from '../components/CartButton.vue'
import { useProductsStore } from '../store/products'
import { useCartStore } from '../store/cart'

const route = useRoute()
const router = useRouter()
const productsStore = useProductsStore()
const cartStore = useCartStore()

const product = ref(null)
const width = ref(0)
const height = ref(0)
const currentImageIndex = ref(0)
const imageOrientation = ref('')
const suggestedProducts = ref([])

const allImages = computed(() => {
  if (!product.value) return []
  
  // Se o produto tem imagens cadastradas, usar elas
  if (product.value.images && Array.isArray(product.value.images) && product.value.images.length > 0) {
    return product.value.images.map(img => ({
      image: img.path || img.image || '',
      isMain: img.is_main || false,
      id: img.id
    }))
  }
  
  // Fallback para main_image se não houver imagens no array
  if (product.value.main_image) {
    return [{ image: product.value.main_image, isMain: true }]
  }
  
  // Fallback para variations se existirem
  if (product.value.variations && product.value.variations.length > 0) {
    return product.value.variations.map(v => ({
      image: v.path || v.image || '',
      variation: v.variation
    }))
  }
  
  return []
})

const currentImage = computed(() => {
  const image = allImages.value[currentImageIndex.value]
  if (!image || !image.image) return ''
  
  // Se a imagem já começa com http ou /storage, retornar como está
  if (image.image.startsWith('http') || image.image.startsWith('/storage')) {
    return image.image
  }
  
  // Caso contrário, adicionar /storage se necessário
  return image.image.startsWith('/') ? image.image : `/storage/${image.image}`
})

const hasMultipleImages = computed(() => allImages.value.length > 1)

const productTitle = computed(() => {
  if (!product.value) return ''
  const area = product.value.area || (width.value * height.value)
  if (width.value > 0 && height.value > 0) {
    return `Tenda ${width.value}m × ${height.value}m (${area.toFixed(0)}m²)`
  }
  return `Tenda ${product.value.width}m × ${product.value.height}m (${area.toFixed(0)}m²)`
})

const price = computed(() => {
  if (!product.value) return 0
  
  if (!product.value.can_calculate_price && product.value.fixed_price) {
    return product.value.fixed_price
  }
  
  if (product.value.can_calculate_price && width.value > 0 && height.value > 0) {
    const area = width.value * height.value
    return area * (product.value.price_per_square_meter || 22.00)
  }
  
  return 0
})

const formattedPrice = computed(() => {
  if (price.value === 0) return 'R$ 0,00'
  if (!product.value?.can_calculate_price && !product.value?.fixed_price) {
    return 'Sob consulta com o vendedor'
  }
  return new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL',
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(price.value)
})

const canAddToCart = computed(() => {
  if (!product.value) return false
  if (product.value.can_calculate_price) {
    return width.value > 0 && height.value > 0
  }
  return true
})

function calculatePrice() {
  // Price is computed, no need for explicit calculation
}

function incrementDimension(type) {
  if (type === 'width') {
    width.value = Math.max(0, width.value + 0.1)
  } else {
    height.value = Math.max(0, height.value + 0.1)
  }
  calculatePrice()
}

function decrementDimension(type) {
  if (type === 'width') {
    width.value = Math.max(0, width.value - 0.1)
  } else {
    height.value = Math.max(0, height.value - 0.1)
  }
  calculatePrice()
}

function previousImage() {
  if (hasMultipleImages.value) {
    currentImageIndex.value = (currentImageIndex.value - 1 + allImages.value.length) % allImages.value.length
  }
}

function nextImage() {
  if (hasMultipleImages.value) {
    currentImageIndex.value = (currentImageIndex.value + 1) % allImages.value.length
  }
}

function goToImage(index) {
  if (index >= 0 && index < allImages.value.length) {
    currentImageIndex.value = index
  }
}

function addToCart() {
  if (!product.value || !canAddToCart.value) return
  
  const finalWidth = width.value || product.value.width
  const finalHeight = height.value || product.value.height
  const finalArea = product.value.area || (finalWidth * finalHeight)
  const finalPrice = price.value
  
  cartStore.addItem({
    id: `tenda-${product.value.id}-${Date.now()}`,
    product_id: product.value.id,
    name: `Tenda ${finalWidth}m × ${finalHeight}m (${finalArea.toFixed(0)}m²)`,
    image: currentImage.value,
    width: finalWidth,
    height: finalHeight,
    area: finalArea,
    price: finalPrice,
    can_calculate_price: product.value.can_calculate_price,
    type: product.value.type,
    tenda_code: product.value.tenda_code,
    specs: 'Envio nacional, consultar frete.'
  })
  
  router.push('/carrinho')
}

function getProductImage(product) {
  if (!product) return ''
  
  // Tentar usar a imagem principal
  if (product.main_image) {
    return product.main_image.startsWith('http') || product.main_image.startsWith('/storage') 
      ? product.main_image 
      : `/storage/${product.main_image}`
  }
  
  // Tentar usar a primeira imagem do array
  if (product.images && Array.isArray(product.images) && product.images.length > 0) {
    const firstImage = product.images[0]
    const imagePath = firstImage.path || firstImage.image || firstImage || ''
    if (imagePath && typeof imagePath === 'string') {
      return imagePath.startsWith('http') || imagePath.startsWith('/storage') 
        ? imagePath 
        : `/storage/${imagePath}`
    }
  }
  
  return ''
}

function getProductArea(product) {
  if (!product) return 0
  const area = product.area || (product.width * product.height)
  return area.toFixed(0)
}

async function fetchSuggestedProducts(productId) {
  try {
    const response = await fetch(`/api/products/${productId}/related`)
    if (response.ok) {
      const data = await response.json()
      // A API pode retornar data.data (se for Resource collection) ou data diretamente
      suggestedProducts.value = data.data || data || []
    }
  } catch (error) {
    console.error('Error fetching suggested products:', error)
    suggestedProducts.value = []
  }
}

onMounted(async () => {
  const productId = route.params.id
  const urlWidth = route.query.w
  const urlHeight = route.query.h
  
  if (productId) {
    const fetchedProduct = await productsStore.fetchProduct(productId)
    if (fetchedProduct) {
      product.value = fetchedProduct
      width.value = urlWidth ? parseFloat(urlWidth) : (fetchedProduct.width || 0)
      height.value = urlHeight ? parseFloat(urlHeight) : (fetchedProduct.height || 0)
      
      // Resetar índice da imagem quando o produto mudar
      currentImageIndex.value = 0
      
      // Buscar produtos sugeridos
      await fetchSuggestedProducts(productId)
    }
  }
})

// Detectar orientação da imagem quando ela mudar
watch([currentImage, product], () => {
  if (currentImage.value && product.value) {
    // Usar nextTick para garantir que a imagem foi renderizada
    setTimeout(() => {
      const img = document.getElementById('mainProductImage')
      if (img) {
        // Se a imagem já está carregada
        if (img.complete && img.naturalWidth > 0) {
          const ratio = img.naturalWidth / img.naturalHeight
          if (ratio > 1.1) {
            imageOrientation.value = 'image-landscape'
          } else if (ratio < 0.9) {
            imageOrientation.value = 'image-portrait'
          } else {
            imageOrientation.value = 'image-landscape'
          }
        } else {
          // Aguardar carregamento
          img.onload = () => {
            const ratio = img.naturalWidth / img.naturalHeight
            if (ratio > 1.1) {
              imageOrientation.value = 'image-landscape'
            } else if (ratio < 0.9) {
              imageOrientation.value = 'image-portrait'
            } else {
              imageOrientation.value = 'image-landscape'
            }
          }
        }
      }
    }, 100)
  }
}, { immediate: true })
</script>
