-- Extensões PostgreSQL necessárias
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";
CREATE EXTENSION IF NOT EXISTS "pg_trgm";

-- Configurações de encoding
SET client_encoding = 'UTF8';

-- Garantir que o banco de dados existe (será criado automaticamente pelo POSTGRES_DB)
-- Este script roda após a criação do banco
