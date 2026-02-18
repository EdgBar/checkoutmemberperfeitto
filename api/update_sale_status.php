<?php
/**
 * API para atualizar status de venda e criar notificação automaticamente
 * Este arquivo é chamado internamente quando um pagamento é aprovado
 * 
 * @author Souza Tech - https://rafaelsouzatech.com.br
 * @version 1.0.0
 */

require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

/**
 * Atualiza status da venda e cria notificação se necessário
 * 
 * @param PDO $pdo Conexão com banco de dados
 * @param string $transacao_id ID da transação
 * @param string $new_status Novo status (approved, pending, etc)
 * @return array Resultado da operação
 */
function updateSaleStatusWithNotification($pdo, $transacao_id, $new_status) {
    $result = [
        'success' => false,
        'message' => '',
        'notification_created' => false
    ];
    
    try {
        // Buscar venda
        $stmt = $pdo->prepare("
            SELECT v.*, p.nome as produto_nome, p.usuario_id as produtor_id 
            FROM vendas v 
            JOIN produtos p ON v.produto_id = p.id 
            WHERE v.transacao_id = ? 
            LIMIT 1
        ");
        $stmt->execute([$transacao_id]);
        $venda = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$venda) {
            $result['message'] = 'Venda não encontrada';
            return $result;
        }
        
        $old_status = $venda['status_pagamento'];
        
        // Se status não mudou, não faz nada
        if ($old_status === $new_status) {
            $result['success'] = true;
            $result['message'] = 'Status já está como ' . $new_status;
            return $result;
        }
        
        // Atualizar status
        $stmt_update = $pdo->prepare("UPDATE vendas SET status_pagamento = ? WHERE transacao_id = ?");
        $stmt_update->execute([$new_status, $transacao_id]);
        
        $result['success'] = true;
        $result['message'] = "Status atualizado de '$old_status' para '$new_status'";
        
        // Se novo status é approved, criar notificação
        if ($new_status === 'approved' || $new_status === 'paid' || $new_status === 'completed') {
            // Verificar se já existe notificação para esta venda
            $stmt_check = $pdo->prepare("SELECT id FROM notificacoes WHERE venda_id_fk = ? LIMIT 1");
            $stmt_check->execute([$venda['id']]);
            
            if (!$stmt_check->fetch()) {
                // Criar notificação
                $produtor_id = $venda['produtor_id'];
                $mensagem = "Nova venda: " . ($venda['produto_nome'] ?? 'Produto') . " - R$ " . number_format($venda['valor'], 2, ',', '.');
                
                $stmt_notif = $pdo->prepare("
                    INSERT INTO notificacoes (usuario_id, tipo, mensagem, valor, metodo_pagamento, venda_id_fk, link_acao, data_notificacao) 
                    VALUES (?, 'Compra Aprovada', ?, ?, ?, ?, ?, NOW())
                ");
                $stmt_notif->execute([
                    $produtor_id,
                    $mensagem,
                    $venda['valor'],
                    $venda['metodo_pagamento'],
                    $venda['id'],
                    "/index?pagina=vendas&id={$venda['id']}"
                ]);
                
                $result['notification_created'] = true;
                $result['message'] .= ' | Notificação criada para produtor ID: ' . $produtor_id;
                
                error_log("NOTIFICACAO CRIADA: Venda #{$venda['id']} aprovada, notificação enviada para produtor $produtor_id");
            } else {
                $result['message'] .= ' | Notificação já existia';
            }
        }
        
        return $result;
        
    } catch (Exception $e) {
        $result['message'] = 'Erro: ' . $e->getMessage();
        error_log("ERRO updateSaleStatusWithNotification: " . $e->getMessage());
        return $result;
    }
}

// Se chamado diretamente via API
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $transacao_id = $data['transacao_id'] ?? null;
    $new_status = $data['status'] ?? null;
    
    if (!$transacao_id || !$new_status) {
        echo json_encode(['error' => 'transacao_id e status são obrigatórios']);
        exit;
    }
    
    $result = updateSaleStatusWithNotification($pdo, $transacao_id, $new_status);
    echo json_encode($result);
    exit;
}
?>
