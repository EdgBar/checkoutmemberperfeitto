<?php
/**
 * Helper para criação de notificações de vendas
 * Cria notificações automaticamente quando PIX é gerado ou pagamento é aprovado
 * 
 * @author Souza Tech - https://rafaelsouzatech.com.br
 * @version 1.0.0
 */

/**
 * Cria notificação de venda para o produtor
 * 
 * @param PDO $pdo Conexão com banco de dados
 * @param int $venda_id ID da venda
 * @param string $tipo Tipo da notificação (Pix Gerado, Compra Aprovada, etc)
 * @param float $valor Valor da venda
 * @param string|null $produto_nome Nome do produto (opcional, será buscado se não informado)
 * @return bool True se criou, false se já existia ou erro
 */
function criar_notificacao_venda($pdo, $venda_id, $tipo, $valor = null, $produto_nome = null) {
    try {
        // Verificar se já existe notificação para esta venda com este tipo
        $stmt_check = $pdo->prepare("SELECT id FROM notificacoes WHERE venda_id_fk = ? AND tipo = ? LIMIT 1");
        $stmt_check->execute([$venda_id, $tipo]);
        
        if ($stmt_check->fetch()) {
            // Já existe notificação deste tipo para esta venda
            return false;
        }
        
        // Buscar dados da venda se não foram informados
        if (!$produto_nome || !$valor) {
            $stmt_venda = $pdo->prepare("
                SELECT v.valor, p.nome as produto_nome, p.usuario_id as produtor_id 
                FROM vendas v 
                JOIN produtos p ON v.produto_id = p.id 
                WHERE v.id = ? 
                LIMIT 1
            ");
            $stmt_venda->execute([$venda_id]);
            $venda = $stmt_venda->fetch(PDO::FETCH_ASSOC);
            
            if (!$venda) {
                error_log("NOTIFICACAO_HELPER: Venda #$venda_id não encontrada");
                return false;
            }
            
            $valor = $valor ?? $venda['valor'];
            $produto_nome = $produto_nome ?? $venda['produto_nome'];
            $produtor_id = $venda['produtor_id'];
        } else {
            // Buscar apenas o produtor_id
            $stmt_prod = $pdo->prepare("
                SELECT p.usuario_id as produtor_id 
                FROM vendas v 
                JOIN produtos p ON v.produto_id = p.id 
                WHERE v.id = ? 
                LIMIT 1
            ");
            $stmt_prod->execute([$venda_id]);
            $prod_data = $stmt_prod->fetch(PDO::FETCH_ASSOC);
            $produtor_id = $prod_data['produtor_id'] ?? null;
        }
        
        if (!$produtor_id) {
            error_log("NOTIFICACAO_HELPER: Produtor não encontrado para venda #$venda_id");
            return false;
        }
        
        // Criar mensagem baseada no tipo
        if ($tipo === 'Pix Gerado') {
            $mensagem = "Pix gerado: {$produto_nome} - R$ " . number_format($valor, 2, ',', '.') . " - Aguardando pagamento";
        } elseif ($tipo === 'Compra Aprovada') {
            $mensagem = "Nova venda: {$produto_nome} - R$ " . number_format($valor, 2, ',', '.');
        } else {
            $mensagem = "{$tipo}: {$produto_nome} - R$ " . number_format($valor, 2, ',', '.');
        }
        
        // Criar notificação
        $stmt_notif = $pdo->prepare("
            INSERT INTO notificacoes (usuario_id, tipo, mensagem, valor, venda_id_fk, link_acao, data_notificacao) 
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt_notif->execute([
            $produtor_id,
            $tipo,
            $mensagem,
            $valor,
            $venda_id,
            "/index?pagina=vendas&id={$venda_id}"
        ]);
        
        error_log("NOTIFICACAO_HELPER: Notificação '$tipo' criada para venda #$venda_id, produtor #$produtor_id");
        return true;
        
    } catch (Exception $e) {
        error_log("NOTIFICACAO_HELPER ERRO: " . $e->getMessage());
        return false;
    }
}

/**
 * Cria notificação de Pix Gerado
 */
function notificar_pix_gerado($pdo, $venda_id, $valor = null, $produto_nome = null) {
    return criar_notificacao_venda($pdo, $venda_id, 'Pix Gerado', $valor, $produto_nome);
}

/**
 * Cria notificação de Compra Aprovada e REMOVE a notificação de Pix Gerado
 */
function notificar_compra_aprovada($pdo, $venda_id, $valor = null, $produto_nome = null) {
    // Primeiro, deletar notificação de "Pix Gerado" para esta venda (não precisa mais)
    try {
        $stmt_delete = $pdo->prepare("DELETE FROM notificacoes WHERE venda_id_fk = ? AND tipo IN ('Pix Gerado', 'Boleto Gerado')");
        $stmt_delete->execute([$venda_id]);
        $deleted = $stmt_delete->rowCount();
        if ($deleted > 0) {
            error_log("NOTIFICACAO_HELPER: Removidas $deleted notificações pendentes para venda #$venda_id");
        }
    } catch (Exception $e) {
        error_log("NOTIFICACAO_HELPER: Erro ao remover notificações pendentes: " . $e->getMessage());
    }
    
    // Agora criar a notificação de Compra Aprovada
    return criar_notificacao_venda($pdo, $venda_id, 'Compra Aprovada', $valor, $produto_nome);
}

/**
 * Cria notificação de Boleto Gerado
 */
function notificar_boleto_gerado($pdo, $venda_id, $valor = null, $produto_nome = null) {
    return criar_notificacao_venda($pdo, $venda_id, 'Boleto Gerado', $valor, $produto_nome);
}
?>
