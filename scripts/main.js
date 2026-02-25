// ============================================
// Configuração
// ============================================

// Número do WhatsApp - ALTERE AQUI
const WHATSAPP_NUMBER = '556282198202'; // Formato: 556282198202 (sem espaços ou caracteres especiais)
const WHATSAPP_MESSAGE = 'Olá! Gostaria de saber mais sobre as estruturas da Psiké Deloun Arts.';

// Preço por metro quadrado para tendas
const PRICE_PER_SQUARE_METER = 22.00; // R$ 22,00 por m²

// ============================================
// Áreas Fixas para Tendas Mandala
// ============================================

// Mapeamento de áreas fixas para tendas mandala (cálculo diferente)
const MANDALA_FIXED_AREAS = {
    '8x8_tenda1': 51,
    '11x11_tenda2': 86,
    '13x13_tenda3': 129,
    '11x11_tenda4': 93,
    '14x14_tenda5': 155,
    '14x14_tenda6': 155,
    '21x21_tenda7': 326,
    '23x23_tenda8': 344,
    '24x24_tenda9': 460,
    '26x26_tenda10': 480,
    '33x33_tenda11': 754
};

// Mapeamento de preços fixos para tendas mandala
const MANDALA_FIXED_PRICES = {
    '8x8_tenda1': 1122.00,
    '11x11_tenda2': 1892.00,
    '13x13_tenda3': 2838.00,
    '11x11_tenda4': 2046.00,
    '14x14_tenda5': 3410.00,
    '14x14_tenda6': 3410.00,
    '21x21_tenda7': 7172.00,
    '23x23_tenda8': 7568.00,
    '24x24_tenda9': 10120.00,
    '26x26_tenda10': 10560.00,
    '33x33_tenda11': 16588.00
};

// Função para obter preço fixo de uma tenda MANDALA
function getMandalaFixedPrice(tendaCode, width, height) {
    if (tendaCode) {
        const key = `${width}x${height}_${tendaCode}`;
        return MANDALA_FIXED_PRICES[key] || null;
    }
    return null;
}

// Tornar função acessível globalmente
window.getMandalaFixedPrice = getMandalaFixedPrice;

// Função para obter área de uma tenda (usa área fixa para MANDALA)
function getTentArea(type, width, height, tendaCode) {
    if (type === 'MANDALA' && tendaCode) {
        // Criar chave no formato widthxheight_tendaX
        const key = `${width}x${height}_${tendaCode}`;
        if (MANDALA_FIXED_AREAS[key]) {
            return MANDALA_FIXED_AREAS[key];
        }
    }
    // Para outros tipos ou se não encontrar, calcular normalmente
    return width * height;
}

// ============================================
// Catálogo de Tendas
// ============================================

// Função para extrair número da pasta "TENDA X"
function extractTendaNumber(folder) {
    const match = folder.match(/TENDA\s+(\d+)/i);
    return match ? parseInt(match[1]) : null;
}

// Função para extrair medidas e código da tenda do nome do arquivo
function parseTentDimensions(imageData) {
    // imageData pode ser string (legado) ou objeto { type, folder, filename, fullPath }
    let filename, type, folder, tendaNumber;
    
    if (typeof imageData === 'string') {
        // Formato legado - apenas filename
        filename = imageData;
        type = null;
        folder = null;
        tendaNumber = null;
    } else {
        // Novo formato - objeto com todas as informações
        filename = imageData.filename;
        type = imageData.type;
        folder = imageData.folder;
        tendaNumber = extractTendaNumber(folder);
    }
    
    // Remove extensão .png
    let name = filename.replace(/\.png$/i, '');
    
    // Remove espaços extras no início e fim
    name = name.trim();
    
    // Padrão para formato: WxH_tendaXX ou WxH_tendaXX(N) ou WxH tendaXX ou WxH_tendasXX
    // Exemplos: 4x20_tenda8.png, 4x20_tenda8(1).png, 10x10_tenda3(2).png, 10x10_ tenda3.png, 14x14_tendas22.png
    const pattern1 = /(\d+)[xX](\d+)[_\s]+tendas?(\d+)(?:\((\d+)\))?/i;
    const match1 = name.match(pattern1);
    
    if (match1) {
        const width = parseInt(match1[1]);
        const height = parseInt(match1[2]);
        const tendaCodeFromFile = `tenda${match1[3]}`;
        const variation = match1[4] ? parseInt(match1[4]) : null;
        
        // Usar tendaNumber da pasta se disponível, senão usar do filename
        const finalTendaNumber = tendaNumber || parseInt(match1[3]);
        const finalTendaCode = `tenda${finalTendaNumber}`;
        
        // baseId agora inclui type se disponível
        const baseId = type 
            ? `tenda-${type}-${width}x${height}-${finalTendaCode}`
            : `tenda-${width}x${height}-${finalTendaCode}`;
        const id = variation ? `${baseId}-${variation}` : baseId;
        
        return {
            width: width,
            height: height,
            tendaCode: finalTendaCode,
            tendaNumber: finalTendaNumber,
            type: type,
            variation: variation,
            baseId: baseId,
            id: id
        };
    }
    
    // Padrão alternativo para formato antigo: WxH ou WxH - N
    // Remove palavras como "TENDA", "NOVO" e espaços extras
    name = name.replace(/TENDA\s*/gi, '').replace(/NOVO\s*/gi, '').trim();
    
    // Remove espaços e hífens extras (ex: "8X25 - 2" vira "8X25-2")
    name = name.replace(/\s*-\s*/, '-');
    
    // Extrai números: formato pode ser "14x14", "15X20", "8X25-2", etc
    const match2 = name.match(/(\d+)[xX](\d+)(?:-(\d+))?/);
    
    if (match2) {
        const width = parseInt(match2[1]);
        const height = parseInt(match2[2]);
        const variation = match2[3] ? parseInt(match2[3]) : null;
        
        // Se temos type e tendaNumber, usar; senão formato legado
        const baseId = type && tendaNumber
            ? `tenda-${type}-${width}x${height}-tenda${tendaNumber}`
            : `tenda-${width}x${height}`;
        const id = variation ? `${baseId}-${variation}` : baseId;
        
        return {
            width: width,
            height: height,
            tendaCode: tendaNumber ? `tenda${tendaNumber}` : null,
            tendaNumber: tendaNumber,
            type: type,
            variation: variation,
            baseId: baseId,
            id: id
        };
    }
    
    return null;
}

// Lista de imagens do catálogo organizadas por tipo
// Estrutura: { type, folder, filename, fullPath }
const QUADRADAS_IMAGES = [
    { type: 'QUADRADAS', folder: 'TENDA 1', filename: '10x10_tenda1.png' },
    { type: 'QUADRADAS', folder: 'TENDA 2', filename: '10x10_ tenda2.png' },
    { type: 'QUADRADAS', folder: 'TENDA 2', filename: '10x10_tenda2.png' },
    { type: 'QUADRADAS', folder: 'TENDA 2', filename: '10x10_tenda2(1).png' },
    { type: 'QUADRADAS', folder: 'TENDA 2', filename: '10x10_tenda2(2).png' },
    { type: 'QUADRADAS', folder: 'TENDA 3', filename: '10x10_tenda3.png' },
    { type: 'QUADRADAS', folder: 'TENDA 3', filename: '10x10_tenda3(1).png' },
    { type: 'QUADRADAS', folder: 'TENDA 4', filename: '20x20_tenda4.png' },
    { type: 'QUADRADAS', folder: 'TENDA 4', filename: '20X20_tenda4(1).png' },
    { type: 'QUADRADAS', folder: 'TENDA 4', filename: '20x20_tenda4(2).png' },
    { type: 'QUADRADAS', folder: 'TENDA 4', filename: '20x20_tenda4(3).png' },
    { type: 'QUADRADAS', folder: 'TENDA 4', filename: '20x20_tenda4(4).png' }
];

