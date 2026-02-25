// ============================================
// Configuração
// ============================================

// Número do WhatsApp - ALTERE AQUI
const WHATSAPP_NUMBER = '556282198202'; // Formato: 556282198202 (sem espaços ou caracteres especiais)
const WHATSAPP_MESSAGE = 'Olá! Gostaria de saber mais sobre as estruturas da Psiké Deloun Arts.';

// Preço por metro quadrado para tendas
const PRICE_PER_SQUARE_METER = 22.00; // R$ 22,00 por m²

// ============================================
// Catálogo de Tendas
// ============================================

// Função para extrair medidas e código da tenda do nome do arquivo
function parseTentDimensions(filename) {
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
        const tendaCode = `tenda${match1[3]}`;
        const variation = match1[4] ? parseInt(match1[4]) : null;
        const baseId = `tenda-${width}x${height}-${tendaCode}`;
        const id = variation ? `${baseId}-${variation}` : baseId;
        
        return {
            width: width,
            height: height,
            tendaCode: tendaCode,
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
        const baseId = `tenda-${width}x${height}`;
        const id = variation ? `${baseId}-${variation}` : baseId;
        
        return {
            width: width,
            height: height,
            tendaCode: null, // Sem código da tenda no formato antigo
            variation: variation,
            baseId: baseId,
            id: id
        };
    }
    
    return null;
}

// Lista de todas as imagens do catálogo
const TENT_IMAGES = [
    '4x20_tenda8.png',
    '4X20_tenda8(1).png',
    '4x20_tenda8(2).png',
    '4x20_tenda8(3).png',
    '6x11_tenda12.png',
    '7x25_tenda11.png',
    '7X25_tenda11(1).png',
    '8X25_tenda9.png',
    '8x25_tenda9(1).png',
    '8x25_tenda10.png',
    '8X25_tenda10(1).png',
    '8x25_tenda10(2).png',
    '10x10_ tenda3.png',
    '10x10_tenda2.png',
    '10x10_tenda3.png',
    '10x10_tenda3(1).png',
    '10x10_tenda3(2).png',
    '10x10_tenda4.png',
    '10x10_tenda4(1).png',
    '10x12_tenda5.png',
    '10x12_tenda5(1).png',
    '10x30_tenda14.png',
    '10x30_tenda16.png',
    '11x11_tenda1.png',
    '11x11_tenda21.png',
    '11x14_tenda7.png',
    '12x20_tenda15.png',
    '12x30_tenda16.png',
    '12x50_tenda13.png',
    '13x13_tenda1.png',
    '14x14_tenda22.png',
    '14x14_tenda22(1).png',
    '14x14_tenda23.png',
    '14x14_tendas22.png',
    '15X20_tenda18.png',
    '15x50_tenda17.png',
    '20x20_tenda6.png',
    '20X20_tenda6(1).png',
    '20x20_tenda6(2).png',
    '20x20_tenda6(3).png',
    '20x20_tenda6(4).png',
    '20x40_tenda19.png',
    '20X40_tenda20.png',
    '20x40_tenda20(1).png',
    '21x21_tenda23.png',
    '23x23_tenda25.png',
    '23x23_tenda25(1).png',
    '24x24_tenda24.png',
    '26x26_tenda25.png',
    '26X26_tenda25(1).png',
    '26x26_tenda25(2).png',
    '8x8_tenda1.png'
];

