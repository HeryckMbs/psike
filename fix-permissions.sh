#!/bin/bash

# Script para corrigir permissões dos arquivos do backend

echo "Corrigindo permissões do backend..."

# Obter UID e GID do usuário atual
CURRENT_UID=$(id -u)
CURRENT_GID=$(id -g)

echo "UID: $CURRENT_UID, GID: $CURRENT_GID"

# Corrigir permissões do diretório backend
if [ -d "backend" ]; then
    sudo chown -R $CURRENT_UID:$CURRENT_GID backend/
    chmod -R 755 backend/
    echo "Permissões do backend corrigidas ✓"
else
    echo "Diretório backend não encontrado"
fi

echo "Concluído!"
