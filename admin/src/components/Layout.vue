<template>
  <v-app theme="dark">
    <v-navigation-drawer v-model="drawer" temporary>
      <v-list>
        <v-list-item prepend-icon="mdi-view-dashboard" title="Dashboard" :to="{ name: 'Dashboard' }"></v-list-item>
        <v-list-item prepend-icon="mdi-trello" title="Kanban de Vendas" :to="{ name: 'OrdersKanban' }"></v-list-item>
        <v-list-item prepend-icon="mdi-package-variant" title="Produtos" :to="{ name: 'ProductsList' }"></v-list-item>
        <v-list-item prepend-icon="mdi-tag" title="Categorias" :to="{ name: 'CategoriesList' }"></v-list-item>
        <v-list-item prepend-icon="mdi-package" title="Caixas Padrões" :to="{ name: 'BoxesList' }"></v-list-item>
        <v-list-item prepend-icon="mdi-cash-multiple" title="Tipos de Custos" :to="{ name: 'CostTypes' }"></v-list-item>
        <v-list-item prepend-icon="mdi-post" title="Blog" :to="{ name: 'BlogList' }"></v-list-item>
      </v-list>
      
      <template v-slot:append>
        <v-list>
          <v-list-item 
            prepend-icon="mdi-account-circle" 
            :title="userName"
            subtitle="Usuário logado"
          ></v-list-item>
          <v-list-item prepend-icon="mdi-logout" title="Sair" @click="handleLogout"></v-list-item>
        </v-list>
      </template>
    </v-navigation-drawer>

    <v-app-bar>
      <v-app-bar-nav-icon @click="drawer = !drawer"></v-app-bar-nav-icon>
      <v-toolbar-title>Psiké Deloun Arts - Admin</v-toolbar-title>
      <v-spacer></v-spacer>
      <v-btn
        variant="outlined"
        color="primary"
        prepend-icon="mdi-home"
        @click="goToSite"
        class="mr-2"
      >
        Ver Site
      </v-btn>
      <v-chip v-if="authStore.user" prepend-icon="mdi-account-circle" variant="text">
        {{ authStore.user.name || authStore.user.email }}
      </v-chip>
      <v-btn icon="mdi-account-circle" @click="drawer = !drawer"></v-btn>
    </v-app-bar>

    <v-main>
      <v-container fluid>
        <router-view />
      </v-container>
    </v-main>
  </v-app>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../store/auth'

const drawer = ref(false)
const router = useRouter()
const authStore = useAuthStore()

const userName = computed(() => {
  if (authStore.user) {
    return authStore.user.name || authStore.user.email || 'Usuário'
  }
  return 'Carregando...'
})

function handleLogout() {
  authStore.logout()
  router.push('/login')
}

function goToSite() {
  window.location.href = '/'
}

onMounted(async () => {
  if (authStore.isAuthenticated && !authStore.user) {
    await authStore.fetchUser()
  }
})
</script>
