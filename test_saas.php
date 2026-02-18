<?php
/**
 * Script de diagnóstico e correção SaaS
 * Acesse: /test_saas.php
 * Autor: Rafael Souza - https://rafaelsouzatech.com.br
 */

require_once __DIR__ . '/config/config.php';

header('Content-Type: text/html; charset=utf-8');
echo "<h1>Diagnóstico e Correção SaaS</h1>";

// DROPAR E RECRIAR tabelas com estrutura correta se solicitado
if (isset($_GET['fix_all']) && $_GET['fix_all'] === '1') {
    echo "<h2 style='color:orange'>⚠ RECRIANDO TABELAS SAAS...</h2>";
    
    try {
        // Dropar tabela saas_payment_methods e recriar
        $pdo->exec("DROP TABLE IF EXISTS saas_payment_methods");
        $pdo->exec("
            CREATE TABLE saas_payment_methods (
                id INT AUTO_INCREMENT PRIMARY KEY,
                payment_method VARCHAR(50) NOT NULL UNIQUE,
                gateway VARCHAR(50) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        echo "<p style='color:green'>✓ Tabela saas_payment_methods recriada!</p>";
        
        // Dropar tabela saas_admin_gateways e recriar
        $pdo->exec("DROP TABLE IF EXISTS saas_admin_gateways");
        $pdo->exec("
            CREATE TABLE saas_admin_gateways (
                id INT AUTO_INCREMENT PRIMARY KEY,
                gateway VARCHAR(50) NOT NULL UNIQUE,
                mp_access_token TEXT,
                mp_public_key TEXT,
                efi_client_id TEXT,
                efi_client_secret TEXT,
                efi_pix_key VARCHAR(255),
                efi_payee_code VARCHAR(255),
                efi_certificate_path VARCHAR(255),
                pushinpay_token TEXT,
                beehive_secret_key TEXT,
                beehive_public_key TEXT,
                hypercash_secret_key TEXT,
                hypercash_public_key TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        echo "<p style='color:green'>✓ Tabela saas_admin_gateways recriada!</p>";
        
        // Dropar tabela saas_planos e recriar
        $pdo->exec("DROP TABLE IF EXISTS saas_planos");
        $pdo->exec("
            CREATE TABLE saas_planos (
                id INT AUTO_INCREMENT PRIMARY KEY,
                nome VARCHAR(100) NOT NULL,
                descricao TEXT,
                preco DECIMAL(10,2) NOT NULL DEFAULT 0,
                periodo VARCHAR(20) DEFAULT 'mensal',
                max_produtos INT DEFAULT NULL,
                max_pedidos_mes INT DEFAULT NULL,
                is_free TINYINT(1) DEFAULT 0,
                ativo TINYINT(1) DEFAULT 1,
                ordem INT DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        echo "<p style='color:green'>✓ Tabela saas_planos recriada!</p>";
        
        // Dropar tabela saas_assinaturas e recriar
        $pdo->exec("DROP TABLE IF EXISTS saas_assinaturas");
        $pdo->exec("
            CREATE TABLE saas_assinaturas (
                id INT AUTO_INCREMENT PRIMARY KEY,
                usuario_id INT NOT NULL,
                plano_id INT NOT NULL,
                status VARCHAR(20) DEFAULT 'pendente',
                data_inicio DATE,
                data_vencimento DATE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_usuario (usuario_id),
                INDEX idx_plano (plano_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        echo "<p style='color:green'>✓ Tabela saas_assinaturas recriada!</p>";
        
        echo "<h2 style='color:green'>✓ TODAS AS TABELAS SAAS FORAM RECRIADAS COM SUCESSO!</h2>";
        echo "<p><a href='/admin?pagina=saas_config'><button style='padding:10px 20px;background:#4ade80;border:none;border-radius:5px;cursor:pointer;font-weight:bold;'>Ir para Configurações SaaS</button></a></p>";
        exit;
        
    } catch (PDOException $e) {
        echo "<p style='color:red'>✗ Erro: " . $e->getMessage() . "</p>";
    }
}

// Limpar imagem de login do banco se solicitado
if (isset($_GET['clear_login_image']) && $_GET['clear_login_image'] === '1') {
    try {
        $pdo->exec("DELETE FROM configuracoes WHERE chave = 'login_image_url'");
        echo "<p style='color:green'>✓ Imagem de login removida do banco de dados!</p>";
    } catch (PDOException $e) {
        echo "<p style='color:red'>✗ Erro ao remover: " . $e->getMessage() . "</p>";
    }
}

// Corrigir tabela notificacoes se solicitado
if (isset($_GET['fix_notifications']) && $_GET['fix_notifications'] === '1') {
    echo "<h2>Corrigindo tabela notificacoes...</h2>";
    try {
        // Verificar se tabela existe
        $stmt = $pdo->query("SHOW TABLES LIKE 'notificacoes'");
        if ($stmt->rowCount() === 0) {
            // Criar tabela
            $pdo->exec("CREATE TABLE notificacoes (
                id INT AUTO_INCREMENT PRIMARY KEY,
                usuario_id INT NOT NULL,
                tipo VARCHAR(50) NOT NULL,
                mensagem TEXT,
                valor DECIMAL(10,2) DEFAULT NULL,
                data_notificacao DATETIME DEFAULT CURRENT_TIMESTAMP,
                lida TINYINT(1) DEFAULT 0,
                link_acao VARCHAR(255) DEFAULT NULL,
                displayed_live TINYINT(1) DEFAULT 0,
                venda_id_fk INT DEFAULT NULL,
                metodo_pagamento VARCHAR(50) DEFAULT NULL,
                INDEX idx_usuario (usuario_id),
                INDEX idx_data (data_notificacao)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
            echo "<p style='color:green'>✓ Tabela notificacoes criada com sucesso!</p>";
        } else {
            // Adicionar colunas faltantes
            $cols = $pdo->query("DESCRIBE notificacoes")->fetchAll(PDO::FETCH_ASSOC);
            $existing_cols = array_column($cols, 'Field');
            
            $columns_to_add = [
                'displayed_live' => "ADD COLUMN displayed_live TINYINT(1) DEFAULT 0",
                'venda_id_fk' => "ADD COLUMN venda_id_fk INT DEFAULT NULL",
                'metodo_pagamento' => "ADD COLUMN metodo_pagamento VARCHAR(50) DEFAULT NULL",
                'link_acao' => "ADD COLUMN link_acao VARCHAR(255) DEFAULT NULL",
                'lida' => "ADD COLUMN lida TINYINT(1) DEFAULT 0",
                'valor' => "ADD COLUMN valor DECIMAL(10,2) DEFAULT NULL"
            ];
            
            foreach ($columns_to_add as $col => $sql) {
                if (!in_array($col, $existing_cols)) {
                    try {
                        $pdo->exec("ALTER TABLE notificacoes $sql");
                        echo "<p style='color:green'>✓ Coluna '$col' adicionada!</p>";
                    } catch (PDOException $e) {
                        echo "<p style='color:orange'>⚠ Coluna '$col': " . $e->getMessage() . "</p>";
                    }
                } else {
                    echo "<p style='color:gray'>- Coluna '$col' já existe</p>";
                }
            }
            echo "<p style='color:green'>✓ Tabela notificacoes verificada e corrigida!</p>";
        }
    } catch (PDOException $e) {
        echo "<p style='color:red'>✗ Erro: " . $e->getMessage() . "</p>";
    }
}

echo "<div style='background:#333;padding:20px;border-radius:10px;margin-bottom:20px;'>";
echo "<h2 style='color:orange;margin-top:0;'>⚠ AÇÃO RECOMENDADA</h2>";
echo "<p>As tabelas SaaS têm estrutura incompatível. Clique no botão abaixo para <strong>RECRIAR TODAS AS TABELAS</strong> com a estrutura correta.</p>";
echo "<p style='color:yellow;'><strong>ATENÇÃO:</strong> Isso vai APAGAR todos os dados existentes nas tabelas SaaS (planos, gateways, preferências).</p>";
echo "<a href='?fix_all=1'><button style='padding:15px 30px;background:#ef4444;color:white;border:none;border-radius:5px;cursor:pointer;font-weight:bold;font-size:16px;'>🔧 RECRIAR TODAS AS TABELAS SAAS</button></a>";
echo " <a href='?clear_login_image=1'><button style='padding:15px 30px;background:#6366f1;color:white;border:none;border-radius:5px;cursor:pointer;font-weight:bold;font-size:16px;'>🗑️ LIMPAR IMAGEM DE LOGIN</button></a>";
echo " <a href='?fix_notifications=1'><button style='padding:15px 30px;background:#22c55e;color:white;border:none;border-radius:5px;cursor:pointer;font-weight:bold;font-size:16px;'>🔔 CORRIGIR NOTIFICAÇÕES</button></a>";
echo "</div>";

// Verificar tabela notificacoes
echo "<h2>Tabela notificacoes</h2>";
try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'notificacoes'");
    if ($stmt->rowCount() > 0) {
        echo "<p style='color:green'>✓ Tabela notificacoes existe</p>";
        $cols = $pdo->query("DESCRIBE notificacoes")->fetchAll(PDO::FETCH_ASSOC);
        $existing_cols = array_column($cols, 'Field');
        echo "<p>Colunas: " . implode(', ', $existing_cols) . "</p>";
        
        // Verificar colunas necessárias
        $required = ['displayed_live', 'venda_id_fk', 'metodo_pagamento', 'link_acao'];
        foreach ($required as $col) {
            if (!in_array($col, $existing_cols)) {
                echo "<p style='color:orange'>⚠ Coluna '$col' não existe - adicionando...</p>";
                $sql = match($col) {
                    'displayed_live' => "ADD COLUMN displayed_live TINYINT(1) DEFAULT 0",
                    'venda_id_fk' => "ADD COLUMN venda_id_fk INT DEFAULT NULL",
                    'metodo_pagamento' => "ADD COLUMN metodo_pagamento VARCHAR(50) DEFAULT NULL",
                    'link_acao' => "ADD COLUMN link_acao VARCHAR(255) DEFAULT NULL",
                    default => null
                };
                if ($sql) {
                    try {
                        $pdo->exec("ALTER TABLE notificacoes $sql");
                        echo "<p style='color:green'>✓ Coluna '$col' adicionada!</p>";
                    } catch (PDOException $e) {
                        echo "<p style='color:red'>✗ Erro: " . $e->getMessage() . "</p>";
                    }
                }
            }
        }
    } else {
        echo "<p style='color:red'>✗ Tabela notificacoes NÃO existe!</p>";
    }
} catch (PDOException $e) {
    echo "<p style='color:red'>✗ Erro: " . $e->getMessage() . "</p>";
}

echo "<hr>";

// 0. Verificar e corrigir estrutura da tabela saas_planos
echo "<h2>0. Estrutura da tabela saas_planos</h2>";
try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'saas_planos'");
    if ($stmt->rowCount() > 0) {
        echo "<p style='color:green'>✓ Tabela saas_planos existe</p>";
        
        // Verificar colunas existentes
        $cols = $pdo->query("DESCRIBE saas_planos")->fetchAll(PDO::FETCH_ASSOC);
        $existing_cols = array_column($cols, 'Field');
        
        // Colunas necessárias
        $required_cols = [
            'preco' => "ADD COLUMN preco DECIMAL(10,2) NOT NULL DEFAULT 0",
            'periodo' => "ADD COLUMN periodo VARCHAR(20) DEFAULT 'mensal'",
            'max_produtos' => "ADD COLUMN max_produtos INT DEFAULT NULL",
            'max_pedidos_mes' => "ADD COLUMN max_pedidos_mes INT DEFAULT NULL",
            'is_free' => "ADD COLUMN is_free TINYINT(1) DEFAULT 0",
            'ativo' => "ADD COLUMN ativo TINYINT(1) DEFAULT 1",
            'ordem' => "ADD COLUMN ordem INT DEFAULT 0"
        ];
        
        // Adicionar colunas faltantes
        foreach ($required_cols as $col => $sql) {
            if (!in_array($col, $existing_cols)) {
                echo "<p style='color:orange'>⚠ Coluna '$col' não existe - adicionando...</p>";
                try {
                    $pdo->exec("ALTER TABLE saas_planos $sql");
                    echo "<p style='color:green'>✓ Coluna '$col' adicionada!</p>";
                } catch (PDOException $e) {
                    echo "<p style='color:red'>✗ Erro ao adicionar '$col': " . $e->getMessage() . "</p>";
                }
            }
        }
        
        // Mostrar estrutura final
        echo "<p><strong>Estrutura atual:</strong></p>";
        $cols = $pdo->query("DESCRIBE saas_planos")->fetchAll(PDO::FETCH_ASSOC);
        echo "<pre>";
        print_r($cols);
        echo "</pre>";
    } else {
        echo "<p style='color:orange'>⚠ Tabela saas_planos NÃO existe - criando...</p>";
        $pdo->exec("
            CREATE TABLE saas_planos (
                id INT AUTO_INCREMENT PRIMARY KEY,
                nome VARCHAR(100) NOT NULL,
                descricao TEXT,
                preco DECIMAL(10,2) NOT NULL DEFAULT 0,
                periodo VARCHAR(20) DEFAULT 'mensal',
                max_produtos INT DEFAULT NULL,
                max_pedidos_mes INT DEFAULT NULL,
                is_free TINYINT(1) DEFAULT 0,
                ativo TINYINT(1) DEFAULT 1,
                ordem INT DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");
        echo "<p style='color:green'>✓ Tabela saas_planos criada!</p>";
    }
} catch (PDOException $e) {
    echo "<p style='color:red'>✗ Erro: " . $e->getMessage() . "</p>";
}

// 0.5 Verificar e corrigir tabela saas_admin_gateways
echo "<h2>0.5 Estrutura da tabela saas_admin_gateways</h2>";
try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'saas_admin_gateways'");
    if ($stmt->rowCount() > 0) {
        echo "<p style='color:green'>✓ Tabela saas_admin_gateways existe</p>";
        
        $cols = $pdo->query("DESCRIBE saas_admin_gateways")->fetchAll(PDO::FETCH_ASSOC);
        $existing_cols = array_column($cols, 'Field');
        
        $required_cols = [
            'mp_access_token' => "ADD COLUMN mp_access_token TEXT",
            'mp_public_key' => "ADD COLUMN mp_public_key TEXT",
            'efi_client_id' => "ADD COLUMN efi_client_id TEXT",
            'efi_client_secret' => "ADD COLUMN efi_client_secret TEXT",
            'efi_pix_key' => "ADD COLUMN efi_pix_key VARCHAR(255)",
            'efi_payee_code' => "ADD COLUMN efi_payee_code VARCHAR(255)",
            'efi_certificate_path' => "ADD COLUMN efi_certificate_path VARCHAR(255)",
            'pushinpay_token' => "ADD COLUMN pushinpay_token TEXT",
            'beehive_secret_key' => "ADD COLUMN beehive_secret_key TEXT",
            'beehive_public_key' => "ADD COLUMN beehive_public_key TEXT",
            'hypercash_secret_key' => "ADD COLUMN hypercash_secret_key TEXT",
            'hypercash_public_key' => "ADD COLUMN hypercash_public_key TEXT"
        ];
        
        foreach ($required_cols as $col => $sql) {
            if (!in_array($col, $existing_cols)) {
                echo "<p style='color:orange'>⚠ Coluna '$col' não existe - adicionando...</p>";
                try {
                    $pdo->exec("ALTER TABLE saas_admin_gateways $sql");
                    echo "<p style='color:green'>✓ Coluna '$col' adicionada!</p>";
                } catch (PDOException $e) {
                    echo "<p style='color:red'>✗ Erro ao adicionar '$col': " . $e->getMessage() . "</p>";
                }
            }
        }
        
        echo "<p><strong>Estrutura atual:</strong></p><pre>";
        $cols = $pdo->query("DESCRIBE saas_admin_gateways")->fetchAll(PDO::FETCH_ASSOC);
        print_r($cols);
        echo "</pre>";
    } else {
        echo "<p style='color:orange'>⚠ Tabela saas_admin_gateways NÃO existe - criando...</p>";
        $pdo->exec("
            CREATE TABLE saas_admin_gateways (
                id INT AUTO_INCREMENT PRIMARY KEY,
                gateway VARCHAR(50) NOT NULL UNIQUE,
                mp_access_token TEXT,
                mp_public_key TEXT,
                efi_client_id TEXT,
                efi_client_secret TEXT,
                efi_pix_key VARCHAR(255),
                efi_payee_code VARCHAR(255),
                efi_certificate_path VARCHAR(255),
                pushinpay_token TEXT,
                beehive_secret_key TEXT,
                beehive_public_key TEXT,
                hypercash_secret_key TEXT,
                hypercash_public_key TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )
        ");
        echo "<p style='color:green'>✓ Tabela saas_admin_gateways criada!</p>";
    }
} catch (PDOException $e) {
    echo "<p style='color:red'>✗ Erro: " . $e->getMessage() . "</p>";
}

// 0.6 Verificar tabela saas_payment_methods
echo "<h2>0.6 Tabela saas_payment_methods</h2>";
try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'saas_payment_methods'");
    if ($stmt->rowCount() == 0) {
        echo "<p style='color:orange'>⚠ Tabela saas_payment_methods NÃO existe - criando...</p>";
        $pdo->exec("
            CREATE TABLE saas_payment_methods (
                id INT AUTO_INCREMENT PRIMARY KEY,
                payment_method VARCHAR(50) NOT NULL UNIQUE,
                gateway VARCHAR(50) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )
        ");
        echo "<p style='color:green'>✓ Tabela saas_payment_methods criada!</p>";
    } else {
        echo "<p style='color:green'>✓ Tabela saas_payment_methods existe</p>";
        
        // Verificar colunas
        $cols = $pdo->query("DESCRIBE saas_payment_methods")->fetchAll(PDO::FETCH_ASSOC);
        $existing_cols = array_column($cols, 'Field');
        
        $required_cols = [
            'payment_method' => "ADD COLUMN payment_method VARCHAR(50) NOT NULL",
            'gateway' => "ADD COLUMN gateway VARCHAR(50) NOT NULL"
        ];
        
        foreach ($required_cols as $col => $sql) {
            if (!in_array($col, $existing_cols)) {
                echo "<p style='color:orange'>⚠ Coluna '$col' não existe - adicionando...</p>";
                try {
                    $pdo->exec("ALTER TABLE saas_payment_methods $sql");
                    echo "<p style='color:green'>✓ Coluna '$col' adicionada!</p>";
                } catch (PDOException $e) {
                    echo "<p style='color:red'>✗ Erro ao adicionar '$col': " . $e->getMessage() . "</p>";
                }
            }
        }
        
        echo "<p><strong>Estrutura atual:</strong></p><pre>";
        $cols = $pdo->query("DESCRIBE saas_payment_methods")->fetchAll(PDO::FETCH_ASSOC);
        print_r($cols);
        echo "</pre>";
    }
} catch (PDOException $e) {
    echo "<p style='color:red'>✗ Erro: " . $e->getMessage() . "</p>";
}

// 1. Testar conexão PDO
echo "<h2>1. Conexão PDO</h2>";
if (isset($pdo) && $pdo instanceof PDO) {
    echo "<p style='color:green'>✓ PDO está disponível</p>";
} else {
    echo "<p style='color:red'>✗ PDO NÃO está disponível</p>";
    exit;
}

// 2. Testar se tabela existe
echo "<h2>2. Tabela saas_config</h2>";
try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'saas_config'");
    if ($stmt->rowCount() > 0) {
        echo "<p style='color:green'>✓ Tabela saas_config existe</p>";
        
        // Mostrar estrutura
        $cols = $pdo->query("DESCRIBE saas_config")->fetchAll(PDO::FETCH_ASSOC);
        echo "<pre>";
        print_r($cols);
        echo "</pre>";
        
        // Mostrar dados
        $data = $pdo->query("SELECT * FROM saas_config")->fetchAll(PDO::FETCH_ASSOC);
        echo "<p>Dados:</p><pre>";
        print_r($data);
        echo "</pre>";
    } else {
        echo "<p style='color:orange'>⚠ Tabela saas_config NÃO existe</p>";
        
        // Tentar criar
        echo "<p>Tentando criar tabela...</p>";
        try {
            $pdo->exec("
                CREATE TABLE saas_config (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    enabled TINYINT(1) DEFAULT 0,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )
            ");
            echo "<p style='color:green'>✓ Tabela criada com sucesso!</p>";
        } catch (PDOException $e) {
            echo "<p style='color:red'>✗ Erro ao criar: " . $e->getMessage() . "</p>";
        }
    }
} catch (PDOException $e) {
    echo "<p style='color:red'>✗ Erro: " . $e->getMessage() . "</p>";
}

// 3. Testar função saas_enable
echo "<h2>3. Testar saas_enable()</h2>";
if (file_exists(__DIR__ . '/saas/includes/saas_functions.php')) {
    require_once __DIR__ . '/saas/includes/saas_functions.php';
    
    if (function_exists('saas_enable')) {
        echo "<p style='color:green'>✓ Função saas_enable existe</p>";
        
        // Tentar habilitar
        echo "<p>Tentando habilitar SaaS...</p>";
        $result = saas_enable();
        if ($result) {
            echo "<p style='color:green'>✓ SaaS habilitado com sucesso!</p>";
        } else {
            echo "<p style='color:red'>✗ Falha ao habilitar SaaS</p>";
        }
    } else {
        echo "<p style='color:red'>✗ Função saas_enable NÃO existe</p>";
    }
} else {
    echo "<p style='color:red'>✗ Arquivo saas_functions.php NÃO existe</p>";
}

// 4. Verificar status final
echo "<h2>4. Status Final</h2>";
if (function_exists('saas_enabled')) {
    $status = saas_enabled();
    echo "<p>SaaS habilitado: " . ($status ? "<span style='color:green'>SIM</span>" : "<span style='color:red'>NÃO</span>") . "</p>";
}

echo "<hr><p><a href='/admin?pagina=saas_config'>Voltar para Configurações SaaS</a></p>";
