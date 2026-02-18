# Licença CheckoutPRO

O CheckoutPRO **só funciona com licença válida** criada no **painel LicencePro**. Sem licença, é exibida a tela de ativação: o cliente cola a chave e clica em **"Validar e Ativar"**. A licença é salva em `storage/licence.key`.

## O que configurar em `licenca_config.php`

- **LP_API_PATH**: caminho até a raiz do LicencePro no mesmo host.
  - `''` = a raiz do site é a pasta do LicencePro → `/api/validar-licenca.php`
  - `'/licencepro'` = a raiz é `www` e `licencepro` é subpasta → `/licencepro/api/validar-licenca.php`
- **API_URL** (opcional): use em vez de `LP_API_PATH` quando o painel LicencePro está em **outro domínio** (ex.: `https://painel.empresa.com/api/validar-licenca.php`).
- **PRODUTO_SLUG**: deve ser `checkout-pro`.
- **CACHE_TEMPO**: segundos de cache (ex.: 3600).

A chave **não** fica em arquivo: o cliente insere na tela.

## No painel LicencePro (não alterar o painel)

1. Cadastre o produto: execute o SQL em `CheckoutPRO/add-checkout-pro.sql` **no banco do LicencePro** (ou crie o produto "CheckoutPRO" com slug `checkout-pro` pela interface).
2. Gere licenças para o produto `checkout-pro` e o domínio do cliente.

## Fluxo para o cliente

1. Ao abrir o CheckoutPRO, aparece **"CheckoutPRO bloqueado"** com o campo para colar a chave.
2. O cliente cola a chave gerada no painel e clica em **"Validar e Ativar"**.
3. Se a licença for válida, a chave é salva e o site é liberado.
4. Se der erro, a mensagem aparece em destaque; o cliente pode tentar outra chave ou falar com o suporte.

## Pasta `storage/`

Guarda `licence.key` e o cache. Deve existir e ser gravável. O `.htaccess` em `storage/` bloqueia acesso direto.
