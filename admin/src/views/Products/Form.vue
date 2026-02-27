<template>
  <div>
    <div class="d-flex justify-space-between align-center mb-6">
      <h1 class="text-h4">{{ isEdit ? 'Editar Produto' : 'Novo Produto' }}</h1>
      <v-btn variant="text" @click="goBack">Voltar</v-btn>
    </div>

    <v-card>
      <v-card-text>
        <v-form ref="formRef" v-model="valid">
          <v-row>
            <v-col cols="12" md="6">
              <v-select
                v-model="form.category_id"
                :items="categories"
                item-title="name"
                item-value="id"
                label="Categoria *"
                :rules="[rules.required]"
                variant="outlined"
                :disabled="loading"
              ></v-select>
            </v-col>

            <v-col cols="12" md="6">
              <v-text-field
                v-model="form.name"
                label="Nome *"
                :rules="[rules.required]"
                variant="outlined"
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

            <v-col cols="12" md="4">
              <v-text-field
                v-model.number="form.width"
                label="Largura (m) *"
                type="number"
                step="0.01"
                :rules="[rules.required, rules.positive]"
                variant="outlined"
              ></v-text-field>
            </v-col>

            <v-col cols="12" md="4">
              <v-text-field
                v-model.number="form.height"
                label="Altura (m) *"
                type="number"
                step="0.01"
                :rules="[rules.required, rules.positive]"
                variant="outlined"
              ></v-text-field>
            </v-col>

            <v-col cols="12" md="4">
              <v-text-field
                v-model="form.tenda_code"
                label="Código da Tenda"
                variant="outlined"
              ></v-text-field>
            </v-col>

            <v-col cols="12" md="6">
              <v-select
                v-model="form.box_id"
                :items="boxes"
                item-title="name"
                item-value="id"
                label="Caixa Padrão *"
                :rules="[rules.required]"
                variant="outlined"
                :disabled="loading"
                hint="Selecione a caixa padrão para frete"
                persistent-hint
              >
                <template v-slot:item="{ item, props }">
                  <v-list-item v-bind="props">
                    <template v-slot:title>
                      {{ item.raw.name }}
                    </template>
                    <template v-slot:subtitle>
                      {{ item.raw.width }}cm × {{ item.raw.height }}cm × {{ item.raw.length }}cm - {{ formatNumber(item.raw.weight) }}kg
                    </template>
                  </v-list-item>
                </template>
              </v-select>
            </v-col>

            <v-col cols="12" md="6">
              <v-switch
                v-model="form.active"
                label="Ativo"
                color="success"
              ></v-switch>
            </v-col>

            <v-col cols="12">
              <v-btn
                color="secondary"
                prepend-icon="mdi-calculator"
                @click="openCalculationsModal"
                :disabled="!form.width || !form.height || form.width <= 0 || form.height <= 0"
                variant="outlined"
              >
                Calcular Peso e Volume
              </v-btn>
            </v-col>

            <v-col cols="12" md="6">
              <v-switch
                v-model="form.can_calculate_price"
                label="Permitir cálculo de preço por m²"
                color="primary"
              ></v-switch>
            </v-col>

            <v-col cols="12" md="6" v-if="form.can_calculate_price">
              <v-text-field
                v-model.number="form.price_per_square_meter"
                label="Preço por m² (R$)"
                type="number"
                step="0.01"
                variant="outlined"
              ></v-text-field>
            </v-col>

            <v-col cols="12" md="6" v-if="!form.can_calculate_price">
              <v-text-field
                v-model.number="form.fixed_price"
                label="Preço Fixo (R$)"
                type="number"
                step="0.01"
                variant="outlined"
              ></v-text-field>
            </v-col>
          </v-row>

          <!-- Seção de Imagens -->
          <v-row v-if="isEdit" class="mt-4">
            <v-col cols="12">
              <v-card variant="outlined">
                <v-card-title class="d-flex align-center">
                  <v-icon class="mr-2">mdi-image-multiple</v-icon>
                  Imagens do Produto
                  <v-chip class="ml-2" size="small" color="primary">
                    {{ productImages.length }}/7
                  </v-chip>
                </v-card-title>
                <v-card-text>
                  <!-- Upload de novas imagens -->
                  <v-file-input
                    v-model="newImages"
                    label="Adicionar Imagens (máx. 7)"
                    multiple
                    accept="image/*"
                    prepend-icon="mdi-camera"
                    :disabled="productImages.length >= 7 || uploadingImages"
                    :rules="[rules.maxImages]"
                    @change="handleImageSelect"
                    variant="outlined"
                    class="mb-4"
                  ></v-file-input>

                  <!-- Grid de imagens existentes -->
                  <v-row v-if="productImages.length > 0">
                    <v-col
                      v-for="(image, index) in productImages"
                      :key="image.id"
                      cols="12"
                      sm="6"
                      md="4"
                      lg="3"
                    >
                      <v-card variant="outlined" class="image-card">
                        <v-img
                          :src="getImageUrl(image.path)"
                          :alt="`Imagem ${index + 1}`"
                          height="200"
                          cover
                          class="image-preview"
                        >
                          <template v-slot:placeholder>
                            <v-row class="fill-height ma-0" align="center" justify="center">
                              <v-progress-circular indeterminate color="primary"></v-progress-circular>
                            </v-row>
                          </template>
                        </v-img>
                        <v-card-actions class="d-flex justify-space-between">
                          <v-chip
                            v-if="image.is_main"
                            size="small"
                            color="primary"
                            prepend-icon="mdi-star"
                          >
                            Principal
                          </v-chip>
                          <v-spacer v-else></v-spacer>
                          <div>
                            <v-btn
                              v-if="!image.is_main"
                              icon="mdi-star-outline"
                              size="small"
                              variant="text"
                              @click="setMainImage(image.id)"
                              :loading="updatingImage === image.id"
                            ></v-btn>
                            <v-btn
                              icon="mdi-delete"
                              size="small"
                              variant="text"
                              color="error"
                              @click="deleteImage(image.id)"
                              :loading="deletingImage === image.id"
                            ></v-btn>
                          </div>
                        </v-card-actions>
                      </v-card>
                    </v-col>
                  </v-row>

                  <v-alert
                    v-else
                    type="info"
                    variant="tonal"
                    class="mt-4"
                  >
                    Nenhuma imagem cadastrada. Adicione até 7 imagens para o produto.
                  </v-alert>
                </v-card-text>
              </v-card>
            </v-col>
          </v-row>

          <v-row class="mt-4">
            <v-col cols="12">
              <v-btn
                color="primary"
                @click="saveProduct"
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

    <!-- Modal de Cálculos -->
    <TentCalculationsModal
      v-model="showCalculationsModal"
      :comprimento="form.height"
      :largura="form.width"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import api from '../../services/api'
