<?php
/**
 * Configuração de Licença - CheckoutPRO
 *
 * A chave NÃO fica aqui: o cliente insere na tela de ativação e ela é salva localmente.
 * Configure o caminho da API (o host é o mesmo do site).
 */

// Slug do produto no painel de licenças (deve ser 'checkout-pro')
define('PRODUTO_SLUG', 'checkout-pro');

// Caminho até a raiz do LicencePro (o host é o mesmo do site).
// ''           = raiz do site = licencepro        → /api/validar-licenca.php
// '/licencepro'= raiz = www, licencepro é subpasta → /licencepro/api/validar-licenca.php
// Se der 404, troque: '' ↔ '/licencepro'
define('LP_API_PATH', '');

// API em outro domínio: CheckoutPRO está em checkout.digitalavance.com.br,
// o painel LicencePro está em revendedor.digitalavance.com.br
define('API_URL', 'https://revendedor.digitalavance.com.br/api/validar-licenca.php');

// Cache em segundos (0 = sempre validar).
// O usuário solicitou NÃO usar cache de licença.
define('CACHE_TEMPO', 0);

// Opcional: define um diretório fixo para persistir a licença (fora de deploys que limpam a pasta do projeto).
// Exemplo: define('LP_LICENSE_STORAGE_DIR', dirname(__DIR__) . '/.checkoutpro_storage');
// define('LP_LICENSE_STORAGE_DIR', '');

// define('LP_SSL_VERIFY', false);   // só se precisar em HTTPS local
define('LP_DEBUG_LICENCA', true);   // DIAGNÓSTICO: mostra HTTP/cURL no erro — desative em produção depois
