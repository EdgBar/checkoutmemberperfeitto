<?php
/**
 * Script de diagnóstico para verificar tabela notificacoes
 * @author Souza Tech - https://rafaelsouzatech.com.br
 */
require_once __DIR__ . '/config/config.php';

echo "<h2>Diagnóstico da tabela notificacoes</h2>";

try {
    // Verificar se a tabela existe
    $stmt = $pdo->query("SHOW TABLES LIKE 'notificacoes'");
    if ($stmt->rowCount() === 0) {
        echo "<p style='color:red'>✗ Tabela notificacoes NÃO existe!</p>";
        exit;
    }
    echo "<p style='color:green'>✓ Tabela notificacoes existe</p>";
    
    // Mostrar estrutura da tabela
    echo "<h3>Estrutura da tabela:</h3>";
    $cols = $pdo->query("DESCRIBE notificacoes")->fetchAll(PDO::FETCH_ASSOC);
    echo "<pre>";
    print_r($cols);
    echo "</pre>";
    
    // Verificar colunas necessárias
    $existing_cols = array_column($cols, 'Field');
    $required_cols = ['id', 'tipo', 'mensagem', 'valor', 'data_notificacao', 'lida', 'link_acao', 'usuario_id', 'displayed_live', 'venda_id_fk', 'metodo_pagamento'];
    
    echo "<h3>Verificação de colunas:</h3>";
    foreach ($required_cols as $col) {
        if (in_array($col, $existing_cols)) {
            echo "<p style='color:green'>✓ Coluna '$col' existe</p>";
        } else {
            echo "<p style='color:red'>✗ Coluna '$col' NÃO existe</p>";
        }
    }
    
    // Testar query de notificações recentes
    echo "<h3>Teste de query get_recent_notifications:</h3>";
    try {
        $s = $pdo->prepare("SELECT id, tipo, mensagem, valor, DATE_FORMAT(data_notificacao, '%Y-%m-%dT%H:%i:%s') as data_notificacao, lida, link_acao FROM notificacoes ORDER BY data_notificacao DESC LIMIT 5");
        $s->execute();
        $results = $s->fetchAll(PDO::FETCH_ASSOC);
        echo "<p style='color:green'>✓ Query executada com sucesso</p>";
        echo "<pre>";
        print_r($results);
        echo "</pre>";
    } catch (PDOException $e) {
        echo "<p style='color:red'>✗ Erro na query: " . $e->getMessage() . "</p>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color:red'>Erro: " . $e->getMessage() . "</p>";
}
?>
