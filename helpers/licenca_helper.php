<?php
/**
 * Validação de Licença - CheckoutPRO
 * Tela de ativação: o cliente cola a chave, clica em "Validar e Ativar" e a licença é salva localmente.
 * Bloqueio total: o sistema só funciona com licença válida.
 */

if (!function_exists('lp_cp_is_json_request')) {
    function lp_cp_is_json_request() {
        $accept = strtolower($_SERVER['HTTP_ACCEPT'] ?? '');
        $xhr    = strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '');
        $uri    = strtolower($_SERVER['REQUEST_URI'] ?? '');
        return (str_contains($accept, 'application/json') || $xhr === 'xmlhttprequest' || str_contains($uri, '/api/'));
    }
}

if (!function_exists('lp_cp_validar_api')) {
    function lp_cp_validar_api($apiUrl, $chave, $produto, $dominio) {
        $post = http_build_query(['chave' => $chave, 'produto' => $produto, 'dominio' => $dominio]);
        $ch   = @curl_init($apiUrl);
        if (!$ch) {
            return ['ok' => false, 'mensagem' => 'Falha ao validar licença (API indisponível). cURL não disponível.'];
        }

        $sslVerify = true;
        if (defined('LP_SSL_VERIFY') && !LP_SSL_VERIFY) {
            $sslVerify = false;
        } else {
            $h = @parse_url($apiUrl, PHP_URL_HOST);
            if (is_string($h) && in_array(strtolower($h), ['localhost', '127.0.0.1', '::1'], true)) {
                $sslVerify = false;
            }
        }

        $headers = [
            'Content-Type: application/x-www-form-urlencoded',
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        ];
        if (!empty($_SERVER['HTTP_HOST'])) {
            $s = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && (string) $_SERVER['SERVER_PORT'] === '443') ? 'https' : 'http';
            $headers[] = 'Referer: ' . $s . '://' . $_SERVER['HTTP_HOST'] . '/';
        }
        @curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $post,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_CONNECTTIMEOUT => 8,
            CURLOPT_SSL_VERIFYPEER => $sslVerify,
            CURLOPT_SSL_VERIFYHOST => $sslVerify ? 2 : 0,
            CURLOPT_FOLLOWLOCATION => true,
        ]);
        $resp    = @curl_exec($ch);
        $httpCode = (int) @curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err     = (string) @curl_error($ch);
        @curl_close($ch);

        if ($resp === false || $err !== '' || $httpCode < 200 || $httpCode >= 300) {
            $msg = 'Falha ao validar licença (API indisponível).';
            if (defined('LP_DEBUG_LICENCA') && LP_DEBUG_LICENCA) {
                $msg .= ' [HTTP ' . $httpCode . ($err !== '' ? ' | cURL: ' . $err : '') . ']';
            }
            return ['ok' => false, 'mensagem' => $msg, 'api_responded' => false];
        }
        $json = @json_decode((string) $resp, true);
        if (!is_array($json) || !isset($json['status'])) {
            return ['ok' => false, 'mensagem' => 'Resposta inválida do servidor de licenças.', 'api_responded' => false];
        }
        if (($json['status'] ?? '') !== 'ativo') {
            return ['ok' => false, 'mensagem' => (string) ($json['mensagem'] ?? 'Licença inválida.'), 'api_responded' => true];
        }
        return ['ok' => true, 'mensagem' => (string) ($json['mensagem'] ?? 'Licença válida.'), 'api_responded' => true];
    }
}

