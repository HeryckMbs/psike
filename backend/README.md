# Psiké Deloun Arts - Backend API

Backend Laravel 11 com API REST para o sistema Psiké Deloun Arts.

## Instalação

### Via Docker (Recomendado)

```bash
# Instalar dependências
docker-compose exec php composer install

# Gerar chave da aplicação
docker-compose exec php php artisan key:generate

# Rodar migrations
docker-compose exec php php artisan migrate

# Instalar Passport
docker-compose exec php php artisan passport:install

# Instalar permissões
docker-compose exec php php artisan permission:cache-reset
```

### Manual

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan passport:install
```

## Estrutura

- `app/Http/Controllers/Api/` - Controllers da API
- `app/Models/` - Modelos Eloquent
- `app/Services/` - Serviços de negócio
- `database/migrations/` - Migrations do banco
- `routes/api.php` - Rotas da API

## Endpoints

### Públicos
- `GET /api/products` - Listar produtos
- `GET /api/products/{id}` - Detalhes do produto
- `GET /api/categories` - Listar categorias
- `GET /api/posts` - Listar posts do blog
- `POST /api/orders` - Criar pedido

### Protegidos (Admin)
- Requer autenticação via Laravel Passport
- Prefixo: `/api/admin/*`
