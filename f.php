<?php
/**
 * Roteador de Funis Publicados
 * Acesso: /slug-do-funil ou /f/slug-do-funil ou /slug-do-funil/pagina
 * Autor: Rafael Souza - https://rafaelsouzatech.com.br
 * Versão: 1.1.0
 * 
 * Changelog v1.1.0:
 * - Suporte a URLs diretas na raiz (sem /f/)
 */

error_reporting(0);
ini_set('display_errors', 0);

require_once __DIR__ . '/config/config.php';

// Verificar se veio da rota direta (sem /f/)
$directSlug = $_GET['direct_slug'] ?? null;
$directPath = $_GET['path'] ?? '';

if ($directSlug) {
    // Rota direta: /slug ou /slug/pagina
    $funnelSlug = $directSlug;
    $pageSlug = trim($directPath, '/') ?: 'index';
} else {
    // Rota tradicional: /f/slug ou /f/slug/pagina
    $requestUri = $_SERVER['REQUEST_URI'];
    $path = parse_url($requestUri, PHP_URL_PATH);
    
    // Remover /f/ do início
    $path = preg_replace('/^\/f\/?/', '', $path);
    $parts = explode('/', trim($path, '/'));
    
    $funnelSlug = $parts[0] ?? '';
    $pageSlug = $parts[1] ?? 'index';
}

if (empty($funnelSlug)) {
    http_response_code(404);
    echo '<!DOCTYPE html><html><head><title>Página não encontrada</title></head><body><h1>404 - Funil não encontrado</h1></body></html>';
    exit;
}

try {
    // Buscar funil pelo slug
    $stmt = $pdo->prepare("SELECT * FROM funnels WHERE slug = ? AND status = 'published'");
    $stmt->execute([$funnelSlug]);
    $funnel = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$funnel) {
        http_response_code(404);
        echo '<!DOCTYPE html><html><head><title>Página não encontrada</title></head><body><h1>404 - Funil não encontrado ou não publicado</h1></body></html>';
        exit;
    }
    
    // Buscar página do funil
    $stmt = $pdo->prepare("SELECT * FROM funnel_pages WHERE funnel_id = ? AND slug = ?");
    $stmt->execute([$funnel['id'], $pageSlug]);
    $page = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$page) {
        // Tentar buscar a homepage
        $stmt = $pdo->prepare("SELECT * FROM funnel_pages WHERE funnel_id = ? AND is_homepage = 1");
        $stmt->execute([$funnel['id']]);
        $page = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    if (!$page) {
        http_response_code(404);
        echo '<!DOCTYPE html><html><head><title>Página não encontrada</title></head><body><h1>404 - Página não encontrada</h1></body></html>';
        exit;
    }
    
    // Buscar configurações do funil (pixels, analytics)
    $stmt = $pdo->prepare("SELECT * FROM funnel_settings WHERE funnel_id = ?");
    $stmt->execute([$funnel['id']]);
    $settings = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    
    // Registrar visita à página do funil
    try {
        // Verificar se tabela existe, se não, criar
        $pdo->exec("CREATE TABLE IF NOT EXISTS funnel_page_views (
            id INT AUTO_INCREMENT PRIMARY KEY,
            funnel_id INT NOT NULL,
            page_id INT DEFAULT NULL,
            session_id VARCHAR(64) NOT NULL,
            ip_address VARCHAR(45) DEFAULT NULL,
            user_agent TEXT DEFAULT NULL,
            referer TEXT DEFAULT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_funnel_id (funnel_id),
            INDEX idx_created_at (created_at),
            INDEX idx_session_funnel (session_id, funnel_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        
        // Gerar session_id único para o visitante
        $session_id = $_COOKIE['funnel_session'] ?? null;
        if (!$session_id) {
            $session_id = bin2hex(random_bytes(16));
            setcookie('funnel_session', $session_id, time() + 86400 * 30, '/', '', true, true);
        }
        
        // Verificar se já registrou visita nesta sessão para este funil (evitar duplicatas)
        $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM funnel_page_views WHERE funnel_id = ? AND session_id = ? AND created_at > DATE_SUB(NOW(), INTERVAL 30 MINUTE)");
        $stmt_check->execute([$funnel['id'], $session_id]);
        
        if ($stmt_check->fetchColumn() == 0) {
            // Registrar nova visita
            $stmt_insert = $pdo->prepare("INSERT INTO funnel_page_views (funnel_id, page_id, session_id, ip_address, user_agent, referer) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt_insert->execute([
                $funnel['id'],
                $page['id'],
                $session_id,
                $_SERVER['REMOTE_ADDR'] ?? null,
                $_SERVER['HTTP_USER_AGENT'] ?? null,
                $_SERVER['HTTP_REFERER'] ?? null
            ]);
        }
    } catch (Exception $e) {
        // Ignora erros de tracking para não afetar a exibição da página
        error_log("Erro ao registrar visita do funil: " . $e->getMessage());
    }
    
    $html = $page['html_content'];
    
    // Injetar scripts de tracking se configurados
    $trackingScripts = '';
    
    if (!empty($settings['facebook_pixel_id'])) {
        $pixelId = htmlspecialchars($settings['facebook_pixel_id']);
        $trackingScripts .= "
<!-- Facebook Pixel -->
<script>
!function(f,b,e,v,n,t,s)
{if(f.fbq)return;n=f.fbq=function(){n.callMethod?
n.callMethod.apply(n,arguments):n.queue.push(arguments)};
if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
n.queue=[];t=b.createElement(e);t.async=!0;
t.src=v;s=b.getElementsByTagName(e)[0];
s.parentNode.insertBefore(t,s)}(window, document,'script',
'https://connect.facebook.net/en_US/fbevents.js');
fbq('init', '{$pixelId}');
fbq('track', 'PageView');
</script>
<noscript><img height=\"1\" width=\"1\" style=\"display:none\" src=\"https://www.facebook.com/tr?id={$pixelId}&ev=PageView&noscript=1\"/></noscript>
<!-- End Facebook Pixel -->";
    }
    
    if (!empty($settings['google_analytics_id'])) {
        $gaId = htmlspecialchars($settings['google_analytics_id']);
        $trackingScripts .= "
<!-- Google Analytics -->
<script async src=\"https://www.googletagmanager.com/gtag/js?id={$gaId}\"></script>
<script>
window.dataLayer = window.dataLayer || [];
function gtag(){dataLayer.push(arguments);}
gtag('js', new Date());
gtag('config', '{$gaId}');
</script>
<!-- End Google Analytics -->";
    }
    
    if (!empty($settings['custom_head_scripts'])) {
        $trackingScripts .= "\n<!-- CheckoutPRO Custom Scripts -->\n" . $settings['custom_head_scripts'] . "\n<!-- End CheckoutPRO Custom Scripts -->";
    }
    
    // Injetar scripts no head
    if (!empty($trackingScripts)) {
        if (stripos($html, '</head>') !== false) {
            $html = str_ireplace('</head>', $trackingScripts . "\n</head>", $html);
        } else {
            $html = $trackingScripts . "\n" . $html;
        }
    }
    
    // Enviar a página
    header('Content-Type: text/html; charset=utf-8');
    echo $html;
    
} catch (Exception $e) {
    http_response_code(500);
    echo '<!DOCTYPE html><html><head><title>Erro</title></head><body><h1>500 - Erro interno</h1></body></html>';
}