const RETANGULAR_IMAGES = [
    { type: 'RETANGULAR', folder: 'TENDA 1', filename: '10x12_tenda1.png' },
    { type: 'RETANGULAR', folder: 'TENDA 1', filename: '10x12_tenda1(1).png' },
    { type: 'RETANGULAR', folder: 'TENDA 2', filename: '6x11_tenda2.png' },
    { type: 'RETANGULAR', folder: 'TENDA 3', filename: '11x14_tenda3.png' },
    { type: 'RETANGULAR', folder: 'TENDA 4', filename: '15X20_tenda4.png' },
    { type: 'RETANGULAR', folder: 'TENDA 5', filename: '12x20_tenda5.png' },
    { type: 'RETANGULAR', folder: 'TENDA 6', filename: '4x20_tenda6.png' },
    { type: 'RETANGULAR', folder: 'TENDA 6', filename: '4x20_tenda6(1).png' },
    { type: 'RETANGULAR', folder: 'TENDA 6', filename: '4x20_tenda6(2).png' },
    { type: 'RETANGULAR', folder: 'TENDA 6', filename: '4X20_tenda6(3).png' },
    { type: 'RETANGULAR', folder: 'TENDA 7', filename: '10x30_tenda7.png' },
    { type: 'RETANGULAR', folder: 'TENDA 8', filename: '10x30_tenda8.png' },
    { type: 'RETANGULAR', folder: 'TENDA 9', filename: '7x25_tenda9.png' },
    { type: 'RETANGULAR', folder: 'TENDA 9', filename: '7X25_tenda9(1).png' },
    { type: 'RETANGULAR', folder: 'TENDA 10', filename: '8x25_tenda10.png' },
    { type: 'RETANGULAR', folder: 'TENDA 10', filename: '8x25_tenda10(1).png' },
    { type: 'RETANGULAR', folder: 'TENDA 10', filename: '8X25_tenda10(2).png' },
    { type: 'RETANGULAR', folder: 'TENDA 11', filename: '8X25_tenda11.png' },
    { type: 'RETANGULAR', folder: 'TENDA 11', filename: '8x25_tenda11(1).png' },
    { type: 'RETANGULAR', folder: 'TENDA 12', filename: '12x30_tenda12.png' },
    { type: 'RETANGULAR', folder: 'TENDA 13', filename: '12x50_tenda13.png' },
    { type: 'RETANGULAR', folder: 'TENDA 14', filename: '15x50_tenda14.png' },
    { type: 'RETANGULAR', folder: 'TENDA 15', filename: '20x40_tenda15.png' },
    { type: 'RETANGULAR', folder: 'TENDA 15', filename: '20x40_tenda15(1).png' },
    { type: 'RETANGULAR', folder: 'TENDA 15', filename: '20x40_tenda15(2).png' },
    { type: 'RETANGULAR', folder: 'TENDA 16', filename: '20X40_tenda16.png' },
    { type: 'RETANGULAR', folder: 'TENDA 16', filename: '20x40_tenda16(1).png' }
];

const MANDALA_IMAGES = [
    { type: 'MANDALA', folder: 'TENDA 1', filename: '8x8_tenda1.png' },
    { type: 'MANDALA', folder: 'TENDA 1', filename: '8x8_tenda1(1).png' },
    { type: 'MANDALA', folder: 'TENDA 1', filename: '8x8_tenda1(2).png' },
    { type: 'MANDALA', folder: 'TENDA 1', filename: '8x8_tenda1(3).png' },
    { type: 'MANDALA', folder: 'TENDA 1', filename: '8x8_tenda1(4).png' },
    { type: 'MANDALA', folder: 'TENDA 2', filename: '11x11_tenda2.png' },
    { type: 'MANDALA', folder: 'TENDA 2', filename: '11x11_tenda2(1).png' },
    { type: 'MANDALA', folder: 'TENDA 2', filename: '11x11_tenda2(2).png' },
    { type: 'MANDALA', folder: 'TENDA 2', filename: '11x11_tenda2(3).png' },
    { type: 'MANDALA', folder: 'TENDA 2', filename: '11x11_tenda2(4).png' },
    { type: 'MANDALA', folder: 'TENDA 3', filename: '13x13_tenda3.png' },
    { type: 'MANDALA', folder: 'TENDA 3', filename: '13x13_tenda3(1).png' },
    { type: 'MANDALA', folder: 'TENDA 3', filename: '13x13_tenda3(2).png' },
    { type: 'MANDALA', folder: 'TENDA 3', filename: '13x13_tenda3(3).png' },
    { type: 'MANDALA', folder: 'TENDA 3', filename: '13x13_tenda3(4).png' },
    { type: 'MANDALA', folder: 'TENDA 4', filename: '11x11_tenda4.png' },
    { type: 'MANDALA', folder: 'TENDA 5', filename: '14x14_tenda5.png' },
    { type: 'MANDALA', folder: 'TENDA 5', filename: '14x14_tenda5(1).png' },
    { type: 'MANDALA', folder: 'TENDA 5', filename: '14x14_tenda5(2).png' },
    { type: 'MANDALA', folder: 'TENDA 6', filename: '14x14_tenda6.png' },
    { type: 'MANDALA', folder: 'TENDA 7', filename: '21x21_tenda7.png' },
    { type: 'MANDALA', folder: 'TENDA 8', filename: '23x23_tenda8.png' },
    { type: 'MANDALA', folder: 'TENDA 8', filename: '23x23_tenda8(1).png' },
    { type: 'MANDALA', folder: 'TENDA 9', filename: '24x24_tenda9.png' },
    { type: 'MANDALA', folder: 'TENDA 10', filename: '26x26_tenda10.png' },
    { type: 'MANDALA', folder: 'TENDA 10', filename: '26x26_tenda10(1).png' },
    { type: 'MANDALA', folder: 'TENDA 10', filename: '26X26_tenda10(2).png' },
    { type: 'MANDALA', folder: 'TENDA 10', filename: '26x26_tenda10(3).png' },
    { type: 'MANDALA', folder: 'TENDA 11', filename: '33x33_tenda11.png' }
];

// Combinar todas as imagens e gerar fullPath
const ALL_TENT_IMAGES = [
    ...QUADRADAS_IMAGES.map(img => ({
        ...img,
        fullPath: `assets/images/CATÁLOGO/${img.type}/${img.folder}/${img.filename}`
    })),
    ...RETANGULAR_IMAGES.map(img => ({
        ...img,
        fullPath: `assets/images/CATÁLOGO/${img.type}/${img.folder}/${img.filename}`
    })),
    ...MANDALA_IMAGES.map(img => ({
        ...img,
        fullPath: `assets/images/CATÁLOGO/${img.type}/${img.folder}/${img.filename}`
    }))
];

