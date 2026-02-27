<template>
  <article class="product-card">
    <div class="product-image">
      <img :src="product.main_image || '/assets/images/product-placeholders/default.png'" 
           :alt="product.name" 
           loading="lazy">
      <div class="product-badge" v-if="product.can_calculate_price">Comprar / Uso Ilimitado</div>
      <div class="product-badge" v-else>Sob Consulta</div>
    </div>
    <div class="product-content">
      <h3 class="product-title">{{ productTitle }}</h3>
      <p class="product-description" v-if="product.description">{{ product.description }}</p>
      <ul class="product-specs">
        <li>Envio nacional</li>
        <li>Consultar frete</li>
      </ul>
      <div class="product-actions">
        <router-link :to="productLink" class="btn-secondary">Fazer orçamento</router-link>
      </div>
    </div>
  </article>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  product: {
    type: Object,
    required: true
  }
})

const productTitle = computed(() => {
  const area = props.product.area || (props.product.width * props.product.height)
  return `Tenda ${props.product.width}m × ${props.product.height}m (${area.toFixed(0)}m²)`
})

const productLink = computed(() => {
  return `/tenda/${props.product.id}?w=${props.product.width}&h=${props.product.height}`
})
</script>
