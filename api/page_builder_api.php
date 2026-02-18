<?php
/**
 * Page Builder API - Sistema de Funis de Vendas
 * Autor: Rafael Souza - https://rafaelsouzatech.com.br
 * Versão: 2.1.0
 */

// Aumentar limites para conteúdo grande
@ini_set('post_max_size', '50M');
@ini_set('upload_max_filesize', '50M');
@ini_set('max_input_vars', 10000);
@ini_set('max_execution_time', 120);

// Suprimir erros para não quebrar JSON
@ini_set('display_errors', 0);
@error_reporting(0);

// Headers CORS e JSON
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');
header('Access-Control-Allow-Credentials: true');
header('Cache-Control: no-cache, no-store, must-revalidate');

// Preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit('{}');
}

// Função de resposta JSON
function jsonResponse($success, $data = [], $error = null) {
    $response = ['success' => $success];
    if ($error) $response['error'] = $error;
    $json = json_encode(array_merge($response, $data), JSON_UNESCAPED_UNICODE);
    if ($json === false) {
        echo '{"success":false,"error":"Erro ao gerar JSON"}';
    } else {
        echo $json;
    }
    exit;
}

// Iniciar sessão
if (session_status() == PHP_SESSION_NONE) {
    @session_start();
}

// Carregar configuração
try {
    require_once __DIR__ . '/../config/config.php';
} catch (Exception $e) {
    jsonResponse(false, [], 'Erro de configuração: ' . $e->getMessage());
}

if (!isset($pdo)) {
    jsonResponse(false, [], 'Banco de dados indisponível');
}