// Função para processar e agrupar imagens do catálogo
function buildTentCatalog() {
    const catalogMap = new Map();
    
    ALL_TENT_IMAGES.forEach(imageData => {
        const parsed = parseTentDimensions(imageData);
        if (!parsed) {
            console.warn(`Não foi possível processar: ${imageData.filename || imageData}`);
            return;
        }
        
        const baseId = parsed.baseId;
        const imagePath = imageData.fullPath || `assets/images/catalogo/${imageData.filename || imageData}`;
        
        if (!catalogMap.has(baseId)) {
            // Calcular área (fixa para MANDALA, calculada para outros)
            const area = getTentArea(parsed.type, parsed.width, parsed.height, parsed.tendaCode);
            
            // Primeira imagem deste baseId - será a principal
            catalogMap.set(baseId, {
                baseId: baseId,
                id: baseId,
                width: parsed.width,
                height: parsed.height,
                area: area,
                tendaCode: parsed.tendaCode,
                tendaNumber: parsed.tendaNumber,
                type: parsed.type,
                canCalculatePrice: parsed.type !== 'MANDALA',
                mainImage: parsed.variation === null ? imagePath : null,
                variations: [],
                allImages: []
            });
        }
        
        const catalogItem = catalogMap.get(baseId);
        
        // Adicionar imagem à lista de todas as imagens
        catalogItem.allImages.push({
            filename: imageData.filename || imageData,
            image: imagePath,
            variation: parsed.variation
        });
        
        // Se não tem variação, é a imagem principal
        if (parsed.variation === null) {
            catalogItem.mainImage = imagePath;
        } else {
            // É uma variação
            catalogItem.variations.push({
                variation: parsed.variation,
                image: imagePath,
                filename: imageData.filename || imageData
            });
            // Ordenar variações
            catalogItem.variations.sort((a, b) => a.variation - b.variation);
        }
    });
    
    // Converter Map para Array e garantir que todas tenham mainImage
    let catalog = Array.from(catalogMap.values()).map(item => {
        // Se não tem mainImage definida, usar a primeira imagem sem variação ou a primeira variação
        if (!item.mainImage) {
            const firstImage = item.allImages.find(img => img.variation === null) || item.allImages[0];
            if (firstImage) {
                item.mainImage = firstImage.image;
            }
        }
        return item;
    });
    
    // Ordenar: primeiro por type (QUADRADAS, RETANGULAR, MANDALA), depois por tendaNumber
    const typeOrder = { 'QUADRADAS': 1, 'RETANGULAR': 2, 'MANDALA': 3 };
    catalog.sort((a, b) => {
        // Ordenar por tipo primeiro
        const typeA = typeOrder[a.type] || 999;
        const typeB = typeOrder[b.type] || 999;
        if (typeA !== typeB) {
            return typeA - typeB;
        }
        // Se mesmo tipo, ordenar por tendaNumber
        const numA = a.tendaNumber || 0;
        const numB = b.tendaNumber || 0;
        return numA - numB;
    });
    
    return catalog;
}

// Catálogo completo de tendas (agrupado por baseId)
const TENT_CATALOG = buildTentCatalog();

// ============================================
// Scroll Suave
// ============================================

document.addEventListener('DOMContentLoaded', function() {
    // Atualizar link do WhatsApp
    updateWhatsAppLink();
    
    // Configurar scroll suave para links internos
    setupSmoothScroll();
    
    // Configurar animações de entrada
    setupScrollAnimations();
    
    // Configurar botões de produtos
    setupProductButtons();
    
    // Configurar modal de imagem para mobile
    setupImageModal();
    
    // Inicializar carrinho
    initCart();
    
    // Inicializar botão de tenda como desabilitado
    const tentButton = document.getElementById('btn-add-tent');
    if (tentButton) {
        tentButton.disabled = true;
        tentButton.style.opacity = '0.5';
        tentButton.style.cursor = 'not-allowed';
    }
    
    // Configurar máscaras para campos do carrinho
    setupCartFormMasks();
});

// ============================================
// Máscaras para Campos do Carrinho
// ============================================

// Função para buscar CEP na API ViaCEP
function buscarCEP(cep) {
    // Remove formatação do CEP
    const cepLimpo = cep.replace(/\D/g, '');
    
    // Verifica se tem 8 dígitos
    if (cepLimpo.length !== 8) {
        return;
    }
    
    const addressInput = document.getElementById('client-address');
    if (!addressInput) return;
    
    // Mostrar indicador de carregamento
    addressInput.placeholder = 'Buscando endereço...';
    addressInput.disabled = true;
    addressInput.style.opacity = '0.6';
    
    // Fazer requisição à API ViaCEP
    fetch(`https://viacep.com.br/ws/${cepLimpo}/json/`)
        .then(response => response.json())
        .then(data => {
            addressInput.disabled = false;
            addressInput.style.opacity = '1';
            
            // Verificar se houve erro
            if (data.erro) {
                addressInput.placeholder = 'CEP não encontrado. Digite o endereço manualmente.';
                addressInput.value = '';
                return;
            }
            
            // Montar endereço completo
            const enderecoCompleto = [];
            
            if (data.logradouro) {
                enderecoCompleto.push(data.logradouro);
            }
            if (data.bairro) {
                enderecoCompleto.push(data.bairro);
            }
            if (data.localidade) {
                enderecoCompleto.push(data.localidade);
            }
            if (data.uf) {
                enderecoCompleto.push(data.uf);
            }
            
            // Pré-preencher o campo de endereço
            if (enderecoCompleto.length > 0) {
                addressInput.value = enderecoCompleto.join(', ');
                addressInput.placeholder = 'Rua, número, complemento, bairro, cidade, estado';
                
                // Focar no campo de endereço para o usuário adicionar número e complemento
                setTimeout(() => {
                    addressInput.focus();
                    // Posicionar cursor no início para facilitar adicionar número
                    addressInput.setSelectionRange(0, 0);
                }, 100);
            } else {
                addressInput.placeholder = 'CEP não encontrado. Digite o endereço manualmente.';
            }
        })
        .catch(error => {
            console.error('Erro ao buscar CEP:', error);
            addressInput.disabled = false;
            addressInput.style.opacity = '1';
            addressInput.placeholder = 'Erro ao buscar CEP. Digite o endereço manualmente.';
        });
}

