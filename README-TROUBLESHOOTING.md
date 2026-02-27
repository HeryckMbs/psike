# Troubleshooting - Psiké Deloun Arts

## Problemas Comuns e Soluções

### 1. Erro de Permissão ao Instalar Composer

**Erro:**
```
file_put_contents(./composer.lock): Failed to open stream: Permission denied
```

**Solução:**
```bash
# Corrigir permissões do diretório backend
chmod -R 777 backend/

# Ou usar o script
./fix-permissions.sh

# Reconstruir o container PHP
docker compose build php
docker compose up -d
```

### 2. PostgreSQL - Database "psike_user" does not exist

**Erro:**
```
FATAL: database "psike_user" does not exist
```

**Solução:**
O PostgreSQL está tentando conectar ao banco errado. Verifique:

1. **Limpar volumes e recriar:**
```bash
docker compose down -v
docker compose up -d postgres
# Aguardar alguns segundos
docker compose up -d
```

2. **Verificar variáveis de ambiente:**
```bash
# Verificar .env do backend
cat backend/.env | grep DB_

# Deve mostrar:
# DB_DATABASE=psike_db
# DB_USERNAME=psike_user
# DB_PASSWORD=psike_password
```

3. **Verificar se o banco foi criado:**
```bash
docker compose exec postgres psql -U psike_user -d psike_db -c "SELECT version();"
```

### 3. Composer bloqueando instalação por avisos de segurança

**Erro:**
```
these were not loaded, because they are affected by security advisories
```

**Solução:**
O `composer.json` já está configurado para ignorar avisos. Se persistir:

```bash
# Instalar com flag adicional
docker compose exec php composer install --no-audit --ignore-platform-reqs
```

### 4. Container PHP não inicia

**Solução:**
```bash
# Ver logs
docker compose logs php

# Reconstruir container
docker compose build --no-cache php
docker compose up -d php
```

### 5. Migrations falhando

**Solução:**
```bash
# Verificar conexão com banco
docker compose exec php php artisan migrate:status

# Limpar e recriar
docker compose exec php php artisan migrate:fresh --seed
```

### 6. Limpar tudo e recomeçar

```bash
# Parar tudo
docker compose down -v

# Remover imagens (opcional)
docker compose down --rmi all

# Limpar permissões
chmod -R 777 backend/

# Iniciar novamente
./start.sh
```

## Comandos Úteis

```bash
# Ver logs de todos os serviços
docker compose logs -f

# Ver logs de um serviço específico
docker compose logs -f php
docker compose logs -f postgres

# Entrar no container PHP
docker compose exec php sh

# Entrar no PostgreSQL
docker compose exec postgres psql -U psike_user -d psike_db

# Reconstruir containers
docker compose build --no-cache

# Ver status dos containers
docker compose ps

# Verificar saúde do PostgreSQL
docker compose exec postgres pg_isready -U psike_user
```
