<?php
/**
 * API dedicada para Sites Clonados
 * Autor: Rafael Souza - https://rafaelsouzatech.com.br
 * Versão: 1.0.0
 * 
 * Esta API resolve o problema de consumo duplo do php://input
 */

// Iniciar sessão se necessário
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Headers JSON
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');

// Suprimir warnings que quebram JSON
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);

// Incluir configuração
require_once __DIR__ . '/../config/config.php';

// Ler input UMA VEZ no início
$raw_input = file_get_contents('php://input');
$input = json_decode($raw_input, true) ?? [];

// Verificar autenticação
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || !isset($_SESSION['id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Não autorizado. Faça login novamente.']);
    exit;
}

$usuario_id = $_SESSION['id'];
$action = $_GET['action'] ?? $input['action'] ?? '';

// Log para debug
error_log("Cloned Sites API: Action=$action, User=$usuario_id");

// ============================================
// AÇÃO: SALVAR SITE CLONADO
// ============================================
if ($action === 'save_cloned_site' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $cloned_site_id = $input['cloned_site_id'] ?? null;
    $edited_html = $input['edited_html_content'] ?? '';
    $title = trim($input['title'] ?? 'Site Clonado');
    $slug = trim($input['slug'] ?? '');
    $status = trim($input['status'] ?? 'draft');
    $facebook_pixel = trim($input['facebook_pixel_id'] ?? '');
    $google_analytics = trim($input['google_analytics_id'] ?? '');
    $custom_scripts = trim($input['custom_head_scripts'] ?? '');
    
    // Validar slug
    $slug = strtolower(preg_replace('/[^a-zA-Z0-9-]/', '-', $slug));
    $slug = preg_replace('/-+/', '-', $slug);
    $slug = trim($slug, '-');
    
    // Não pode publicar sem slug
    if (empty($slug) && $status === 'published') {
        echo json_encode(['success' => false, 'error' => 'Defina um slug para publicar o site.']);
        exit;
    }
    
    if (empty($slug)) {
        $slug = null;
    }
    
    if (!$cloned_site_id) {
        echo json_encode(['success' => false, 'error' => 'ID do site é obrigatório.']);
        exit;
    }
    
    try {
        // Verificar propriedade
        $stmt = $pdo->prepare("SELECT id FROM cloned_sites WHERE id = ? AND usuario_id = ?");
        $stmt->execute([$cloned_site_id, $usuario_id]);
        
        if ($stmt->rowCount() === 0) {
            echo json_encode(['success' => false, 'error' => 'Site não encontrado ou não pertence a você.']);
            exit;
        }
        
        // Verificar slug único
        if ($slug) {
            $stmt = $pdo->prepare("SELECT id FROM cloned_sites WHERE slug = ? AND id != ?");
            $stmt->execute([$slug, $cloned_site_id]);
            if ($stmt->rowCount() > 0) {
                echo json_encode(['success' => false, 'error' => 'Este slug já está em uso.']);
                exit;
            }
        }
        
        // Atualizar site
        $stmt = $pdo->prepare("UPDATE cloned_sites SET edited_html = ?, title = ?, slug = ?, status = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$edited_html, $title, $slug, $status, $cloned_site_id]);
        
        // Atualizar ou inserir configurações
        $stmt = $pdo->prepare("SELECT id FROM cloned_site_settings WHERE cloned_site_id = ?");
        $stmt->execute([$cloned_site_id]);
        
        if ($stmt->rowCount() > 0) {
            $stmt = $pdo->prepare("UPDATE cloned_site_settings SET facebook_pixel_id = ?, google_analytics_id = ?, custom_head_scripts = ?, updated_at = NOW() WHERE cloned_site_id = ?");
            $stmt->execute([$facebook_pixel, $google_analytics, $custom_scripts, $cloned_site_id]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO cloned_site_settings (cloned_site_id, facebook_pixel_id, google_analytics_id, custom_head_scripts) VALUES (?, ?, ?, ?)");
            $stmt->execute([$cloned_site_id, $facebook_pixel, $google_analytics, $custom_scripts]);
        }
        
        error_log("Cloned Sites API: Site $cloned_site_id salvo com sucesso. Slug: $slug, Status: $status");
        
        echo json_encode(['success' => true, 'message' => 'Site salvo com sucesso!', 'slug' => $slug]);
        
    } catch (PDOException $e) {
        error_log("Cloned Sites API ERROR: " . $e->getMessage());
        echo json_encode(['success' => false, 'error' => 'Erro ao salvar: ' . $e->getMessage()]);
    }
    exit;
}

// ============================================
// AÇÃO: LISTAR SITES CLONADOS
// ============================================
if ($action === 'get_cloned_sites') {
    try {
        $stmt = $pdo->prepare("SELECT id, original_url, title, slug, status, created_at FROM cloned_sites WHERE usuario_id = ? ORDER BY created_at DESC");
        $stmt->execute([$usuario_id]);
        $sites = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['success' => true, 'cloned_sites' => $sites]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => 'Erro ao buscar sites: ' . $e->getMessage()]);
    }
    exit;
}

