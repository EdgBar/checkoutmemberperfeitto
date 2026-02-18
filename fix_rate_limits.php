<?php
/**
 * Corrigir tabela rate_limits
 * @author Souza Tech - https://rafaelsouzatech.com.br
 */
require_once __DIR__ . '/config/config.php';

echo "<h2>Corrigindo tabela rate_limits</h2>";

try {
    // Verificar se tabela existe
    $stmt = $pdo->query("SHOW TABLES LIKE 'rate_limits'");
    if ($stmt->rowCount() === 0) {
        echo "<p>Tabela não existe, criando...</p>";
        $pdo->exec("
            CREATE TABLE rate_limits (
                id INT AUTO_INCREMENT PRIMARY KEY,
                rate_key VARCHAR(255) NOT NULL,
                identifier VARCHAR(255),
                attempts INT DEFAULT 1,
                first_attempt DATETIME DEFAULT CURRENT_TIMESTAMP,
                last_attempt DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                blocked_until DATETIME NULL,
                INDEX idx_key_identifier (rate_key, identifier),
                INDEX idx_last_attempt (last_attempt)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        echo "<p style='color:green'>✓ Tabela criada com sucesso!</p>";
    } else {
        echo "<p>Tabela existe, verificando colunas...</p>";
        
        // Verificar colunas
        $cols = $pdo->query("DESCRIBE rate_limits")->fetchAll(PDO::FETCH_COLUMN);
        echo "<p>Colunas existentes: " . implode(', ', $cols) . "</p>";
        
        // Adicionar coluna last_attempt se não existir
        if (!in_array('last_attempt', $cols)) {
            echo "<p>Adicionando coluna last_attempt...</p>";
            $pdo->exec("ALTER TABLE rate_limits ADD COLUMN last_attempt DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");
            echo "<p style='color:green'>✓ Coluna last_attempt adicionada!</p>";
        } else {
            echo "<p style='color:green'>✓ Coluna last_attempt já existe</p>";
        }
        
        // Adicionar coluna first_attempt se não existir
        if (!in_array('first_attempt', $cols)) {
            echo "<p>Adicionando coluna first_attempt...</p>";
            $pdo->exec("ALTER TABLE rate_limits ADD COLUMN first_attempt DATETIME DEFAULT CURRENT_TIMESTAMP");
            echo "<p style='color:green'>✓ Coluna first_attempt adicionada!</p>";
        }
        
        // Adicionar coluna blocked_until se não existir
        if (!in_array('blocked_until', $cols)) {
            echo "<p>Adicionando coluna blocked_until...</p>";
            $pdo->exec("ALTER TABLE rate_limits ADD COLUMN blocked_until DATETIME NULL");
            echo "<p style='color:green'>✓ Coluna blocked_until adicionada!</p>";
        }
    }
    
    echo "<h3>Estrutura final da tabela:</h3>";
    $cols = $pdo->query("DESCRIBE rate_limits")->fetchAll(PDO::FETCH_ASSOC);
    echo "<pre>" . print_r($cols, true) . "</pre>";
    
    echo "<p style='color:green;font-weight:bold'>✓ Tabela rate_limits corrigida!</p>";
    
} catch (PDOException $e) {
    echo "<p style='color:red'>Erro: " . $e->getMessage() . "</p>";
}

// Criar notificações para vendas sem notificação
echo "<hr><h2>Criar notificações para vendas sem notificação</h2>";

if (isset($_GET['create_notifs']) && $_GET['create_notifs'] === '1') {
    try {
        $stmt = $pdo->query("SELECT v.id, v.valor, v.produto_id, p.nome as produto_nome, p.usuario_id as produtor_id
                             FROM vendas v 
                             LEFT JOIN produtos p ON v.produto_id = p.id
                             LEFT JOIN notificacoes n ON n.venda_id_fk = v.id 
                             WHERE n.id IS NULL");
        $vendas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $created = 0;
        foreach ($vendas as $v) {
            $produtor_id = $v['produtor_id'] ?? 33;
            $valor = $v['valor'] ?? 0;
            $mensagem = "Nova venda: " . ($v['produto_nome'] ?? 'Produto') . " - R$ " . number_format($valor, 2, ',', '.');
            
            $stmt_insert = $pdo->prepare("INSERT INTO notificacoes (usuario_id, tipo, mensagem, valor, venda_id_fk, data_notificacao) VALUES (?, 'venda', ?, ?, ?, NOW())");
            $stmt_insert->execute([$produtor_id, $mensagem, $valor, $v['id']]);
            $created++;
            echo "<p>✓ Notificação criada para venda #{$v['id']}</p>";
        }
        
        echo "<p style='color:green;font-weight:bold'>✓ $created notificações criadas!</p>";
    } catch (PDOException $e) {
        echo "<p style='color:red'>Erro: " . $e->getMessage() . "</p>";
    }
} else {
    // Mostrar vendas sem notificação
    try {
        $stmt = $pdo->query("SELECT v.id, v.valor, v.status_pagamento FROM vendas v 
                             LEFT JOIN notificacoes n ON n.venda_id_fk = v.id 
                             WHERE n.id IS NULL ORDER BY v.id DESC LIMIT 10");
        $vendas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($vendas) > 0) {
            echo "<p style='color:orange'>⚠ " . count($vendas) . " vendas sem notificação:</p>";
            foreach ($vendas as $v) {
                echo "<p>- Venda #{$v['id']} - R$ " . number_format($v['valor'], 2, ',', '.') . " ({$v['status_pagamento']})</p>";
            }
            echo "<br><a href='?create_notifs=1'><button style='padding:10px 20px;background:#22c55e;color:white;border:none;border-radius:5px;cursor:pointer;font-weight:bold;'>Criar Notificações Faltantes</button></a>";
        } else {
            echo "<p style='color:green'>✓ Todas as vendas têm notificação</p>";
        }
    } catch (PDOException $e) {
        echo "<p style='color:red'>Erro: " . $e->getMessage() . "</p>";
    }
}

echo "<hr><p><a href='/index?pagina=dashboard'>Voltar ao Dashboard</a></p>";
?>
