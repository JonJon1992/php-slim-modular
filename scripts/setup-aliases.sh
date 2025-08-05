#!/bin/bash

# Script para configurar aliases úteis
# Execute: source ./scripts/setup-aliases.sh

# Obter diretório do projeto
PROJECT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"

# Aliases para o projeto
alias commit='$PROJECT_DIR/scripts/quick-commit.sh'
alias release='$PROJECT_DIR/scripts/release.sh'
alias qc='$PROJECT_DIR/scripts/quick-commit.sh'
alias rel='$PROJECT_DIR/scripts/release.sh'

echo "Aliases configurados:"
echo "  commit  - Commit rápido"
echo "  release - Commit com release"
echo "  qc      - Commit rápido (abreviado)"
echo "  rel     - Release (abreviado)"
echo ""
echo "Exemplos:"
echo "  commit 'adiciona nova funcionalidade'"
echo "  release feat 'nova feature' 2.6.0"
echo "  qc 'correção rápida'" 