function setupCartFormMasks() {
    // Máscara para CEP
    const cepInput = document.getElementById('client-cep');
    if (cepInput) {
        let lastCepValue = '';
        
        cepInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length <= 8) {
                value = value.replace(/^(\d{5})(\d)/, '$1-$2');
                e.target.value = value;
                
                // Buscar CEP quando tiver 8 dígitos completos
                const cepLimpo = value.replace(/\D/g, '');
                if (cepLimpo.length === 8 && cepLimpo !== lastCepValue) {
                    lastCepValue = cepLimpo;
                    buscarCEP(cepLimpo);
                }
            }
        });
        
        // Também buscar quando o campo perder o foco (caso o usuário cole o CEP)
        cepInput.addEventListener('blur', function(e) {
            const cepLimpo = e.target.value.replace(/\D/g, '');
            if (cepLimpo.length === 8 && cepLimpo !== lastCepValue) {
                lastCepValue = cepLimpo;
                buscarCEP(cepLimpo);
            }
        });
    }
    
    // Máscara para CPF ou CNPJ
    const documentInput = document.getElementById('client-document');
    if (documentInput) {
        documentInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            
            // Se tiver 11 dígitos ou menos, formatar como CPF
            if (value.length <= 11) {
                value = value.replace(/^(\d{3})(\d)/, '$1.$2');
                value = value.replace(/^(\d{3})\.(\d{3})(\d)/, '$1.$2.$3');
                value = value.replace(/\.(\d{3})(\d)/, '.$1-$2');
            } else {
                // Se tiver mais de 11 dígitos, formatar como CNPJ
                value = value.replace(/^(\d{2})(\d)/, '$1.$2');
                value = value.replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3');
                value = value.replace(/\.(\d{3})(\d)/, '.$1/$2');
                value = value.replace(/(\d{4})(\d)/, '$1-$2');
            }
            
            e.target.value = value;
        });
    }
}

// ============================================
// Atualizar Link do WhatsApp
// ============================================

function updateWhatsAppLink() {
    const whatsappLink = document.querySelector('.btn-whatsapp');
    if (whatsappLink && WHATSAPP_NUMBER) {
        const encodedMessage = encodeURIComponent(WHATSAPP_MESSAGE);
        whatsappLink.href = `https://wa.me/${WHATSAPP_NUMBER}?text=${encodedMessage}`;
    }
}

// ============================================
// Scroll Suave
// ============================================

function setupSmoothScroll() {
    // Links com hash (#) que apontam para seções da página
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            
            // Ignorar links vazios
            if (href === '#' || href === '') {
                return;
            }
            
            e.preventDefault();
            
            const targetId = href.substring(1);
            const targetElement = document.getElementById(targetId);
            
            if (targetElement) {
                const offsetTop = targetElement.offsetTop - 80; // Offset para header fixo (se houver)
                
                window.scrollTo({
                    top: offsetTop,
                    behavior: 'smooth'
                });
            }
        });
    });
}

// ============================================
// Animações de Entrada (Intersection Observer)
// ============================================

function setupScrollAnimations() {
    // Garantir que os produtos apareçam imediatamente
    document.querySelectorAll('.product-card').forEach(card => {
        card.classList.add('visible');
    });
    
    // Opções do Intersection Observer
    const observerOptions = {
        root: null,
        rootMargin: '0px',
        threshold: 0.1 // Elemento precisa estar 10% visível
    };
    
    // Callback do observer
    const observerCallback = (entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                // Opcional: parar de observar após animar (melhor performance)
                // observer.unobserve(entry.target);
            }
        });
    };
    
    // Criar observer
    const observer = new IntersectionObserver(observerCallback, observerOptions);
    
    // Observar cards de produtos (para animação adicional se necessário)
    document.querySelectorAll('.product-card').forEach(card => {
        observer.observe(card);
    });
    
    // Observar itens de engenharia
    document.querySelectorAll('.engenharia-item').forEach(item => {
        observer.observe(item);
    });
    
    // Observar itens de benefícios (sobre)
    document.querySelectorAll('.sobre-benefit-item').forEach(item => {
        observer.observe(item);
    });
    
    // Observar itens de serviços principais
    document.querySelectorAll('.servico-main-item').forEach(item => {
        observer.observe(item);
    });
}

// ============================================
// Botões de Produtos
// ============================================

function setupProductButtons() {
    document.querySelectorAll('.btn-secondary[data-product]').forEach(button => {
        button.addEventListener('click', function() {
            const product = this.getAttribute('data-product');
            handleProductClick(product);
        });
    });
}

function handleProductClick(product) {
    // Scroll para a seção de contato (footer)
    const contactSection = document.getElementById('contato');
    if (contactSection) {
        const offsetTop = contactSection.offsetTop - 80;
        
        window.scrollTo({
            top: offsetTop,
            behavior: 'smooth'
        });
        
        // Opcional: Adicionar mensagem personalizada baseada no produto
        setTimeout(() => {
            // Pode adicionar lógica aqui para personalizar a mensagem do WhatsApp
            // baseada no produto selecionado
        }, 1000);
    }
}

// ============================================
// Modal de Imagem em Tela Cheia (Mobile)
// ============================================

function setupImageModal() {
    // Verificar se está no mobile
    const isMobile = window.innerWidth <= 768;
    
    // Remover todos os listeners antigos primeiro
    document.querySelectorAll('.product-image img').forEach(img => {
        // Clonar a imagem para remover todos os event listeners
        const newImg = img.cloneNode(true);
        img.parentNode.replaceChild(newImg, img);
    });
    
    if (isMobile) {
        // Adicionar evento de clique nas imagens dos produtos
        document.querySelectorAll('.product-image img').forEach(img => {
            img.style.cursor = 'pointer';
            img.addEventListener('click', function(e) {
                e.stopPropagation();
                e.preventDefault();
                openImageModal(this.src, this.alt);
            });
        });
    } else {
        // No desktop, garantir que o cursor seja normal
        document.querySelectorAll('.product-image img').forEach(img => {
            img.style.cursor = 'default';
        });
    }
}

// Abrir modal de imagem
function openImageModal(imageSrc, imageAlt) {
    const modal = document.getElementById('imageModal');
    const modalImage = document.getElementById('modalImage');
    
    if (modal && modalImage) {
        modalImage.src = imageSrc;
        modalImage.alt = imageAlt || 'Imagem ampliada';
        modal.classList.add('active');
        document.body.style.overflow = 'hidden'; // Prevenir scroll do body
    }
}

// Fechar modal de imagem
function closeImageModal() {
    const modal = document.getElementById('imageModal');
    
    if (modal) {
        modal.classList.remove('active');
        document.body.style.overflow = ''; // Restaurar scroll do body
    }
}

// Tornar funções globais para uso em onclick
window.openImageModal = openImageModal;
window.closeImageModal = closeImageModal;

// Atualizar ao redimensionar a janela (para modal de imagem)
let imageModalResizeTimeout;
window.addEventListener('resize', function() {
    clearTimeout(imageModalResizeTimeout);
    imageModalResizeTimeout = setTimeout(function() {
        // Reconfigurar modal de imagem se necessário
        setupImageModal();
    }, 250);
});


// ============================================
// Melhorar Performance de Scroll
// ============================================

let ticking = false;

function onScroll() {
    if (!ticking) {
        window.requestAnimationFrame(function() {
            // Código de scroll aqui (parallax, etc)
            ticking = false;
        });
        ticking = true;
    }
}

// ============================================
// Detectar Redimensionamento da Janela
// ============================================

let resizeTimer;
window.addEventListener('resize', function() {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(function() {
        // Recalcular posições se necessário
        setupScrollAnimations();
    }, 250);
});

// ============================================
// Prevenir Flash de Conteúdo Não Estilizado (FOUC)
// ============================================

