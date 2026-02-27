# Scripts de Inicialização

## start.sh
Script completo que inicia toda a aplicação:
- Verifica Docker e Docker Compose
- Inicia containers Docker
- Instala dependências do backend (Composer)
- Gera chave da aplicação
- Roda migrations e seeders
- Instala Passport
- Instala dependências dos frontends (npm)
- Faz build dos frontends
- Mostra status final

**Uso:**
```bash
./start.sh
```

## start-dev.sh
Script simplificado para desenvolvimento:
- Inicia apenas os containers Docker
- Instala dependências básicas do backend
- Não faz build dos frontends (use `npm run dev` manualmente)

**Uso:**
```bash
./start-dev.sh

# Em terminais separados:
cd frontend && npm run dev
cd admin && npm run dev
```

## Requisitos
- Docker e Docker Compose instalados
- Node.js e npm instalados (para desenvolvimento)
- Arquivos .env configurados

## Troubleshooting

### Erro ao iniciar containers
```bash
docker compose down
docker compose up -d
```

### Erro nas migrations
```bash
docker compose exec php php artisan migrate:fresh --seed
```

### Limpar tudo e recomeçar
```bash
docker compose down -v
docker compose exec php php artisan migrate:fresh --seed
```
