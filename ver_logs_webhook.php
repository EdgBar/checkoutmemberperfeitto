<?php
/**
 * Visualizar logs do webhook
 * @author Souza Tech - https://rafaelsouzatech.com.br
 */
require_once __DIR__ . '/config/config.php';

echo "<h1>📋 LOGS DO WEBHOOK</h1>";

$log_file = __DIR__ . '/webhook_log.txt';

if (file_exists($log_file)) {
    $size = filesize($log_file);
    echo "<p>✅ Arquivo existe: $size bytes</p>";
    
    // Ler últimas 100 linhas
    $lines = file($log_file);
    $total_lines = count($lines);
    echo "<p>Total de linhas: $total_lines</p>";
    
    // Filtrar linhas relevantes
    echo "<h2>🔍 Últimas 50 linhas do log:</h2>";
    $last_lines = array_slice($lines, -50);
    
    echo "<pre style='background:#1a1a1a;color:#00ff00;padding:15px;font-size:11px;max-height:600px;overflow:auto;white-space:pre-wrap;'>";
    foreach ($last_lines as $line) {
        // Destacar linhas importantes
        if (stripos($line, 'CREATE_NOTIFICATION') !== false) {
            echo "<span style='color:#ff0;font-weight:bold;'>" . htmlspecialchars($line) . "</span>";
        } elseif (stripos($line, 'ERRO') !== false || stripos($line, 'ERROR') !== false) {
            echo "<span style='color:#f00;'>" . htmlspecialchars($line) . "</span>";
        } elseif (stripos($line, 'approved') !== false) {
            echo "<span style='color:#0f0;font-weight:bold;'>" . htmlspecialchars($line) . "</span>";
        } else {
            echo htmlspecialchars($line);
        }
    }
    echo "</pre>";
    
    // Buscar especificamente por CREATE_NOTIFICATION
    echo "<h2>🔔 Linhas com CREATE_NOTIFICATION:</h2>";
    $notif_lines = array_filter($lines, function($line) {
        return stripos($line, 'CREATE_NOTIFICATION') !== false;
    });
    
    if (count($notif_lines) > 0) {
        echo "<pre style='background:#1a1a1a;color:#ff0;padding:15px;font-size:11px;max-height:300px;overflow:auto;'>";
        foreach (array_slice($notif_lines, -20) as $line) {
            echo htmlspecialchars($line);
        }
        echo "</pre>";
    } else {
        echo "<p style='color:red;font-weight:bold;'>❌ NENHUMA LINHA COM CREATE_NOTIFICATION ENCONTRADA!</p>";
        echo "<p>Isso significa que a função create_notification NÃO está sendo chamada quando pagamentos são aprovados.</p>";
    }
    
} else {
    echo "<p style='color:red'>❌ Arquivo webhook_log.txt NÃO EXISTE</p>";
    echo "<p>Isso significa que nenhum webhook foi processado ainda.</p>";
}

// Verificar notification_api_errors.log
echo "<hr><h2>📋 LOGS DE ERRO DA API:</h2>";
$error_log = __DIR__ . '/notification_api_errors.log';
if (file_exists($error_log)) {
    $lines = file($error_log);
    $last_lines = array_slice($lines, -30);
    
    echo "<pre style='background:#1a1a1a;color:#f90;padding:15px;font-size:11px;max-height:300px;overflow:auto;'>";
    echo htmlspecialchars(implode('', $last_lines));
    echo "</pre>";
} else {
    echo "<p>⚠️ Arquivo notification_api_errors.log não existe</p>";
}

// Verificar últimas vendas e se webhook foi chamado
echo "<hr><h2>🛒 ÚLTIMAS VENDAS (verificar se webhook foi chamado):</h2>";
try {
    $stmt = $pdo->query("SELECT v.id, v.status_pagamento, v.transacao_id, v.data_venda, v.metodo_pagamento,
                                (SELECT COUNT(*) FROM notificacoes n WHERE n.venda_id_fk = v.id) as tem_notif
                         FROM vendas v 
                         ORDER BY v.id DESC LIMIT 10");
    $vendas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' cellpadding='5' style='font-size:12px;'>";
    echo "<tr><th>ID</th><th>Status</th><th>Transação ID</th><th>Data</th><th>Método</th><th>Tem Notif?</th></tr>";
    
    foreach ($vendas as $v) {
        $cor = $v['status_pagamento'] === 'approved' ? '#e6ffe6' : ($v['status_pagamento'] === 'pending' ? '#fff3e6' : '#ffe6e6');
        $notif_status = $v['tem_notif'] > 0 ? '✅' : '❌';
        echo "<tr style='background:$cor'>";
        echo "<td>{$v['id']}</td>";
        echo "<td><strong>{$v['status_pagamento']}</strong></td>";
        echo "<td>" . substr($v['transacao_id'], 0, 15) . "...</td>";
        echo "<td>{$v['data_venda']}</td>";
        echo "<td>{$v['metodo_pagamento']}</td>";
        echo "<td>$notif_status</td>";
        echo "</tr>";
    }
    echo "</table>";
    
} catch (Exception $e) {
    echo "<p style='color:red'>Erro: " . $e->getMessage() . "</p>";
}

echo "<hr><h2>🎯 DIAGNÓSTICO:</h2>";
echo "<p>Se não há linhas com <strong>CREATE_NOTIFICATION</strong> no log, significa que:</p>";
echo "<ul>";
echo "<li>O webhook NÃO está sendo chamado pelas gateways de pagamento</li>";
echo "<li>OU o código não está chegando até a função create_notification</li>";
echo "</ul>";
echo "<p><strong>Solução:</strong> Verificar se a URL do webhook está configurada corretamente nas gateways (PushinPay, Efí, etc.)</p>";

echo "<hr><p><a href='/index?pagina=dashboard'>Voltar ao Dashboard</a></p>";
?>