document.addEventListener('DOMContentLoaded', function() {
    document.body.classList.add('loaded');
});

// ============================================
// Adicionar Classe ao Body quando Scrollar
// ============================================

let lastScroll = 0;
window.addEventListener('scroll', function() {
    const currentScroll = window.pageYOffset;
    
    if (currentScroll > 100) {
        document.body.classList.add('scrolled');
    } else {
        document.body.classList.remove('scrolled');
    }
    
    lastScroll = currentScroll;
});

// ============================================
// Sistema de Carrinho
// ============================================

// Array para armazenar itens do carrinho
let cart = [];

// Inicializar carrinho
function initCart() {
    // Carregar carrinho do localStorage (se existir)
    const savedCart = localStorage.getItem('psikeCart');
    if (savedCart) {
        try {
            cart = JSON.parse(savedCart);
            // Garantir que itens antigos tenham a propriedade observations e valores corretos para MANDALA
            cart = cart.map(item => {
                if (!item.hasOwnProperty('observations')) {
                    item.observations = '';
                }
                // Corrigir área e preço para MANDALA se necessário
                if (item.isTent && item.type === 'MANDALA' && item.tendaCode && item.width && item.height) {
                    const key = `${item.width}x${item.height}_${item.tendaCode}`;
                    if (MANDALA_FIXED_AREAS[key]) {
                        item.area = MANDALA_FIXED_AREAS[key];
                    }
                    if (MANDALA_FIXED_PRICES[key]) {
                        item.price = MANDALA_FIXED_PRICES[key];
                    }
                    // Atualizar nome com área correta
                    if (item.area) {
                        item.name = `Tenda ${item.width}m × ${item.height}m (${item.area.toFixed(0)}m²)`;
                    }
                }
                return item;
            });
            saveCart(); // Salvar com a nova estrutura
            updateCartUI();
        } catch (e) {
            console.error('Erro ao carregar carrinho:', e);
            cart = [];
        }
    }
    
    // Botão de carrinho agora é um link, não precisa de evento
    
    // Configurar botões "Adicionar ao Carrinho"
    document.querySelectorAll('.btn-add-cart, .btn-primary-large').forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.getAttribute('data-product-id');
            const productName = this.getAttribute('data-product-name');
            const productImage = this.getAttribute('data-product-image');
            const productSpecs = this.getAttribute('data-product-specs');
            
            // Se for tenda, capturar dimensões e calcular preço
            if (productId === 'tendas') {
                // Tentar pegar da página de detalhes primeiro, depois da página de produtos
                const heightInput = document.getElementById('tent-height-large') || document.getElementById('tent-height');
                const widthInput = document.getElementById('tent-width-large') || document.getElementById('tent-width');
                
                if (!heightInput || !widthInput) {
                    alert('Por favor, informe as dimensões da tenda (altura e largura).');
                    return;
                }
                
                const height = parseFloat(heightInput.value) || 0;
                const width = parseFloat(widthInput.value) || 0;
                
                if (height <= 0 || width <= 0) {
                    alert('Por favor, informe dimensões válidas (maior que zero).');
                    return;
                }
                
                // Buscar tenda do catálogo para obter informações completas
                const urlParams = new URLSearchParams(window.location.search);
                const tentId = urlParams.get('id');
                let tendaCode = null;
                let tentType = null;
                let variation = null;
                let currentImagePath = productImage;
                let finalAreaForCart = calculateArea(height, width);
                let finalPrice = finalAreaForCart * PRICE_PER_SQUARE_METER;
                
                if (tentId && typeof TENT_CATALOG !== 'undefined') {
                    const tent = TENT_CATALOG.find(t => t.baseId === tentId);
                    if (tent) {
                        if (tent.tendaCode) {
                            tendaCode = tent.tendaCode;
                        }
                        if (tent.type) {
                            tentType = tent.type;
                        }
                        // Usar área do catálogo se disponível (fixa para MANDALA)
                        if (tent.area) {
                            finalAreaForCart = tent.area;
                        }
                        // Se for MANDALA, usar preço fixo
                        if (tentType === 'MANDALA' && tendaCode) {
                            const fixedPrice = getMandalaFixedPrice(tendaCode, tent.width, tent.height);
                            if (fixedPrice) {
                                finalPrice = fixedPrice;
                            }
                        }
                        // Capturar imagem e variação atual do carrossel (se disponível)
                        if (typeof window.currentCarouselIndex !== 'undefined' && typeof window.carouselImages !== 'undefined' && window.carouselImages.length > 0) {
                            const currentIndex = window.currentCarouselIndex;
                            const currentImage = window.carouselImages[currentIndex];
                            if (currentImage && currentImage.image) {
                                currentImagePath = currentImage.image;
                            }
                            if (currentImage && currentImage.variation !== null && currentImage.variation !== undefined) {
                                variation = currentImage.variation;
                            }
                        }
                    }
                }
                
                // Criar nome com dimensões e área correta
                const tentName = `Tenda ${width}m × ${height}m (${finalAreaForCart.toFixed(0)}m²)`;
                
                addToCart({
                    id: productId,
                    name: tentName,
                    image: currentImagePath,
                    specs: productSpecs,
                    height: height,
                    width: width,
                    area: finalAreaForCart,
                    price: finalPrice,
                    isTent: true,
                    tendaCode: tendaCode,
                    type: tentType,
                    variation: variation
                });
                
                // Redirecionar para carrinho após adicionar
                setTimeout(() => {
                    window.location.href = 'carrinho.html';
                }, 500);
            } else {
                addToCart({
                    id: productId,
                    name: productName,
                    image: productImage,
                    specs: productSpecs
                });
            }
        });
    });
    
    // Configurar botão enviar para WhatsApp
    const sendButton = document.getElementById('sendCartToWhatsApp');
    if (sendButton) {
        sendButton.addEventListener('click', sendCartToWhatsApp);
    }
}