// Função para processar e agrupar imagens do catálogo
function buildTentCatalog() {
    const catalogMap = new Map();
    
    TENT_IMAGES.forEach(filename => {
        const parsed = parseTentDimensions(filename);
        if (!parsed) {
            console.warn(`Não foi possível processar: ${filename}`);
            return;
        }
        
        const baseId = parsed.baseId;
        const imagePath = `assets/images/catalogo/${filename}`;
        
        if (!catalogMap.has(baseId)) {
            // Primeira imagem deste baseId - será a principal
            catalogMap.set(baseId, {
                baseId: baseId,
                id: baseId,
                width: parsed.width,
                height: parsed.height,
                tendaCode: parsed.tendaCode,
                mainImage: parsed.variation === null ? imagePath : null,
                variations: [],
                allImages: []
            });
        }
        
        const catalogItem = catalogMap.get(baseId);
        
        // Adicionar imagem à lista de todas as imagens
        catalogItem.allImages.push({
            filename: filename,
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
                filename: filename
            });
            // Ordenar variações
            catalogItem.variations.sort((a, b) => a.variation - b.variation);
        }
    });
    
    // Converter Map para Array e garantir que todas tenham mainImage
    const catalog = Array.from(catalogMap.values()).map(item => {
        // Se não tem mainImage definida, usar a primeira imagem sem variação ou a primeira variação
        if (!item.mainImage) {
            const firstImage = item.allImages.find(img => img.variation === null) || item.allImages[0];
            if (firstImage) {
                item.mainImage = firstImage.image;
            }
        }
        return item;
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

function setupCartFormMasks() {
    // Máscara para CEP
    const cepInput = document.getElementById('client-cep');
    if (cepInput) {
        cepInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length <= 8) {
                value = value.replace(/^(\d{5})(\d)/, '$1-$2');
                e.target.value = value;
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
            // Garantir que itens antigos tenham a propriedade observations
            cart = cart.map(item => {
                if (!item.hasOwnProperty('observations')) {
                    item.observations = '';
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
                
                const area = calculateArea(height, width);
                const price = area * PRICE_PER_SQUARE_METER;
                
                // Criar nome com dimensões
                const tentName = `Tenda ${width}m × ${height}m`;
                
                // Buscar tendaCode e variação do catálogo se houver ID na URL
                let tendaCode = null;
                let variation = null;
                let currentImagePath = productImage; // Usar imagem padrão do botão
                
                const urlParams = new URLSearchParams(window.location.search);
                const tentId = urlParams.get('id');
                if (tentId && typeof TENT_CATALOG !== 'undefined') {
                    const tent = TENT_CATALOG.find(t => t.baseId === tentId);
                    if (tent) {
                        if (tent.tendaCode) {
                            tendaCode = tent.tendaCode;
                        }
                        // Capturar imagem e variação atual do carrossel (se disponível)
                        if (typeof window.currentCarouselIndex !== 'undefined' && typeof window.carouselImages !== 'undefined' && window.carouselImages.length > 0) {
                            const currentIndex = window.currentCarouselIndex;
                            const currentImage = window.carouselImages[currentIndex];
                            // Usar a imagem atual do carrossel
                            if (currentImage && currentImage.image) {
                                currentImagePath = currentImage.image;
                            }
                            // Se a imagem atual tem uma variação definida, usar ela
                            if (currentImage && currentImage.variation !== null && currentImage.variation !== undefined) {
                                variation = currentImage.variation;
                            }
                        }
                    }
                }
                
                addToCart({
                    id: productId,
                    name: tentName,
                    image: currentImagePath, // Usar imagem atual do carrossel
                    specs: productSpecs,
                    height: height,
                    width: width,
                    area: area,
                    price: price,
                    isTent: true,
                    tendaCode: tendaCode,
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
        newItem.area = product.area;
        newItem.price = product.price;
        // Atualizar nome para incluir dimensões
        newItem.name = `Tenda ${product.width}m × ${product.height}m`;
        // Adicionar código da tenda se disponível
        if (product.tendaCode) {
            newItem.tendaCode = product.tendaCode;
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
        dimensionsInfo = `
            <div class="cart-item-dimensions">
                <p class="cart-item-dimensions-text">
                    <strong>Dimensões:</strong> ${item.height}m × ${item.width}m = ${item.area.toFixed(2)}m²
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
                message += `Código: ${item.tendaCode}\n`;
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
    return `
        <article class="product-card">
            <div class="product-image">
                <img src="${image}" alt="Tenda ${tent.width}m x ${tent.height}m" loading="lazy">
            </div>
            <div class="product-content">
                <h3 class="product-title">Tenda ${tent.width}m × ${tent.height}m</h3>
                <div class="product-actions">
                    <a href="tenda.html?w=${tent.width}&h=${tent.height}&id=${tent.baseId}" class="btn-secondary">Fazer orçamento completo</a>
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
    
    // Criar cards para cada tenda
    TENT_CATALOG.forEach(tent => {
        const cardHTML = createProductCard(tent);
        catalogGrid.insertAdjacentHTML('beforeend', cardHTML);
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
