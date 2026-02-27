<template>
  <div>
    <CartButton />
    <Header />
    <Hero />
    
    <!-- Catálogo de Produtos -->
    <section class="catalogo" id="catalogo">
      <div class="container">
        <h2 class="section-title">Nossas Estruturas</h2>
        <p class="section-subtitle">
          Soluções personalizadas para transformar qualquer espaço em uma experiência memorável
        </p>
        
        <div class="catalogo-grid">
          <ProductCard 
            v-for="product in featuredProducts" 
            :key="product.id"
            :product="product"
          />
        </div>
        
        <div class="catalogo-actions">
          <router-link to="/produtos" class="btn-primary">
            Ver Catálogo Completo e Montar Seu Pedido
          </router-link>
        </div>
      </div>
    </section>

    <!-- Seção Sobre -->
    <section class="sobre-section" id="sobre">
      <div class="container">
        <h2 class="section-title">SOBRE A PSIKÉ DELOUN ARTS</h2>
        <p class="sobre-intro">
          Desde 2015, produzimos e instalamos tendas em malha para cenografia e estruturas especiais em todo o Brasil. 
          Somos especialistas em projetos sob medida, com acabamento premium e foco em impacto visual — entregando soluções 
          personalizadas para eventos que exigem presença.
        </p>

        <!-- Galeria de Imagens -->
        <div class="sobre-gallery">
          <div class="gallery-main-wrapper">
            <button class="gallery-nav-btn gallery-nav-prev" @click="previousImage" aria-label="Anterior">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="15 18 9 12 15 6"/>
              </svg>
            </button>
            <div class="gallery-main" id="galleryMain">
              <img v-if="currentMedia.type === 'image'" 
                   :src="currentMedia.src" 
                   :alt="currentMedia.alt" 
                   id="galleryMainImage">
              <video v-else 
                     :src="currentMedia.src" 
                     id="galleryMainVideo" 
                     controls>
                Seu navegador não suporta vídeos.
              </video>
            </div>
            <button class="gallery-nav-btn gallery-nav-next" @click="nextImage" aria-label="Próximo">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="9 18 15 12 9 6"/>
              </svg>
            </button>
          </div>
        </div>

        <h3 class="sobre-subtitle">POR QUE A PSIKÉ</h3>
        <div class="sobre-benefits-grid">
          <div v-for="benefit in benefits" :key="benefit.title" class="sobre-benefit-item">
            <div class="sobre-benefit-icon" v-html="benefit.icon"></div>
            <h4 class="sobre-benefit-title">{{ benefit.title }}</h4>
          </div>
        </div>
      </div>
    </section>

    <!-- Seção O que fazemos -->
    <section class="servicos-section" id="servicos">
      <div class="container">
        <h2 class="section-title">O QUE FAZEMOS</h2>
        
        <div class="servicos-main-grid">
          <div v-for="service in mainServices" :key="service.title" class="servico-main-item">
            <div class="servico-main-icon" v-html="service.icon"></div>
            <h3 class="servico-main-title">{{ service.title }}</h3>
          </div>
        </div>

        <h3 class="servicos-subtitle">SERVIÇOS OPCIONAIS</h3>
        <div class="servicos-optional-grid">
          <div v-for="optional in optionalServices" :key="optional" class="servico-optional-item">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M22 11.08V12a10 10 0 11-5.93-9.14"/>
              <polyline points="22 4 12 14.01 9 11.01"/>
            </svg>
            <span>{{ optional }}</span>
          </div>
        </div>
      </div>
    </section>

    <!-- Seção Engenharia -->
    <section class="engenharia" id="engenharia">
      <div class="container">
        <h2 class="section-title">Precisão e Engenharia</h2>
        <p class="section-subtitle">Por trás de cada estrutura, há cálculo rigoroso e expertise técnica</p>
        
        <div class="engenharia-grid">
          <div v-for="item in engineeringItems" :key="item.title" class="engenharia-item">
            <div class="engenharia-icon" v-html="item.icon"></div>
            <h3 class="engenharia-title">{{ item.title }}</h3>
            <p class="engenharia-description">{{ item.description }}</p>
          </div>
        </div>
      </div>
    </section>

    <Footer />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import Header from '../components/Header.vue'
