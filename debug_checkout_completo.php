<?php
/**
 * DEBUG COMPLETO DO CHECKOUT - Identificar todos os problemas
 * @author Souza Tech - https://rafaelsouzatech.com.br
 */
require_once __DIR__ . '/config/config.php';

echo "<h1>🔍 DEBUG COMPLETO DO CHECKOUT</h1>";

// 1. VERIFICAR CONFIGURAÇÕES DE PAGAMENTO
echo "<h2>1. 📋 CONFIGURAÇÕES DE PAGAMENTO</h2>";
try {
    $gateways = ['pushinpay', 'efi', 'mercadopago'];
    foreach ($gateways as $gateway) {
        $enabled = getSystemSetting("{$gateway}_enabled", false);
        $status = $enabled ? '✅ ATIVO' : '❌ INATIVO';
        echo "<p><strong>$gateway:</strong> $status</p>";
        
        if ($enabled) {
            $key = getSystemSetting("{$gateway}_api_key", '');
            $secret = getSystemSetting("{$gateway}_api_secret", '');
            echo "<p>  - API Key: " . (empty($key) ? '❌ VAZIA' : '✅ CONFIGURADA') . "</p>";
            echo "<p>  - API Secret: " . (empty($secret) ? '❌ VAZIA' : '✅ CONFIGURADA') . "</p>";
        }
    }
} catch (Exception $e) {
    echo "<p style='color:red'>ERRO: " . $e->getMessage() . "</p>";
}

// 2. TESTAR CRIAÇÃO DE VENDA
echo "<hr><h2>2. 🛒 TESTE DE CRIAÇÃO DE VENDA</h2>";
try {
    // Buscar um produto para teste
    $stmt = $pdo->query("SELECT * FROM produtos LIMIT 1");
    $produto = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($produto) {
        echo "<p>✅ Produto encontrado: {$produto['nome']} (ID: {$produto['id']})</p>";
        
        // Simular criação de venda
        $test_data = [
            'produto_id' => $produto['id'],
            'valor' => 1.00,
            'comprador_email' => 'teste@teste.com',
            'comprador_nome' => 'Teste Debug',
            'transacao_id' => 'DEBUG_' . time(),
            'metodo_pagamento' => 'Pix',
            'status_pagamento' => 'pending'
        ];
        
        $stmt = $pdo->prepare("INSERT INTO vendas (produto_id, valor, comprador_email, comprador_nome, transacao_id, metodo_pagamento, status_pagamento, data_venda) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
        $result = $stmt->execute([
            $test_data['produto_id'],
            $test_data['valor'],
            $test_data['comprador_email'],
            $test_data['comprador_nome'],
            $test_data['transacao_id'],
            $test_data['metodo_pagamento'],
            $test_data['status_pagamento']
        ]);
        
        if ($result) {
            $venda_id = $pdo->lastInsertId();
            echo "<p style='color:green'>✅ VENDA CRIADA COM SUCESSO! ID: $venda_id</p>";
            
            // Testar criação de notificação
            echo "<h3>📢 Testando criação de notificação:</h3>";
            $usuario_id = $produto['usuario_id'] ?? 33;
            $mensagem = "Teste de venda: {$produto['nome']} - R$ 1,00";
            
            $stmt_notif = $pdo->prepare("INSERT INTO notificacoes (usuario_id, tipo, mensagem, valor, venda_id_fk, data_notificacao) VALUES (?, 'venda', ?, ?, ?, NOW())");
            $notif_result = $stmt_notif->execute([$usuario_id, $mensagem, 1.00, $venda_id]);
            
            if ($notif_result) {
                $notif_id = $pdo->lastInsertId();
                echo "<p style='color:green'>✅ NOTIFICAÇÃO CRIADA! ID: $notif_id</p>";
            } else {
                echo "<p style='color:red'>❌ ERRO AO CRIAR NOTIFICAÇÃO</p>";
                echo "<pre>" . print_r($stmt_notif->errorInfo(), true) . "</pre>";
            }
            
        } else {
            echo "<p style='color:red'>❌ ERRO AO CRIAR VENDA</p>";
            echo "<pre>" . print_r($stmt->errorInfo(), true) . "</pre>";
        }
        
    } else {
        echo "<p style='color:red'>❌ NENHUM PRODUTO ENCONTRADO</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color:red'>ERRO: " . $e->getMessage() . "</p>";
}

// 3. VERIFICAR ARQUIVOS CRÍTICOS
echo "<hr><h2>3. 📁 VERIFICAÇÃO DE ARQUIVOS</h2>";
$arquivos_criticos = [
    'checkout.php' => 'Página de checkout',
    'notification.php' => 'API de notificações',
    'api/payment_api.php' => 'API de pagamentos',
    'helpers/pushinpay_helper.php' => 'Helper PushinPay',
    'helpers/efi_helper.php' => 'Helper Efí',
    'helpers/mercadopago_helper.php' => 'Helper Mercado Pago'
];

foreach ($arquivos_criticos as $arquivo => $descricao) {
    $caminho = __DIR__ . '/' . $arquivo;
    if (file_exists($caminho)) {
        $tamanho = filesize($caminho);
        echo "<p>✅ $descricao: <code>$arquivo</code> ($tamanho bytes)</p>";
    } else {
        echo "<p style='color:red'>❌ $descricao: <code>$arquivo</code> NÃO ENCONTRADO</p>";
    }
}

// 4. VERIFICAR LOGS DE ERRO
echo "<hr><h2>4. 📋 LOGS DE ERRO RECENTES</h2>";
$log_files = [
    'notification_api_errors.log',
    'webhook_logs.txt',
    'payment_errors.log',
    'checkout_errors.log'
];

foreach ($log_files as $log_file) {
    $caminho = __DIR__ . '/' . $log_file;
    if (file_exists($caminho)) {
        echo "<h3>📄 $log_file (últimas 10 linhas):</h3>";
        $lines = file($caminho);
        $last_lines = array_slice($lines, -10);
        echo "<pre style='background:#f5f5f5;padding:10px;font-size:11px;max-height:200px;overflow:auto;'>";
        echo htmlspecialchars(implode('', $last_lines));
        echo "</pre>";
    } else {
        echo "<p>⚠️ $log_file não encontrado</p>";
    }
}

// 5. TESTAR CONEXÃO COM APIS
echo "<hr><h2>5. 🌐 TESTE DE CONEXÃO COM APIS</h2>";

// Teste PushinPay
if (getSystemSetting('pushinpay_enabled', false)) {
    echo "<h3>🔵 Testando PushinPay:</h3>";
    $api_key = getSystemSetting('pushinpay_api_key', '');
    if (!empty($api_key)) {
        $test_url = 'https://api.pushinpay.com.br/api/pix/create';
        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/json\r\nAuthorization: Bearer $api_key\r\n",
                'content' => json_encode(['valor' => 1, 'descricao' => 'Teste'])
            ]
        ]);
        
        $response = @file_get_contents($test_url, false, $context);
        if ($response !== false) {
            echo "<p style='color:green'>✅ API PushinPay respondeu</p>";
        } else {
            echo "<p style='color:red'>❌ API PushinPay não respondeu</p>";
        }
    } else {
        echo "<p style='color:red'>❌ API Key PushinPay não configurada</p>";
    }
}

