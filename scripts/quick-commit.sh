#!/bin/bash

# Script Rápido para Commits
# Uso: ./scripts/quick-commit.sh [descrição]

set -e

# Cores para output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

log() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

warn() {
    echo -e "${YELLOW}[WARN]${NC} $1"
}

# Verificar se estamos no diretório correto
if [ ! -f "composer.json" ]; then
    echo "Execute este script na raiz do projeto"
    exit 1
fi

# Verificar se há mudanças
if [ -z "$(git status --porcelain)" ]; then
    warn "Não há mudanças para commitar"
    exit 0
fi

# Parâmetros
DESCRICAO=${1:-"atualização"}

# Detectar tipo de commit baseado nas mudanças
TIPO="feat"

# Verificar se é uma correção
if git diff --name-only | grep -E "\.(php|js|css|html)$" > /dev/null; then
    if git diff | grep -i "fix\|corrige\|error\|bug" > /dev/null; then
        TIPO="fix"
    elif git diff | grep -i "refactor\|simplifica\|melhora" > /dev/null; then
        TIPO="refactor"
    elif git diff --name-only | grep -E "(README|CHANGELOG|\.md)$" > /dev/null; then
        TIPO="docs"
    fi
fi

# Fazer commit usando o script principal
./scripts/release.sh "$TIPO" "$DESCRICAO"

log "Commit rápido concluído!" 