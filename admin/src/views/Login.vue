<template>
  <v-app-bar color="surface" prominent>
    <v-app-bar-title>
      Psiké Deloun Arts
    </v-app-bar-title>
    <v-spacer></v-spacer>
    <v-btn
      variant="outlined"
      color="primary"
      prepend-icon="mdi-home"
      @click="goToHome"
    >
      Voltar ao Site
    </v-btn>
  </v-app-bar>

  <v-main>
    <v-container class="fill-height" fluid>
      <v-row align="center" justify="center">
        <v-col cols="12" sm="8" md="4">
          <v-card>
            <v-card-title class="text-h5 text-center pa-6">
              Área Administrativa
            </v-card-title>
            <v-card-subtitle class="text-center">
              Faça login para continuar
            </v-card-subtitle>
            
            <v-card-text>
              <v-form @submit.prevent="handleLogin">
                <v-text-field
                  v-model="email"
                  label="E-mail"
                  type="email"
                  prepend-inner-icon="mdi-email"
                  variant="outlined"
                  required
                ></v-text-field>
                
                <v-text-field
                  v-model="password"
                  label="Senha"
                  type="password"
                  prepend-inner-icon="mdi-lock"
                  variant="outlined"
                  required
                ></v-text-field>
                
                <v-alert
                  v-if="error"
                  type="error"
                  class="mt-4"
                  closable
                  @click:close="error = null"
                >
                  {{ error }}
                </v-alert>
                
                <v-btn
                  type="submit"
                  color="primary"
                  block
                  size="large"
                  class="mt-4"
                  :loading="loading"
                >
                  Entrar
                </v-btn>
              </v-form>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
    </v-container>
  </v-main>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../store/auth'

const router = useRouter()
const authStore = useAuthStore()

const email = ref('')
const password = ref('')
const loading = ref(false)
const error = ref(null)

function goToHome() {
  window.location.href = '/'
}

async function handleLogin() {
  loading.value = true
  error.value = null
  
  try {
    await authStore.login(email.value, password.value)
    router.push('/')
  } catch (e) {
    error.value = e.message || 'Credenciais inválidas. Tente novamente.'
  } finally {
    loading.value = false
  }
}
</script>