// Adicionar item ao carrinho
function addToCart(product) {
    // Se for tenda, criar ID único baseado nas dimensões
    let itemId = product.id;
    if (product.isTent && product.height && product.width) {
        // Criar ID único para cada combinação de dimensões
        itemId = `tenda-${product.width}x${product.height}-${Date.now()}`;
    } else {
        // Para outros produtos, verificar se já existe
        const existingItem = cart.find(item => item.id === product.id);
        if (existingItem) {
            existingItem.quantity += 1;
            saveCart();
            updateCartUI();
            
            // Feedback visual
            const button = document.querySelector(`[data-product-id="${product.id}"]`);
            if (button) {
                const originalText = button.textContent;
                button.textContent = ' Adicionado!';
                button.style.backgroundColor = '#00cc6a';
                setTimeout(() => {
                    button.textContent = originalText;
                    button.style.backgroundColor = '';
                }, 1500);
            }
            return;
        }
    }
    
    // Criar novo item (sempre para tendas, ou se não existir para outros produtos)
    const newItem = {
        id: itemId,
        name: product.name,
        image: product.image,
        specs: product.specs || '',
        quantity: 1,
        price: product.price || 'Sob consulta',
        observations: '', // Observações específicas deste produto
        isTent: product.isTent || false
    };
    
    // Se for tenda, adicionar dimensões, código e variação
    if (product.isTent && product.height && product.width) {
        newItem.height = product.height;
        newItem.width = product.width;
        
        // Para MANDALA, garantir que área e preço sejam os fixos
        let finalArea = product.area;
        let finalPrice = product.price;
        
        if (product.type === 'MANDALA' && product.tendaCode) {
            // Buscar área fixa
            const key = `${product.width}x${product.height}_${product.tendaCode}`;
            if (MANDALA_FIXED_AREAS[key]) {
                finalArea = MANDALA_FIXED_AREAS[key];
            }
            // Buscar preço fixo
            if (MANDALA_FIXED_PRICES[key]) {
                finalPrice = MANDALA_FIXED_PRICES[key];
            }
        }
        
        newItem.area = finalArea;
        newItem.price = finalPrice;
        
        // Atualizar nome para incluir dimensões e área
        newItem.name = `Tenda ${product.width}m × ${product.height}m (${finalArea.toFixed(0)}m²)`;
        
        // Adicionar código da tenda se disponível
        if (product.tendaCode) {
            newItem.tendaCode = product.tendaCode;
        }
        // Adicionar tipo da tenda se disponível
        if (product.type) {
            newItem.type = product.type;
        }
        // Adicionar variação se disponível
        if (product.variation !== null && product.variation !== undefined) {
            newItem.variation = product.variation;
        }
    }
    
    cart.push(newItem);
    saveCart();
    updateCartUI();
    
    // Feedback visual
    const button = document.querySelector(`[data-product-id="${product.id}"]`);
    if (button) {
        const originalText = button.textContent;
        button.textContent = ' Adicionado!';
        button.style.backgroundColor = '#00cc6a';
        setTimeout(() => {
            button.textContent = originalText;
            button.style.backgroundColor = '';
        }, 1500);
    }
}

// Remover item do carrinho (global para onclick)
window.removeFromCart = function(productId) {
    cart = cart.filter(item => item.id !== productId);
    saveCart();
    updateCartUI();
};

// Atualizar quantidade de um item (global para onclick)
window.updateQuantity = function(productId, newQuantity) {
    // Converter para número inteiro
    newQuantity = parseInt(newQuantity);
    
    if (isNaN(newQuantity) || newQuantity <= 0) {
        window.removeFromCart(productId);
        return;
    }
    
    const item = cart.find(item => item.id === productId);
    if (!item) {
        console.error('Item não encontrado no carrinho:', productId);
        return;
    }
    
    // Atualizar quantidade no objeto
    item.quantity = newQuantity;
    saveCart();
    
    // Buscar elemento apenas dentro do container do carrinho
    const cartItemsContainer = document.getElementById('cartItems');
    if (!cartItemsContainer) {
        // Se não estiver na página do carrinho, apenas atualizar badge
        const badge = document.getElementById('cartBadge');
        if (badge) {
            const total = getCartTotal();
            badge.textContent = total > 0 ? total : '';
        }
        return;
    }
    
    // Buscar o item específico dentro do container do carrinho
    // Tentar múltiplas formas de encontrar o elemento
    let cartItemElement = cartItemsContainer.querySelector(`[data-product-id="${productId}"]`);
    if (!cartItemElement) {
        // Tentar com escape CSS
        try {
            cartItemElement = cartItemsContainer.querySelector(`[data-product-id="${CSS.escape(productId)}"]`);
        } catch (e) {
            // Fallback: buscar todos os itens e comparar
            const allItems = cartItemsContainer.querySelectorAll('.cart-item');
            allItems.forEach(el => {
                if (el.getAttribute('data-product-id') === productId) {
                    cartItemElement = el;
                }
            });
        }
    }
    
    if (cartItemElement) {
        // Atualizar quantidade exibida
        const quantityElement = cartItemElement.querySelector('.cart-quantity-value');
        if (quantityElement) {
            quantityElement.textContent = newQuantity;
        }
        
        // Atualizar os botões + e - com os novos valores
        const quantityButtons = cartItemElement.querySelectorAll('.cart-quantity-btn');
        const escapedId = productId.replace(/'/g, "\\'").replace(/"/g, '&quot;');
        
        if (quantityButtons.length >= 2) {
            // Botão menos (primeiro)
            quantityButtons[0].setAttribute('onclick', `updateQuantity('${escapedId}', ${newQuantity - 1})`);
            // Botão mais (último)
            quantityButtons[quantityButtons.length - 1].setAttribute('onclick', `updateQuantity('${escapedId}', ${newQuantity + 1})`);
        }
        
        // Atualizar preço total do item se for tenda
        if (item.isTent && item.price) {
            const itemTotalPrice = item.price * newQuantity;
            const priceTotalElement = cartItemElement.querySelector('.cart-item-price-total .cart-item-price-value');
            if (priceTotalElement) {
                priceTotalElement.textContent = formatPrice(itemTotalPrice);
            }
        }
    }
    
    // Atualizar totais no resumo
    const totalItems = document.getElementById('cartTotalItems');
    if (totalItems) {
        totalItems.textContent = getCartTotal();
    }
    
    const totalPrice = document.getElementById('cartTotalPrice');
    if (totalPrice) {
        const total = getCartTotalPrice();
        if (total > 0) {
            totalPrice.textContent = formatPrice(total);
        } else {
            totalPrice.textContent = 'Sob consulta';
        }
    }
    
    // Atualizar badge do carrinho
    const badge = document.getElementById('cartBadge');
    if (badge) {
        const total = getCartTotal();
        badge.textContent = total > 0 ? total : '';
    }
};

// Atualizar observações de um item (global para uso)
window.updateItemObservations = function(productId, observations) {
    const item = cart.find(item => item.id === productId);
    if (item) {
        item.observations = observations;
        saveCart();
    }
};

// Calcular total de itens
function getCartTotal() {
    return cart.reduce((total, item) => total + item.quantity, 0);
}

// Calcular total do pedido em reais
function getCartTotalPrice() {
    return cart.reduce((total, item) => {
        if (item.isTent && item.price) {
            return total + (item.price * item.quantity);
        }
        return total;
    }, 0);
}

// Salvar carrinho no localStorage
function saveCart() {
    localStorage.setItem('psikeCart', JSON.stringify(cart));
}

