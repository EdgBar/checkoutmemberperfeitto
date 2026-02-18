<?php
/**
 * CORREÇÃO URGENTE DO SISTEMA DE NOTIFICAÇÕES
 * @author Souza Tech - https://rafaelsouzatech.com.br
 */
require_once __DIR__ . '/config/config.php';

echo "<h1>🚨 CORREÇÃO URGENTE - SISTEMA DE NOTIFICAÇÕES</h1>";

// 1. VERIFICAR E CORRIGIR TABELA NOTIFICAÇÕES
echo "<h2>1. 🔧 CORRIGINDO TABELA NOTIFICAÇÕES</h2>";
try {
    // Verificar se tabela existe
    $stmt = $pdo->query("SHOW TABLES LIKE 'notificacoes'");
    if ($stmt->rowCount() === 0) {
        echo "<p style='color:red'>❌ Tabela notificacoes NÃO EXISTE! Criando...</p>";
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
        echo "<p style='color:green'>✅ Tabela criada!</p>";
    } else {
        echo "<p>✅ Tabela notificacoes existe</p>";
    }
    
    // Verificar colunas
    $cols = $pdo->query("DESCRIBE notificacoes")->fetchAll(PDO::FETCH_COLUMN);
    echo "<p>Colunas: " . implode(', ', $cols) . "</p>";
    
} catch (Exception $e) {
    echo "<p style='color:red'>ERRO: " . $e->getMessage() . "</p>";
}

// 2. TESTAR CRIAÇÃO MANUAL DE NOTIFICAÇÃO
echo "<hr><h2>2. 🧪 TESTE DE CRIAÇÃO DE NOTIFICAÇÃO</h2>";
session_start();
$usuario_id = $_SESSION['id'] ?? 33;

try {
    $stmt = $pdo->prepare("INSERT INTO notificacoes (usuario_id, tipo, mensagem, valor, data_notificacao) VALUES (?, 'Compra Aprovada', 'TESTE URGENTE - Nova compra R$ 97,00', 97.00, NOW())");
    $result = $stmt->execute([$usuario_id]);
    
    if ($result) {
        $notif_id = $pdo->lastInsertId();
        echo "<p style='color:green'>✅ NOTIFICAÇÃO TESTE CRIADA! ID: $notif_id</p>";
    } else {
        echo "<p style='color:red'>❌ ERRO AO CRIAR NOTIFICAÇÃO</p>";
        echo "<pre>" . print_r($stmt->errorInfo(), true) . "</pre>";
    }
} catch (Exception $e) {
    echo "<p style='color:red'>ERRO: " . $e->getMessage() . "</p>";
}

// 3. VERIFICAR ÚLTIMAS VENDAS SEM NOTIFICAÇÃO
echo "<hr><h2>3. 🛒 VENDAS SEM NOTIFICAÇÃO</h2>";
try {
    $stmt = $pdo->query("SELECT v.id, v.valor, v.status_pagamento, v.data_venda, v.comprador_nome 
                         FROM vendas v 
                         LEFT JOIN notificacoes n ON n.venda_id_fk = v.id 
                         WHERE n.id IS NULL 
                         ORDER BY v.id DESC LIMIT 10");
    $vendas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($vendas) > 0) {
        echo "<p style='color:orange'>⚠️ " . count($vendas) . " vendas SEM notificação:</p>";
        echo "<table border='1' cellpadding='5' style='font-size:12px;'>";
        echo "<tr><th>ID</th><th>Valor</th><th>Status</th><th>Data</th><th>Comprador</th><th>Ação</th></tr>";
        
        foreach ($vendas as $v) {
            echo "<tr>";
            echo "<td>{$v['id']}</td>";
            echo "<td>R$ " . number_format($v['valor'], 2, ',', '.') . "</td>";
            echo "<td>{$v['status_pagamento']}</td>";
            echo "<td>{$v['data_venda']}</td>";
            echo "<td>" . substr($v['comprador_nome'], 0, 20) . "</td>";
            echo "<td><a href='?create_notif={$v['id']}' style='color:green;'>Criar Notif</a></td>";
            echo "</tr>";
        }
        echo "</table>";
        
        echo "<br><a href='?create_all=1'><button style='padding:10px 20px;background:#ef4444;color:white;border:none;border-radius:5px;cursor:pointer;font-weight:bold;'>🚨 CRIAR TODAS AS NOTIFICAÇÕES FALTANTES</button></a>";
    } else {
        echo "<p style='color:green'>✅ Todas as vendas têm notificação</p>";
    }
} catch (Exception $e) {
    echo "<p style='color:red'>ERRO: " . $e->getMessage() . "</p>";
}

