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

// Função para extrair medidas do nome do arquivo
function parseTentDimensions(filename) {
    // Remove extensão .png
    let name = filename.replace(/\.png$/i, '');
    
    // Remove palavras como "TENDA", "NOVO" e espaços extras
    name = name.replace(/TENDA\s*/gi, '').replace(/NOVO\s*/gi, '').trim();
    
    // Remove espaços e hífens extras (ex: "8X25 - 2" vira "8X25-2")
    name = name.replace(/\s*-\s*/, '-');
    
    // Extrai números: formato pode ser "14x14", "15X20", "8X25-2", etc
    const match = name.match(/(\d+)[xX](\d+)(?:-(\d+))?/);
    
    if (match) {
        const width = parseInt(match[1]);
        const height = parseInt(match[2]);
        const variation = match[3] ? parseInt(match[3]) : null;
        
        return {
            width: width,
            height: height,
            variation: variation,
            id: variation ? `tenda-${width}x${height}-${variation}` : `tenda-${width}x${height}`
        };
    }
    
    return null;
}

// Catálogo completo de tendas
const TENT_CATALOG = [
    { filename: '10x10.png', image: 'assets/images/catalogo/10x10.png', width: 10, height: 10, id: 'tenda-10x10' },
    { filename: '10x30 NOVO.png', image: 'assets/images/catalogo/10x30 NOVO.png', width: 10, height: 30, id: 'tenda-10x30' },
    { filename: '12x20.png', image: 'assets/images/catalogo/12x20.png', width: 12, height: 20, id: 'tenda-12x20' },
    { filename: '14x14.png', image: 'assets/images/catalogo/14x14.png', width: 14, height: 14, id: 'tenda-14x14' },
    { filename: '15X20.png', image: 'assets/images/catalogo/15X20.png', width: 15, height: 20, id: 'tenda-15x20' },
    { filename: '20X20.png', image: 'assets/images/catalogo/20X20.png', width: 20, height: 20, id: 'tenda-20x20' },
    { filename: '20X40.png', image: 'assets/images/catalogo/20X40.png', width: 20, height: 40, id: 'tenda-20x40' },
    { filename: '23x23.png', image: 'assets/images/catalogo/23x23.png', width: 23, height: 23, id: 'tenda-23x23' },
    { filename: '26X26.png', image: 'assets/images/catalogo/26X26.png', width: 26, height: 26, id: 'tenda-26x26' },
    { filename: '40X20.png', image: 'assets/images/catalogo/40X20.png', width: 40, height: 20, id: 'tenda-40x20' },
    { filename: '7X25.png', image: 'assets/images/catalogo/7X25.png', width: 7, height: 25, id: 'tenda-7x25' },
    { filename: '8X25.png', image: 'assets/images/catalogo/8X25.png', width: 8, height: 25, id: 'tenda-8x25' },
    { filename: '8X25 - 2.png', image: 'assets/images/catalogo/8X25 - 2.png', width: 8, height: 25, id: 'tenda-8x25-2' },
    { filename: 'TENDA 4X20.png', image: 'assets/images/catalogo/TENDA 4X20.png', width: 4, height: 20, id: 'tenda-4x20' }
];

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
});

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
                
                addToCart({
                    id: productId,
                    name: tentName,
                    image: productImage,
                    specs: productSpecs,
                    height: height,
                    width: width,
                    area: area,
                    price: price,
                    isTent: true
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
    
    // Se for tenda, adicionar dimensões
    if (product.isTent && product.height && product.width) {
        newItem.height = product.height;
        newItem.width = product.width;
        newItem.area = product.area;
        newItem.price = product.price;
        // Atualizar nome para incluir dimensões
        newItem.name = `Tenda ${product.width}m × ${product.height}m`;
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
                <label for="${observationsId}" class="cart-item-observations-label">Observações deste produto:</label>
                <textarea 
                    id="${observationsId}" 
                    class="cart-item-observations-field" 
                    placeholder="Cores desejadas, medidas personalizadas ou outras especificações..."
                    rows="2"
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
function generateWhatsAppMessage() {
    let message = '*PEDIDO - PSIKÉ DELOUN ARTS*\n\n';
    
    // Calcular total do pedido
    let totalPrice = 0;
    
    cart.forEach((item, index) => {
        message += `*${index + 1}. ${item.name}*\n`;
        message += `Qtd: ${item.quantity}x\n\n`;
        
        // Se for tenda, adicionar dimensões e preço
        if (item.isTent && item.height && item.width && item.area && item.price) {
            const itemTotal = item.price * item.quantity;
            totalPrice += itemTotal;
            message += `Dimensões: ${item.height}m × ${item.width}m\n`;
            message += `Área: ${item.area.toFixed(2)}m²\n`;
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
    message += 'Por favor, envie:\n';
    message += '• Nome completo\n';
    message += '• CEP\n';
    message += '• Endereço completo\n';
    message += '• CPF ou CNPJ\n\n';
    message += 'Aguardo retorno.';
    
    return message;
}

// Enviar carrinho para WhatsApp
function sendCartToWhatsApp() {
    if (cart.length === 0) {
        alert('Seu carrinho está vazio!');
        return;
    }
    
    const message = generateWhatsAppMessage();
    const encodedMessage = encodeURIComponent(message);
    const whatsappUrl = `https://wa.me/${WHATSAPP_NUMBER}?text=${encodedMessage}`;
    
    // Abrir WhatsApp
    window.open(whatsappUrl, '_blank');
    
    // Limpar carrinho após enviar
    cart = [];
    saveCart();
    updateCartUI();
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
    return `
        <article class="product-card">
            <div class="product-image">
                <img src="${tent.image}" alt="Tenda ${tent.width}m x ${tent.height}m" loading="lazy">
            </div>
            <div class="product-content">
                <h3 class="product-title">Tenda ${tent.width}m × ${tent.height}m</h3>
                <div class="product-actions">
                    <a href="tenda.html?w=${tent.width}&h=${tent.height}&id=${tent.id}" class="btn-secondary">Comprar</a>
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
