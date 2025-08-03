# Instruções para Publicação no Packagist

## Pré-requisitos

1. Ter uma conta no [Packagist](https://packagist.org)
2. Ter o repositório no GitHub configurado corretamente
3. Ter o webhook do Packagist configurado no GitHub

## Passos para Publicação

### 1. Configurar Webhook no GitHub

1. Vá para o repositório no GitHub
2. Acesse Settings > Webhooks
3. Adicione um novo webhook:
   - **Payload URL**: `https://packagist.org/api/github`
   - **Content type**: `application/json`
   - **Secret**: (deixe em branco ou configure se necessário)
   - **Events**: Selecione "Just the push event"

### 2. Registrar o Pacote no Packagist

1. Acesse [Packagist](https://packagist.org)
2. Faça login na sua conta
3. Clique em "Submit Package"
4. Cole a URL do repositório: `https://github.com/JonJon1992/php-slim-modular`
5. Clique em "Check" e depois "Submit"

### 3. Configurar Auto-update (Opcional)

1. No Packagist, vá para a página do seu pacote
2. Clique em "Settings"
3. Configure o auto-update se desejar

## Estrutura do Release

### Arquivos Incluídos

- ✅ `composer.json` - Configuração do pacote
- ✅ `README.md` - Documentação principal
- ✅ `CHANGELOG.md` - Histórico de mudanças
- ✅ `LICENSE` - Licença MIT
- ✅ `src/` - Código fonte
- ✅ `cli.php` - Script CLI

### Arquivos Excluídos

- ❌ `.git/` - Controle de versão
- ❌ `.idea/` - Configurações do IDE
- ❌ `vendor/` - Dependências (instaladas via Composer)
- ❌ `node_modules/` - Dependências Node.js (se houver)
- ❌ `*.log` - Arquivos de log
- ❌ `*.cache` - Arquivos de cache

## Verificação da Publicação

Após a publicação, verifique:

1. **Packagist**: https://packagist.org/packages/jonjon1992/php-slim-modular
2. **Instalação**: `composer require jonjon1992/php-slim-modular:^2.2.0`
3. **Documentação**: README.md está sendo exibido corretamente
4. **Versões**: Todas as tags estão sendo reconhecidas

## Comandos Úteis

```bash
# Verificar se o pacote está disponível
composer show jonjon1992/php-slim-modular

# Instalar versão específica
composer require jonjon1992/php-slim-modular:^2.2.0

# Atualizar para última versão
composer update jonjon1992/php-slim-modular
```

## Troubleshooting

### Problema: Pacote não aparece no Packagist
- Verifique se o webhook está configurado corretamente
- Aguarde alguns minutos para o processamento
- Verifique se a tag foi criada corretamente

### Problema: Erro de autoload
- Verifique se o `composer.json` está correto
- Execute `composer dump-autoload` no projeto que usa o pacote

### Problema: Dependências não encontradas
- Verifique se todas as dependências estão listadas no `require`
- Verifique se as versões são compatíveis

## Próximos Passos

1. **Monitorar**: Acompanhe downloads e issues
2. **Manter**: Atualize regularmente as dependências
3. **Documentar**: Mantenha a documentação atualizada
4. **Testar**: Teste em diferentes ambientes

---

**Última atualização**: 19 de Dezembro de 2024
**Versão**: 2.2.1 