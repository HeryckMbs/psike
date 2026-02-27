<template>
  <v-dialog v-model="dialog" max-width="600" persistent>
    <v-card>
      <v-card-title class="d-flex justify-space-between align-center">
        <span class="text-h5">Cálculo de Peso, Volume e Cubagem</span>
        <v-btn icon="mdi-close" variant="text" @click="close"></v-btn>
      </v-card-title>

      <v-divider></v-divider>

      <v-card-text class="pt-6">
        <div v-if="erro" class="mb-4">
          <v-alert type="error" variant="tonal">
            {{ erro }}
          </v-alert>
        </div>

        <div v-else>
          <!-- Informações de Entrada -->
          <v-card variant="outlined" class="mb-4">
            <v-card-title class="text-subtitle-1">Dimensões Informadas</v-card-title>
            <v-card-text>
              <div class="d-flex justify-space-between">
                <span><strong>Comprimento:</strong> {{ formatNumber(comprimento) }} m</span>
                <span><strong>Largura:</strong> {{ formatNumber(largura) }} m</span>
              </div>
              <div class="mt-2">
                <span><strong>Área Total:</strong> {{ formatNumber(comprimento * largura) }} m²</span>
              </div>
            </v-card-text>
          </v-card>

          <!-- Resultados Principais -->
          <v-row>
            <v-col cols="12" md="4">
              <v-card variant="outlined" class="text-center">
                <v-card-text>
                  <div class="text-h6 mb-2">
                    <v-icon color="primary" size="large">mdi-weight-kilogram</v-icon>
                  </div>
                  <div class="text-h5 font-weight-bold text-primary">
                    {{ formatNumber(resultado.peso) }} kg
                  </div>
                  <div class="text-caption text-medium-emphasis mt-1">Peso</div>
                </v-card-text>
              </v-card>
            </v-col>

            <v-col cols="12" md="4">
              <v-card variant="outlined" class="text-center">
                <v-card-text>
                  <div class="text-h6 mb-2">
                    <v-icon color="success" size="large">mdi-cube-outline</v-icon>
                  </div>
                  <div class="text-h5 font-weight-bold text-success">
                    {{ formatNumber(resultado.volume) }} m³
                  </div>
                  <div class="text-caption text-medium-emphasis mt-1">Volume</div>
                </v-card-text>
              </v-card>
            </v-col>

            <v-col cols="12" md="4">
              <v-card variant="outlined" class="text-center">
                <v-card-text>
                  <div class="text-h6 mb-2">
                    <v-icon color="info" size="large">mdi-cube-scan</v-icon>
                  </div>
                  <div class="text-h5 font-weight-bold text-info">
                    {{ formatNumber(resultado.cubagem) }} m³
                  </div>
                  <div class="text-caption text-medium-emphasis mt-1">Cubagem</div>
                </v-card-text>
              </v-card>
            </v-col>
          </v-row>

          <!-- Informações de Caixas -->
          <v-card variant="outlined" class="mt-4">
            <v-card-title class="text-subtitle-1">
              <v-icon class="mr-2">mdi-package-variant</v-icon>
              Sugestão de Caixas
            </v-card-title>
            <v-card-text>
              <div class="mb-2">
                <span class="text-h6 font-weight-bold">{{ resultado.caixas.quantidade }}</span>
                <span class="ml-2">{{ resultado.caixas.mensagem }}</span>
              </div>
              <div v-if="resultado.caixas.dimensoes" class="text-body-2 text-medium-emphasis">
                Dimensões da caixa: {{ resultado.caixas.dimensoes }}
              </div>
              <div v-if="resultado.caixas.volumeCaixa" class="text-body-2 text-medium-emphasis">
                Volume por caixa: {{ formatNumber(resultado.caixas.volumeCaixa) }} m³
              </div>
              <div v-if="resultado.caixas.volumeTotal" class="text-body-2 text-medium-emphasis">
                Volume total: {{ formatNumber(resultado.caixas.volumeTotal) }} m³
              </div>
            </v-card-text>
          </v-card>

          <!-- Detalhes do Cálculo (Expansível) -->
          <v-expansion-panels class="mt-4">
            <v-expansion-panel>
              <v-expansion-panel-title>
                <v-icon class="mr-2">mdi-information</v-icon>
                Detalhes do Cálculo
              </v-expansion-panel-title>
              <v-expansion-panel-text>
                <v-list density="compact">
                  <v-list-item>
                    <v-list-item-title>Comprimento Ajustado</v-list-item-title>
                    <v-list-item-subtitle>
                      {{ formatNumber(resultado.detalhes.comprimentoAjustado) }} m
                      <span class="text-caption">({{ comprimento }} ÷ 1.55)</span>
                    </v-list-item-subtitle>
                  </v-list-item>
                  <v-list-item>
                    <v-list-item-title>Largura Ajustada</v-list-item-title>
                    <v-list-item-subtitle>
                      {{ formatNumber(resultado.detalhes.larguraAjustada) }} m
                      <span class="text-caption">({{ largura }} ÷ 1.55)</span>
                    </v-list-item-subtitle>
                  </v-list-item>
                  <v-list-item>
                    <v-list-item-title>Nova Área</v-list-item-title>
                    <v-list-item-subtitle>
                      {{ formatNumber(resultado.detalhes.novaArea) }} m²
                    </v-list-item-subtitle>
                  </v-list-item>
                  <v-list-item>
                    <v-list-item-title>Comprimento Dobrado</v-list-item-title>
                    <v-list-item-subtitle>
                      {{ formatNumber(resultado.detalhes.comprimentoDobrado) }} m
                    </v-list-item-subtitle>
                  </v-list-item>
                  <v-list-item>
                    <v-list-item-title>Largura Dobrada</v-list-item-title>
                    <v-list-item-subtitle>
                      {{ formatNumber(resultado.detalhes.larguraDobrada) }} m
                    </v-list-item-subtitle>
                  </v-list-item>
                  <v-list-item>
                    <v-list-item-title>Espessura Média Dobrada</v-list-item-title>
                    <v-list-item-subtitle>
                      {{ formatNumber(resultado.detalhes.espessura) }} m
                    </v-list-item-subtitle>
                  </v-list-item>
                </v-list>
              </v-expansion-panel-text>
            </v-expansion-panel>
          </v-expansion-panels>
        </div>
      </v-card-text>

      <v-divider></v-divider>

      <v-card-actions>
        <v-spacer></v-spacer>
        <v-btn color="primary" @click="close">Fechar</v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { calculateTentMetrics, formatNumber as formatNumberUtil } from '../utils/tentCalculations'

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false
  },
  comprimento: {
    type: Number,
    default: 0
  },
  largura: {
    type: Number,
    default: 0
  }
})