import TentCalculationsModal from '../../components/TentCalculationsModal.vue'

const router = useRouter()
const route = useRoute()

const form = ref({
  category_id: null,
  name: '',
  description: '',
  width: null,
  height: null,
  tenda_code: '',
  can_calculate_price: true,
  price_per_square_meter: null,
  fixed_price: null,
  active: true,
  box_id: null
})

const categories = ref([])
const boxes = ref([])
const loading = ref(false)
const valid = ref(false)
const snackbar = ref(false)
const snackbarText = ref('')
const snackbarColor = ref('success')
const formRef = ref(null)
const productImages = ref([])
const newImages = ref([])
const uploadingImages = ref(false)
const updatingImage = ref(null)
const deletingImage = ref(null)
const showCalculationsModal = ref(false)

const isEdit = computed(() => !!route.params.id)

const rules = {
  required: (v) => !!v || 'Campo obrigatório',
  positive: (v) => v > 0 || 'Valor deve ser maior que zero',
  maxImages: (v) => {
    if (!v || v.length === 0) return true
    const total = productImages.value.length + v.length
    return total <= 7 || 'Máximo de 7 imagens permitidas'
  }
}

async function fetchCategories() {
  try {
    const response = await api.get('/categories')
    const cats = response.data.data || response.data
    // Garantir que os IDs sejam números para evitar problemas de tipo
    categories.value = Array.isArray(cats) ? cats.map(cat => ({
      ...cat,
      id: Number(cat.id)
    })) : []
  } catch (error) {
    console.error('Error fetching categories:', error)
    categories.value = []
  }
}

