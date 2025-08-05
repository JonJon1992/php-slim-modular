#!/bin/bash

# Script de Automação para Commit e Release
# Uso: ./scripts/release.sh [tipo] [descrição] [versão]

set -e

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Função para exibir mensagens coloridas
log() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

warn() {
    echo -e "${YELLOW}[WARN]${NC} $1"
}

error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Verificar se estamos no diretório correto
if [ ! -f "composer.json" ]; then
    error "Execute este script na raiz do projeto (onde está o composer.json)"
    exit 1
fi

# Verificar se git está disponível
if ! command -v git &> /dev/null; then
    error "Git não está instalado ou não está no PATH"
    exit 1
fi

# Verificar se há mudanças para commitar (apenas se não for uma release)
if [ -z "$(git status --porcelain)" ] && [ -z "$3" ]; then
    warn "Não há mudanças para commitar"
    exit 0
fi

# Parâmetros
TIPO=${1:-"feat"}
DESCRICAO=${2:-"atualização automática"}
VERSAO=${3:-""}

# Tipos de commit válidos
TIPOS_VALIDOS=("feat" "fix" "docs" "style" "refactor" "test" "chore" "wip" "project")

# Validar tipo de commit
if [[ ! " ${TIPOS_VALIDOS[@]} " =~ " ${TIPO} " ]]; then
    error "Tipo de commit inválido. Tipos válidos: ${TIPOS_VALIDOS[*]}"
    exit 1
fi

# Função para obter versão atual do composer.json
get_current_version() {
    grep '"version"' composer.json | sed 's/.*"version": "\(.*\)",/\1/'
}

# Função para atualizar versão no composer.json
update_version() {
    local new_version=$1
    if [[ "$OSTYPE" == "darwin"* ]]; then
        # macOS
        sed -i '' "s/\"version\": \"[^\"]*\"/\"version\": \"$new_version\"/" composer.json
    else
        # Linux
        sed -i "s/\"version\": \"[^\"]*\"/\"version\": \"$new_version\"/" composer.json
    fi
}

# Função para gerar changelog
generate_changelog() {
    local version=$1
    local date=$(date +"%Y-%m-%d")
    
    echo "## [$version] - $date" >> CHANGELOG.md
    echo "" >> CHANGELOG.md
    echo "### Adicionado" >> CHANGELOG.md
    echo "- $DESCRICAO" >> CHANGELOG.md
    echo "" >> CHANGELOG.md
}

# Função para criar tag
create_tag() {
    local version=$1
    git tag -a "v$version" -m "Release v$version: $DESCRICAO"
    log "Tag v$version criada"
}

# Função para fazer push
push_changes() {
    local version=$1
    git push origin main
    git push origin "v$version"
    log "Mudanças enviadas para o repositório remoto"
}

# Processo principal
log "Iniciando processo de release..."

# 1. Adicionar todas as mudanças (se houver)
if [ -n "$(git status --porcelain)" ]; then
    log "Adicionando mudanças ao staging..."
    git add .
    
    # 2. Fazer commit
    log "Criando commit..."
    git commit -m "$TIPO: $DESCRICAO"
else
    log "Nenhuma mudança para commitar"
fi

# 3. Se versão foi especificada, fazer release
if [ -n "$VERSAO" ]; then
    log "Preparando release v$VERSAO..."
    
    # Atualizar versão no composer.json
    update_version "$VERSAO"
    
    # Adicionar mudança de versão
    git add composer.json
    git commit -m "chore: atualiza versão para v$VERSAO"
    
    # Gerar changelog se arquivo existir
    if [ -f "CHANGELOG.md" ]; then
        generate_changelog "$VERSAO"
        git add CHANGELOG.md
        git commit -m "docs: atualiza changelog para v$VERSAO"
    fi
    
    # Criar tag
    create_tag "$VERSAO"
    
    # Fazer push
    push_changes "$VERSAO"
    
    log "Release v$VERSAO concluída com sucesso!"
else
    # Apenas push das mudanças
    log "Enviando mudanças..."
    git push origin main
    log "Commit enviado com sucesso!"
fi

log "Processo concluído!" 