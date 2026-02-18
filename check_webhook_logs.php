<?php
/**
 * Verificar logs do webhook
 * @author Souza Tech - https://rafaelsouzatech.com.br
 */
require_once __DIR__ . '/config/config.php';

echo "<h2>Verificação de Logs do Webhook</h2>";

// Procurar arquivo de log
$log_files = [
    __DIR__ . '/webhook_logs.txt',
    __DIR__ . '/logs/webhook.log',
    __DIR__ . '/notification_api_errors.log',
    __DIR__ . '/webhook.log'
];

echo "<h3>Arquivos de log encontrados:</h3>";
foreach ($log_files as $log_file) {
    if (file_exists($log_file)) {
        echo "<p style='color:green'>✓ Encontrado: $log_file</p>";
        
        // Ler últimas 50 linhas
        $lines = file($log_file);
        $last_lines = array_slice($lines, -50);
        
        echo "<h4>Últimas 50 linhas de " . basename($log_file) . ":</h4>";
        echo "<pre style='background:#f5f5f5;padding:10px;max-height:400px;overflow:auto;font-size:11px;'>";
        echo htmlspecialchars(implode('', $last_lines));
        echo "</pre>";
    } else {
        echo "<p style='color:gray'>- Não encontrado: $log_file</p>";
    }
}

// Verificar últimas vendas e seus webhooks
echo "<h3>Últimas 5 vendas e status:</h3>";
try {
    $stmt = $pdo->query("SELECT * FROM vendas ORDER BY id DESC LIMIT 5");
    $vendas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($vendas) > 0) {
        echo "<table border='1' cellpadding='5' style='font-size:12px;'>";
        echo "<tr>";
        foreach (array_keys($vendas[0]) as $key) {
            echo "<th>$key</th>";
        }
        echo "</tr>";
        
        foreach ($vendas as $v) {
            echo "<tr>";
            foreach ($v as $val) {
                echo "<td>" . htmlspecialchars(substr($val ?? '', 0, 30)) . "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    }
} catch (PDOException $e) {
    echo "<p style='color:red'>Erro: " . $e->getMessage() . "</p>";
}

// Verificar se há notificações para essas vendas
echo "<h3>Notificações das últimas 5 vendas:</h3>";
try {
    $stmt = $pdo->query("SELECT n.*, v.id as venda_id FROM notificacoes n 
                         RIGHT JOIN vendas v ON n.venda_id_fk = v.id 
                         ORDER BY v.id DESC LIMIT 10");
    $notifs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($notifs) > 0) {
        echo "<table border='1' cellpadding='5' style='font-size:12px;'>";
        echo "<tr><th>Venda ID</th><th>Notif ID</th><th>Tipo</th><th>Mensagem</th><th>Data Notif</th></tr>";
        foreach ($notifs as $n) {
            $has_notif = !empty($n['id']);
            $color = $has_notif ? 'white' : '#ffcccc';
            echo "<tr style='background:$color'>";
            echo "<td>{$n['venda_id']}</td>";
            echo "<td>" . ($n['id'] ?? 'SEM NOTIF') . "</td>";
            echo "<td>" . ($n['tipo'] ?? '-') . "</td>";
            echo "<td>" . substr($n['mensagem'] ?? '-', 0, 40) . "</td>";
            echo "<td>" . ($n['data_notificacao'] ?? '-') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
} catch (PDOException $e) {
    echo "<p style='color:red'>Erro: " . $e->getMessage() . "</p>";
}
?>
