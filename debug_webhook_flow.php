<?php
/**
 * Debug do fluxo de webhook - identificar onde falha
 * @author Souza Tech - https://rafaelsouzatech.com.br
 */
require_once __DIR__ . '/config/config.php';

echo "<h1>🔍 DEBUG DO FLUXO DE WEBHOOK</h1>";

// 1. VERIFICAR SE WEBHOOK É CHAMADO
echo "<h2>1. 📡 VERIFICAR WEBHOOK</h2>";

// Simular webhook de pagamento aprovado
if (isset($_GET['test_webhook']) && $_GET['test_webhook'] === '1') {
    echo "<h3>🧪 SIMULANDO WEBHOOK DE PAGAMENTO APROVADO</h3>";
    
    // Buscar uma venda pending para testar
    $stmt = $pdo->query("SELECT * FROM vendas WHERE status_pagamento = 'pending' ORDER BY id DESC LIMIT 1");
    $venda = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($venda) {
        echo "<p>Testando com venda ID: {$venda['id']}</p>";
        
        // Simular dados do webhook
        $webhook_data = [
            'status' => 'approved',
            'transaction_id' => $venda['transacao_id'],
            'amount' => $venda['valor'],
            'payment_method' => 'pix'
        ];
        
        echo "<p>Dados do webhook simulado:</p>";
        echo "<pre>" . json_encode($webhook_data, JSON_PRETTY_PRINT) . "</pre>";
        
        // Chamar a função de processamento do webhook
        try {
            // Atualizar status da venda
            $stmt_update = $pdo->prepare("UPDATE vendas SET status_pagamento = 'approved' WHERE id = ?");
            $stmt_update->execute([$venda['id']]);
            echo "<p style='color:green'>✅ Venda atualizada para 'approved'</p>";
            
            // Buscar dados do produto
            $stmt_produto = $pdo->prepare("SELECT * FROM produtos WHERE id = ?");
            $stmt_produto->execute([$venda['produto_id']]);
            $produto = $stmt_produto->fetch(PDO::FETCH_ASSOC);
            
            if ($produto) {
                echo "<p>Produto encontrado: {$produto['nome']}</p>";
                
                // CRIAR NOTIFICAÇÃO (função que deveria ser chamada automaticamente)
                $usuario_id = $produto['usuario_id'];
                $mensagem = "Nova venda: {$produto['nome']} - R$ " . number_format($venda['valor'], 2, ',', '.');
                
                echo "<p>Criando notificação para usuário ID: $usuario_id</p>";
                echo "<p>Mensagem: $mensagem</p>";
                
                $stmt_notif = $pdo->prepare("INSERT INTO notificacoes (usuario_id, tipo, mensagem, valor, venda_id_fk, data_notificacao) VALUES (?, 'Compra Aprovada', ?, ?, ?, NOW())");
                $result = $stmt_notif->execute([$usuario_id, $mensagem, $venda['valor'], $venda['id']]);
                
                if ($result) {
                    $notif_id = $pdo->lastInsertId();
                    echo "<p style='color:green;font-weight:bold;'>✅ NOTIFICAÇÃO CRIADA COM SUCESSO! ID: $notif_id</p>";
                    echo "<p>🔔 Agora você deveria receber a notificação no dashboard!</p>";
                } else {
                    echo "<p style='color:red'>❌ ERRO AO CRIAR NOTIFICAÇÃO</p>";
                    echo "<pre>" . print_r($stmt_notif->errorInfo(), true) . "</pre>";
                }
            } else {
                echo "<p style='color:red'>❌ Produto não encontrado</p>";
            }
            
        } catch (Exception $e) {
            echo "<p style='color:red'>ERRO: " . $e->getMessage() . "</p>";
        }
        
    } else {
        echo "<p style='color:red'>❌ Nenhuma venda 'pending' encontrada para testar</p>";
    }
}

// 2. VERIFICAR ARQUIVO notification.php
echo "<hr><h2>2. 📄 VERIFICAR notification.php</h2>";
$notif_file = __DIR__ . '/notification.php';
if (file_exists($notif_file)) {
    echo "<p>✅ Arquivo existe: " . filesize($notif_file) . " bytes</p>";
    
    // Verificar se função create_notification existe
    $content = file_get_contents($notif_file);
    if (strpos($content, 'function create_notification') !== false) {
        echo "<p>✅ Função create_notification encontrada</p>";
    } else {
        echo "<p style='color:red'>❌ Função create_notification NÃO encontrada</p>";
    }
    
    if (strpos($content, 'CREATE_NOTIFICATION chamada') !== false) {
        echo "<p>✅ Logs de debug estão ativos</p>";
    } else {
        echo "<p style='color:orange'>⚠️ Logs de debug não encontrados</p>";
    }
    
} else {
    echo "<p style='color:red'>❌ Arquivo notification.php NÃO EXISTE</p>";
}