import Hero from '../components/Hero.vue'
import Footer from '../components/Footer.vue'
import CartButton from '../components/CartButton.vue'
import ProductCard from '../components/ProductCard.vue'
import { useProductsStore } from '../store/products'

const productsStore = useProductsStore()

const featuredProducts = ref([])

const galleryMedia = [
  { type: 'image', src: '/assets/images/sobre_nos/foto_render_tenda.png', alt: 'Render de tenda tensionada', order: 2 },
  { type: 'image', src: '/assets/images/sobre_nos/tenda_aberta.jpg', alt: 'Tenda aberta', order: 7 },
  { type: 'image', src: '/assets/images/sobre_nos/foto_tenda_evento.jpg', alt: 'Tenda em evento', order: 3 },
  { type: 'image', src: '/assets/images/sobre_nos/foto_construcao_tenda.jpg', alt: 'Construção de tenda', order: 4 },
  { type: 'image', src: '/assets/images/sobre_nos/RENDER TENDA 16X16M LOSANGO.png', alt: 'Render tenda losango', order: 5 },
  { type: 'image', src: '/assets/images/sobre_nos/domo_2.jpg', alt: 'Domo geodésico', order: 6 },
  { type: 'image', src: '/assets/images/sobre_nos/foto_tenda_psicodelcia_2.jpg', alt: 'Tenda psicodélica', order: 1 },
  { type: 'video', src: '/assets/images/sobre_nos/video_palhacos_com_tenda.mp4', alt: 'Vídeo: Palhaços com tenda', order: 8 }
].sort((a, b) => a.order - b.order)

const currentMediaIndex = ref(0)
const currentMedia = ref(galleryMedia[0])

