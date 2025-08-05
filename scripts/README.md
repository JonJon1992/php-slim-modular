# Scripts de Automação

## Script de Release (`release.sh`)

Script automatizado para fazer commits e releases seguindo as convenções do projeto.

### Uso

```bash
# Commit simples (sem release)
./scripts/release.sh [tipo] [descrição]

# Commit com release
./scripts/release.sh [tipo] [descrição] [versão]
```

### Parâmetros

- **tipo**: Tipo do commit (padrão: `feat`)
  - `feat`: Nova funcionalidade
  - `fix`: Correção de bug
  - `docs`: Documentação
  - `style`: Formatação de código
  - `refactor`: Refatoração
  - `test`: Testes
  - `chore`: Manutenção
  - `wip`: Trabalho em progresso
  - `project`: Alterações na estrutura do projeto

- **descrição**: Descrição da mudança
- **versão**: Versão para release (opcional)

### Exemplos

```bash
# Commit simples
./scripts/release.sh fix "corrige validação de CPF"

# Commit com release
./scripts/release.sh feat "adiciona validação de CNPJ" "2.6.0"

# Commit de documentação
./scripts/release.sh docs "atualiza README"

# Commit de refatoração
./scripts/release.sh refactor "simplifica lógica de validação"
```

### O que o script faz

1. **Validações**:
   - Verifica se está no diretório correto
   - Verifica se git está disponível
   - Valida tipo de commit
   - Verifica se há mudanças para commitar

2. **Commit**:
   - Adiciona todas as mudanças
   - Cria commit com mensagem padronizada

3. **Release** (se versão especificada):
   - Atualiza versão no `composer.json`
   - Gera entrada no `CHANGELOG.md`
   - Cria tag git
   - Faz push das mudanças e tag

4. **Push**:
   - Envia mudanças para o repositório remoto

### Convenções

- **Mensagens de commit**: Seguem o padrão `tipo: descrição`
- **Tags**: Formato `vX.Y.Z`
- **Changelog**: Entrada automática com data e descrição
- **Versão**: Atualizada automaticamente no `composer.json`

### Dependências

- Git
- Bash
- Sed (para edição de arquivos)

### Compatibilidade

- Linux
- macOS
- Windows (WSL/Git Bash) 