// Atualizar interface do carrinho
function updateCartUI() {
    // Atualizar badge do contador (se existir)
    const badge = document.getElementById('cartBadge');
    const total = getCartTotal();
    if (badge) {
        badge.textContent = total > 0 ? total : '';
    }
    
    // Atualizar lista de itens (página do carrinho)
    const cartItems = document.getElementById('cartItems');
    const cartEmpty = document.getElementById('cartEmpty');
    const cartSummary = document.getElementById('cartSummary');
    const cartObservationsSection = document.getElementById('cartObservationsSection');
    const cartSidebar = document.getElementById('cartSidebar');
    const sendButton = document.getElementById('sendCartToWhatsApp');
    
    if (cart.length === 0) {
        // Carrinho vazio
        if (cartEmpty) cartEmpty.style.display = 'flex';
        if (cartSummary) cartSummary.style.display = 'none';
        if (cartObservationsSection) cartObservationsSection.style.display = 'none';
        if (cartSidebar) cartSidebar.style.display = 'none';
        if (sendButton) sendButton.style.display = 'none';
        
        // Limpar lista de itens (exceto mensagem vazia)
        if (cartItems) {
            const items = cartItems.querySelectorAll('.cart-item');
            items.forEach(item => item.remove());
        }
    } else {
        // Carrinho com itens
        if (cartEmpty) cartEmpty.style.display = 'none';
        if (cartSummary) cartSummary.style.display = 'block';
        if (cartObservationsSection) cartObservationsSection.style.display = 'none'; // Removido campo geral
        if (cartSidebar) cartSidebar.style.display = 'block';
        if (sendButton) sendButton.style.display = 'flex';
        
        // Renderizar itens
        if (cartItems) {
            // Remover itens antigos (exceto mensagem vazia)
            const existingItems = cartItems.querySelectorAll('.cart-item');
            existingItems.forEach(item => item.remove());
            
            // Adicionar novos itens
            cart.forEach(item => {
                const cartItem = createCartItemElement(item);
                cartItems.appendChild(cartItem);
            });
        }
        
        // Atualizar resumo
        const totalItems = document.getElementById('cartTotalItems');
        if (totalItems) {
            totalItems.textContent = getCartTotal();
        }
        
        // Atualizar valor total do pedido
        const totalPrice = document.getElementById('cartTotalPrice');
        if (totalPrice) {
            const total = getCartTotalPrice();
            if (total > 0) {
                totalPrice.textContent = formatPrice(total);
            } else {
                totalPrice.textContent = 'Sob consulta';
            }
        }
    }
}

// Criar elemento HTML de item do carrinho
function createCartItemElement(item) {
    const div = document.createElement('div');
    div.className = 'cart-item';
    div.setAttribute('data-product-id', item.id);
    
    const safeId = item.id.replace(/[^a-zA-Z0-9-]/g, '-');
    const observationsId = `observations-${safeId}`;
    const escapedObservations = (item.observations || '').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
    const escapedItemId = item.id.replace(/'/g, "\\'").replace(/"/g, '&quot;');
    
    // Informações de dimensões e preço para tendas
    let dimensionsInfo = '';
    let priceInfo = '';
    
    if (item.isTent && item.height && item.width && item.area && item.price) {
        const itemTotalPrice = item.price * item.quantity;
        // Formatar área com vírgula como separador decimal
        const areaFormatted = item.area.toFixed(2).replace('.', ',');
        dimensionsInfo = `
            <div class="cart-item-dimensions">
                <p class="cart-item-dimensions-text">
                    <strong>Área:</strong> ${areaFormatted}m²
                </p>
            </div>
        `;
        priceInfo = `
            <div class="cart-item-price">
                <span class="cart-item-price-label">Preço unitário:</span>
                <span class="cart-item-price-value">${formatPrice(item.price)}</span>
            </div>
            <div class="cart-item-price-total">
                <span class="cart-item-price-label">Preço total:</span>
                <span class="cart-item-price-value">${formatPrice(itemTotalPrice)}</span>
            </div>
        `;
    }
    
    div.innerHTML = `
        <img src="${item.image}" alt="${item.name}" class="cart-item-image">
        <div class="cart-item-content">
            <h4 class="cart-item-name">${item.name}</h4>
            <p class="cart-item-specs">${item.specs || ''}</p>
            ${dimensionsInfo}
            ${priceInfo}
            <div class="cart-item-controls">
                <div class="cart-item-quantity">
                    <button class="cart-quantity-btn" onclick="updateQuantity('${escapedItemId}', ${item.quantity - 1})">-</button>
                    <span class="cart-quantity-value">${item.quantity}</span>
                    <button class="cart-quantity-btn" onclick="updateQuantity('${escapedItemId}', ${item.quantity + 1})">+</button>
                </div>
                <button class="cart-item-remove" onclick="removeFromCart('${escapedItemId}')" aria-label="Remover item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>
            <div class="cart-item-observations">
                <label for="${observationsId}" class="cart-item-observations-label">Personalizações desejadas para este pedido:</label>
                <textarea 
                    id="${observationsId}" 
                    class="cart-item-observations-field" 
                    placeholder="Cores desejadas, medidas personalizadas ou outras especificações..."
                    rows="4"
                    oninput="updateItemObservations('${escapedItemId}', this.value)"
                >${escapedObservations}</textarea>
            </div>
        </div>
    `;
    
    return div;
}

// Formatar número com separador de milhares
function formatNumber(value) {
    return value.toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.');
}

// Gerar mensagem formatada para WhatsApp
function generateWhatsAppMessage(clientData) {
    let message = '*PEDIDO - PSIKÉ DELOUN ARTS*\n\n';
    
    // Calcular total do pedido
    let totalPrice = 0;
    
    cart.forEach((item, index) => {
        message += `*${index + 1}. ${item.name}*\n`;
        message += `Qtd: ${item.quantity}x\n\n`;
        
        // Adicionar imagem do produto (URL completa)
        if (item.image) {
            // Construir URL completa da imagem
            let imageUrl = item.image;
            
            // Se não for URL absoluta, converter para absoluta
            if (!imageUrl.startsWith('http://') && !imageUrl.startsWith('https://')) {
                // Remover barra inicial se houver
                if (imageUrl.startsWith('/')) {
                    imageUrl = imageUrl.substring(1);
                }
                // Construir URL base (diretório raiz do site)
                const baseUrl = window.location.origin;
                // Se estiver em subdiretório, adicionar ao path
                const pathParts = window.location.pathname.split('/').filter(p => p && !p.endsWith('.html'));
                const basePath = pathParts.length > 0 ? '/' + pathParts.join('/') + '/' : '/';
                imageUrl = baseUrl + basePath + imageUrl;
            }
            message += `*Imagem:*\n${imageUrl}\n\n`;
        }
        
        // Se for tenda, adicionar dimensões, código, variação e preço
        if (item.isTent && item.height && item.width && item.area && item.price) {
            const itemTotal = item.price * item.quantity;
            totalPrice += itemTotal;
            message += `Dimensões: ${item.height}m × ${item.width}m\n`;
            message += `Área: ${item.area.toFixed(2)}m²\n`;
            if (item.tendaCode) {
                const typeText = item.type ? ` (${item.type})` : '';
                message += `Código: ${item.tendaCode}${typeText}\n`;
            }
            if (item.variation !== null && item.variation !== undefined) {
                message += `Variação: ${item.variation}\n`;
            }
            message += `Preço unitário: R$ ${formatNumber(item.price)}\n`;
            message += `Preço total: R$ ${formatNumber(itemTotal)}\n\n`;
        }
        
        if (item.observations && item.observations.trim()) {
            message += `*Observações:*\n${item.observations.trim()}\n\n`;
        }
    });
    
    message += '━━━━━━━━━━━━━━━━━━━━\n\n';
    
    if (totalPrice > 0) {
        message += `*TOTAL: R$ ${formatNumber(totalPrice)}*\n\n`;
    }
    
    message += `Total de itens: ${getCartTotal()}\n\n`;
    message += '━━━━━━━━━━━━━━━━━━━━\n\n';
    message += '*DADOS PARA FRETE:*\n\n';
    
    if (clientData) {
        message += `*Nome Completo:*\n${clientData.name}\n\n`;
        message += `*CEP:*\n${clientData.cep}\n\n`;
        message += `*Endereço Completo:*\n${clientData.address}\n\n`;
        message += `*CPF ou CNPJ:*\n${clientData.document}\n\n`;
    }
    
    message += 'Aguardo retorno.';
    
    return message;
}

