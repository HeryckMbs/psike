/**
 * Calcula peso, volume, cubagem e sugestão de caixas para malhas tensionadas de tendas
 * 
 * @param {number} comprimento - Comprimento da tenda em metros
 * @param {number} largura - Largura da tenda em metros
 * @param {number} foldedThickness - Espessura média dobrada em metros (padrão: 0.20)
 * @param {Object} boxDimensions - Dimensões da caixa padrão (padrão: { length: 0.60, width: 0.40, height: 0.40 })
 * @returns {Object} Objeto com peso, volume, cubagem e informações sobre caixas
 */
export function calculateTentMetrics(comprimento, largura, foldedThickness = 0.20, boxDimensions = { length: 0.60, width: 0.40, height: 0.40 }) {
  // Validações
  if (!comprimento || !largura || comprimento <= 0 || largura <= 0) {
    return {
      peso: 0,
      volume: 0,
      cubagem: 0,
      caixas: {
        quantidade: 0,
        mensagem: 'Dimensões inválidas'
      },
      erro: 'Comprimento e largura devem ser números positivos'
    }
  }

  // Cálculo de Peso
  // Dividir comprimento e largura por 1.55
  const comprimentoAjustado = comprimento / 1.55
  const larguraAjustada = largura / 1.55
  
  // Calcular nova área
  const novaArea = comprimentoAjustado * larguraAjustada
  
  // Calcular peso (kg)
  const peso = novaArea * 0.31

  // Cálculo de Volume
  // Determinar maior e menor dimensão
  const maiorDimensao = Math.max(comprimento, largura)
  const menorDimensao = Math.min(comprimento, largura)
  
  // Comprimento e largura dobrados
  const comprimentoDobrado = maiorDimensao / 1.55
  const larguraDobrada = menorDimensao / 1.55
  
  // Volume em m³
  const volume = comprimentoDobrado * larguraDobrada * foldedThickness

  // Cubagem (igual ao volume)
  const cubagem = volume

  // Cálculo de Caixas
  const caixas = calculateBoxes(volume, boxDimensions)

  // Arredondar valores para 2 casas decimais
  return {
    peso: Math.round(peso * 100) / 100,
    volume: Math.round(volume * 100) / 100,
    cubagem: Math.round(cubagem * 100) / 100,
    caixas: caixas,
    detalhes: {
      comprimentoAjustado: Math.round(comprimentoAjustado * 100) / 100,
      larguraAjustada: Math.round(larguraAjustada * 100) / 100,
      novaArea: Math.round(novaArea * 100) / 100,
      comprimentoDobrado: Math.round(comprimentoDobrado * 100) / 100,
      larguraDobrada: Math.round(larguraDobrada * 100) / 100,
      espessura: foldedThickness
    }
  }
}

/**
 * Calcula quantidade de caixas necessárias para armazenar o volume
 * 
 * @param {number} volume - Volume em m³
 * @param {Object} boxDimensions - Dimensões da caixa { length, width, height }
 * @returns {Object} Informações sobre as caixas necessárias
 */
function calculateBoxes(volume, boxDimensions) {
  if (volume <= 0) {
    return {
      quantidade: 0,
      mensagem: 'Volume inválido'
    }
  }

  const { length, width, height } = boxDimensions
  const volumeCaixa = length * width * height

  if (volumeCaixa <= 0) {
    return {
      quantidade: 0,
      mensagem: 'Dimensões da caixa inválidas'
    }
  }

  // Verificar se cabe em uma única caixa
  if (volume <= volumeCaixa) {
    return {
      quantidade: 1,
      mensagem: 'Cabe em 1 caixa',
      volumeCaixa: Math.round(volumeCaixa * 100) / 100,
      dimensoes: `${length}m × ${width}m × ${height}m`
    }
  }

  // Calcular quantidade mínima de caixas necessárias
  // Usar Math.ceil para arredondar para cima
  const quantidade = Math.ceil(volume / volumeCaixa)

  return {
    quantidade: quantidade,
    mensagem: `Necessário ${quantidade} caixa(s)`,
    volumeCaixa: Math.round(volumeCaixa * 100) / 100,
    dimensoes: `${length}m × ${width}m × ${height}m`,
    volumeTotal: Math.round(quantidade * volumeCaixa * 100) / 100
  }
}

/**
 * Formata número para exibição em português (vírgula como separador decimal)
 * 
 * @param {number} value - Valor numérico
 * @param {number} decimals - Número de casas decimais (padrão: 2)
 * @returns {string} Valor formatado
 */
export function formatNumber(value, decimals = 2) {
  if (value === null || value === undefined || isNaN(value)) {
    return '0,00'
  }
  
  return value.toFixed(decimals).replace('.', ',')
}
