<?php
/**
 * Arquivo de configuração do sistema
 */

// Suprimir warnings em produção para não quebrar JSON da API
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);

// ---- Bloqueio por licença: o sistema só funciona com licença válida ----
// Para reativar: descomente as 3 linhas abaixo.
// require_once __DIR__ . '/../licenca_config.php';
// require_once __DIR__ . '/../helpers/licenca_helper.php';
// validarLicencaCheckoutPRO();

define('DB_HOST', 'localhost');
define('DB_USER', 'u698447590_bdcheckout');
define('DB_PASS', 'U698447590_bdcheckout');
define('DB_NAME', 'u698447590_bdcheckout');

// Define o fuso horário padrão para o PHP para 'America/Sao_Paulo' (Horário de Brasília)
date_default_timezone_set('America/Sao_Paulo');

// Inicia a sessão ANTES de qualquer output ou configuração de banco
if (session_status() == PHP_SESSION_NONE) {
    // Configura a sessão com timeout mais seguro (1 hora)
    @ini_set('session.cookie_lifetime', 3600);
    @ini_set('session.gc_maxlifetime', 3600);
    
    // Configurações de segurança de sessão
    @ini_set('session.cookie_httponly', 1);
    @ini_set('session.use_strict_mode', 1);
    @ini_set('session.cookie_samesite', 'Lax');
    
    // Se HTTPS disponível, usar cookie seguro
    $is_https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || 
                (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) ||
                (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
    if ($is_https) {
        @ini_set('session.cookie_secure', 1);
    }
    
    session_start();
}

try {
    // Usar conexão persistente para reduzir consumo de conexões por hora
    // Isso reutiliza conexões existentes ao invés de criar novas
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_PERSISTENT => true,  // Conexão persistente
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_EMULATE_PREPARES => false,  // Usar prepared statements nativos
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    // Define o fuso horário da sessão do MySQL para UTC-03:00 (Horário de Brasília)
    $pdo->exec("SET time_zone = '-03:00';");
    
    // Verificar e adicionar coluna checkout_config se não existir
    try {
        $stmt = $pdo->query("SHOW COLUMNS FROM produtos LIKE 'checkout_config'");
        if ($stmt->rowCount() === 0) {
            $pdo->exec("ALTER TABLE produtos ADD COLUMN checkout_config LONGTEXT DEFAULT NULL");
        }
    } catch (PDOException $e) {
        // Ignora erro se tabela não existir ainda
    }
    
    // Verificar e adicionar coluna is_active na tabela order_bumps se não existir
    try {
        $stmt = $pdo->query("SHOW COLUMNS FROM order_bumps LIKE 'is_active'");
        if ($stmt->rowCount() === 0) {
            $pdo->exec("ALTER TABLE order_bumps ADD COLUMN is_active TINYINT(1) DEFAULT 1");
        }
    } catch (PDOException $e) {
        // Ignora erro se tabela não existir ainda
    }

    // Verificar e adicionar coluna is_active na tabela produto_ofertas se não existir
    try {
        $stmt = $pdo->query("SHOW TABLES LIKE 'produto_ofertas'");
        if ($stmt->rowCount() > 0) {
            $stmt = $pdo->query("SHOW COLUMNS FROM produto_ofertas LIKE 'is_active'");
            if ($stmt->rowCount() === 0) {
                $pdo->exec("ALTER TABLE produto_ofertas ADD COLUMN is_active TINYINT(1) DEFAULT 1");
            }
        }
    } catch (PDOException $e) {
        // Ignora erro se tabela não existir ainda
    }

    // Verificar e adicionar coluna saber_mais_url na tabela product_exclusive_offers (link opcional para ofertas na área de membros)
    try {
        $stmt = $pdo->query("SHOW TABLES LIKE 'product_exclusive_offers'");
        if ($stmt->rowCount() > 0) {
            $stmt = $pdo->query("SHOW COLUMNS FROM product_exclusive_offers LIKE 'saber_mais_url'");
            if ($stmt->rowCount() === 0) {
                $pdo->exec("ALTER TABLE product_exclusive_offers ADD COLUMN saber_mais_url VARCHAR(500) DEFAULT NULL");
            }
        }
    } catch (PDOException $e) {
        // Ignora erro se tabela não existir ainda
    }

    // Verificar e adicionar colunas Stripe na tabela usuarios (integração Stripe)
    try {
        $stmt = $pdo->query("SHOW COLUMNS FROM usuarios LIKE 'stripe_secret_key'");
        if ($stmt->rowCount() === 0) {
            $pdo->exec("ALTER TABLE usuarios ADD COLUMN stripe_secret_key VARCHAR(500) DEFAULT NULL");
        }
        $stmt = $pdo->query("SHOW COLUMNS FROM usuarios LIKE 'stripe_publishable_key'");
        if ($stmt->rowCount() === 0) {
            $pdo->exec("ALTER TABLE usuarios ADD COLUMN stripe_publishable_key VARCHAR(500) DEFAULT NULL");
        }
    } catch (PDOException $e) {
        // Ignora erro se tabela não existir ainda
    }
    
    // Verificar e criar tabela notificacoes se não existir
    try {
        $stmt = $pdo->query("SHOW TABLES LIKE 'notificacoes'");
        if ($stmt->rowCount() === 0) {
            $pdo->exec("CREATE TABLE notificacoes (
                id INT AUTO_INCREMENT PRIMARY KEY,
                usuario_id INT NOT NULL,
                tipo VARCHAR(50) NOT NULL DEFAULT 'venda',
                mensagem TEXT,
                valor DECIMAL(10,2) DEFAULT NULL,
                metodo_pagamento VARCHAR(50) DEFAULT NULL,
                venda_id_fk INT DEFAULT NULL,
                data_notificacao DATETIME DEFAULT CURRENT_TIMESTAMP,
                lida TINYINT(1) DEFAULT 0,
                displayed_live TINYINT(1) DEFAULT 0,
                link_acao VARCHAR(500) DEFAULT NULL,
                INDEX idx_usuario_id (usuario_id),
                INDEX idx_lida (lida),
                INDEX idx_displayed_live (displayed_live)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
            error_log("Tabela 'notificacoes' criada automaticamente.");
        }
    } catch (PDOException $e) {
        error_log("Erro ao criar tabela notificacoes: " . $e->getMessage());
    }
    
    // Verificar e criar tabela starfy_tracking_products se não existir
    try {
        $stmt = $pdo->query("SHOW TABLES LIKE 'starfy_tracking_products'");
        if ($stmt->rowCount() === 0) {
            $pdo->exec("CREATE TABLE starfy_tracking_products (
                id INT AUTO_INCREMENT PRIMARY KEY,
                usuario_id INT NOT NULL,
                produto_id INT NOT NULL,
                tracking_id VARCHAR(100) NOT NULL UNIQUE,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_usuario_id (usuario_id),
                INDEX idx_produto_id (produto_id),
                INDEX idx_tracking_id (tracking_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
            error_log("Tabela 'starfy_tracking_products' criada automaticamente.");
        }
    } catch (PDOException $e) {
        error_log("Erro ao criar tabela starfy_tracking_products: " . $e->getMessage());
    }
    
    // Verificar e criar tabela starfy_tracking_events se não existir
    try {
        $stmt = $pdo->query("SHOW TABLES LIKE 'starfy_tracking_events'");
        if ($stmt->rowCount() === 0) {
            $pdo->exec("CREATE TABLE starfy_tracking_events (
                id INT AUTO_INCREMENT PRIMARY KEY,
                tracking_product_id INT NOT NULL,
                session_id VARCHAR(100) NOT NULL,
                event_type VARCHAR(50) NOT NULL,
                event_data JSON DEFAULT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_tracking_product_id (tracking_product_id),
                INDEX idx_session_id (session_id),
                INDEX idx_event_type (event_type),
                INDEX idx_created_at (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
            error_log("Tabela 'starfy_tracking_events' criada automaticamente.");
        }
    } catch (PDOException $e) {
        error_log("Erro ao criar tabela starfy_tracking_events: " . $e->getMessage());
    }
} catch (PDOException $e) {
    // Verificar se é erro de limite de conexões
    if (strpos($e->getMessage(), 'max_connections') !== false || strpos($e->getMessage(), '1226') !== false) {
        die("ERRO: Limite de conexões do banco de dados atingido. Aguarde alguns minutos e tente novamente.");
    }
    die("ERRO: Não foi possível conectar ao banco de dados. " . $e->getMessage());
}

// Configurações de sessão após inicialização
if (session_status() == PHP_SESSION_ACTIVE) {
    
    // Timeout de inatividade (30 minutos)
    if (isset($_SESSION['last_activity'])) {
        $inactivity_timeout = 1800; // 30 minutos
        if ((time() - $_SESSION['last_activity']) > $inactivity_timeout) {
            // Sessão expirada por inatividade
            session_unset();
            session_destroy();
            session_start();
        }
    }
    $_SESSION['last_activity'] = time();
    
    // Regenerar ID de sessão periodicamente (a cada 30 minutos de atividade)
    if (!isset($_SESSION['last_regeneration'])) {
        $_SESSION['last_regeneration'] = time();
    } elseif ((time() - $_SESSION['last_regeneration']) > 1800) { // 30 minutos
        session_regenerate_id(true);
        $_SESSION['last_regeneration'] = time();
    }
}

// Sistema de "Lembrar-me" - Restaura sessão se houver token válido e não expirado
// Só verifica se a sessão não estiver ativa
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    if (isset($_COOKIE['remember_token']) && !empty($_COOKIE['remember_token'])) {
        try {
            $remember_token = $_COOKIE['remember_token'];
            
            // Verificar se coluna remember_token_expires existe
            $stmt_check_col = $pdo->query("SHOW COLUMNS FROM usuarios LIKE 'remember_token_expires'");
            $col_exists = $stmt_check_col->rowCount() > 0;
            
            if ($col_exists) {
                // Verificar token com expiração
                $stmt = $pdo->prepare("
                    SELECT id, usuario, nome, tipo 
                    FROM usuarios 
                    WHERE remember_token = ? 
                    AND remember_token IS NOT NULL 
                    AND remember_token != ''
                    AND (remember_token_expires IS NULL OR remember_token_expires > NOW())
                ");
            } else {
                // Fallback: verificar sem expiração (compatibilidade)
                $stmt = $pdo->prepare("
                    SELECT id, usuario, nome, tipo 
                    FROM usuarios 
                    WHERE remember_token = ? 
                    AND remember_token IS NOT NULL 
                    AND remember_token != ''
                ");
            }
            
            $stmt->execute([$remember_token]);
            
            if ($stmt->rowCount() == 1) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                // Restaura a sessão
                $_SESSION["loggedin"] = true;
                $_SESSION["id"] = $row["id"];
                $_SESSION["usuario"] = $row["usuario"];
                $_SESSION["nome"] = $row["nome"];
                $_SESSION["tipo"] = $row["tipo"];
                
                if ($row["tipo"] == 'infoprodutor') {
                    $_SESSION['is_infoprodutor'] = true;
                } else {
                    $_SESSION['is_infoprodutor'] = false;
                }
            } else {
                // Token inválido ou expirado, remove o cookie
                setcookie('remember_token', "", time() - 3600, "/");
                setcookie('remember_user', "", time() - 3600, "/");
            }
        } catch (PDOException $e) {
            error_log("Erro ao verificar remember_token: " . $e->getMessage());
            // Em caso de erro, remove cookie por segurança
            setcookie('remember_token', "", time() - 3600, "/");
        }
    }
}

/**
 * Busca uma configuração do sistema
 * @param string $chave Chave da configuração
 * @param string $default Valor padrão se não encontrar
 * @return string Valor da configuração
 */
function getSystemSetting($chave, $default = '') {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT valor FROM configuracoes_sistema WHERE chave = ?");
        $stmt->execute([$chave]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['valor'] : $default;
    } catch (PDOException $e) {
        error_log("Erro ao buscar configuração: " . $e->getMessage());
        return $default;
    }
}

/**
 * Salva ou atualiza uma configuração do sistema
 * @param string $chave Chave da configuração
 * @param string $valor Valor da configuração
 * @return bool True se sucesso, False se erro
 */
function setSystemSetting($chave, $valor) {
    global $pdo;
    
    if (!$pdo) {
        error_log("setSystemSetting: PDO não está disponível");
        return false;
    }
    
    try {
        error_log("setSystemSetting: Tentando salvar chave='$chave', valor='$valor'");
        
        // Primeiro verifica se existe
        $stmt_check = $pdo->prepare("SELECT id FROM configuracoes_sistema WHERE chave = ?");
        $stmt_check->execute([$chave]);
        $exists = $stmt_check->fetch(PDO::FETCH_ASSOC);
        
        if ($exists) {
            error_log("setSystemSetting: Configuração existe, atualizando...");
            // Tenta UPDATE com updated_at primeiro
            try {
                $stmt = $pdo->prepare("UPDATE configuracoes_sistema SET valor = ?, updated_at = CURRENT_TIMESTAMP WHERE chave = ?");
                $result = $stmt->execute([$valor, $chave]);
                error_log("setSystemSetting: UPDATE executado, rows affected: " . $stmt->rowCount());
            } catch (PDOException $e) {
                error_log("setSystemSetting: Erro no UPDATE com updated_at: " . $e->getMessage() . ", tentando sem updated_at...");
                // Se falhar, tenta sem updated_at
                $stmt = $pdo->prepare("UPDATE configuracoes_sistema SET valor = ? WHERE chave = ?");
                $result = $stmt->execute([$valor, $chave]);
                error_log("setSystemSetting: UPDATE sem updated_at executado, rows affected: " . $stmt->rowCount());
            }
        } else {
            error_log("setSystemSetting: Configuração não existe, inserindo nova...");
            // Insere nova
            $stmt = $pdo->prepare("INSERT INTO configuracoes_sistema (chave, valor) VALUES (?, ?)");
            $result = $stmt->execute([$chave, $valor]);
            error_log("setSystemSetting: INSERT executado, rows affected: " . $stmt->rowCount());
        }
        
        // Verifica se realmente salvou
        $stmt_verify = $pdo->prepare("SELECT valor FROM configuracoes_sistema WHERE chave = ?");
        $stmt_verify->execute([$chave]);
        $saved = $stmt_verify->fetch(PDO::FETCH_ASSOC);
        
        if ($saved && $saved['valor'] === $valor) {
            error_log("setSystemSetting: Configuração salva com sucesso!");
            return true;
        } else {
            error_log("setSystemSetting: ERRO - Configuração não foi salva corretamente. Valor esperado: '$valor', Valor salvo: " . ($saved ? $saved['valor'] : 'NULL'));
            return false;
        }
        
    } catch (PDOException $e) {
        error_log("setSystemSetting: EXCEÇÃO ao salvar configuração '$chave': " . $e->getMessage() . " | SQL State: " . $e->getCode() . " | File: " . $e->getFile() . " | Line: " . $e->getLine());
        return false;
    } catch (Exception $e) {
        error_log("setSystemSetting: EXCEÇÃO GERAL ao salvar configuração '$chave': " . $e->getMessage());
        return false;
    }
}

/**
 * Busca todas as configurações do sistema
 * @return array Array associativo com chave => valor
 */
function getAllSystemSettings() {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT chave, valor FROM configuracoes_sistema");
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $settings = [];
        foreach ($results as $row) {
            $settings[$row['chave']] = $row['valor'];
        }
        return $settings;
    } catch (PDOException $e) {
        error_log("Erro ao buscar todas as configurações: " . $e->getMessage());
        return [];
    }
}

/**
 * Obtém a versão atual da plataforma
 * @return string Versão da plataforma (ex: "1.0.0")
 */
function get_platform_version() {
    $version_file = __DIR__ . '/../VERSION.txt';
    if (file_exists($version_file)) {
        $version = trim(file_get_contents($version_file));
        return !empty($version) ? $version : '1.0.0';
    }
    return '1.0.0'; // Versão padrão se arquivo não existir
}

// Carrega sistema de plugins
require_once __DIR__ . '/../helpers/plugin_hooks.php';
require_once __DIR__ . '/../helpers/plugin_loader.php';

// Carregar sistema SaaS se habilitado (verifica saas_config, não plugins)
if (file_exists(__DIR__ . '/../saas/includes/saas_functions.php')) {
    require_once __DIR__ . '/../saas/includes/saas_functions.php';
    if (function_exists('saas_enabled') && saas_enabled()) {
        // Carregar sistema SaaS completo
        if (file_exists(__DIR__ . '/../saas/includes/saas_limits.php')) {
            require_once __DIR__ . '/../saas/includes/saas_limits.php';
        }
        if (file_exists(__DIR__ . '/../saas/saas.php')) {
            require_once __DIR__ . '/../saas/saas.php';
        }
    }
}

// Aplicar headers de segurança (apenas se ainda não foram enviados)
if (!headers_sent()) {
    require_once __DIR__ . '/security_headers.php';
    if (function_exists('apply_security_headers')) {
        apply_security_headers(false); // false = CSP mais permissivo para compatibilidade
    }
}
?>