// Criar tabelas se não existirem
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS `funnels` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `usuario_id` INT NOT NULL,
        `name` VARCHAR(255) NOT NULL,
        `slug` VARCHAR(100) NOT NULL,
        `status` ENUM('draft', 'published') DEFAULT 'draft',
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
        `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY `unique_slug` (`slug`),
        INDEX `idx_usuario` (`usuario_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    // Migrar coluna subdomain para slug se existir
    try {
        $pdo->exec("ALTER TABLE `funnels` CHANGE `subdomain` `slug` VARCHAR(100) NOT NULL");
    } catch (Exception $e) {}
    
    // Garantir que a coluna slug existe
    try {
        $pdo->exec("ALTER TABLE `funnels` ADD COLUMN `slug` VARCHAR(100) NOT NULL AFTER `name`");
    } catch (Exception $e) {}
    
    $pdo->exec("CREATE TABLE IF NOT EXISTS `funnel_pages` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `funnel_id` INT NOT NULL,
        `name` VARCHAR(255) NOT NULL,
        `slug` VARCHAR(100) NOT NULL,
        `html_content` LONGTEXT,
        `is_homepage` TINYINT(1) DEFAULT 0,
        `order_index` INT DEFAULT 0,
        `status` ENUM('draft', 'published') DEFAULT 'draft',
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
        `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX `idx_funnel` (`funnel_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    $pdo->exec("CREATE TABLE IF NOT EXISTS `funnel_settings` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `funnel_id` INT NOT NULL,
        `facebook_pixel_id` VARCHAR(50) DEFAULT NULL,
        `google_analytics_id` VARCHAR(50) DEFAULT NULL,
        `custom_head_scripts` LONGTEXT DEFAULT NULL,
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
        `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    // Alterar coluna para LONGTEXT se já existir como TEXT
    try {
        $pdo->exec("ALTER TABLE funnel_settings MODIFY COLUMN custom_head_scripts LONGTEXT DEFAULT NULL");
    } catch (Exception $e) {
        // Ignora se já for LONGTEXT
    }
} catch (Exception $e) {
    // Tabelas já existem ou erro silencioso
}

$rawInput = file_get_contents('php://input');
$input = json_decode($rawInput, true) ?: [];
$action = $_GET['action'] ?? $input['action'] ?? '';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || !isset($_SESSION['id'])) {
    http_response_code(401);
    jsonResponse(false, [], 'Faça login para continuar');
}

$userId = $_SESSION['id'];

// ============================================
// AÇÕES DA API
// ============================================

switch ($action) {

    // ----------------------------------------
    // ESTATÍSTICAS DO DASHBOARD
    // ----------------------------------------
    case 'get_stats':
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM funnels WHERE usuario_id = ?");
            $stmt->execute([$userId]);
            $totalFunnels = $stmt->fetchColumn();
            
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM funnels WHERE usuario_id = ? AND status = 'published'");
            $stmt->execute([$userId]);
            $published = $stmt->fetchColumn();
            
            $drafts = $totalFunnels - $published;
            
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM funnel_pages fp JOIN funnels f ON fp.funnel_id = f.id WHERE f.usuario_id = ?");
            $stmt->execute([$userId]);
            $totalPages = $stmt->fetchColumn();
            
            jsonResponse(true, [
                'total_funnels' => (int)$totalFunnels,
                'published' => (int)$published,
                'drafts' => (int)$drafts,
                'total_pages' => (int)$totalPages
            ]);
        } catch (Exception $e) {
            jsonResponse(false, [], 'Erro ao buscar estatísticas');
        }
        break;

    // ----------------------------------------
    // LISTAR FUNIS
    // ----------------------------------------
    case 'get_funnels':
        try {
            $stmt = $pdo->prepare("
                SELECT f.*, 
                       (SELECT COUNT(*) FROM funnel_pages WHERE funnel_id = f.id) as page_count
                FROM funnels f 
                WHERE f.usuario_id = ? 
                ORDER BY f.updated_at DESC
            ");
            $stmt->execute([$userId]);
            $funnels = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            jsonResponse(true, ['funnels' => $funnels]);
        } catch (Exception $e) {
            jsonResponse(false, [], 'Erro ao listar funis');
        }
        break;

    // ----------------------------------------
    // CRIAR FUNIL
    // ----------------------------------------
    case 'create_funnel':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(false, [], 'Método não permitido');
        }
        
        $name = trim($input['name'] ?? '');
        $slug = trim($input['slug'] ?? '');
        
        if (empty($name)) {
            jsonResponse(false, [], 'Nome do funil é obrigatório');
        }
        
        if (empty($slug)) {
            jsonResponse(false, [], 'Slug é obrigatório');
        }
        
        // Limpar slug
        $slug = strtolower(preg_replace('/[^a-zA-Z0-9-]/', '', $slug));
        $slug = preg_replace('/-+/', '-', $slug);
        $slug = trim($slug, '-');
        
        if (strlen($slug) < 3) {
            jsonResponse(false, [], 'Slug deve ter pelo menos 3 caracteres');
        }
        
        try {
            // Verificar se slug já existe
            $stmt = $pdo->prepare("SELECT id FROM funnels WHERE slug = ?");
            $stmt->execute([$slug]);
            if ($stmt->rowCount() > 0) {
                jsonResponse(false, [], 'Este slug já está em uso');
            }
            
            // Criar funil
            $stmt = $pdo->prepare("INSERT INTO funnels (usuario_id, name, slug, status) VALUES (?, ?, ?, 'draft')");
            $stmt->execute([$userId, $name, $slug]);
            $funnelId = $pdo->lastInsertId();
            
            // Criar página inicial
            $stmt = $pdo->prepare("INSERT INTO funnel_pages (funnel_id, name, slug, html_content, is_homepage, order_index) VALUES (?, 'Página Inicial', 'index', '', 1, 0)");
            $stmt->execute([$funnelId]);
            
            jsonResponse(true, [
                'funnel_id' => (int)$funnelId,
                'slug' => $slug,
                'message' => 'Funil criado com sucesso!'
            ]);
        } catch (Exception $e) {
            jsonResponse(false, [], 'Erro ao criar funil: ' . $e->getMessage());
        }
        break;

    // ----------------------------------------
    // OBTER FUNIL
    // ----------------------------------------
    case 'get_funnel':
        $funnelId = $_GET['funnel_id'] ?? $input['funnel_id'] ?? null;
        
        if (!$funnelId) {
            jsonResponse(false, [], 'ID do funil é obrigatório');
        }
        
        try {
            $stmt = $pdo->prepare("SELECT * FROM funnels WHERE id = ? AND usuario_id = ?");
            $stmt->execute([$funnelId, $userId]);
            $funnel = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$funnel) {
                jsonResponse(false, [], 'Funil não encontrado');
            }
            
            $stmt = $pdo->prepare("SELECT * FROM funnel_pages WHERE funnel_id = ? ORDER BY order_index ASC");
            $stmt->execute([$funnelId]);
            $pages = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $stmt = $pdo->prepare("SELECT * FROM funnel_settings WHERE funnel_id = ?");
            $stmt->execute([$funnelId]);
            $settings = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
            
            jsonResponse(true, [
                'funnel' => $funnel,
                'pages' => $pages,
                'settings' => $settings
            ]);
        } catch (Exception $e) {
            jsonResponse(false, [], 'Erro ao buscar funil');
        }
        break;

    // ----------------------------------------
    // EXCLUIR FUNIL
    // ----------------------------------------
    case 'delete_funnel':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(false, [], 'Método não permitido');
        }
        
        $funnelId = $input['funnel_id'] ?? null;
        
        if (!$funnelId) {
            jsonResponse(false, [], 'ID do funil é obrigatório');
        }
        
        try {
            $stmt = $pdo->prepare("SELECT id FROM funnels WHERE id = ? AND usuario_id = ?");
            $stmt->execute([$funnelId, $userId]);
            if ($stmt->rowCount() === 0) {
                jsonResponse(false, [], 'Funil não encontrado');
            }
            
            $pdo->prepare("DELETE FROM funnel_settings WHERE funnel_id = ?")->execute([$funnelId]);
            $pdo->prepare("DELETE FROM funnel_pages WHERE funnel_id = ?")->execute([$funnelId]);
            $pdo->prepare("DELETE FROM funnels WHERE id = ?")->execute([$funnelId]);
            
            jsonResponse(true, ['message' => 'Funil excluído']);
        } catch (Exception $e) {
            jsonResponse(false, [], 'Erro ao excluir');
        }
        break;

    // ----------------------------------------
    // PUBLICAR/DESPUBLICAR FUNIL
    // ----------------------------------------
    case 'toggle_funnel_status':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(false, [], 'Método não permitido');
        }
        
        $funnelId = $input['funnel_id'] ?? null;
        $status = $input['status'] ?? 'draft';
        
        if (!$funnelId) {
            jsonResponse(false, [], 'ID do funil é obrigatório');
        }
        
        try {
            $stmt = $pdo->prepare("UPDATE funnels SET status = ? WHERE id = ? AND usuario_id = ?");
            $stmt->execute([$status, $funnelId, $userId]);
            
            jsonResponse(true, ['message' => $status === 'published' ? 'Funil publicado!' : 'Funil despublicado']);
        } catch (Exception $e) {
            jsonResponse(false, [], 'Erro ao atualizar status');
        }
        break;

    // ----------------------------------------
    // CRIAR PÁGINA
    // ----------------------------------------
    case 'create_page':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(false, [], 'Método não permitido');
        }
        
        $funnelId = $input['funnel_id'] ?? null;
        $name = trim($input['name'] ?? 'Nova Página');
        $slug = trim($input['slug'] ?? '');
        
        if (!$funnelId) {
            jsonResponse(false, [], 'ID do funil é obrigatório');
        }
        
        // Gerar slug se vazio
        if (empty($slug)) {
            $slug = strtolower(preg_replace('/[^a-zA-Z0-9-]/', '-', $name));
            $slug = preg_replace('/-+/', '-', $slug);
            $slug = trim($slug, '-');
        }
        
        try {
            // Verificar propriedade do funil
            $stmt = $pdo->prepare("SELECT id FROM funnels WHERE id = ? AND usuario_id = ?");
            $stmt->execute([$funnelId, $userId]);
            if ($stmt->rowCount() === 0) {
                jsonResponse(false, [], 'Funil não encontrado');
            }
            
            // Verificar slug único no funil
            $stmt = $pdo->prepare("SELECT id FROM funnel_pages WHERE funnel_id = ? AND slug = ?");
            $stmt->execute([$funnelId, $slug]);
            if ($stmt->rowCount() > 0) {
                $slug .= '-' . time();
            }
            
            // Obter próximo order_index
            $stmt = $pdo->prepare("SELECT MAX(order_index) FROM funnel_pages WHERE funnel_id = ?");
            $stmt->execute([$funnelId]);
            $maxOrder = $stmt->fetchColumn() ?: 0;
            
            $stmt = $pdo->prepare("INSERT INTO funnel_pages (funnel_id, name, slug, html_content, order_index) VALUES (?, ?, ?, '', ?)");
            $stmt->execute([$funnelId, $name, $slug, $maxOrder + 1]);
            $pageId = $pdo->lastInsertId();
            
            jsonResponse(true, [
                'page_id' => (int)$pageId,
                'slug' => $slug,
                'message' => 'Página criada!'
            ]);
        } catch (Exception $e) {
            jsonResponse(false, [], 'Erro ao criar página');
        }
        break;

    // ----------------------------------------
    // OBTER PÁGINA
    // ----------------------------------------
    case 'get_page':
        $pageId = $_GET['page_id'] ?? $input['page_id'] ?? null;
        
        if (!$pageId) {
            jsonResponse(false, [], 'ID da página é obrigatório');
        }
        
        try {
            $stmt = $pdo->prepare("
                SELECT fp.*, f.slug as funnel_slug, f.name as funnel_name
                FROM funnel_pages fp
                JOIN funnels f ON fp.funnel_id = f.id
                WHERE fp.id = ? AND f.usuario_id = ?
            ");
            $stmt->execute([$pageId, $userId]);
            $page = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$page) {
                jsonResponse(false, [], 'Página não encontrada');
            }
            
            jsonResponse(true, ['page' => $page]);
        } catch (Exception $e) {
            jsonResponse(false, [], 'Erro ao buscar página');
        }
        break;

    // ----------------------------------------
    // SALVAR PÁGINA
    // ----------------------------------------
    case 'save_page':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(false, [], 'Método não permitido');
        }
        
        $pageId = $input['page_id'] ?? null;
        $name = trim($input['name'] ?? '');
        
        // Suporte para HTML em base64 (evita bloqueio do ModSecurity)
        $htmlContent = '';
        if (!empty($input['html_base64'])) {
            $htmlContent = base64_decode($input['html_base64']);
            if ($htmlContent === false) {
                jsonResponse(false, [], 'Erro ao decodificar HTML');
            }
            // Decodificar UTF-8
            $htmlContent = rawurldecode($htmlContent);
        } elseif (!empty($input['html_content'])) {
            $htmlContent = $input['html_content'];
        }
        
        if (!$pageId) {
            jsonResponse(false, [], 'ID da página é obrigatório');
        }
        
        if (empty($htmlContent)) {
            jsonResponse(false, [], 'Conteúdo HTML é obrigatório');
        }
        
        try {
            // Verificar propriedade
            $stmt = $pdo->prepare("
                SELECT fp.id, fp.funnel_id FROM funnel_pages fp
                JOIN funnels f ON fp.funnel_id = f.id
                WHERE fp.id = ? AND f.usuario_id = ?
            ");
            $stmt->execute([$pageId, $userId]);
            $pageCheck = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$pageCheck) {
                jsonResponse(false, [], 'Página não encontrada ou sem permissão');
            }
            
            // Salvar conteúdo
            if (!empty($name)) {
                $stmt = $pdo->prepare("UPDATE funnel_pages SET name = ?, html_content = ?, updated_at = NOW() WHERE id = ?");
                $result = $stmt->execute([$name, $htmlContent, $pageId]);
            } else {
                $stmt = $pdo->prepare("UPDATE funnel_pages SET html_content = ?, updated_at = NOW() WHERE id = ?");
                $result = $stmt->execute([$htmlContent, $pageId]);
            }
            
            if ($result) {
                // Atualizar timestamp do funil também
                $pdo->prepare("UPDATE funnels SET updated_at = NOW() WHERE id = ?")->execute([$pageCheck['funnel_id']]);
                jsonResponse(true, ['message' => 'Página salva com sucesso!', 'rows_affected' => $stmt->rowCount()]);
            } else {
                jsonResponse(false, [], 'Falha ao executar UPDATE');
            }
        } catch (Exception $e) {
            jsonResponse(false, [], 'Erro ao salvar: ' . $e->getMessage());
        }
        break;

    // ----------------------------------------
    // EXCLUIR PÁGINA
    // ----------------------------------------
    case 'delete_page':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(false, [], 'Método não permitido');
        }
        
        $pageId = $input['page_id'] ?? null;
        
        if (!$pageId) {
            jsonResponse(false, [], 'ID da página é obrigatório');
        }
        
        try {
            $stmt = $pdo->prepare("
                SELECT fp.id, fp.is_homepage FROM funnel_pages fp
                JOIN funnels f ON fp.funnel_id = f.id
                WHERE fp.id = ? AND f.usuario_id = ?
            ");
            $stmt->execute([$pageId, $userId]);
            $page = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$page) {
                jsonResponse(false, [], 'Página não encontrada');
            }
            
            if ($page['is_homepage']) {
                jsonResponse(false, [], 'Não é possível excluir a página inicial');
            }
            
            $pdo->prepare("DELETE FROM funnel_pages WHERE id = ?")->execute([$pageId]);
            
            jsonResponse(true, ['message' => 'Página excluída']);
        } catch (Exception $e) {
            jsonResponse(false, [], 'Erro ao excluir');
        }
        break;

    // ----------------------------------------
    // CLONAR URL PARA PÁGINA
    // ----------------------------------------
    case 'clone_url':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(false, [], 'Método não permitido');
        }
        
        $pageId = $input['page_id'] ?? null;
        $url = trim($input['url'] ?? '');
        
        if (!$pageId) {
            jsonResponse(false, [], 'ID da página é obrigatório');
        }
        
        if (empty($url)) {
            jsonResponse(false, [], 'URL é obrigatória');
        }
        
        if (!preg_match('/^https?:\/\//i', $url)) {
            $url = 'https://' . $url;
        }
        
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            jsonResponse(false, [], 'URL inválida');
        }
        
        try {
            // Verificar propriedade
            $stmt = $pdo->prepare("
                SELECT fp.id FROM funnel_pages fp
                JOIN funnels f ON fp.funnel_id = f.id
                WHERE fp.id = ? AND f.usuario_id = ?
            ");
            $stmt->execute([$pageId, $userId]);
            if ($stmt->rowCount() === 0) {
                jsonResponse(false, [], 'Página não encontrada');
            }
            
            // Buscar conteúdo da URL
            $opts = [
                'http' => [
                    'method' => 'GET',
                    'header' => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36\r\n" .
                               "Accept: text/html,application/xhtml+xml\r\n",
                    'timeout' => 30,
                    'follow_location' => true
                ],
                'ssl' => ['verify_peer' => false, 'verify_peer_name' => false]
            ];
            
            $context = stream_context_create($opts);
            $html = @file_get_contents($url, false, $context);
            
            if ($html === false) {
                jsonResponse(false, [], 'Não foi possível acessar a URL');
            }
            
            // Resolver URLs relativas
            $parsedUrl = parse_url($url);
            $baseUrl = $parsedUrl['scheme'] . '://' . $parsedUrl['host'];
            
            $html = preg_replace_callback(
                '/(src|href)=(["\'])(?!https?:\/\/|\/\/|data:|javascript:|#|mailto:)([^"\']+)\2/i',
                function($m) use ($baseUrl) {
                    $path = $m[3];
                    if (strpos($path, '/') === 0) {
                        return $m[1] . '=' . $m[2] . $baseUrl . $path . $m[2];
                    }
                    return $m[1] . '=' . $m[2] . $baseUrl . '/' . $path . $m[2];
                },
                $html
            );
            
            // Salvar na página
            $stmt = $pdo->prepare("UPDATE funnel_pages SET html_content = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$html, $pageId]);
            
            jsonResponse(true, [
                'html_content' => $html,
                'message' => 'Página clonada com sucesso!'
            ]);
        } catch (Exception $e) {
            jsonResponse(false, [], 'Erro ao clonar: ' . $e->getMessage());
        }
        break;

    // ----------------------------------------
    // OBTER CONFIGURAÇÕES DO FUNIL
    // ----------------------------------------
    case 'get_funnel_settings':
        $funnelId = $_GET['funnel_id'] ?? $input['funnel_id'] ?? null;
        
        if (!$funnelId) {
            jsonResponse(false, [], 'ID do funil é obrigatório');
        }
        
        try {
            $stmt = $pdo->prepare("SELECT id FROM funnels WHERE id = ? AND usuario_id = ?");
            $stmt->execute([$funnelId, $userId]);
            if ($stmt->rowCount() === 0) {
                jsonResponse(false, [], 'Funil não encontrado');
            }
            
            $stmt = $pdo->prepare("SELECT facebook_pixel_id, google_analytics_id, custom_head_scripts FROM funnel_settings WHERE funnel_id = ?");
            $stmt->execute([$funnelId]);
            $settings = $stmt->fetch(PDO::FETCH_ASSOC);
            
            jsonResponse(true, ['settings' => $settings ?: []]);
        } catch (Exception $e) {
            jsonResponse(false, [], 'Erro ao obter configurações');
        }
        break;

    // ----------------------------------------
    // SALVAR CONFIGURAÇÕES DO FUNIL
    // ----------------------------------------
    case 'save_settings':
    case 'save_funnel_settings':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(false, [], 'Método não permitido');
        }
        
        $funnelId = $input['funnel_id'] ?? null;
        $facebookPixel = trim($input['facebook_pixel_id'] ?? '');
        $googleAnalytics = trim($input['google_analytics_id'] ?? '');
        
        // Decodificar scripts de base64 (para evitar bloqueio do WAF)
        $customScripts = '';
        if (!empty($input['custom_head_scripts_b64'])) {
            $customScripts = base64_decode($input['custom_head_scripts_b64']);
        } elseif (!empty($input['custom_head_scripts'])) {
            $customScripts = trim($input['custom_head_scripts']);
        }
        
        if (!$funnelId) {
            jsonResponse(false, [], 'ID do funil é obrigatório');
        }
        
        try {
            $stmt = $pdo->prepare("SELECT id FROM funnels WHERE id = ? AND usuario_id = ?");
            $stmt->execute([$funnelId, $userId]);
            if ($stmt->rowCount() === 0) {
                jsonResponse(false, [], 'Funil não encontrado');
            }
            
            $stmt = $pdo->prepare("SELECT id FROM funnel_settings WHERE funnel_id = ?");
            $stmt->execute([$funnelId]);
            
            if ($stmt->rowCount() > 0) {
                $stmt = $pdo->prepare("UPDATE funnel_settings SET facebook_pixel_id = ?, google_analytics_id = ?, custom_head_scripts = ? WHERE funnel_id = ?");
                $stmt->execute([$facebookPixel, $googleAnalytics, $customScripts, $funnelId]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO funnel_settings (funnel_id, facebook_pixel_id, google_analytics_id, custom_head_scripts) VALUES (?, ?, ?, ?)");
                $stmt->execute([$funnelId, $facebookPixel, $googleAnalytics, $customScripts]);
            }
            
            jsonResponse(true, ['message' => 'Configurações salvas!']);
        } catch (Exception $e) {
            jsonResponse(false, [], 'Erro ao salvar configurações');
        }
        break;

    // ----------------------------------------
    // OBTER ESTATÍSTICAS DO FUNIL (VISITAS)
    // ----------------------------------------
    case 'get_funnel_stats':
        $funnelId = $_GET['funnel_id'] ?? $input['funnel_id'] ?? null;
        $period = $_GET['period'] ?? $input['period'] ?? 'all';
        
        if (!$funnelId) {
            jsonResponse(false, [], 'ID do funil é obrigatório');
        }
        
        try {
            // Verificar se funil pertence ao usuário
            $stmt = $pdo->prepare("SELECT id FROM funnels WHERE id = ? AND usuario_id = ?");
            $stmt->execute([$funnelId, $userId]);
            if ($stmt->rowCount() === 0) {
                jsonResponse(false, [], 'Funil não encontrado');
            }
            
            // Definir filtro de período
            $dateFilter = '';
            switch ($period) {
                case 'today':
                    $dateFilter = "AND DATE(created_at) = CURDATE()";
                    break;
                case 'yesterday':
                    $dateFilter = "AND DATE(created_at) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)";
                    break;
                case '7days':
                    $dateFilter = "AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
                    break;
                case 'month':
                    $dateFilter = "AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
                    break;
                case 'year':
                    $dateFilter = "AND created_at >= DATE_SUB(NOW(), INTERVAL 1 YEAR)";
                    break;
                default:
                    $dateFilter = '';
            }
            
            // Verificar se tabela existe
            $tableExists = false;
            try {
                $stmt = $pdo->query("SHOW TABLES LIKE 'funnel_page_views'");
                $tableExists = $stmt->rowCount() > 0;
            } catch (Exception $e) {
                $tableExists = false;
            }
            
            $pageViews = 0;
            $uniqueVisitors = 0;
            
            if ($tableExists) {
                // Total de visitas
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM funnel_page_views WHERE funnel_id = ? $dateFilter");
                $stmt->execute([$funnelId]);
                $pageViews = (int)$stmt->fetchColumn();
                
                // Visitantes únicos (por session_id)
                $stmt = $pdo->prepare("SELECT COUNT(DISTINCT session_id) FROM funnel_page_views WHERE funnel_id = ? $dateFilter");
                $stmt->execute([$funnelId]);
                $uniqueVisitors = (int)$stmt->fetchColumn();
            }
            
            jsonResponse(true, [
                'stats' => [
                    'page_views' => $pageViews,
                    'unique_visitors' => $uniqueVisitors,
                    'checkout_visits' => 0, // TODO: integrar com checkout
                    'purchases' => 0 // TODO: integrar com vendas
                ]
            ]);
        } catch (Exception $e) {
            jsonResponse(false, [], 'Erro ao obter estatísticas: ' . $e->getMessage());
        }
        break;

    default:
        jsonResponse(false, [], 'Ação não reconhecida: ' . $action);
}