async function fetchBoxes() {
  try {
    const response = await api.get('/boxes')
    const boxesData = response.data.data || response.data
    // Garantir que os IDs sejam números e filtrar apenas ativas
    boxes.value = Array.isArray(boxesData) ? boxesData
      .filter(box => box.active)
      .map(box => ({
        ...box,
        id: Number(box.id)
      })) : []
  } catch (error) {
    console.error('Error fetching boxes:', error)
    boxes.value = []
  }
}

function formatNumber(value) {
  return new Intl.NumberFormat('pt-BR', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(value)
}

async function fetchProduct() {
  if (!isEdit.value) return

  loading.value = true
  try {
    const response = await api.get(`/admin/products/${route.params.id}`)
    const product = response.data.data || response.data
    
    // Garantir que category_id seja um número (pode vir de product.category_id ou product.category.id)
    const categoryId = product.category_id 
      ? Number(product.category_id) 
      : (product.category?.id ? Number(product.category.id) : null)
    
    // Garantir que box_id seja um número (pode vir de product.box_id ou product.box.id)
    let boxId = null
    if (product.box_id !== null && product.box_id !== undefined) {
      boxId = Number(product.box_id)
    } else if (product.box?.id !== null && product.box?.id !== undefined) {
      boxId = Number(product.box.id)
    }

    form.value = {
      category_id: categoryId,
      name: product.name,
      description: product.description || '',
      width: product.width,
      height: product.height,
      tenda_code: product.tenda_code || '',
      can_calculate_price: product.can_calculate_price ?? true,
      price_per_square_meter: product.price_per_square_meter,
      fixed_price: product.fixed_price,
      active: product.active ?? true,
      box_id: boxId
    }

    // Carregar imagens do produto
    if (product.images && Array.isArray(product.images)) {
      productImages.value = product.images.sort((a, b) => {
        if (a.is_main) return -1
        if (b.is_main) return 1
        return (a.order || 0) - (b.order || 0)
      })
    }
  } catch (error) {
    console.error('Error fetching product:', error)
    showSnackbar('Erro ao carregar produto', 'error')
  } finally {
    loading.value = false
  }
}

async function saveProduct() {
  if (!valid.value) return

  loading.value = true
  try {
    const url = isEdit.value 
      ? `/admin/products/${route.params.id}`
      : '/admin/products'
    
    const method = isEdit.value ? 'put' : 'post'
    
    // Garantir que category_id e box_id sejam números antes de enviar
    const payload = {
      ...form.value,
      category_id: form.value.category_id ? Number(form.value.category_id) : null,
      box_id: form.value.box_id ? Number(form.value.box_id) : null
    }
    
    await api[method](url, payload)
    
    showSnackbar(
      isEdit.value ? 'Produto atualizado com sucesso' : 'Produto criado com sucesso',
      'success'
    )
    
    setTimeout(() => {
      router.push({ name: 'ProductsList' })
    }, 1000)
  } catch (error) {
    console.error('Error saving product:', error)
    const errorMessage = error.response?.data?.message || 'Erro ao salvar produto'
    showSnackbar(errorMessage, 'error')
  } finally {
    loading.value = false
  }
}

function goBack() {
  router.push({ name: 'ProductsList' })
}

function openCalculationsModal() {
  if (!form.value.width || !form.value.height || form.value.width <= 0 || form.value.height <= 0) {
    showSnackbar('Por favor, informe largura e altura válidas antes de calcular', 'warning')
    return
  }
  showCalculationsModal.value = true
}

function showSnackbar(text, color = 'success') {
  snackbarText.value = text
  snackbarColor.value = color
  snackbar.value = true
}

function getImageUrl(path) {
  if (!path) return ''
  // Se já começa com http, retornar como está
  if (path.startsWith('http')) return path
  // Se começa com /storage, retornar como está
  if (path.startsWith('/storage')) return path
  // Caso contrário, adicionar /storage
  return path.startsWith('/') ? path : `/storage/${path}`
}

function handleImageSelect(event) {
  // Vuetify v-file-input pode passar o evento ou os arquivos diretamente
  // Se for evento, pegar os arquivos do target ou usar newImages.value
  let files = event
  if (event?.target?.files) {
    files = Array.from(event.target.files)
  } else if (Array.isArray(event)) {
    files = event
  } else if (newImages.value && Array.isArray(newImages.value)) {
    files = newImages.value
  } else if (newImages.value && newImages.value.length !== undefined) {
    files = Array.from(newImages.value)
  }
  
  if (!files || files.length === 0) {
    return
  }
  
  if (!isEdit.value) {
    showSnackbar('Salve o produto primeiro antes de adicionar imagens', 'warning')
    newImages.value = []
    return
  }

  const remainingSlots = 7 - productImages.value.length
  if (files.length > remainingSlots) {
    showSnackbar(`Você pode adicionar apenas ${remainingSlots} imagem(ns)`, 'warning')
    files = files.slice(0, remainingSlots)
  }

  uploadImages(files)
}

async function uploadImages(files) {
  if (!isEdit.value || !files || files.length === 0) {
    return
  }

  uploadingImages.value = true
  try {
    const formData = new FormData()
    // Usar 'images[]' para arrays - Laravel processa isso corretamente
    const fileArray = Array.from(files).filter(file => file !== null && file !== undefined)
    
    if (fileArray.length === 0) {
      showSnackbar('Nenhuma imagem válida selecionada', 'error')
      uploadingImages.value = false
      return
    }
    
    fileArray.forEach((file) => {
      formData.append('images[]', file)
    })
    
    const response = await api.post(
      `/admin/products/${route.params.id}/images`,
      formData
    )

    // Adicionar novas imagens à lista
    if (response.data.images && Array.isArray(response.data.images)) {
      productImages.value.push(...response.data.images)
      productImages.value.sort((a, b) => {
        if (a.is_main) return -1
        if (b.is_main) return 1
        return (a.order || 0) - (b.order || 0)
      })
    }

    showSnackbar('Imagens enviadas com sucesso', 'success')
    newImages.value = []
  } catch (error) {
    console.error('Error uploading images:', error)
    console.error('Error details:', error.response?.data)
    const errorMessage = error.response?.data?.message || error.response?.data?.error || 'Erro ao enviar imagens'
    showSnackbar(errorMessage, 'error')
  } finally {
    uploadingImages.value = false
  }
}

async function deleteImage(imageId) {
  if (!isEdit.value) return

  if (!confirm('Tem certeza que deseja deletar esta imagem?')) return

  deletingImage.value = imageId
  try {
    await api.delete(`/admin/products/${route.params.id}/images/${imageId}`)
    
    // Remover da lista
    productImages.value = productImages.value.filter(img => img.id !== imageId)
    
    showSnackbar('Imagem deletada com sucesso', 'success')
  } catch (error) {
    console.error('Error deleting image:', error)
    showSnackbar('Erro ao deletar imagem', 'error')
  } finally {
    deletingImage.value = null
  }
}

async function setMainImage(imageId) {
  if (!isEdit.value) return

  updatingImage.value = imageId
  try {
    await api.put(`/admin/products/${route.params.id}/images/${imageId}/main`)
    
    // Atualizar lista: remover is_main de todas e definir a selecionada
    productImages.value.forEach(img => {
      img.is_main = img.id === imageId
    })
    
    // Reordenar: principal primeiro
    productImages.value.sort((a, b) => {
      if (a.is_main) return -1
      if (b.is_main) return 1
      return (a.order || 0) - (b.order || 0)
    })
    
    showSnackbar('Imagem principal definida', 'success')
  } catch (error) {
    console.error('Error setting main image:', error)
    showSnackbar('Erro ao definir imagem principal', 'error')
  } finally {
    updatingImage.value = null
  }
}

onMounted(async () => {
  await Promise.all([fetchCategories(), fetchBoxes()])
  if (isEdit.value) {
    await fetchProduct()
  }
})
</script>
