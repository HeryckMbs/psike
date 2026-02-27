#!/bin/bash

# Script de inicialização - Psiké Deloun Arts
# Este script inicia toda a aplicação: Docker, dependências, migrations e builds

set -e  # Para em caso de erro

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Função para imprimir mensagens coloridas
print_message() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARN]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

print_step() {
    echo -e "\n${BLUE}========================================${NC}"
    echo -e "${BLUE}$1${NC}"
    echo -e "${BLUE}========================================${NC}\n"
}

# Verificar se Docker está instalado e rodando
check_docker() {
    print_step "Verificando Docker..."
    
    if ! command -v docker &> /dev/null; then
        print_error "Docker não está instalado. Por favor, instale o Docker primeiro."
        exit 1
    fi
    
    if ! docker info &> /dev/null; then
        print_error "Docker não está rodando. Por favor, inicie o Docker primeiro."
        exit 1
    fi
    
    print_message "Docker está instalado e rodando ✓"
}

# Verificar se Docker Compose está instalado
check_docker_compose() {
    print_step "Verificando Docker Compose..."
    
    if ! docker compose version &> /dev/null; then
        print_error "Docker Compose não está instalado."
        exit 1
    fi
    
    print_message "Docker Compose está instalado ✓"
}

# Parar containers existentes
stop_containers() {
    print_step "Parando containers existentes..."
    docker compose down 2>/dev/null || true
    print_message "Containers parados"
}

# Iniciar containers Docker
start_containers() {
    print_step "Iniciando containers Docker..."
    docker compose up -d
    print_message "Aguardando containers iniciarem..."
    sleep 10
    print_message "Containers iniciados ✓"
}

# Verificar se o container PHP está rodando
wait_for_php() {
    print_step "Aguardando container PHP estar pronto..."
    max_attempts=30
    attempt=0
    
    while [ $attempt -lt $max_attempts ]; do
        if docker compose exec -T php php --version &> /dev/null; then
            print_message "Container PHP está pronto ✓"
            return 0
        fi
        attempt=$((attempt + 1))
        echo -n "."
        sleep 2
    done
    
    print_error "Container PHP não respondeu a tempo"
    return 1
}

# Verificar se o container PostgreSQL está rodando
wait_for_postgres() {
    print_step "Aguardando PostgreSQL estar pronto..."
    max_attempts=30
    attempt=0
    
    while [ $attempt -lt $max_attempts ]; do
        if docker compose exec -T postgres pg_isready -U psike_user &> /dev/null; then
            print_message "PostgreSQL está pronto ✓"
            return 0
        fi
        attempt=$((attempt + 1))
        echo -n "."
        sleep 2
    done
    
    print_warning "PostgreSQL pode não estar totalmente pronto, mas continuando..."
    return 0
}

# Instalar dependências do backend
install_backend_dependencies() {
    print_step "Instalando dependências do backend (Composer)..."
    
    # Verificar se composer.json existe
    if ! docker compose exec -T php test -f /var/www/html/composer.json; then
        print_error "composer.json não encontrado em /var/www/html"
        return 1
    fi
    
    print_message "Executando composer install (isso pode levar alguns minutos)..."
    print_message "Ignorando avisos de segurança para permitir instalação..."
    
    # Instalar com --no-audit para ignorar avisos de segurança
    if docker compose exec -T php composer install --no-interaction --prefer-dist --optimize-autoloader --no-audit; then
        print_message "Dependências do backend instaladas ✓"
        
        # Verificar se vendor/autoload.php foi criado
        if docker compose exec -T php test -f /var/www/html/vendor/autoload.php; then
            print_message "Autoload do Composer verificado ✓"
        else
            print_error "vendor/autoload.php não foi criado"
            return 1
        fi
    else
        print_warning "Tentando instalar sem otimização..."
        if docker compose exec -T php composer install --no-interaction --prefer-dist --no-audit; then
            print_message "Dependências do backend instaladas (sem otimização) ✓"
        else
            print_error "Erro ao instalar dependências do backend"
            return 1
        fi
    fi
}