// 3. VERIFICAR LOGS DE WEBHOOK
echo "<hr><h2>3. 📋 LOGS DE WEBHOOK</h2>";
$log_files = [
    'notification_api_errors.log',
    'webhook_logs.txt',
    'payment_logs.txt'
];

foreach ($log_files as $log_file) {
    $path = __DIR__ . '/' . $log_file;
    if (file_exists($path)) {
        echo "<h4>📄 $log_file (últimas 20 linhas):</h4>";
        $lines = file($path);
        $recent_lines = array_slice($lines, -20);
        
        // Filtrar apenas linhas com CREATE_NOTIFICATION ou webhook
        $filtered_lines = array_filter($recent_lines, function($line) {
            return stripos($line, 'CREATE_NOTIFICATION') !== false || 
                   stripos($line, 'webhook') !== false ||
                   stripos($line, 'notification') !== false;
        });
        
        if (count($filtered_lines) > 0) {
            echo "<pre style='background:#f5f5f5;padding:10px;font-size:11px;'>";
            echo htmlspecialchars(implode('', $filtered_lines));
            echo "</pre>";
        } else {
            echo "<p>⚠️ Nenhum log de webhook/notificação encontrado</p>";
        }
    }
}

// 4. VERIFICAR CONFIGURAÇÃO DE WEBHOOK
echo "<hr><h2>4. ⚙️ CONFIGURAÇÃO DE WEBHOOK</h2>";
$webhook_url = 'https://' . $_SERVER['HTTP_HOST'] . '/notification.php';
echo "<p>URL do webhook: <a href='$webhook_url' target='_blank'>$webhook_url</a></p>";

// Testar se webhook responde
$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => "Content-Type: application/json\r\n",
        'content' => json_encode(['test' => true])
    ]
]);

$response = @file_get_contents($webhook_url, false, $context);
if ($response !== false) {
    echo "<p style='color:green'>✅ Webhook responde</p>";
    echo "<pre>" . htmlspecialchars(substr($response, 0, 200)) . "</pre>";
} else {
    echo "<p style='color:red'>❌ Webhook não responde</p>";
}

// 5. VERIFICAR VENDAS RECENTES
echo "<hr><h2>5. 🛒 VENDAS RECENTES</h2>";
$stmt = $pdo->query("SELECT v.*, p.nome as produto_nome, p.usuario_id as produtor_id 
                     FROM vendas v 
                     LEFT JOIN produtos p ON v.produto_id = p.id 
                     ORDER BY v.id DESC LIMIT 5");
$vendas = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<table border='1' cellpadding='5' style='font-size:12px;'>";
echo "<tr><th>ID</th><th>Status</th><th>Valor</th><th>Data</th><th>Produto</th><th>Produtor ID</th><th>Tem Notif?</th></tr>";

foreach ($vendas as $v) {
    // Verificar se tem notificação
    $stmt_notif = $pdo->prepare("SELECT id FROM notificacoes WHERE venda_id_fk = ?");
    $stmt_notif->execute([$v['id']]);
    $has_notif = $stmt_notif->fetch() ? '✅' : '❌';
    
    $cor = $v['status_pagamento'] === 'approved' ? '#e6ffe6' : '#ffe6e6';
    echo "<tr style='background:$cor'>";
    echo "<td>{$v['id']}</td>";
    echo "<td>{$v['status_pagamento']}</td>";
    echo "<td>R$ " . number_format($v['valor'], 2, ',', '.') . "</td>";
    echo "<td>{$v['data_venda']}</td>";
    echo "<td>" . substr($v['produto_nome'] ?? 'N/A', 0, 20) . "</td>";
    echo "<td>{$v['produtor_id']}</td>";
    echo "<td>$has_notif</td>";
    echo "</tr>";
}
echo "</table>";

echo "<hr><h2>🧪 TESTE</h2>";
echo "<p><a href='?test_webhook=1'><button style='padding:15px 30px;background:#ef4444;color:white;border:none;border-radius:5px;cursor:pointer;font-weight:bold;font-size:16px;'>🚨 SIMULAR WEBHOOK DE PAGAMENTO APROVADO</button></a></p>";

echo "<hr><h2>🎯 DIAGNÓSTICO</h2>";
echo "<p>Este script simula o que deveria acontecer quando um pagamento é aprovado:</p>";
echo "<p>1. Webhook é chamado pela gateway de pagamento</p>";
echo "<p>2. Status da venda é atualizado para 'approved'</p>";
echo "<p>3. Notificação é criada para o produtor</p>";
echo "<p>4. Notificação aparece no dashboard</p>";
echo "<p>5. Som toca (se configurado)</p>";

echo "<p><strong>Se o teste funcionar, o problema é que o webhook não está sendo chamado pelas gateways de pagamento.</strong></p>";
?>
