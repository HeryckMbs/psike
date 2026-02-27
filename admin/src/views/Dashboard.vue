<template>
  <div>
    <h1 class="text-h4 mb-6">Dashboard</h1>
    
    <v-row>
      <v-col cols="12" md="3">
        <v-card>
          <v-card-title>Total de Pedidos</v-card-title>
          <v-card-text>
            <div class="text-h3">{{ stats.totalOrders }}</div>
          </v-card-text>
        </v-card>
      </v-col>
      
      <v-col cols="12" md="3">
        <v-card>
          <v-card-title>Pedidos Pendentes</v-card-title>
          <v-card-text>
            <div class="text-h3">{{ stats.pendingOrders }}</div>
          </v-card-text>
        </v-card>
      </v-col>
      
      <v-col cols="12" md="3">
        <v-card>
          <v-card-title>Total de Produtos</v-card-title>
          <v-card-text>
            <div class="text-h3">{{ stats.totalProducts }}</div>
          </v-card-text>
        </v-card>
      </v-col>
      
      <v-col cols="12" md="3">
        <v-card>
          <v-card-title>Receita Total</v-card-title>
          <v-card-text>
            <div class="text-h3">{{ formatCurrency(stats.totalRevenue) }}</div>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>
    
    <v-row class="mt-4">
      <v-col cols="12">
        <v-card>
          <v-card-title>Pedidos Recentes</v-card-title>
          <v-card-text>
            <v-data-table
              :headers="headers"
              :items="recentOrders"
              :loading="loading"
            >
              <template v-slot:item.total_amount="{ item }">
                {{ formatCurrency(item.total_amount) }}
              </template>
              <template v-slot:item.created_at="{ item }">
                {{ formatDate(item.created_at) }}
              </template>
            </v-data-table>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '../services/api'

const stats = ref({
  totalOrders: 0,
  pendingOrders: 0,
  totalProducts: 0,
  totalRevenue: 0
})

const recentOrders = ref([])
const loading = ref(false)

const headers = [
  { title: 'Número', key: 'order_number' },
  { title: 'Cliente', key: 'customer.name' },
  { title: 'Status', key: 'status.name' },
  { title: 'Total', key: 'total_amount' },
  { title: 'Data', key: 'created_at' }
]

function formatCurrency(value) {
  return new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL'
  }).format(value)
}

function formatDate(date) {
  return new Date(date).toLocaleDateString('pt-BR')
}

async function fetchStats() {
  loading.value = true
  try {
    const [ordersRes, productsRes] = await Promise.all([
      api.get('/admin/orders').catch(() => ({ data: { data: [] } })),
      api.get('/admin/products').catch(() => ({ data: { data: [] } }))
    ])
    
    const orders = ordersRes.data.data || ordersRes.data || []
    stats.value.totalOrders = orders.length || 0
    stats.value.pendingOrders = orders.filter(o => o.status?.slug === 'pending').length || 0
    stats.value.totalRevenue = orders.reduce((sum, o) => sum + parseFloat(o.total_amount || 0), 0)
    
    const products = productsRes.data.data || productsRes.data || []
    stats.value.totalProducts = products.length || 0
    
    recentOrders.value = orders.slice(0, 10).map(order => ({
      ...order,
      customer: order.customer || {}
    }))
  } catch (error) {
    console.error('Error fetching stats:', error)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchStats()
})
</script>
