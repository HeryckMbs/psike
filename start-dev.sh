#!/bin/bash

# Script de inicialização para desenvolvimento - Psiké Deloun Arts
# Inicia apenas os containers Docker, sem fazer build dos frontends

set -e

# Cores
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

print_message() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

print_step() {
    echo -e "\n${BLUE}========================================${NC}"
    echo -e "${BLUE}$1${NC}"
    echo -e "${BLUE}========================================${NC}\n"
}

print_step "Iniciando aplicação em modo desenvolvimento..."

# Verificar Docker
if ! command -v docker &> /dev/null || ! docker info &> /dev/null; then
    echo "Erro: Docker não está instalado ou rodando"
    exit 1
fi

# Parar containers existentes
print_message "Parando containers existentes..."
docker compose down 2>/dev/null || true

# Iniciar containers
print_message "Iniciando containers Docker..."
docker compose up -d

print_message "Aguardando containers iniciarem..."
sleep 10

# Verificar se precisa instalar dependências do backend
print_step "Verificando dependências do backend..."
if ! docker compose exec -T php test -f /var/www/html/vendor/autoload.php 2>/dev/null; then
    print_message "Instalando dependências do backend (Composer)..."
    docker compose exec -T php composer install --no-interaction --prefer-dist
    sleep 2
fi

# Verificar se precisa gerar chave
if docker compose exec -T php test -f /var/www/html/vendor/autoload.php 2>/dev/null; then
    if ! docker compose exec -T php php artisan key:generate --show &> /dev/null; then
        print_step "Gerando chave da aplicação..."
        docker compose exec -T php php artisan key:generate --force
    fi
    
    # Verificar se precisa rodar migrations
    print_step "Verificando migrations..."
    docker compose exec -T php php artisan migrate --force || true
else
    print_warning "Dependências não instaladas. Execute: docker compose exec php composer install"
fi

print_step "Aplicação iniciada em modo desenvolvimento! 🚀"
echo ""
echo -e "${GREEN}URLs:${NC}"
echo "  • Frontend: http://localhost (via Nginx) ou http://localhost:5173 (direto)"
echo "  • Admin: http://localhost/admin (via Nginx) ou http://localhost:5174 (direto)"
echo "  • API: http://localhost/api"
echo ""
echo -e "${GREEN}Todos os serviços estão rodando em containers Docker!${NC}"
echo ""
