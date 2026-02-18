<?php
/**
 * Script de diagnóstico para verificar URL de fundo do login
 * @author Souza Tech - https://rafaelsouzatech.com.br
 */
require_once __DIR__ . '/config/config.php';

echo "<h2>Verificando configurações de fundo do login</h2>";

try {
    // Buscar na tabela correta: configuracoes_sistema
    $stmt = $pdo->query("SELECT chave, valor FROM configuracoes_sistema WHERE chave LIKE 'login%'");
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>Configurações na tabela configuracoes_sistema:</h3>";
    echo "<pre>";
    print_r($results);
    echo "</pre>";
    
    // Verificar especificamente login_bg_url
    $stmt2 = $pdo->prepare("SELECT valor FROM configuracoes_sistema WHERE chave = 'login_bg_url' LIMIT 1");
    $stmt2->execute();
    $result = $stmt2->fetch(PDO::FETCH_ASSOC);
    
    echo "<h3>login_bg_url:</h3>";
    if ($result && !empty($result['valor'])) {
        echo "<p style='color:green'>Valor encontrado: " . htmlspecialchars($result['valor']) . "</p>";
        echo "<p>Preview:</p>";
        if (preg_match('/\.(mp4|webm)$/i', $result['valor'])) {
            echo "<video src='" . htmlspecialchars($result['valor']) . "' width='400' autoplay muted loop></video>";
        } else {
            echo "<img src='" . htmlspecialchars($result['valor']) . "' width='400'>";
        }
    } else {
        echo "<p style='color:red'>Nenhum valor encontrado para login_bg_url</p>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color:red'>Erro: " . $e->getMessage() . "</p>";
}
?>