if (!function_exists('bloquearCheckoutPRO')) {
    function bloquearCheckoutPRO($mensagem, $formError = '', $asJson = false, $showForm = true) {
        if (ob_get_level() > 0) {
            while (ob_get_level() > 0) { @ob_end_clean(); }
        }
        http_response_code(403);
        header('Cache-Control: no-store, no-cache, must-revalidate');

        if ($asJson) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['status' => 'bloqueado', 'mensagem' => $mensagem], JSON_UNESCAPED_UNICODE);
            exit;
        }

        header('Content-Type: text/html; charset=utf-8');
        $m   = htmlspecialchars($mensagem, ENT_QUOTES, 'UTF-8');
        $err = $formError !== '' ? htmlspecialchars($formError, ENT_QUOTES, 'UTF-8') : '';

        ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CheckoutPRO bloqueado</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif; background: #0b1020; color: #e5e7eb; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 24px; }
        .c { max-width: 520px; width: 100%; background: rgba(255,255,255,.06); border: 1px solid rgba(255,255,255,.12); border-radius: 16px; padding: 32px; box-shadow: 0 18px 60px rgba(0,0,0,.4); }
        h1 { margin: 0 0 8px; font-size: 22px; display: flex; align-items: center; gap: 10px; }
        .lock { font-size: 28px; }
        p { margin: 0 0 16px; color: rgba(229,231,235,.85); line-height: 1.5; }
        label { display: block; margin: 14px 0 6px; color: rgba(229,231,235,.8); font-size: 14px; }
        input { width: 100%; padding: 12px 14px; border-radius: 10px; border: 1px solid rgba(34,211,238,.4); background: rgba(15,23,42,.6); color: #e5e7eb; font-size: 15px; outline: none; }
        input:focus { border-color: rgba(34,211,238,.7); box-shadow: 0 0 0 3px rgba(34,211,238,.15); }
        input::placeholder { color: rgba(229,231,235,.4); }
        button { cursor: pointer; border: 0; padding: 12px 20px; border-radius: 10px; background: linear-gradient(135deg, #7c3aed, #22d3ee); color: #fff; font-weight: 700; font-size: 15px; }
        button:hover { opacity: .95; }
        .row { display: flex; gap: 12px; flex-wrap: wrap; margin-top: 14px; align-items: center; }
        .muted { color: rgba(229,231,235,.5); font-size: 13px; }
        .err { margin-top: 14px; border: 1px solid rgba(239,68,68,.4); background: rgba(239,68,68,.12); padding: 12px 14px; border-radius: 10px; color: #fecaca; font-size: 14px; }
        .support { margin-top: 16px; color: rgba(229,231,235,.55); font-size: 13px; }
    </style>
</head>
<body>
    <div class="c">
        <h1><span class="lock">🔒</span> CheckoutPRO bloqueado</h1>
        <p><?php echo $m; ?></p>
        <?php if ($showForm): ?>
            <form method="POST">
                <label>Inserir licença</label>
                <input name="lp_license_key" placeholder="Cole sua chave de licença aqui" autocomplete="off" required>
                <div class="row">
                    <button type="submit">Validar e Ativar</button>
                    <span class="muted">A licença será salva localmente.</span>
                </div>
            </form>
        <?php else: ?>
            <p class="muted">Sua licença já está salva no servidor. Assim que o servidor de licenças voltar, o acesso será liberado automaticamente.</p>
        <?php endif; ?>
        <?php if ($err !== '') { echo '<div class="err">' . $err . '</div>'; } ?>
        <p class="support">Se não tiver a licença, entre em contato com o suporte.</p>
    </div>
</body>
</html>
        <?php
        exit;
    }
}

if (!function_exists('lp_cp_api_url')) {
    function lp_cp_api_url() {
        if (defined('API_URL') && (string) API_URL !== '') {
            return (string) API_URL;
        }
        if (!defined('LP_API_PATH')) {
            return '';
        }
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && (string) $_SERVER['SERVER_PORT'] === '443')
            ? 'https' : 'http';
        $host = isset($_SERVER['HTTP_HOST']) ? (string) $_SERVER['HTTP_HOST'] : 'localhost';
        $path = rtrim((string) LP_API_PATH, '/');
        return $scheme . '://' . $host . ($path !== '' ? $path : '') . '/api/validar-licenca.php';
    }
}

if (!function_exists('validarLicencaCheckoutPRO')) {
    function validarLicencaCheckoutPRO() {
        if (!defined('PRODUTO_SLUG')) {
            bloquearCheckoutPRO('Configuração de licença incompleta (PRODUTO_SLUG).', '', lp_cp_is_json_request());
            return;
        }
        $apiUrl = lp_cp_api_url();
        if ($apiUrl === '') {
            bloquearCheckoutPRO('Configure LP_API_PATH ou API_URL em licenca_config.php.', '', lp_cp_is_json_request());
            return;
        }

        $dominio = isset($_SERVER['HTTP_HOST']) ? (string) $_SERVER['HTTP_HOST'] : '';
        $dominio = preg_replace('/:\d+$/', '', $dominio);
        if ($dominio === '') {
            bloquearCheckoutPRO('Não foi possível identificar o domínio.', '', lp_cp_is_json_request());
            return;
        }

        // --- Persistência da licença em arquivo do site ---
        // Alguns hosts fazem limpeza automática de arquivos "*.key" ou de diretórios específicos diariamente.
        // Para evitar o problema de "pedir licença todo dia", salvamos em um arquivo texto e com fallback.
        $rootDir = realpath(dirname(__DIR__)); // .../CheckoutPRO
        $parentDir = $rootDir ? realpath(dirname($rootDir)) : null;

        // Diretório preferencial pode ser configurado em licenca_config.php via LP_LICENSE_STORAGE_DIR
        $candidates = [];
        if (defined('LP_LICENSE_STORAGE_DIR') && is_string(LP_LICENSE_STORAGE_DIR) && LP_LICENSE_STORAGE_DIR !== '') {
            $candidates[] = (string) LP_LICENSE_STORAGE_DIR;
        }
        if (is_string($parentDir) && $parentDir !== '') {
            $candidates[] = $parentDir . DIRECTORY_SEPARATOR . '.checkoutpro_storage';
        }
        if (is_string($rootDir) && $rootDir !== '') {
            $candidates[] = $rootDir . DIRECTORY_SEPARATOR . 'storage';
        }

        $storageDir = '';
        foreach ($candidates as $dir) {
            $dir = rtrim((string)$dir, "\\/"); // normaliza
            if ($dir === '') continue;
            if (!is_dir($dir)) {
                @mkdir($dir, 0755, true);
            }
            if (is_dir($dir) && is_writable($dir)) {
                $storageDir = $dir;
                break;
            }
        }
        if ($storageDir === '') {
            // Último fallback: usa o diretório do projeto (pode não ser gravável, mas tentamos)
            $storageDir = (string) ($rootDir ?: dirname(__DIR__));
        }

        // Novo arquivo (mais compatível com hosts): licenca.txt
        $keyFile  = $storageDir . DIRECTORY_SEPARATOR . 'licenca.txt';
        // Arquivo antigo (compatibilidade): storage/licence.key
        $legacyKeyFile = (string) (($rootDir ?: dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'licence.key');

        $cacheDir = $storageDir;

        // Ativação via formulário (POST)
        if (!lp_cp_is_json_request() && ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['lp_license_key'])) {
            $key = trim((string) $_POST['lp_license_key']);
            if ($key === '') {
                bloquearCheckoutPRO('A licença é obrigatória.', 'Informe a licença.');
                return;
            }
            $res = lp_cp_validar_api($apiUrl, $key, PRODUTO_SLUG, $dominio);
            if ($res['ok']) {
                // grava com lock; e valida leitura de volta para evitar "ativou mas não salvou"
                $written = @file_put_contents($keyFile, $key, LOCK_EX);
                $readBack = is_file($keyFile) ? trim((string)@file_get_contents($keyFile)) : '';
                if (!$written || $readBack === '') {
                    bloquearCheckoutPRO(
                        'Não foi possível salvar a licença no servidor.',
                        'Verifique permissões de escrita no diretório de armazenamento.',
                        lp_cp_is_json_request(),
                        true
                    );
                    return;
                }
                $url = $_SERVER['REQUEST_URI'] ?? '/';
                header('Location: ' . $url);
                exit;
            }
            bloquearCheckoutPRO('Licença inválida.', $res['mensagem']);
            return;
        }

        // Ler chave salva
        $licenca = is_file($keyFile) ? trim((string) @file_get_contents($keyFile)) : '';
        if ($licenca === '' && is_file($legacyKeyFile)) {
            // Migra automaticamente do arquivo antigo para o novo
            $licencaLegacy = trim((string) @file_get_contents($legacyKeyFile));
            if ($licencaLegacy !== '') {
                @file_put_contents($keyFile, $licencaLegacy, LOCK_EX);
                $licenca = $licencaLegacy;
            }
        }
        if ($licenca === '') {
            bloquearCheckoutPRO('Licença não configurada. Insira sua chave abaixo ou entre em contato com o suporte.', '', lp_cp_is_json_request());
            return;
        }

        $cacheTempo = defined('CACHE_TEMPO') ? (int) CACHE_TEMPO : 0;
        $cacheFile  = $cacheDir . DIRECTORY_SEPARATOR . 'licenca_' . md5($licenca . '|' . PRODUTO_SLUG . '|' . $dominio) . '.json';

        if ($cacheTempo > 0 && is_file($cacheFile)) {
            $cached = @json_decode((string) @file_get_contents($cacheFile), true);
            if (is_array($cached) && isset($cached['status'], $cached['ts'])) {
                $age = time() - (int) $cached['ts'];
                if (($cached['status'] ?? '') === 'ativo' && $age <= $cacheTempo) {
                    return;
                }
            }
        }

        $res = lp_cp_validar_api($apiUrl, $licenca, PRODUTO_SLUG, $dominio);

        if ($cacheTempo > 0 && $res['ok']) {
            @file_put_contents($cacheFile, json_encode([
                'status'   => 'ativo',
                'ts'       => time(),
                'mensagem' => $res['mensagem'] ?? '',
            ], JSON_UNESCAPED_UNICODE));
        }

        if (!$res['ok']) {
            // IMPORTANTE: nunca apagar a licença automaticamente.
            // O usuário quer a licença persistida em arquivo do site e não ser obrigado a reinserir.
            // Se a API respondeu que é inválida, apenas bloqueia (mantendo a chave salva para o usuário substituir se desejar).
            if (!empty($res['api_responded'])) {
                bloquearCheckoutPRO($res['mensagem'], $res['mensagem'], lp_cp_is_json_request(), true);
                return;
            }
            // Erro de rede/timeout: não apagar a chave; usar cache antigo como graça (até 60 dias), se existir.
            $graceDays = 60;
            $graceSeconds = $graceDays * 86400;
            if (is_file($cacheFile)) {
                $cached = @json_decode((string) @file_get_contents($cacheFile), true);
                if (is_array($cached) && isset($cached['status'], $cached['ts'])
                    && ($cached['status'] ?? '') === 'ativo') {
                    $age = time() - (int) $cached['ts'];
                    if ($age <= $graceSeconds) {
                        return; // Confia no cache válido enquanto a API estiver indisponível
                    }
                }
            }
            bloquearCheckoutPRO(
                'Não foi possível validar a licença (servidor indisponível). Tente novamente mais tarde.',
                $res['mensagem'],
                lp_cp_is_json_request(),
                false
            );
            return;
        }
    }
}