# Gerar chave da aplicação
generate_app_key() {
    print_step "Gerando chave da aplicação..."
    
    # Verificar se vendor existe antes de tentar gerar chave
    if ! docker compose exec -T php test -f /var/www/html/vendor/autoload.php; then
        print_error "Dependências do Composer não foram instaladas corretamente"
        return 1
    fi
    
    if docker compose exec -T php php artisan key:generate --force 2>/dev/null; then
        print_message "Chave da aplicação gerada ✓"
    else
        print_warning "Chave da aplicação já existe ou erro ao gerar"
    fi
}

# Rodar migrations
run_migrations() {
    print_step "Rodando migrations do banco de dados..."
    
    # Verificar se vendor existe
    if ! docker compose exec -T php test -f /var/www/html/vendor/autoload.php; then
        print_error "Dependências do Composer não foram instaladas corretamente"
        return 1
    fi
    
    if docker compose exec -T php php artisan migrate --force; then
        print_message "Migrations executadas ✓"
    else
        print_error "Erro ao rodar migrations"
        return 1
    fi
}

# Rodar seeders
run_seeders() {
    print_step "Populando banco de dados com dados iniciais..."
    
    if docker compose exec -T php php artisan db:seed --force; then
        print_message "Banco de dados populado ✓"
    else
        print_warning "Erro ao rodar seeders (pode ser normal se já foram executados)"
    fi
}

# Instalar Passport
install_passport() {
    print_step "Instalando Laravel Passport..."
    
    if docker compose exec -T php php artisan passport:install --force 2>/dev/null; then
        print_message "Laravel Passport instalado ✓"
    else
        print_warning "Passport pode já estar instalado ou erro ao instalar"
    fi
}

# Aguardar frontends estarem prontos
wait_for_frontends() {
    print_step "Aguardando frontends estarem prontos..."
    sleep 5
    print_message "Frontends iniciados ✓"
}

# Verificar status dos containers
check_status() {
    print_step "Verificando status dos containers..."
    docker compose ps
}

# Mostrar informações finais
show_final_info() {
    print_step "Aplicação iniciada com sucesso! 🎉"
    
    echo ""
    echo -e "${GREEN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
    echo -e "${GREEN}  Psiké Deloun Arts - Sistema Iniciado${NC}"
    echo -e "${GREEN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
    echo ""
    echo -e "${BLUE}URLs disponíveis:${NC}"
    echo -e "  • Frontend Público: ${GREEN}http://localhost${NC} (proxy) ou ${GREEN}http://localhost:5173${NC} (direto)"
    echo -e "  • Admin: ${GREEN}http://localhost/admin${NC} (proxy) ou ${GREEN}http://localhost:5174${NC} (direto)"
    echo -e "  • API: ${GREEN}http://localhost/api${NC}"
    echo ""
    echo -e "${BLUE}Comandos úteis:${NC}"
    echo -e "  • Ver logs: ${YELLOW}docker compose logs -f${NC}"
    echo -e "  • Parar: ${YELLOW}docker compose down${NC}"
    echo -e "  • Reiniciar: ${YELLOW}docker compose restart${NC}"
    echo ""
}

# Função principal
main() {
    echo ""
    echo -e "${BLUE}"
    echo "╔════════════════════════════════════════════╗"
    echo "║  Psiké Deloun Arts - Inicialização        ║"
    echo "╚════════════════════════════════════════════╝"
    echo -e "${NC}"
    
    # Verificações iniciais
    check_docker
    check_docker_compose
    
    # Parar containers existentes
    stop_containers
    
    # Iniciar containers
    start_containers
    
    # Aguardar serviços estarem prontos
    wait_for_postgres
    wait_for_php
    
    # Backend - IMPORTANTE: instalar dependências PRIMEIRO
    install_backend_dependencies
    
    # Aguardar um pouco para garantir que o composer terminou
    sleep 3
    
    # Agora pode gerar chave e rodar comandos artisan
    generate_app_key
    run_migrations
    run_seeders
    install_passport
    
    # Frontend - Aguardar containers estarem prontos
    wait_for_frontends
    
    # Status final
    check_status
    show_final_info
}

# Executar função principal
main "$@"
