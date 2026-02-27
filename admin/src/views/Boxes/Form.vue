<template>
  <div>
    <div class="d-flex justify-space-between align-center mb-6">
      <h1 class="text-h4">{{ isEdit ? 'Editar Caixa' : 'Nova Caixa' }}</h1>
      <v-btn variant="text" @click="goBack">Voltar</v-btn>
    </div>

    <v-card>
      <v-card-text>
        <v-form ref="formRef" v-model="valid">
          <v-row>
            <v-col cols="12" md="6">
              <v-text-field
                v-model="form.name"
                label="Nome *"
                :rules="[rules.required]"
                variant="outlined"
                hint="Ex: Caixa Pequena, Caixa Média, Caixa Grande"
              ></v-text-field>
            </v-col>

            <v-col cols="12" md="6">
              <v-switch
                v-model="form.active"
                label="Ativa"
                color="success"
              ></v-switch>
            </v-col>

            <v-col cols="12">
              <v-divider class="my-4"></v-divider>
              <h3 class="text-subtitle-1 mb-4">Dimensões (em centímetros)</h3>
            </v-col>

            <v-col cols="12" md="4">
              <v-text-field
                v-model.number="form.width"
                label="Largura (cm) *"
                type="number"
                step="0.01"
                min="11"
                :rules="[rules.required, rules.minDimension]"
                variant="outlined"
                hint="Mínimo: 11cm (requisito Melhor Envio)"
              ></v-text-field>
            </v-col>

            <v-col cols="12" md="4">
              <v-text-field
                v-model.number="form.height"
                label="Altura (cm) *"
                type="number"
                step="0.01"
                min="11"
                :rules="[rules.required, rules.minDimension]"
                variant="outlined"
                hint="Mínimo: 11cm (requisito Melhor Envio)"
              ></v-text-field>
            </v-col>

            <v-col cols="12" md="4">
              <v-text-field
                v-model.number="form.length"
                label="Comprimento (cm) *"
                type="number"
                step="0.01"
                min="11"
                :rules="[rules.required, rules.minDimension]"
                variant="outlined"
                hint="Mínimo: 11cm (requisito Melhor Envio)"
              ></v-text-field>
            </v-col>

            <v-col cols="12">
              <v-divider class="my-4"></v-divider>
              <h3 class="text-subtitle-1 mb-4">Peso</h3>
            </v-col>

            <v-col cols="12" md="6">
              <v-text-field
                v-model.number="form.weight"
                label="Peso (kg) *"
                type="number"
                step="0.01"
                min="0.1"
                :rules="[rules.required, rules.minWeight]"
                variant="outlined"
                hint="Mínimo: 0.1kg"
              ></v-text-field>
            </v-col>

            <v-col cols="12" md="6">
              <v-alert
                type="info"
                variant="tonal"
                class="mt-4"
              >
                <strong>Volume:</strong> {{ calculatedVolume }} m³
              </v-alert>
            </v-col>
          </v-row>

          <v-row class="mt-4">
            <v-col cols="12">
              <v-btn
                color="primary"
                @click="saveBox"
                :loading="loading"
                :disabled="!valid"
              >
                {{ isEdit ? 'Atualizar' : 'Criar' }}
              </v-btn>
              <v-btn
                variant="text"
                @click="goBack"
                class="ml-2"
              >
                Cancelar
              </v-btn>
            </v-col>
          </v-row>
        </v-form>
      </v-card-text>
    </v-card>

    <v-snackbar v-model="snackbar" :color="snackbarColor" timeout="3000">
      {{ snackbarText }}
    </v-snackbar>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import api from '../../services/api'

const router = useRouter()
const route = useRoute()

const formRef = ref(null)
const form = ref({
  name: '',
  width: null,
  height: null,
  length: null,
  weight: null,
  active: true
})

const loading = ref(false)
const valid = ref(false)
const snackbar = ref(false)
const snackbarText = ref('')
const snackbarColor = ref('success')

const isEdit = computed(() => !!route.params.id)

const calculatedVolume = computed(() => {
  const width = form.value.width || 0
  const height = form.value.height || 0
  const length = form.value.length || 0
  
  if (width === 0 || height === 0 || length === 0) {
    return '0.000'
  }
  const volume = (width * height * length) / 1000000 // cm³ para m³
  return volume.toFixed(3)
})

const rules = {
  required: (v) => {
    if (v === null || v === undefined || v === '') {
      return 'Campo obrigatório'
    }
    return true
  },
  minDimension: (v) => {
    if (v === null || v === undefined || v === '') {
      return true // required já trata isso
    }
    const num = parseFloat(v)
    if (isNaN(num) || num < 11) {
      return 'Mínimo: 11cm (requisito Melhor Envio)'
    }
    return true
  },
  minWeight: (v) => {
    if (v === null || v === undefined || v === '') {
      return true // required já trata isso
    }
    const num = parseFloat(v)
    if (isNaN(num) || num < 0.1) {
      return 'Mínimo: 0.1kg'
    }
    return true
  }
}

async function fetchBox() {
  if (!isEdit.value) return

  loading.value = true
  try {
    const response = await api.get(`/admin/boxes/${route.params.id}`)
    const box = response.data.data || response.data
    form.value = {
      name: box.name || '',
      width: box.width || null,
      height: box.height || null,
      length: box.length || null,
      weight: box.weight || null,
      active: box.active ?? true
    }
  } catch (error) {
    console.error('Error fetching box:', error)
    showSnackbar('Erro ao carregar caixa', 'error')
  } finally {
    loading.value = false
  }
}

async function saveBox() {
  const { valid: formValid } = await formRef.value.validate()
  if (!formValid) return

  loading.value = true
  try {
    const url = isEdit.value 
      ? `/admin/boxes/${route.params.id}`
      : '/admin/boxes'
    
    const method = isEdit.value ? 'put' : 'post'
    
    const payload = {
      name: form.value.name,
      active: form.value.active,
      width: form.value.width,
      height: form.value.height,
      length: form.value.length,
      weight: form.value.weight,
    }
    
    await api[method](url, payload)
    
    showSnackbar(
      isEdit.value ? 'Caixa atualizada com sucesso' : 'Caixa criada com sucesso',
      'success'
    )
    
    setTimeout(() => {
      router.push({ name: 'BoxesList' })
    }, 1000)
  } catch (error) {
    console.error('Error saving box:', error)
    const errorMessage = error.response?.data?.message || 'Erro ao salvar caixa'
    showSnackbar(errorMessage, 'error')
  } finally {
    loading.value = false
  }
}

function goBack() {
  router.push({ name: 'BoxesList' })
}

function showSnackbar(text, color = 'success') {
  snackbarText.value = text
  snackbarColor.value = color
  snackbar.value = true
}

onMounted(async () => {
  if (isEdit.value) {
    await fetchBox()
  }
})
</script>
