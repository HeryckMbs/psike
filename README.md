# Psiké Deloun Arts - Sistema Completo

Sistema completo de gestão para Psiké Deloun Arts, incluindo frontend público, área administrativa, API REST e infraestrutura Docker.

## Estrutura do Projeto

```
psike/
├── frontend/          # Vue.js 3 (público)
├── admin/             # Vue.js 3 + Vuetify (administrativo)
├── backend/           # Laravel 11 (API REST)
├── docker/            # Configurações Docker
└── docker-compose.yml # Orquestração Docker
```

## Tecnologias

- **Frontend Público**: Vue.js 3, Vite, Vue Router, Pinia
- **Frontend Admin**: Vue.js 3, Vuetify 3
- **Backend**: Laravel 11, PostgreSQL, Laravel Passport
- **Infraestrutura**: Docker, Docker Compose, Nginx
- **Outros**: DomPDF, Spatie Laravel Permission

## Instalação

### Pré-requisitos

- Docker e Docker Compose instalados
- Git

### Passos

1. **Clone o repositório** (se aplicável)

2. **Configure o ambiente**

```bash
# Copiar arquivo de exemplo
cp .env.example backend/.env

# Editar variáveis de ambiente
nano backend/.env
```

3. **Iniciar containers Docker**

```bash
# Desenvolvimento
docker-compose up -d

# Produção
docker-compose -f docker-compose.prod.yml up -d
```

4. **Instalar dependências do backend**

```bash
docker-compose exec php composer install
docker-compose exec php php artisan key:generate
```

5. **Configurar banco de dados**

```bash
# Rodar migrations
docker-compose exec php php artisan migrate

# Instalar Passport
docker-compose exec php php artisan passport:install

# Popular banco com dados iniciais
docker-compose exec php php artisan db:seed
```

6. **Instalar dependências do frontend**

```bash
# Frontend público
cd frontend
npm install
npm run build

# Admin
cd ../admin
npm install
npm run build
```

## Desenvolvimento

### Frontend Público

```bash
cd frontend
npm run dev
```

Acesse: http://localhost:5173

### Admin

```bash
cd admin
npm run dev
```

Acesse: http://localhost:5174

### Backend API

A API estará disponível em: http://localhost/api

## Estrutura de Dados

### Categorias
- QUADRADAS
- RETANGULAR
- MANDALA

### Status de Pedidos
- Novo (padrão)
- Em Análise
- Em Produção
- Pronto
- Enviado
- Entregue
- Cancelado

### Permissões

**Roles:**
- `admin`: Acesso total
- `manager`: Gerenciamento de pedidos e produtos
- `seller`: Visualização e edição de pedidos
- `viewer`: Apenas visualização

## Endpoints da API

### Públicos
- `GET /api/products` - Listar produtos
- `GET /api/products/{id}` - Detalhes do produto
- `GET /api/categories` - Listar categorias
- `GET /api/posts` - Listar posts do blog
- `POST /api/orders` - Criar pedido

### Protegidos (Admin)
- Requer autenticação via Laravel Passport
- Prefixo: `/api/admin/*`

## Funcionalidades

- ✅ Catálogo de produtos com imagens e variações
- ✅ Sistema de carrinho de compras
- ✅ Criação de pedidos com dados do cliente
- ✅ Kanban de vendas com status editáveis
- ✅ Geração de PDF de propostas
- ✅ Blog (notícias e eventos)
- ✅ Sistema de permissões
- ✅ Área administrativa completa

## Licença

Proprietário - Psiké Deloun Arts
