<?php
/**
 * Verificar vendas recentes e notificações
 * @author Souza Tech - https://rafaelsouzatech.com.br
 */
require_once __DIR__ . '/config/config.php';

echo "<h2>Verificação de Vendas e Notificações</h2>";

// 0. Estrutura da tabela vendas
echo "<h3>0. Estrutura da tabela vendas:</h3>";
try {
    $cols = $pdo->query("DESCRIBE vendas")->fetchAll(PDO::FETCH_ASSOC);
    $col_names = array_column($cols, 'Field');
    echo "<p>Colunas: " . implode(', ', $col_names) . "</p>";
} catch (PDOException $e) {
    echo "<p style='color:red'>Erro: " . $e->getMessage() . "</p>";
}

// 1. Últimas vendas
echo "<h3>1. Últimas 5 vendas:</h3>";
try {
    $stmt = $pdo->query("SELECT * FROM vendas ORDER BY id DESC LIMIT 5");
    $vendas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($vendas) > 0) {
        echo "<pre>";
        print_r($vendas[0]); // Mostrar estrutura da primeira venda
        echo "</pre>";
        
        echo "<table border='1' cellpadding='5'>";
        // Cabeçalho dinâmico
        echo "<tr>";
        foreach (array_keys($vendas[0]) as $key) {
            echo "<th>$key</th>";
        }
        echo "</tr>";
        
        foreach ($vendas as $v) {
            echo "<tr>";
            foreach ($v as $val) {
                echo "<td>" . htmlspecialchars(substr($val ?? '', 0, 50)) . "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>Nenhuma venda encontrada</p>";
    }
} catch (PDOException $e) {
    echo "<p style='color:red'>Erro: " . $e->getMessage() . "</p>";
}

// 2. Últimas notificações
echo "<h3>2. Últimas 10 notificações:</h3>";
try {
    $stmt = $pdo->query("SELECT n.*, u.nome as usuario_nome 
                         FROM notificacoes n 
                         LEFT JOIN usuarios u ON n.usuario_id = u.id 
                         ORDER BY n.data_notificacao DESC LIMIT 10");
    $notifs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($notifs) > 0) {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID</th><th>Tipo</th><th>Mensagem</th><th>Valor</th><th>Data</th><th>Usuario</th><th>Lida</th></tr>";
        foreach ($notifs as $n) {
            echo "<tr>";
            echo "<td>{$n['id']}</td>";
            echo "<td>{$n['tipo']}</td>";
            echo "<td>" . substr($n['mensagem'] ?? '', 0, 50) . "</td>";
            echo "<td>" . ($n['valor'] ? "R$ " . number_format($n['valor'], 2, ',', '.') : '-') . "</td>";
            echo "<td>{$n['data_notificacao']}</td>";
            echo "<td>{$n['usuario_nome']} (ID: {$n['usuario_id']})</td>";
            echo "<td>" . ($n['lida'] ? 'Sim' : 'Não') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color:orange'>⚠ Nenhuma notificação encontrada no banco!</p>";
    }
} catch (PDOException $e) {
    echo "<p style='color:red'>Erro: " . $e->getMessage() . "</p>";
}

// 3. Verificar se há vendas sem notificação
echo "<h3>3. Vendas sem notificação correspondente:</h3>";
try {
    // Primeiro descobrir nome da coluna de status
    $cols = $pdo->query("DESCRIBE vendas")->fetchAll(PDO::FETCH_COLUMN);
    $status_col = in_array('status', $cols) ? 'status' : (in_array('status_pagamento', $cols) ? 'status_pagamento' : null);
    
    if ($status_col) {
        $stmt = $pdo->query("SELECT v.* FROM vendas v 
                             LEFT JOIN notificacoes n ON n.venda_id_fk = v.id 
                             WHERE v.$status_col IN ('approved', 'paid', 'completed', 'Aprovado', 'Pago') 
                             AND n.id IS NULL 
                             ORDER BY v.id DESC LIMIT 10");
    } else {
        // Se não tem coluna de status, pega todas as vendas sem notificação
        $stmt = $pdo->query("SELECT v.* FROM vendas v 
                             LEFT JOIN notificacoes n ON n.venda_id_fk = v.id 
                             WHERE n.id IS NULL 
                             ORDER BY v.id DESC LIMIT 10");
    }
    $vendas_sem_notif = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($vendas_sem_notif) > 0) {
        echo "<p style='color:orange'>⚠ Encontradas " . count($vendas_sem_notif) . " vendas SEM notificação:</p>";
        echo "<pre>" . print_r($vendas_sem_notif[0], true) . "</pre>";
        
        // Botão para criar notificações faltantes
        echo "<br><a href='?create_missing=1'><button style='padding:10px 20px;background:#22c55e;color:white;border:none;border-radius:5px;cursor:pointer;'>Criar Notificações Faltantes</button></a>";
    } else {
        echo "<p style='color:green'>✓ Todas as vendas têm notificação (ou não há vendas)</p>";
    }
} catch (PDOException $e) {
    echo "<p style='color:red'>Erro: " . $e->getMessage() . "</p>";
}

// Criar notificações faltantes se solicitado
if (isset($_GET['create_missing']) && $_GET['create_missing'] === '1') {
    echo "<h3>Criando notificações faltantes...</h3>";
    try {
        // Descobrir estrutura da tabela vendas
        $cols = $pdo->query("DESCRIBE vendas")->fetchAll(PDO::FETCH_COLUMN);
        $valor_col = in_array('valor', $cols) ? 'valor' : (in_array('valor_total', $cols) ? 'valor_total' : 'preco');
        
        $stmt = $pdo->query("SELECT v.*, p.nome as produto_nome, p.usuario_id as produtor_id
                             FROM vendas v 
                             LEFT JOIN produtos p ON v.produto_id = p.id
                             LEFT JOIN notificacoes n ON n.venda_id_fk = v.id 
                             WHERE n.id IS NULL");
        $vendas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $created = 0;
        foreach ($vendas as $v) {
            // Criar notificação para o produtor
            $produtor_id = $v['produtor_id'] ?? $v['usuario_id'] ?? 33; // Fallback para seu ID
            $valor = $v[$valor_col] ?? $v['valor'] ?? $v['valor_total'] ?? 0;
            $stmt_insert = $pdo->prepare("INSERT INTO notificacoes (usuario_id, tipo, mensagem, valor, venda_id_fk, data_notificacao) VALUES (?, 'venda', ?, ?, ?, NOW())");
            $mensagem = "Nova venda: " . ($v['produto_nome'] ?? 'Produto') . " - R$ " . number_format($valor, 2, ',', '.');
            $stmt_insert->execute([$produtor_id, $mensagem, $valor, $v['id']]);
            $created++;
        }
        
        echo "<p style='color:green'>✓ $created notificações criadas!</p>";
        echo "<p><a href='?'>Recarregar página</a></p>";
    } catch (PDOException $e) {
        echo "<p style='color:red'>Erro: " . $e->getMessage() . "</p>";
    }
}

// 4. Verificar seu usuário logado
echo "<h3>4. Seu usuário na sessão:</h3>";
session_start();
if (isset($_SESSION['id'])) {
    echo "<p style='color:green'>✓ Logado como ID: {$_SESSION['id']}</p>";
    
    // Verificar notificações para este usuário
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM notificacoes WHERE usuario_id = ?");
    $stmt->execute([$_SESSION['id']]);
    $count = $stmt->fetchColumn();
    echo "<p>Notificações para seu usuário: $count</p>";
} else {
    echo "<p style='color:orange'>⚠ Não está logado</p>";
}
?>
