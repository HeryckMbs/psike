# Painel Administrativo - Psiké Deloun Arts

## Configuração Inicial

### 1. Configurar Laravel Passport

O Passport já foi instalado. As credenciais do cliente estão configuradas no arquivo `admin/.env`:

```
VITE_CLIENT_ID=a12cb6ce-4288-4556-abb8-f768af5b796c
VITE_CLIENT_SECRET=$2y$12$lGFiNm5QdwpOwk9WzT.4ceR57ak1BHbp97vPvoiEA1KEX5eJiCqSm
```

### 2. Usuário Admin

Um usuário admin foi criado automaticamente via seeder:

- **Email:** admin@psike.com
- **Senha:** admin123

Para alterar essas credenciais, edite as variáveis de ambiente no `backend/.env`:

```
ADMIN_EMAIL=seu-email@exemplo.com
ADMIN_PASSWORD=sua-senha-segura
```

Depois execute:
```bash
docker compose exec php php artisan db:seed --class=AdminUserSeeder
```

### 3. Acessar o Painel

1. Acesse `http://localhost/admin`
2. Faça login com as credenciais do admin
3. Você será redirecionado para o Dashboard

## Funcionalidades

### Dashboard
- Visão geral de pedidos, produtos e receita
- Lista de pedidos recentes

### Produtos
- Listar todos os produtos (ativos e inativos)
- Criar novo produto
- Editar produto existente
- Excluir produto
- Campos: nome, descrição, categoria, dimensões, preços

### Categorias
- Listar todas as categorias
- Criar nova categoria
- Editar categoria existente
- Excluir categoria
- Campos: nome, slug, descrição, ordem

### Kanban de Vendas
- Visualizar pedidos em formato Kanban
- Arrastar pedidos entre status
- Gerenciar status de pedidos

### Blog
- Gerenciar postagens do blog
- Criar, editar e excluir posts

## Estrutura de Rotas

### Frontend (Admin)
- `/admin/login` - Página de login
- `/admin/` - Dashboard
- `/admin/products` - Lista de produtos
- `/admin/products/new` - Criar produto
- `/admin/products/:id/edit` - Editar produto
- `/admin/categories` - Lista de categorias
- `/admin/categories/new` - Criar categoria
- `/admin/categories/:id/edit` - Editar categoria
- `/admin/orders` - Kanban de vendas
- `/admin/blog` - Gerenciar blog

### Backend (API)
- `POST /api/oauth/token` - Autenticação (Laravel Passport)
- `GET /api/user` - Dados do usuário autenticado
- `GET /api/admin/products` - Listar produtos (admin)
- `POST /api/admin/products` - Criar produto
- `PUT /api/admin/products/{id}` - Atualizar produto
- `DELETE /api/admin/products/{id}` - Excluir produto
- `GET /api/admin/categories` - Listar categorias (admin)
- `POST /api/admin/categories` - Criar categoria
- `PUT /api/admin/categories/{id}` - Atualizar categoria
- `DELETE /api/admin/categories/{id}` - Excluir categoria

## Autenticação

O sistema usa Laravel Passport para autenticação OAuth2. O token de acesso é armazenado no `localStorage` do navegador e incluído automaticamente em todas as requisições via interceptor do Axios.

Em caso de token expirado (401), o usuário é automaticamente redirecionado para a página de login.

## Desenvolvimento

### Variáveis de Ambiente

Certifique-se de que o arquivo `admin/.env` contém:

```env
VITE_API_URL=http://localhost/api
VITE_APP_NAME=Psiké Deloun Arts Admin
VITE_CLIENT_ID=a12cb6ce-4288-4556-abb8-f768af5b796c
VITE_CLIENT_SECRET=$2y$12$lGFiNm5QdwpOwk9WzT.4ceR57ak1BHbp97vPvoiEA1KEX5eJiCqSm
```

### Recriar Cliente Passport

Se precisar recriar as credenciais do Passport:

```bash
docker compose exec php php artisan passport:client --personal --name="Admin Client"
```

Depois atualize o `admin/.env` com as novas credenciais.