// 6. VERIFICAR SESSÃO E USUÁRIO
echo "<hr><h2>6. 👤 VERIFICAÇÃO DE SESSÃO</h2>";
session_start();
echo "<p><strong>Session Status:</strong> " . session_status() . "</p>";
echo "<p><strong>Session ID:</strong> " . session_id() . "</p>";
echo "<p><strong>Usuário Logado:</strong> " . ($_SESSION['id'] ?? 'NÃO') . "</p>";
echo "<p><strong>Nome:</strong> " . ($_SESSION['nome'] ?? 'N/A') . "</p>";

// 7. VERIFICAR TABELAS DO BANCO
echo "<hr><h2>7. 🗄️ VERIFICAÇÃO DE TABELAS</h2>";
$tabelas = ['vendas', 'notificacoes', 'produtos', 'usuarios'];
foreach ($tabelas as $tabela) {
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM $tabela");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "<p>✅ Tabela <strong>$tabela</strong>: {$result['total']} registros</p>";
    } catch (Exception $e) {
        echo "<p style='color:red'>❌ Tabela <strong>$tabela</strong>: " . $e->getMessage() . "</p>";
    }
}

echo "<hr><h2>🎯 PRÓXIMOS PASSOS</h2>";
echo "<p>1. Verifique os erros acima</p>";
echo "<p>2. Configure as APIs que estão inativas</p>";
echo "<p>3. Corrija os arquivos que não foram encontrados</p>";
echo "<p>4. Analise os logs de erro</p>";

echo "<hr><p><a href='/checkout.php?produto_id={$produto['id']}' target='_blank'>🧪 TESTAR CHECKOUT</a></p>";
?>