// Enviar carrinho para WhatsApp
function sendCartToWhatsApp() {
    if (cart.length === 0) {
        alert('Seu carrinho está vazio!');
        return;
    }
    
    // Validar campos obrigatórios
    const clientName = document.getElementById('client-name');
    const clientCep = document.getElementById('client-cep');
    const clientAddress = document.getElementById('client-address');
    const clientDocument = document.getElementById('client-document');
    
    if (!clientName || !clientCep || !clientAddress || !clientDocument) {
        alert('Erro: Campos de dados do cliente não encontrados.');
        return;
    }
    
    // Remover espaços em branco
    const name = clientName.value.trim();
    const cep = clientCep.value.trim();
    const address = clientAddress.value.trim();
    const documentValue = clientDocument.value.trim();
    
    // Validar campos preenchidos
    if (!name) {
        alert('Por favor, preencha o nome completo.');
        clientName.focus();
        return;
    }
    
    if (!cep) {
        alert('Por favor, preencha o CEP.');
        clientCep.focus();
        return;
    }
    
    if (!address) {
        alert('Por favor, preencha o endereço completo.');
        clientAddress.focus();
        return;
    }
    
    if (!documentValue) {
        alert('Por favor, preencha o CPF ou CNPJ.');
        clientDocument.focus();
        return;
    }
    
    // Preparar dados do cliente
    const clientData = {
        name: name,
        cep: cep,
        address: address,
        document: documentValue
    };
    
    // Gerar mensagem com dados do cliente
    const message = generateWhatsAppMessage(clientData);
    const encodedMessage = encodeURIComponent(message);
    const whatsappUrl = `https://wa.me/${WHATSAPP_NUMBER}?text=${encodedMessage}`;
    
    // Abrir WhatsApp
    window.open(whatsappUrl, '_blank');
    
    // Limpar carrinho após enviar
    cart = [];
    saveCart();
    updateCartUI();
    
    // Limpar campos do formulário
    clientName.value = '';
    clientCep.value = '';
    clientAddress.value = '';
    clientDocument.value = '';
}

// ============================================
// Sistema de Cálculo de Preço para Tendas
// ============================================

// Calcular área em metros quadrados
function calculateArea(height, width) {
    if (!height || !width || height <= 0 || width <= 0) {
        return 0;
    }
    return parseFloat(height) * parseFloat(width);
}

// Calcular preço da tenda
function calculateTentPrice(productId) {
    const heightInput = document.getElementById('tent-height');
    const widthInput = document.getElementById('tent-width');
    const priceDisplay = document.getElementById('tent-price-value');
    const addButton = document.getElementById('btn-add-tent');
    
    if (!heightInput || !widthInput || !priceDisplay) {
        return 0;
    }
    
    const height = parseFloat(heightInput.value) || 0;
    const width = parseFloat(widthInput.value) || 0;
    
    const area = calculateArea(height, width);
    const price = area * PRICE_PER_SQUARE_METER;
    
    // Atualizar display do preço
    priceDisplay.textContent = formatPrice(price);
    
    // Habilitar/desabilitar botão baseado nas dimensões
    if (addButton) {
        if (height > 0 && width > 0) {
            addButton.disabled = false;
            addButton.style.opacity = '1';
            addButton.style.cursor = 'pointer';
        } else {
            addButton.disabled = true;
            addButton.style.opacity = '0.5';
            addButton.style.cursor = 'not-allowed';
        }
    }
    
    return price;
}

// Formatar preço em Real brasileiro
function formatPrice(price) {
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(price);
}

// Função global para ser chamada via oninput
window.calculateTentPrice = function(productId) {
    calculateTentPrice(productId);
};

// ============================================
// Gerar Cards de Produtos Dinamicamente
// ============================================

// Função para criar card de produto
function createProductCard(tent) {
    const image = tent.mainImage || (tent.allImages && tent.allImages[0] ? tent.allImages[0].image : '');
    const canCalculate = tent.canCalculatePrice !== false; // Default true se não especificado
    
    // Calcular área (fixa para MANDALA, calculada para outros)
    const tentArea = tent.area || (tent.width * tent.height);
    
    // Título diferente para MANDALA
    let title;
    if (canCalculate) {
        title = `Tenda ${tent.width}m × ${tent.height}m (${tentArea.toFixed(0)}m²)`;
    } else {
        title = `Tenda ${tent.width}m × ${tent.height}m (${tentArea.toFixed(0)}m²)`;
    }
    
    return `
        <article class="product-card">
            <div class="product-image">
                <img src="${image}" alt="Tenda ${tent.width}m x ${tent.height}m" loading="lazy">
            </div>
            <div class="product-content">
                <h3 class="product-title">${title}</h3>
                ${!canCalculate ? '<p class="product-price-note" style="color: var(--color-accent-primary); font-size: 0.9rem; margin-top: 0.5rem;">Sob consulta</p>' : ''}
                <div class="product-actions">
                    <a href="tenda.html?w=${tent.width}&h=${tent.height}&id=${tent.baseId}" class="btn-secondary">Fazer orçamento </a>
                </div>
            </div>
        </article>
    `;
}

// Função para renderizar catálogo na página de produtos
function renderProductCatalog() {
    const catalogGrid = document.getElementById('catalogGrid');
    if (!catalogGrid || !TENT_CATALOG) return;
    
    // Limpar grid
    catalogGrid.innerHTML = '';
    
    // Agrupar tendas por tipo
    const tentsByType = {
        'QUADRADAS': [],
        'RETANGULAR': [],
        'MANDALA': []
    };
    
    TENT_CATALOG.forEach(tent => {
        if (tent.type && tentsByType[tent.type]) {
            tentsByType[tent.type].push(tent);
        }
    });
    
    // Nomes das seções
    const sectionNames = {
        'QUADRADAS': 'Tendas Quadradas',
        'RETANGULAR': 'Tendas Retangulares',
        'MANDALA': 'Tendas Mandala'
    };
    
    // Renderizar cada seção
    Object.keys(tentsByType).forEach(type => {
        const tents = tentsByType[type];
        if (tents.length === 0) return;
        
        // Criar container da seção
        const sectionHTML = `
            <div class="catalog-section" data-type="${type}">
                <h3 class="catalog-section-title">${sectionNames[type]}</h3>
                <div class="catalog-section-grid">
                    ${tents.map(tent => createProductCard(tent)).join('')}
                </div>
            </div>
        `;
        
        catalogGrid.insertAdjacentHTML('beforeend', sectionHTML);
    });
    
    // Adicionar animações aos novos cards
    setupScrollAnimations();
}

// Tornar função global
window.renderProductCatalog = renderProductCatalog;

// ============================================
// Log de Debug (remover em produção)
// ============================================

if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
    console.log('Psiké Deloun Arts - Landing Page carregada');
    console.log('WhatsApp configurado para:', WHATSAPP_NUMBER);
}
