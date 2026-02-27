<template>
  <div>
    <div class="d-flex justify-space-between align-center mb-6">
      <h1 class="text-h4">{{ isEdit ? 'Editar Categoria' : 'Nova Categoria' }}</h1>
      <v-btn variant="text" @click="goBack">Voltar</v-btn>
    </div>

    <v-card>
      <v-card-text>
        <v-form ref="form" v-model="valid">
          <v-row>
            <v-col cols="12" md="6">
              <v-text-field
                v-model="form.name"
                label="Nome *"
                :rules="[rules.required]"
                variant="outlined"
                @input="generateSlug"
              ></v-text-field>
            </v-col>

            <v-col cols="12" md="6">
              <v-text-field
                v-model="form.slug"
                label="Slug *"
                :rules="[rules.required]"
                variant="outlined"
                hint="URL amigável (ex: quadradas, retangular)"
              ></v-text-field>
            </v-col>

            <v-col cols="12">
              <v-textarea
                v-model="form.description"
                label="Descrição"
                variant="outlined"
                rows="3"
              ></v-textarea>
            </v-col>

            <v-col cols="12" md="6">
              <v-text-field
                v-model.number="form.order"
                label="Ordem"
                type="number"
                variant="outlined"
                hint="Ordem de exibição (menor número aparece primeiro)"
              ></v-text-field>
            </v-col>

            <v-col cols="12" md="6">
              <v-switch
                v-model="form.active"
                label="Ativa"
                color="success"
              ></v-switch>
            </v-col>
          </v-row>

          <v-row class="mt-4">
            <v-col cols="12">
              <v-btn
                color="primary"
                @click="saveCategory"
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

const form = ref({
  name: '',
  slug: '',
  description: '',
  order: 0,
  active: true
})

const loading = ref(false)
const valid = ref(false)
const snackbar = ref(false)
const snackbarText = ref('')
const snackbarColor = ref('success')

const isEdit = computed(() => !!route.params.id)

const rules = {
  required: (v) => !!v || 'Campo obrigatório'
}

function generateSlug() {
  if (!isEdit.value && form.value.name) {
    form.value.slug = form.value.name
      .toLowerCase()
      .normalize('NFD')
      .replace(/[\u0300-\u036f]/g, '')
      .replace(/[^a-z0-9]+/g, '-')
      .replace(/(^-|-$)/g, '')
  }
}

async function fetchCategory() {
  if (!isEdit.value) return

  loading.value = true
  try {
    const response = await api.get(`/admin/categories/${route.params.id}`)
    const category = response.data.data || response.data
    form.value = {
      name: category.name,
      slug: category.slug,
      description: category.description || '',
      order: category.order || 0,
      active: category.active ?? true
    }
  } catch (error) {
    console.error('Error fetching category:', error)
    showSnackbar('Erro ao carregar categoria', 'error')
  } finally {
    loading.value = false
  }
}

async function saveCategory() {
  if (!valid.value) return

  loading.value = true
  try {
    const url = isEdit.value 
      ? `/admin/categories/${route.params.id}`
      : '/admin/categories'
    
    const method = isEdit.value ? 'put' : 'post'
    
    await api[method](url, form.value)
    
    showSnackbar(
      isEdit.value ? 'Categoria atualizada com sucesso' : 'Categoria criada com sucesso',
      'success'
    )
    
    setTimeout(() => {
      router.push({ name: 'CategoriesList' })
    }, 1000)
  } catch (error) {
    console.error('Error saving category:', error)
    const errorMessage = error.response?.data?.message || 'Erro ao salvar categoria'
    showSnackbar(errorMessage, 'error')
  } finally {
    loading.value = false
  }
}

function goBack() {
  router.push({ name: 'CategoriesList' })
}

function showSnackbar(text, color = 'success') {
  snackbarText.value = text
  snackbarColor.value = color
  snackbar.value = true
}

onMounted(async () => {
  if (isEdit.value) {
    await fetchCategory()
  }
})
</script>
