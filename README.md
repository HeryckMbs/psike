# Landing Page Psiké Deloun Arts

Landing page moderna e responsiva desenvolvida para apresentar o catálogo de estruturas tensionadas e domos geodésicos da Psiké Deloun Arts. Design "Visionária Premium" com foco em conversão para WhatsApp.

## 🎨 Características

- **Design Dark Mode Premium**: Fundo grafite/ônix com acentos em verde neon
- **Totalmente Responsivo**: Mobile-first, adaptável para todos os dispositivos
- **Animações Suaves**: Scroll suave e animações de entrada com Intersection Observer
- **Otimizado para Conversão**: CTAs claros e botão WhatsApp destacado
- **Performance**: Código limpo e otimizado

## 📁 Estrutura de Arquivos

```
psike/
├── index.html              # Página principal
├── styles/
│   └── main.css           # Estilos principais
├── scripts/
│   └── main.js            # JavaScript (scroll, animações, WhatsApp)
├── assets/
│   └── images/
│       └── logo.png       # Logo da empresa
└── README.md              # Este arquivo
```

## 🚀 Como Usar

### 1. Personalizar Número do WhatsApp

Abra o arquivo `scripts/main.js` e altere a constante no topo do arquivo:

```javascript
const WHATSAPP_NUMBER = '5511999999999'; // Seu número aqui
const WHATSAPP_MESSAGE = 'Olá! Gostaria de saber mais sobre as estruturas da Psiké Deloun Arts.';
```

**Formato do número**: 
- Use apenas dígitos
- Inclua o código do país (55 para Brasil)
- Exemplo: `5511999999999` (11 99999-9999)

### 2. Substituir Imagens

As imagens atualmente usam placeholders do Unsplash. Para substituir:

#### Hero Section (Imagem Principal)
No arquivo `index.html`, linha ~30:
```html
<img src="https://images.unsplash.com/..." alt="Estrutura tensionada em evento" class="hero-img">
```
Substitua pela URL da sua imagem ou use um caminho local:
```html
<img src="assets/images/hero-image.jpg" alt="Estrutura tensionada em evento" class="hero-img">
```

#### Imagens dos Produtos
No arquivo `index.html`, procure por:
```html
<img src="https://images.unsplash.com/..." alt="...">
```
Substitua pelas suas imagens. Recomendações:
- **Tendas Tensionadas**: `assets/images/product-placeholders/tendas.jpg`
- **Locação**: `assets/images/product-placeholders/locacao.jpg`
- **Domos**: `assets/images/product-placeholders/domos.jpg`
- **Decoração**: `assets/images/product-placeholders/decoracao.jpg`

**Dimensões recomendadas**:
- Hero: 1920x1080px (ou proporção 16:9)
- Produtos: 800x600px (ou proporção 4:3)

### 3. Personalizar Textos

Todos os textos estão no arquivo `index.html`. Principais seções:

- **Hero Section** (linhas ~25-35): Título e subtítulo principais
- **Catálogo** (linhas ~40-120): Descrições dos produtos
- **Engenharia** (linhas ~125-165): Textos sobre qualidade técnica
- **Footer** (linhas ~170-185): Texto final e CTA

### 4. Ajustar Cores (Opcional)

As cores estão definidas no arquivo `styles/main.css` nas variáveis CSS (linhas ~5-30):

```css
:root {
    --color-bg-primary: #0f0f0f;
    --color-accent-primary: #00ff88;
    /* ... outras cores ... */
}
```

## 🌐 Hospedagem

### Opção 1: GitHub Pages (Gratuito)

1. Crie um repositório no GitHub
2. Faça upload dos arquivos
3. Vá em Settings > Pages
4. Selecione a branch `main` e pasta `/root`
5. Sua página estará disponível em `https://seu-usuario.github.io/psike`

### Opção 2: Netlify (Gratuito)

1. Acesse [netlify.com](https://netlify.com)
2. Arraste a pasta do projeto para a área de deploy
3. Pronto! Sua página estará no ar

### Opção 3: Vercel (Gratuito)

1. Instale o Vercel CLI: `npm i -g vercel`
2. No diretório do projeto: `vercel`
3. Siga as instruções

### Opção 4: Servidor Próprio

1. Faça upload dos arquivos via FTP
2. Certifique-se de que o `index.html` está na raiz
3. Acesse via navegador

## 📱 Testar Localmente

### Método 1: Abrir Direto
Abra o arquivo `index.html` no navegador (funcionalidade limitada devido a CORS).

### Método 2: Servidor Local (Recomendado)

**Python 3:**
```bash
python3 -m http.server 8000
```
Acesse: `http://localhost:8000`

**Node.js (com http-server):**
```bash
npx http-server -p 8000
```
Acesse: `http://localhost:8000`

**PHP:**
```bash
php -S localhost:8000
```
Acesse: `http://localhost:8000`

## ✨ Funcionalidades Implementadas

- ✅ Scroll suave entre seções
- ✅ Animações de entrada ao fazer scroll
- ✅ Link do WhatsApp configurável
- ✅ Design responsivo (mobile, tablet, desktop)
- ✅ Efeito parallax sutil no hero
- ✅ Hover effects nos cards
- ✅ SEO básico (meta tags)
- ✅ Acessibilidade (HTML semântico)

## 🎯 Próximos Passos (Opcional)

- Adicionar formulário de contato
- Integrar Google Analytics
- Adicionar mais seções (depoimentos, portfólio)
- Implementar lazy loading de imagens
- Adicionar modo claro/escuro toggle
- Criar página de detalhes para cada produto

## 📝 Notas

- As imagens do Unsplash são placeholders. Substitua pelas suas fotos reais.
- O número do WhatsApp está como placeholder. **Não esqueça de alterar!**
- O design foi pensado para conversão, com CTAs claros e visíveis.
- Todas as cores seguem a identidade "Visionária Premium" (dark mode + verde neon).

## 🐛 Problemas Comuns

**Imagens não aparecem:**
- Verifique os caminhos das imagens
- Certifique-se de que as imagens existem nos diretórios corretos

**WhatsApp não abre:**
- Verifique o formato do número (apenas dígitos, com código do país)
- Teste o link manualmente: `https://wa.me/5511999999999`

**Animações não funcionam:**
- Verifique se o JavaScript está carregando (console do navegador)
- Certifique-se de que está usando um servidor local (não apenas abrindo o HTML)

## 📞 Suporte

Para dúvidas ou problemas, verifique:
1. Console do navegador (F12) para erros
2. Network tab para verificar se arquivos estão carregando
3. Este README para instruções

---

**Desenvolvido para Psiké Deloun Arts**  
*Arquitetura Efêmera e Cenografia de Alto Impacto*
# psike
