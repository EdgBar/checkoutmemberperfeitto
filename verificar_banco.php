<?php
/**
 * Script de verificação do banco de dados
 * Verifica estrutura das tabelas, dados e integridade
 * 
 * @author Souza Tech - https://rafaelsouzatech.com.br
 * @version 1.0.0
 */

require_once __DIR__ . '/config/config.php';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificação do Banco de Dados</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #1a1a2e; color: #eee; padding: 20px; }
        .container { max-width: 1200px; margin: 0 auto; }
        h1 { color: #00d4ff; border-bottom: 2px solid #00d4ff; padding-bottom: 10px; }
        h2 { color: #ffd700; margin-top: 30px; }
        .card { background: #16213e; border-radius: 10px; padding: 20px; margin: 15px 0; border-left: 4px solid #00d4ff; }
        .success { border-left-color: #22c55e; }
        .warning { border-left-color: #f59e0b; }
        .error { border-left-color: #ef4444; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #333; }
        th { background: #0f3460; color: #00d4ff; }
        tr:hover { background: #1a1a4e; }
        .badge { padding: 3px 8px; border-radius: 4px; font-size: 12px; }
        .badge-success { background: #22c55e; color: #fff; }
        .badge-warning { background: #f59e0b; color: #000; }
        .badge-error { background: #ef4444; color: #fff; }
        .badge-info { background: #3b82f6; color: #fff; }
        pre { background: #0a0a1a; padding: 10px; border-radius: 5px; overflow-x: auto; }
        .stat { display: inline-block; background: #0f3460; padding: 10px 20px; border-radius: 8px; margin: 5px; text-align: center; }
        .stat-value { font-size: 24px; font-weight: bold; color: #00d4ff; }
        .stat-label { font-size: 12px; color: #888; }
    </style>
</head>
<body>
<div class="container">
    <h1>🔍 Verificação do Banco de Dados</h1>
    <p>Data: <?php echo date('d/m/Y H:i:s'); ?></p>

<?php
try {
    // 1. Verificar conexão
    echo '<div class="card success"><h3>✅ Conexão com Banco de Dados</h3>';
    echo '<p>Conexão estabelecida com sucesso!</p>';
    
    // Informações do servidor
    $server_info = $pdo->getAttribute(PDO::ATTR_SERVER_VERSION);
    echo "<p><strong>Versão MySQL:</strong> $server_info</p>";
    echo '</div>';

    // 2. Listar todas as tabelas
    echo '<h2>📋 Tabelas do Banco</h2>';
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo '<div class="card">';
    echo '<p><strong>Total de tabelas:</strong> ' . count($tables) . '</p>';
    echo '<table><tr><th>Tabela</th><th>Registros</th><th>Status</th></tr>';
    
    foreach ($tables as $table) {
        $count = $pdo->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
        $status = $count > 0 ? '<span class="badge badge-success">OK</span>' : '<span class="badge badge-warning">Vazia</span>';
        echo "<tr><td>$table</td><td>$count</td><td>$status</td></tr>";
    }
    echo '</table></div>';

    // 3. Verificar tabela de notificações
    echo '<h2>🔔 Tabela: notificacoes</h2>';
    $notif_exists = in_array('notificacoes', $tables);
    
    if ($notif_exists) {
        echo '<div class="card success">';
        
        // Estrutura da tabela
        $columns = $pdo->query("DESCRIBE notificacoes")->fetchAll(PDO::FETCH_ASSOC);
        echo '<h4>Estrutura:</h4>';
        echo '<table><tr><th>Coluna</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th></tr>';
        foreach ($columns as $col) {
            echo "<tr><td>{$col['Field']}</td><td>{$col['Type']}</td><td>{$col['Null']}</td><td>{$col['Key']}</td><td>{$col['Default']}</td></tr>";
        }
        echo '</table>';
        
        // Últimas notificações
        $notifs = $pdo->query("SELECT * FROM notificacoes ORDER BY id DESC LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
        echo '<h4>Últimas 10 notificações:</h4>';
        if (count($notifs) > 0) {
            echo '<table><tr><th>ID</th><th>Usuario</th><th>Tipo</th><th>Mensagem</th><th>Valor</th><th>Venda ID</th><th>Data</th></tr>';
            foreach ($notifs as $n) {
                $data = isset($n['data_notificacao']) ? $n['data_notificacao'] : (isset($n['created_at']) ? $n['created_at'] : 'N/A');
                echo "<tr>
                    <td>{$n['id']}</td>
                    <td>{$n['usuario_id']}</td>
                    <td><span class='badge badge-info'>{$n['tipo']}</span></td>
                    <td>" . substr($n['mensagem'], 0, 50) . "...</td>
                    <td>R$ " . number_format($n['valor'] ?? 0, 2, ',', '.') . "</td>
                    <td>" . ($n['venda_id_fk'] ?? 'N/A') . "</td>
                    <td>$data</td>
                </tr>";
            }
            echo '</table>';
        } else {
            echo '<p class="badge badge-warning">Nenhuma notificação encontrada</p>';
        }
        
        // Contagem por tipo
        $tipos = $pdo->query("SELECT tipo, COUNT(*) as total FROM notificacoes GROUP BY tipo ORDER BY total DESC")->fetchAll(PDO::FETCH_ASSOC);
        echo '<h4>Notificações por tipo:</h4>';
        foreach ($tipos as $t) {
            echo "<div class='stat'><div class='stat-value'>{$t['total']}</div><div class='stat-label'>{$t['tipo']}</div></div>";
        }
        
        echo '</div>';
    } else {
        echo '<div class="card error"><p>❌ Tabela notificacoes NÃO existe!</p></div>';
    }

    // 4. Verificar tabela de vendas
    echo '<h2>💰 Tabela: vendas</h2>';
    $vendas_exists = in_array('vendas', $tables);
    
    if ($vendas_exists) {
        echo '<div class="card success">';
        
        // Estrutura
        $columns = $pdo->query("DESCRIBE vendas")->fetchAll(PDO::FETCH_ASSOC);
        echo '<h4>Estrutura (principais colunas):</h4>';
        echo '<table><tr><th>Coluna</th><th>Tipo</th><th>Null</th><th>Key</th></tr>';
        $important_cols = ['id', 'produto_id', 'valor', 'status_pagamento', 'transacao_id', 'metodo_pagamento', 'data_venda', 'comprador_nome', 'comprador_email'];
        foreach ($columns as $col) {
            if (in_array($col['Field'], $important_cols)) {
                echo "<tr><td>{$col['Field']}</td><td>{$col['Type']}</td><td>{$col['Null']}</td><td>{$col['Key']}</td></tr>";
            }
        }
        echo '</table>';
        
        // Estatísticas
        $stats = $pdo->query("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status_pagamento = 'approved' THEN 1 ELSE 0 END) as aprovadas,
                SUM(CASE WHEN status_pagamento = 'pending' THEN 1 ELSE 0 END) as pendentes,
                SUM(CASE WHEN status_pagamento = 'approved' THEN valor ELSE 0 END) as valor_aprovado
            FROM vendas
        ")->fetch(PDO::FETCH_ASSOC);
        
        echo '<h4>Estatísticas:</h4>';
        echo "<div class='stat'><div class='stat-value'>{$stats['total']}</div><div class='stat-label'>Total Vendas</div></div>";
        echo "<div class='stat'><div class='stat-value'>{$stats['aprovadas']}</div><div class='stat-label'>Aprovadas</div></div>";
        echo "<div class='stat'><div class='stat-value'>{$stats['pendentes']}</div><div class='stat-label'>Pendentes</div></div>";
        echo "<div class='stat'><div class='stat-value'>R$ " . number_format($stats['valor_aprovado'] ?? 0, 2, ',', '.') . "</div><div class='stat-label'>Valor Aprovado</div></div>";
        
        // Últimas vendas
        $vendas = $pdo->query("SELECT id, produto_id, valor, status_pagamento, metodo_pagamento, comprador_nome, data_venda FROM vendas ORDER BY id DESC LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
        echo '<h4>Últimas 10 vendas:</h4>';
        echo '<table><tr><th>ID</th><th>Produto</th><th>Valor</th><th>Status</th><th>Método</th><th>Comprador</th><th>Data</th></tr>';
        foreach ($vendas as $v) {
            $status_class = $v['status_pagamento'] == 'approved' ? 'badge-success' : ($v['status_pagamento'] == 'pending' ? 'badge-warning' : 'badge-error');
            echo "<tr>
                <td>{$v['id']}</td>
                <td>{$v['produto_id']}</td>
                <td>R$ " . number_format($v['valor'], 2, ',', '.') . "</td>
                <td><span class='badge $status_class'>{$v['status_pagamento']}</span></td>
                <td>{$v['metodo_pagamento']}</td>
                <td>" . substr($v['comprador_nome'] ?? '', 0, 20) . "</td>
                <td>{$v['data_venda']}</td>
            </tr>";
        }
        echo '</table>';
        
        // Verificar vendas aprovadas sem notificação
        $sem_notif = $pdo->query("
            SELECT v.id, v.valor, v.status_pagamento, v.comprador_nome, v.data_venda
            FROM vendas v
            LEFT JOIN notificacoes n ON n.venda_id_fk = v.id AND n.tipo = 'Compra Aprovada'
            WHERE v.status_pagamento = 'approved' AND n.id IS NULL
            ORDER BY v.id DESC
            LIMIT 20
        ")->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($sem_notif) > 0) {
            echo '<h4>⚠️ Vendas APROVADAS sem notificação:</h4>';
            echo '<div class="card warning">';
            echo '<table><tr><th>ID</th><th>Valor</th><th>Comprador</th><th>Data</th></tr>';
            foreach ($sem_notif as $v) {
                echo "<tr>
                    <td>{$v['id']}</td>
                    <td>R$ " . number_format($v['valor'], 2, ',', '.') . "</td>
                    <td>" . substr($v['comprador_nome'] ?? '', 0, 30) . "</td>
                    <td>{$v['data_venda']}</td>
                </tr>";
            }
            echo '</table>';
            echo '<p><strong>Total:</strong> ' . count($sem_notif) . ' vendas aprovadas sem notificação</p>';
            echo '</div>';
        } else {
            echo '<div class="card success"><p>✅ Todas as vendas aprovadas têm notificação!</p></div>';
        }
        
        echo '</div>';
    }

    // 5. Verificar tabela rate_limits
    echo '<h2>🛡️ Tabela: rate_limits</h2>';
    if (in_array('rate_limits', $tables)) {
        echo '<div class="card success">';
        $columns = $pdo->query("DESCRIBE rate_limits")->fetchAll(PDO::FETCH_ASSOC);
        echo '<table><tr><th>Coluna</th><th>Tipo</th><th>Null</th><th>Default</th></tr>';
        foreach ($columns as $col) {
            echo "<tr><td>{$col['Field']}</td><td>{$col['Type']}</td><td>{$col['Null']}</td><td>{$col['Default']}</td></tr>";
        }
        echo '</table>';
        
        // Verificar colunas necessárias
        $col_names = array_column($columns, 'Field');
        $required = ['last_attempt', 'attempts', 'first_attempt'];
        $missing = array_diff($required, $col_names);
        
        if (empty($missing)) {
            echo '<p class="badge badge-success">✅ Todas as colunas necessárias existem</p>';
        } else {
            echo '<p class="badge badge-error">❌ Colunas faltando: ' . implode(', ', $missing) . '</p>';
        }
        echo '</div>';
    } else {
        echo '<div class="card warning"><p>⚠️ Tabela rate_limits não existe (será criada automaticamente)</p></div>';
    }

    // 6. Verificar tabela de produtos
    echo '<h2>📦 Tabela: produtos</h2>';
    if (in_array('produtos', $tables)) {
        echo '<div class="card success">';
        $prod_count = $pdo->query("SELECT COUNT(*) FROM produtos")->fetchColumn();
        $prod_ativos = $pdo->query("SELECT COUNT(*) FROM produtos WHERE status = 'ativo' OR status = 1")->fetchColumn();
        echo "<p><strong>Total:</strong> $prod_count produtos | <strong>Ativos:</strong> $prod_ativos</p>";
        echo '</div>';
    }

    // 7. Verificar tabela de usuários
    echo '<h2>👥 Tabela: usuarios</h2>';
    if (in_array('usuarios', $tables)) {
        echo '<div class="card success">';
        $user_count = $pdo->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();
        echo "<p><strong>Total:</strong> $user_count usuários</p>";
        echo '</div>';
    }

    // 8. Resumo final
    echo '<h2>📊 Resumo Final</h2>';
    echo '<div class="card">';
    
    $issues = [];
    
    // Verificar se notificacoes existe
    if (!$notif_exists) {
        $issues[] = 'Tabela notificacoes não existe';
    }
    
    // Verificar vendas sem notificação
    if (isset($sem_notif) && count($sem_notif) > 0) {
        $issues[] = count($sem_notif) . ' vendas aprovadas sem notificação';
    }
    
    if (empty($issues)) {
        echo '<p class="badge badge-success" style="font-size: 16px; padding: 10px 20px;">✅ BANCO DE DADOS OK - Nenhum problema encontrado!</p>';
    } else {
        echo '<p class="badge badge-warning" style="font-size: 16px; padding: 10px 20px;">⚠️ Problemas encontrados:</p>';
        echo '<ul>';
        foreach ($issues as $issue) {
            echo "<li>$issue</li>";
        }
        echo '</ul>';
    }
    
    echo '</div>';

} catch (PDOException $e) {
    echo '<div class="card error">';
    echo '<h3>❌ Erro de Conexão</h3>';
    echo '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '</div>';
}
?>

</div>
</body>
</html>
