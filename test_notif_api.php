<?php
/**
 * Teste direto da API de notificações
 * @author Souza Tech - https://rafaelsouzatech.com.br
 */
session_start();
header('Content-Type: text/html; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/config/config.php';

echo "<h2>Teste da API de Notificações</h2>";
echo "<p><strong>Data:</strong> " . date('d/m/Y H:i:s') . "</p>";

// 0. Verificar sessão
echo "<h3>0. Verificando Sessão:</h3>";
echo "<p><strong>Session ID:</strong> " . session_id() . "</p>";
echo "<p><strong>Usuario logado (SESSION[id]):</strong> " . (isset($_SESSION['id']) ? $_SESSION['id'] : '<span style=\"color:red\">NÃO LOGADO</span>') . "</p>";
if (!isset($_SESSION['id'])) {
    echo "<p style='color:orange'>⚠️ Você precisa estar logado para ver notificações. Faça login primeiro.</p>";
}

// 1. Verificar se tabela existe
echo "<h3>1. Verificando tabela notificacoes:</h3>";
try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'notificacoes'");
    if ($stmt->rowCount() === 0) {
        echo "<p style='color:red'>✗ Tabela notificacoes NÃO existe!</p>";
        
        // Criar tabela
        echo "<p>Criando tabela...</p>";
        $pdo->exec("CREATE TABLE IF NOT EXISTS notificacoes (
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
        echo "<p style='color:green'>✓ Tabela criada!</p>";
    } else {
        echo "<p style='color:green'>✓ Tabela existe</p>";
    }
} catch (PDOException $e) {
    echo "<p style='color:red'>Erro: " . $e->getMessage() . "</p>";
}

// 2. Verificar estrutura
echo "<h3>2. Estrutura da tabela:</h3>";
try {
    $cols = $pdo->query("DESCRIBE notificacoes")->fetchAll(PDO::FETCH_ASSOC);
    $existing_cols = array_column($cols, 'Field');
    echo "<p>Colunas: " . implode(', ', $existing_cols) . "</p>";
    
    // Verificar e adicionar colunas faltantes
    $required = [
        'displayed_live' => "ADD COLUMN displayed_live TINYINT(1) DEFAULT 0",
        'venda_id_fk' => "ADD COLUMN venda_id_fk INT DEFAULT NULL",
        'metodo_pagamento' => "ADD COLUMN metodo_pagamento VARCHAR(50) DEFAULT NULL",
        'link_acao' => "ADD COLUMN link_acao VARCHAR(255) DEFAULT NULL",
        'lida' => "ADD COLUMN lida TINYINT(1) DEFAULT 0",
        'valor' => "ADD COLUMN valor DECIMAL(10,2) DEFAULT NULL"
    ];
    
    foreach ($required as $col => $sql) {
        if (!in_array($col, $existing_cols)) {
            echo "<p style='color:orange'>⚠ Coluna '$col' faltando - adicionando...</p>";
            try {
                $pdo->exec("ALTER TABLE notificacoes $sql");
                echo "<p style='color:green'>✓ Coluna '$col' adicionada!</p>";
            } catch (PDOException $e) {
                echo "<p style='color:red'>Erro ao adicionar '$col': " . $e->getMessage() . "</p>";
            }
        }
    }
} catch (PDOException $e) {
    echo "<p style='color:red'>Erro: " . $e->getMessage() . "</p>";
}

// 3. Testar query exata usada pela API
echo "<h3>3. Testando query da API:</h3>";
try {
    // Simular usuário logado (pegar primeiro usuário admin)
    $user = $pdo->query("SELECT id FROM usuarios LIMIT 1")->fetch(PDO::FETCH_ASSOC);
    $uid = $user ? $user['id'] : 1;
    echo "<p>Usando usuario_id: $uid</p>";
    
    $s = $pdo->prepare("SELECT id, tipo, mensagem, valor, DATE_FORMAT(data_notificacao, '%Y-%m-%dT%H:%i:%s') as data_notificacao, lida, link_acao FROM notificacoes WHERE usuario_id = ? ORDER BY data_notificacao DESC LIMIT 10");
    $s->execute([$uid]);
    $results = $s->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p style='color:green'>✓ Query executada com sucesso!</p>";
    echo "<p>Notificações encontradas: " . count($results) . "</p>";
    
    if (count($results) > 0) {
        echo "<pre>" . print_r($results, true) . "</pre>";
    }
} catch (PDOException $e) {
    echo "<p style='color:red'>✗ Erro na query: " . $e->getMessage() . "</p>";
}

// 4. Testar chamada direta à API
echo "<h3>4. Testando API diretamente:</h3>";
if (isset($_SESSION['id'])) {
    $uid = $_SESSION['id'];
    echo "<p>Buscando notificações para usuario_id: $uid</p>";
    
    try {
        $s = $pdo->prepare("SELECT id, tipo, mensagem, valor, DATE_FORMAT(data_notificacao, '%Y-%m-%dT%H:%i:%s') as data_notificacao, lida, link_acao FROM notificacoes WHERE usuario_id = ? ORDER BY data_notificacao DESC LIMIT 10");
        $s->execute([$uid]);
        $results = $s->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<p style='color:green'>✓ Query OK! Encontradas: " . count($results) . " notificações</p>";
        
        if (count($results) > 0) {
            echo "<table border='1' cellpadding='5'><tr><th>ID</th><th>Tipo</th><th>Mensagem</th><th>Valor</th><th>Data</th></tr>";
            foreach ($results as $r) {
                echo "<tr><td>{$r['id']}</td><td>{$r['tipo']}</td><td>" . substr($r['mensagem'], 0, 40) . "</td><td>R$ " . number_format($r['valor'] ?? 0, 2, ',', '.') . "</td><td>{$r['data_notificacao']}</td></tr>";
            }
            echo "</table>";
        }
    } catch (PDOException $e) {
        echo "<p style='color:red'>✗ Erro: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p style='color:red'>✗ Você precisa estar logado! Faça login primeiro.</p>";
}

echo "<hr><p><a href='/index'>Voltar ao Dashboard</a></p>";
?>