const emit = defineEmits(['update:modelValue'])

const dialog = computed({
  get: () => props.modelValue,
  set: (value) => emit('update:modelValue', value)
})

const resultado = ref({
  peso: 0,
  volume: 0,
  cubagem: 0,
  caixas: {
    quantidade: 0,
    mensagem: ''
  },
  detalhes: {}
})

const erro = ref('')

// Calcular quando as dimensões mudarem ou o modal abrir
watch([() => props.comprimento, () => props.largura, dialog], () => {
  if (dialog.value) {
    calcular()
  }
}, { immediate: true })

function calcular() {
  erro.value = ''
  
  if (!props.comprimento || !props.largura || props.comprimento <= 0 || props.largura <= 0) {
    erro.value = 'Por favor, informe comprimento e largura válidos (valores maiores que zero)'
    resultado.value = {
      peso: 0,
      volume: 0,
      cubagem: 0,
      caixas: { quantidade: 0, mensagem: 'Dimensões inválidas' },
      detalhes: {}
    }
    return
  }

  const calculo = calculateTentMetrics(props.comprimento, props.largura)
  
  if (calculo.erro) {
    erro.value = calculo.erro
  }
  
  resultado.value = calculo
}

function close() {
  dialog.value = false
}

function formatNumber(value) {
  return formatNumberUtil(value, 2)
}
</script>