const benefits = [
  { title: 'Produtos exclusivos', icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>' },
  { title: 'Alta durabilidade', icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>' },
  { title: 'Acabamento profissional', icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14.7 6.3a1 1 0 000 1.4l1.6 1.6a1 1 0 001.4 0l3.77-3.77a6 6 0 01-7.94 7.94l-6.91 6.91a2.12 2.12 0 01-3-3l6.91-6.91a6 6 0 017.94-7.94l-3.76 3.76z"/></svg>' },
  { title: 'Montagem rápida e prática', icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>' },
  { title: 'Estrutura própria, não é aluguel', icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>' },
  { title: 'Uso ilimitado', icon: '<svg class="infinito" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 12c-2-2.67-4-4-6-4a4 4 0 1 0 0 8c2 0 4-1.33 6-4zm0 0c2 2.67 4 4 6 4a4 4 0 1 0 0-8c-2 0-4 1.33-6 4z"/></svg>' }
]

const mainServices = [
  { title: 'Venda de tendas personalizadas', icon: '<img src="/assets/images/RECUPERADO - APRESENTAÇÃO PARA CATÁLOGO.png" alt="Venda de tendas" style="width: 100%; height: 100%; object-fit: contain;">' },
  { title: 'Montagem e aluguel para eventos', icon: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1280.000000 1112.000000" preserveAspectRatio="xMidYMid meet" class="svg-base"><g transform="translate(0.000000,1112.000000) scale(0.100000,-0.100000)" fill="currentColor" stroke="none"><path d="M6800 11114 c-377 -28 -726 -85 -1100 -182 -74 -19 -147 -37 -162 -40 -16 -2 -28 -8 -28 -12 0 -15 75 -17 125 -4 111 28 287 38 432 25 522 -48 1088 -272 1565 -621 149 -108 387 -349 471 -476 108 -163 158 -289 177 -449 17 -144 -5 -254 -84 -415 l-51 -105 0 -115 c0 -68 6 -136 14 -165 16 -57 65 -161 89 -188 15 -17 13 -20 -19 -51 -19 -19 -496 -457 -1060 -974 l-1027 -940 -778 675 c-429 371 -792 686 -807 699 l-28 24 45 182 c143 575 385 1555 388 1570 2 13 -68 59 -287 189 -1010 599 -1819 1082 -1970 1178 -66 41 -125 76 -132 77 -9 2 -360 -314 -635 -572 l-67 -63 527 -483 c668 -612 776 -711 834 -764 l46 -43 -60 -193 c-33 -106 -68 -220 -78 -253 -37 -121 -119 -381 -127 -404 -9 -23 -45 -33 -483 -147 -453 -118 -475 -123 -494 -106 -10 9 -328 299 -704 645 l-685 627 -26 -23 c-377 -343 -610 -560 -614 -571 -3 -8 146 -209 370 -497 386 -497 658 -851 1032 -1346 118 -156 220 -283 228 -283 13 0 79 11 328 55 50 9 141 25 203 36 61 10 163 28 225 39 61 10 164 28 227 39 63 11 167 29 230 40 63 11 167 29 230 40 63 11 169 30 235 41 66 12 143 25 170 30 l50 8 125 -122 c124 -122 951 -926 1023 -995 l37 -37 -437 -384 c-241 -212 -723 -636 -1073 -944 -349 -307 -833 -732 -1074 -945 -893 -785 -1227 -1078 -1452 -1277 -128 -112 -257 -231 -287 -264 -119 -129 -156 -274 -103 -413 29 -78 65 -135 138 -219 60 -69 902 -842 986 -905 70 -53 193 -114 292 -145 61 -19 97 -23 195 -23 139 0 196 14 294 72 63 38 150 121 1456 1383 226 218 489 472 585 565 170 164 292 281 1175 1135 629 609 863 832 870 831 5 0 815 -787 1415 -1376 124 -121 495 -484 825 -805 330 -322 787 -767 1015 -990 707 -689 743 -724 810 -772 135 -95 241 -147 390 -188 111 -31 343 -35 458 -7 255 61 473 205 633 417 53 69 132 234 153 320 71 280 -16 562 -241 781 -65 64 -3593 3128 -4362 3789 -132 113 -239 210 -239 215 0 6 276 262 612 570 336 308 806 738 1045 957 l433 397 78 -55 c91 -63 197 -115 290 -140 98 -27 313 -25 449 4 127 27 203 28 305 2 194 -49 340 -165 634 -503 l120 -138 -98 -95 c-79 -77 -103 -108 -131 -167 -32 -67 -34 -79 -34 -172 0 -85 4 -110 28 -170 53 -135 164 -252 302 -316 95 -45 158 -59 266 -59 111 0 192 22 273 74 51 33 892 795 973 882 132 141 149 356 43 535 -53 90 -161 189 -252 233 -104 49 -150 60 -263 60 -103 0 -164 -14 -249 -56 l-38 -19 -25 23 c-50 45 -196 225 -255 313 -33 50 -109 187 -168 305 -125 250 -150 295 -233 410 -100 140 -323 397 -428 495 -114 105 -508 474 -594 555 -131 124 -831 778 -890 831 -101 93 -231 192 -369 284 -420 279 -861 438 -1421 511 -116 15 -618 27 -745 18z m4361 -9815 c90 -25 181 -89 234 -161 59 -79 79 -143 79 -248 0 -77 -3 -92 -37 -161 -63 -132 -173 -213 -335 -247 -103 -21 -204 -7 -307 43 -87 42 -120 75 -146 150 -61 174 -17 416 100 547 50 57 105 84 191 96 80 11 135 6 221 -19z"/></g></svg>' },
  { title: 'Cenografia e ativações', icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="M2 8h20M8 4v4M16 4v4"/><circle cx="12" cy="14" r="2"/><path d="M6 20h12"/></svg>' },
  { title: 'Aluguel de Domos Geodésicos', icon: '<svg viewBox="-110 -110 220 220" xmlns="http://www.w3.org/2000/svg"><defs><style>.borda-externa { fill: none; stroke: currentColor; stroke-width: 2.5; stroke-linecap: round; stroke-linejoin: round; }.linha-top { fill: none; stroke: currentColor; stroke-width: 2.5; stroke-linecap: round; stroke-linejoin: round; }.no-top { fill: currentColor; stroke: currentColor; stroke-width: 1.5; opacity: 0.6; }</style></defs><circle class="borda-externa" cx="0" cy="0" r="100" /><path class="linha-top" d="M 0,-40 L 34.6,-20 L 34.6,20 L 0,40 L -34.6,20 L -34.6,-20 Z M 0,0 L 0,-40 M 0,0 L 34.6,-20 M 0,0 L 34.6,20 M 0,0 L 0,40 M 0,0 L -34.6,20 M 0,0 L -34.6,-20 M 0,-80 L 40,-69.3 L 69.3,-40 L 80,0 L 69.3,40 L 40,69.3 L 0,80 L -40,69.3 L -69.3,40 L -80,0 L -69.3,-40 L -40,-69.3 Z M 0,-40 L 0,-80 M 34.6,-20 L 69.3,-40 M 34.6,20 L 69.3,40 M 0,40 L 0,80 M -34.6,20 L -69.3,40 M -34.6,-20 L -69.3,-40 M 0,-40 L 40,-69.3 L 34.6,-20 M 34.6,-20 L 80,0 L 34.6,20 M 34.6,20 L 40,69.3 L 0,40 M 0,40 L -40,69.3 L -34.6,20 M -34.6,20 L -80,0 L -34.6,-20 M -34.6,-20 L -40,-69.3 L 0,-40 M 0,-80 L 0,-100 M 69.3,-40 L 86.6,-50 M 69.3,40 L 86.6,50 M 0,80 L 0,100 M -69.3,40 L -86.6,50 M -69.3,-40 L -86.6,-50 M 0,-100 L 40,-69.3 L 50,-86.6 L 80,0 L 100,0" /><g><circle class="no-top" cx="0" cy="0" r="4.5" /><circle class="no-top" cx="0" cy="-40" r="3.5" /><circle class="no-top" cx="34.6" cy="-20" r="3.5" /><circle class="no-top" cx="34.6" cy="20" r="3.5" /><circle class="no-top" cx="0" cy="40" r="3.5" /><circle class="no-top" cx="-34.6" cy="20" r="3.5" /><circle class="no-top" cx="-34.6" cy="-20" r="3.5" /><circle class="no-top" cx="0" cy="-80" r="3.5" /><circle class="no-top" cx="40" cy="-69.3" r="3" /><circle class="no-top" cx="69.3" cy="-40" r="3.5" /><circle class="no-top" cx="80" cy="0" r="3" /><circle class="no-top" cx="69.3" cy="40" r="3.5" /><circle class="no-top" cx="40" cy="69.3" r="3" /><circle class="no-top" cx="0" cy="80" r="3.5" /><circle class="no-top" cx="-40" cy="69.3" r="3" /><circle class="no-top" cx="-69.3" cy="40" r="3.5" /><circle class="no-top" cx="-80" cy="0" r="3" /><circle class="no-top" cx="-69.3" cy="-40" r="3.5" /><circle class="no-top" cx="-40" cy="-69.3" r="3" /></g></svg>' }
]

const optionalServices = [
  'Montagem profissional',
  'Projetos especiais',
  'Acompanhamento técnico',
  'Personalização avançada'
]

const engineeringItems = [
  {
    title: 'Modelagem 3D',
    description: 'Projetos detalhados em 3D antes da execução, garantindo precisão e visualização prévia do resultado final.',
    icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>'
  },
  {
    title: 'Normas de Segurança',
    description: 'Todas as estruturas atendem às normas de segurança, incluindo teste de combustibilidade, e podem ser aprovadas pelos órgãos competentes.',
    icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>'
  },
  {
    title: 'Bioconstrução',
    description: 'Materiais sustentáveis e processos que respeitam o meio ambiente, alinhados com princípios de bioconstrução.',
    icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>'
  }
]

function previousImage() {
  currentMediaIndex.value = (currentMediaIndex.value - 1 + galleryMedia.length) % galleryMedia.length
  currentMedia.value = galleryMedia[currentMediaIndex.value]
}

function nextImage() {
  currentMediaIndex.value = (currentMediaIndex.value + 1) % galleryMedia.length
  currentMedia.value = galleryMedia[currentMediaIndex.value]
}

onMounted(async () => {
  await productsStore.fetchProducts({ limit: 3 })
  featuredProducts.value = productsStore.products.slice(0, 3)
})
</script>