// ============================================
// AÇÃO: OBTER DETALHES DO SITE
// ============================================
if ($action === 'get_cloned_site_details') {
    $cloned_site_id = $_GET['cloned_site_id'] ?? $input['cloned_site_id'] ?? null;
    
    if (!$cloned_site_id) {
        echo json_encode(['success' => false, 'error' => 'ID do site é obrigatório.']);
        exit;
    }
    
    try {
        $stmt = $pdo->prepare("
            SELECT cs.*, css.facebook_pixel_id, css.google_analytics_id, css.custom_head_scripts
            FROM cloned_sites cs
            LEFT JOIN cloned_site_settings css ON cs.id = css.cloned_site_id
            WHERE cs.id = ? AND cs.usuario_id = ?
        ");
        $stmt->execute([$cloned_site_id, $usuario_id]);
        $site = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$site) {
            echo json_encode(['success' => false, 'error' => 'Site não encontrado.']);
            exit;
        }
        
        echo json_encode(['success' => true, 'details' => $site]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => 'Erro ao buscar detalhes: ' . $e->getMessage()]);
    }
    exit;
}

// ============================================
// AÇÃO: CLONAR URL
// ============================================
if ($action === 'clone_url' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $url = trim($input['url'] ?? '');
    
    if (empty($url)) {
        echo json_encode(['success' => false, 'error' => 'URL não fornecida.']);
        exit;
    }
    
    // Adicionar https:// se não tiver
    if (!preg_match('/^https?:\/\//i', $url)) {
        $url = 'https://' . $url;
    }
    
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        echo json_encode(['success' => false, 'error' => 'URL inválida.']);
        exit;
    }
    
    try {
        // Buscar conteúdo da URL
        $context = stream_context_create([
            'http' => [
                'header' => 'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'timeout' => 10,
                'follow_location' => true
            ],
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false
            ]
        ]);
        
        $html = @file_get_contents($url, false, $context);
        
        if ($html === false) {
            echo json_encode(['success' => false, 'error' => 'Não foi possível acessar a URL.']);
            exit;
        }
        
        // Extrair título
        $title = 'Site Clonado';
        if (preg_match('/<title[^>]*>([^<]+)<\/title>/i', $html, $matches)) {
            $title = html_entity_decode(trim($matches[1]), ENT_QUOTES, 'UTF-8');
        }
        
        // Resolver URLs relativas para absolutas
        $base_parts = parse_url($url);
        $base_url = $base_parts['scheme'] . '://' . $base_parts['host'];
        
        // Converter URLs relativas em absolutas
        $html = preg_replace_callback(
            '/(src|href)=["\'](?!https?:\/\/|\/\/|data:|#)([^"\']+)["\']/i',
            function($matches) use ($base_url) {
                $attr = $matches[1];
                $path = $matches[2];
                if (strpos($path, '/') === 0) {
                    return $attr . '="' . $base_url . $path . '"';
                }
                return $attr . '="' . $base_url . '/' . $path . '"';
            },
            $html
        );
        
        // Inserir no banco
        $stmt = $pdo->prepare("INSERT INTO cloned_sites (usuario_id, original_url, title, original_html, edited_html, status) VALUES (?, ?, ?, ?, ?, 'draft')");
        $stmt->execute([$usuario_id, $url, $title, $html, $html]);
        $cloned_site_id = $pdo->lastInsertId();
        
        error_log("Cloned Sites API: Site clonado com sucesso. ID: $cloned_site_id, URL: $url");
        
        echo json_encode([
            'success' => true,
            'message' => 'Site clonado com sucesso!',
            'cloned_site_id' => $cloned_site_id,
            'html_content' => $html,
            'title' => $title,
            'original_url' => $url
        ]);
        
    } catch (Exception $e) {
        error_log("Cloned Sites API ERROR: " . $e->getMessage());
        echo json_encode(['success' => false, 'error' => 'Erro ao clonar: ' . $e->getMessage()]);
    }
    exit;
}

// ============================================
// AÇÃO: EXCLUIR SITE
// ============================================
if ($action === 'delete_cloned_site' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $cloned_site_id = $input['cloned_site_id'] ?? null;
    
    if (!$cloned_site_id) {
        echo json_encode(['success' => false, 'error' => 'ID do site é obrigatório.']);
        exit;
    }
    
    try {
        // Verificar propriedade
        $stmt = $pdo->prepare("SELECT id FROM cloned_sites WHERE id = ? AND usuario_id = ?");
        $stmt->execute([$cloned_site_id, $usuario_id]);
        
        if ($stmt->rowCount() === 0) {
            echo json_encode(['success' => false, 'error' => 'Site não encontrado.']);
            exit;
        }
        
        // Excluir configurações primeiro
        $stmt = $pdo->prepare("DELETE FROM cloned_site_settings WHERE cloned_site_id = ?");
        $stmt->execute([$cloned_site_id]);
        
        // Excluir site
        $stmt = $pdo->prepare("DELETE FROM cloned_sites WHERE id = ?");
        $stmt->execute([$cloned_site_id]);
        
        echo json_encode(['success' => true, 'message' => 'Site excluído com sucesso!']);
        
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => 'Erro ao excluir: ' . $e->getMessage()]);
    }
    exit;
}

// Ação não reconhecida
echo json_encode(['success' => false, 'error' => 'Ação inválida: ' . $action]);
?>
