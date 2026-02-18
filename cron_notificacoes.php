<?php
/**
 * CRON - Criar notificações para vendas aprovadas sem notificação
 * Execute este arquivo periodicamente (a cada 1 minuto) via cron job
 * 
 * Exemplo de cron: * * * * * php /path/to/cron_notificacoes.php
 * 
 * @author Souza Tech - https://rafaelsouzatech.com.br
 * @version 1.0.0
 */

// Desabilitar output para cron
if (php_sapi_name() !== 'cli') {
    // Se acessado via web, mostrar resultado
    header('Content-Type: text/html; charset=utf-8');
}

require_once __DIR__ . '/config/config.php';

$is_web = php_sapi_name() !== 'cli';
$log = [];

function cron_log($msg) {
    global $log, $is_web;
    $log[] = date('Y-m-d H:i:s') . " - " . $msg;
    if (!$is_web) {
        echo $msg . "\n";
    }
}

cron_log("Iniciando verificação de notificações...");

try {
    // Buscar vendas aprovadas sem notificação (últimas 24 horas)
    $stmt = $pdo->query("
        SELECT v.id, v.valor, v.produto_id, v.data_venda, v.comprador_nome, v.metodo_pagamento,
               p.nome as produto_nome, p.usuario_id as produtor_id
        FROM vendas v 
        LEFT JOIN produtos p ON v.produto_id = p.id
        LEFT JOIN notificacoes n ON n.venda_id_fk = v.id 
        WHERE v.status_pagamento = 'approved'
        AND n.id IS NULL
        AND v.data_venda >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
        ORDER BY v.id DESC
    ");
    $vendas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    cron_log("Vendas aprovadas sem notificação: " . count($vendas));
    
    $created = 0;
    foreach ($vendas as $v) {
        $produtor_id = $v['produtor_id'];
        if (!$produtor_id) {
            cron_log("AVISO: Venda #{$v['id']} sem produtor_id, pulando...");
            continue;
        }
        
        $mensagem = "Nova venda: " . ($v['produto_nome'] ?? 'Produto') . " - R$ " . number_format($v['valor'], 2, ',', '.');
        
        $stmt_notif = $pdo->prepare("
            INSERT INTO notificacoes (usuario_id, tipo, mensagem, valor, venda_id_fk, metodo_pagamento, data_notificacao) 
            VALUES (?, 'Compra Aprovada', ?, ?, ?, ?, NOW())
        ");
        $result = $stmt_notif->execute([$produtor_id, $mensagem, $v['valor'], $v['id'], $v['metodo_pagamento']]);
        
        if ($result) {
            $created++;
            cron_log("✓ Notificação criada para venda #{$v['id']} (Produtor: $produtor_id)");
        } else {
            cron_log("✗ Erro ao criar notificação para venda #{$v['id']}");
        }
    }
    
    cron_log("Total de notificações criadas: $created");
    
} catch (Exception $e) {
    cron_log("ERRO: " . $e->getMessage());
}

// Se acessado via web, mostrar resultado
if ($is_web) {
    echo "<h2>🔔 CRON - Notificações Automáticas</h2>";
    echo "<pre style='background:#1a1a1a;color:#0f0;padding:15px;'>";
    echo implode("\n", $log);
    echo "</pre>";
    echo "<p><a href='/index?pagina=dashboard'>Voltar ao Dashboard</a></p>";
}
?>
