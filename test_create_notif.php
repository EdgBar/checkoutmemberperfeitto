<?php
/**
 * Teste manual de criação de notificação
 * @author Souza Tech - https://rafaelsouzatech.com.br
 */
require_once __DIR__ . '/config/config.php';

echo "<h2>Teste de Criação de Notificação</h2>";

// Pegar seu usuário
session_start();
$usuario_id = $_SESSION['id'] ?? 33;

// Verificar vendas recentes sem notificação
echo "<h3>Vendas recentes sem notificação:</h3>";
try {
    $stmt = $pdo->query("SELECT v.* FROM vendas v 
                         LEFT JOIN notificacoes n ON n.venda_id_fk = v.id 
                         WHERE n.id IS NULL 
                         ORDER BY v.id DESC LIMIT 5");
    $vendas_sem_notif = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($vendas_sem_notif) > 0) {
        echo "<p style='color:orange'>⚠ Encontradas " . count($vendas_sem_notif) . " vendas SEM notificação:</p>";
        echo "<pre>" . print_r($vendas_sem_notif[0], true) . "</pre>";
    } else {
        echo "<p style='color:green'>✓ Todas as vendas têm notificação</p>";
    }
} catch (PDOException $e) {
    echo "<p style='color:red'>Erro: " . $e->getMessage() . "</p>";
}

echo "<h3>Criando notificação de teste:</h3>";
echo "<p>Criando notificação de teste para usuário ID: $usuario_id</p>";

try {
    // Criar notificação de teste SEM venda_id_fk (NULL)
    $tipo = 'Compra Aprovada';
    $mensagem = 'Teste de notificação - Venda Aprovada! R$ 97,00';
    $valor = 97.00;
    $link = NULL;
    $venda_id_fk = NULL; // NULL para evitar foreign key constraint
    $metodo = 'pix';
    
    $stmt = $pdo->prepare("INSERT INTO notificacoes (usuario_id, tipo, mensagem, valor, link_acao, venda_id_fk, metodo_pagamento, data_notificacao) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
    $result = $stmt->execute([$usuario_id, $tipo, $mensagem, $valor, $link, $venda_id_fk, $metodo]);
    
    if ($result) {
        $notif_id = $pdo->lastInsertId();
        echo "<p style='color:green'>✓ Notificação criada com sucesso! ID: $notif_id</p>";
        
        // Verificar se foi criada
        $stmt_check = $pdo->prepare("SELECT * FROM notificacoes WHERE id = ?");
        $stmt_check->execute([$notif_id]);
        $notif = $stmt_check->fetch(PDO::FETCH_ASSOC);
        
        echo "<h3>Dados da notificação criada:</h3>";
        echo "<pre>" . print_r($notif, true) . "</pre>";
        
        echo "<p><a href='/index?pagina=dashboard'>Ver no Dashboard</a></p>";
    } else {
        echo "<p style='color:red'>✗ Erro ao criar notificação</p>";
        echo "<pre>" . print_r($stmt->errorInfo(), true) . "</pre>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color:red'>Erro: " . $e->getMessage() . "</p>";
}

// Mostrar últimas notificações
echo "<h3>Últimas 5 notificações do seu usuário:</h3>";
try {
    $stmt = $pdo->prepare("SELECT * FROM notificacoes WHERE usuario_id = ? ORDER BY data_notificacao DESC LIMIT 5");
    $stmt->execute([$usuario_id]);
    $notifs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($notifs) > 0) {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID</th><th>Tipo</th><th>Mensagem</th><th>Valor</th><th>Data</th><th>Lida</th></tr>";
        foreach ($notifs as $n) {
            echo "<tr>";
            echo "<td>{$n['id']}</td>";
            echo "<td>{$n['tipo']}</td>";
            echo "<td>" . substr($n['mensagem'], 0, 50) . "</td>";
            echo "<td>" . ($n['valor'] ? "R$ " . number_format($n['valor'], 2, ',', '.') : '-') . "</td>";
            echo "<td>{$n['data_notificacao']}</td>";
            echo "<td>" . ($n['lida'] ? 'Sim' : 'Não') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>Nenhuma notificação encontrada</p>";
    }
} catch (PDOException $e) {
    echo "<p style='color:red'>Erro: " . $e->getMessage() . "</p>";
}
?>