// 4. CRIAR NOTIFICAÇÃO ESPECÍFICA
if (isset($_GET['create_notif']) && is_numeric($_GET['create_notif'])) {
    $venda_id = (int)$_GET['create_notif'];
    echo "<hr><h2>🔨 CRIANDO NOTIFICAÇÃO PARA VENDA #$venda_id</h2>";
    
    try {
        $stmt = $pdo->prepare("SELECT v.*, p.nome as produto_nome, p.usuario_id as produtor_id 
                               FROM vendas v 
                               LEFT JOIN produtos p ON v.produto_id = p.id 
                               WHERE v.id = ?");
        $stmt->execute([$venda_id]);
        $venda = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($venda) {
            $produtor_id = $venda['produtor_id'] ?? $usuario_id;
            $mensagem = "Nova venda: " . ($venda['produto_nome'] ?? 'Produto') . " - R$ " . number_format($venda['valor'], 2, ',', '.');
            
            $stmt_notif = $pdo->prepare("INSERT INTO notificacoes (usuario_id, tipo, mensagem, valor, venda_id_fk, data_notificacao) VALUES (?, 'Compra Aprovada', ?, ?, ?, NOW())");
            $result = $stmt_notif->execute([$produtor_id, $mensagem, $venda['valor'], $venda_id]);
            
            if ($result) {
                echo "<p style='color:green'>✅ Notificação criada para venda #$venda_id</p>";
            } else {
                echo "<p style='color:red'>❌ Erro ao criar notificação</p>";
            }
        }
    } catch (Exception $e) {
        echo "<p style='color:red'>ERRO: " . $e->getMessage() . "</p>";
    }
}

// 5. CRIAR TODAS AS NOTIFICAÇÕES FALTANTES
if (isset($_GET['create_all']) && $_GET['create_all'] === '1') {
    echo "<hr><h2>🚨 CRIANDO TODAS AS NOTIFICAÇÕES FALTANTES</h2>";
    
    try {
        $stmt = $pdo->query("SELECT v.id, v.valor, v.produto_id, p.nome as produto_nome, p.usuario_id as produtor_id
                             FROM vendas v 
                             LEFT JOIN produtos p ON v.produto_id = p.id
                             LEFT JOIN notificacoes n ON n.venda_id_fk = v.id 
                             WHERE n.id IS NULL");
        $vendas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $created = 0;
        foreach ($vendas as $v) {
            $produtor_id = $v['produtor_id'] ?? $usuario_id;
            $mensagem = "Nova venda: " . ($v['produto_nome'] ?? 'Produto') . " - R$ " . number_format($v['valor'], 2, ',', '.');
            
            $stmt_notif = $pdo->prepare("INSERT INTO notificacoes (usuario_id, tipo, mensagem, valor, venda_id_fk, data_notificacao) VALUES (?, 'Compra Aprovada', ?, ?, ?, NOW())");
            $stmt_notif->execute([$produtor_id, $mensagem, $v['valor'], $v['id']]);
            $created++;
        }
        
        echo "<p style='color:green;font-weight:bold;font-size:18px;'>✅ $created NOTIFICAÇÕES CRIADAS COM SUCESSO!</p>";
        
    } catch (Exception $e) {
        echo "<p style='color:red'>ERRO: " . $e->getMessage() . "</p>";
    }
}

// 6. VERIFICAR API DE NOTIFICAÇÕES
echo "<hr><h2>6. 🔌 TESTE DA API DE NOTIFICAÇÕES</h2>";
$api_url = 'https://' . $_SERVER['HTTP_HOST'] . '/notification?action=get_recent_notifications';
echo "<p>Testando: <a href='$api_url' target='_blank'>$api_url</a></p>";

$context = stream_context_create([
    'http' => [
        'method' => 'GET',
        'header' => 'Cookie: ' . $_SERVER['HTTP_COOKIE'] . "\r\n"
    ]
]);

$response = @file_get_contents($api_url, false, $context);
if ($response !== false) {
    $data = json_decode($response, true);
    if ($data && isset($data['notifications'])) {
        echo "<p style='color:green'>✅ API funcionando! " . count($data['notifications']) . " notificações encontradas</p>";
    } else {
        echo "<p style='color:red'>❌ API retornou dados inválidos</p>";
        echo "<pre>" . htmlspecialchars($response) . "</pre>";
    }
} else {
    echo "<p style='color:red'>❌ API não respondeu</p>";
}

// 7. SUAS NOTIFICAÇÕES
echo "<hr><h2>7. 📢 SUAS ÚLTIMAS NOTIFICAÇÕES</h2>";
try {
    $stmt = $pdo->prepare("SELECT * FROM notificacoes WHERE usuario_id = ? ORDER BY data_notificacao DESC LIMIT 10");
    $stmt->execute([$usuario_id]);
    $notifs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($notifs) > 0) {
        echo "<table border='1' cellpadding='5' style='font-size:12px;'>";
        echo "<tr><th>ID</th><th>Tipo</th><th>Mensagem</th><th>Valor</th><th>Data</th><th>Lida</th></tr>";
        foreach ($notifs as $n) {
            $cor = $n['lida'] ? 'white' : '#e6ffe6';
            echo "<tr style='background:$cor'>";
            echo "<td>{$n['id']}</td>";
            echo "<td>{$n['tipo']}</td>";
            echo "<td>" . substr($n['mensagem'], 0, 50) . "</td>";
            echo "<td>" . ($n['valor'] ? "R$ " . number_format($n['valor'], 2, ',', '.') : '-') . "</td>";
            echo "<td>{$n['data_notificacao']}</td>";
            echo "<td>" . ($n['lida'] ? 'Sim' : 'NÃO') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color:red'>❌ VOCÊ NÃO TEM NENHUMA NOTIFICAÇÃO!</p>";
    }
} catch (Exception $e) {
    echo "<p style='color:red'>ERRO: " . $e->getMessage() . "</p>";
}

echo "<hr><h2>🎯 AÇÕES URGENTES</h2>";
echo "<p>1. <a href='?create_all=1' style='color:red;font-weight:bold;'>CRIAR TODAS AS NOTIFICAÇÕES FALTANTES</a></p>";
echo "<p>2. <a href='/index?pagina=dashboard' target='_blank'>VER DASHBOARD</a></p>";
echo "<p>3. <a href='/notification?action=get_recent_notifications' target='_blank'>TESTAR API</a></p>";
?>
