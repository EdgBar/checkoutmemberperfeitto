<?php
/**
 * Funções Auxiliares do Sistema SaaS
 * 
 * Este arquivo contém todas as funções principais para gerenciamento do sistema SaaS
 */

if (!function_exists('saas_enabled')) {
    /**
     * Verifica se o sistema SaaS está habilitado
     * @return bool
     */
    function saas_enabled() {
        global $pdo;
        
        if (!isset($pdo)) {
            return false;
        }
        
        try {
            // Verifica se a tabela existe
            $stmt = $pdo->query("SHOW TABLES LIKE 'saas_config'");
            if ($stmt->rowCount() == 0) {
                return false;
            }
            
            // Busca o valor da chave 'enabled' na tabela chave/valor
            $stmt = $pdo->prepare("SELECT valor FROM saas_config WHERE chave = 'enabled' LIMIT 1");
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $result && (int)$result['valor'] === 1;
        } catch (PDOException $e) {
            error_log("Erro ao verificar status SaaS: " . $e->getMessage());
            return false;
        }
    }
}

if (!function_exists('saas_enable')) {
    /**
     * Habilita o sistema SaaS
     * @return bool
     */
    function saas_enable() {
        global $pdo;
        
        if (!isset($pdo)) {
            return false;
        }
        
        try {
            // Verifica se já existe a chave 'enabled'
            $stmt = $pdo->prepare("SELECT id FROM saas_config WHERE chave = 'enabled' LIMIT 1");
            $stmt->execute();
            $exists = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($exists) {
                $stmt = $pdo->prepare("UPDATE saas_config SET valor = '1' WHERE chave = 'enabled'");
            } else {
                $stmt = $pdo->prepare("INSERT INTO saas_config (chave, valor, descricao) VALUES ('enabled', '1', 'Sistema SaaS habilitado')");
            }
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erro ao habilitar SaaS: " . $e->getMessage());
            return false;
        }
    }
}

if (!function_exists('saas_disable')) {
    /**
     * Desabilita o sistema SaaS
     * @return bool
     */
    function saas_disable() {
        global $pdo;
        
        if (!isset($pdo)) {
            return false;
        }
        
        try {
            $stmt = $pdo->prepare("UPDATE saas_config SET valor = '0' WHERE chave = 'enabled'");
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erro ao desabilitar SaaS: " . $e->getMessage());
            return false;
        }
    }
}

if (!function_exists('saas_get_user_plan')) {
    /**
     * Retorna o plano atual do usuário
     * @param int $usuario_id
     * @return array|null
     */
    function saas_get_user_plan($usuario_id) {
        global $pdo;
        
        if (!isset($pdo) || !$usuario_id) {
            error_log("SaaS get_user_plan: PDO não definido ou usuario_id inválido: " . ($usuario_id ?? 'null'));
            return null;
        }
        
        try {
            // Verificar se a coluna data_vencimento existe na tabela
            $stmt_cols = $pdo->query("SHOW COLUMNS FROM saas_assinaturas");
            $colunas = $stmt_cols->fetchAll(PDO::FETCH_COLUMN);
            $has_data_vencimento = in_array('data_vencimento', $colunas);
            
            error_log("SaaS get_user_plan: Verificando plano para usuário #$usuario_id. Coluna data_vencimento existe: " . ($has_data_vencimento ? 'sim' : 'não'));
            
            // Construir query dinamicamente baseado na existência da coluna
            $sql = "
                SELECT sa.*, sp.id as plano_id, sp.nome as plano_nome, sp.preco, sp.periodo, sp.max_produtos, sp.max_pedidos_mes
                FROM saas_assinaturas sa
                JOIN saas_planos sp ON sa.plano_id = sp.id
                WHERE sa.usuario_id = ? 
                AND sa.status IN ('ativa', 'ativo')";
            
            // Se a coluna existe, permitir NULL ou data futura
            // Se não existe, não usar essa condição
            if ($has_data_vencimento) {
                $sql .= " AND (sa.data_vencimento IS NULL OR sa.data_vencimento >= CURDATE() OR sa.data_vencimento = '0000-00-00' OR sa.data_vencimento = '')";
            }
            
            $sql .= "
                ORDER BY sa.data_inicio DESC
                LIMIT 1";
            
            error_log("SaaS get_user_plan: Query SQL: " . str_replace('?', $usuario_id, $sql));
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$usuario_id]);
            $plano = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($plano) {
                error_log("SaaS get_user_plan: Plano encontrado para usuário #$usuario_id - Plano ID: {$plano['plano_id']}, Status: {$plano['status']}, Data vencimento: " . ($plano['data_vencimento'] ?? 'NULL'));
            } else {
                error_log("SaaS get_user_plan: Nenhum plano ativo encontrado para usuário #$usuario_id");
                // Debug: Verificar se há assinaturas para este usuário (mesmo inativas)
                try {
                    $stmt_debug = $pdo->prepare("SELECT id, plano_id, status, data_vencimento, data_inicio FROM saas_assinaturas WHERE usuario_id = ? ORDER BY data_inicio DESC LIMIT 5");
                    $stmt_debug->execute([$usuario_id]);
                    $assinaturas_debug = $stmt_debug->fetchAll(PDO::FETCH_ASSOC);
                    if ($assinaturas_debug) {
                        error_log("SaaS get_user_plan: DEBUG - Assinaturas encontradas para usuário #$usuario_id: " . json_encode($assinaturas_debug));
                    } else {
                        error_log("SaaS get_user_plan: DEBUG - Nenhuma assinatura encontrada para usuário #$usuario_id");
                    }
                } catch (PDOException $e) {
                    error_log("SaaS get_user_plan: Erro ao buscar assinaturas para debug: " . $e->getMessage());
                }
            }
            
            return $plano;
        } catch (PDOException $e) {
            error_log("SaaS get_user_plan: Erro ao buscar plano do usuário #$usuario_id: " . $e->getMessage());
            error_log("SaaS get_user_plan: Stack trace: " . $e->getTraceAsString());
            return null;
        }
    }
}

if (!function_exists('saas_assign_free_plan')) {
    /**
     * Atribui plano free automaticamente ao usuário
     * @param int $usuario_id
     * @return bool
     */
    function saas_assign_free_plan($usuario_id) {
        global $pdo;
        
        if (!isset($pdo) || !$usuario_id) {
            return false;
        }
        
        try {
            // Verifica se já tem plano atribuído
            $plano_atual = saas_get_user_plan($usuario_id);
            if ($plano_atual) {
                return true; // Já tem plano
            }
            
            // Verifica se já foi atribuído anteriormente
            $stmt = $pdo->prepare("SELECT saas_plano_free_atribuido FROM usuarios WHERE id = ?");
            $stmt->execute([$usuario_id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && (int)$user['saas_plano_free_atribuido'] === 1) {
                // Migração/compatibilidade: instalações antigas usavam status 'ativo'
                try {
                    $pdo->prepare("UPDATE saas_assinaturas SET status = 'ativa' WHERE usuario_id = ? AND status = 'ativo'")
                        ->execute([$usuario_id]);
                } catch (PDOException $e) {
                    // ignora
                }
                // Revalida: se ainda não houver plano, segue para (re)atribuir o free
                $plano_atual = saas_get_user_plan($usuario_id);
                if ($plano_atual) {
                    return true;
                }
            }
            
            // Busca plano free (is_free = 1 OU preco = 0)
            $stmt = $pdo->prepare("SELECT id FROM saas_planos WHERE (is_free = 1 OR preco = 0) AND ativo = 1 ORDER BY is_free DESC, preco ASC LIMIT 1");
            $stmt->execute();
            $plano_free = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$plano_free) {
                error_log("Nenhum plano free disponível para atribuir ao usuário ID: $usuario_id");
                return false; // Não há plano free disponível
            }
            
            // Calcula data de vencimento (30 dias para plano mensal)
            $data_inicio = date('Y-m-d');
            $data_vencimento = date('Y-m-d', strtotime('+30 days'));
            
            // Garantir estrutura da tabela
            saas_ensure_table_structure();
            
            // Verificar quais colunas existem
            $stmt_cols = $pdo->query("SHOW COLUMNS FROM saas_assinaturas");
            $colunas = $stmt_cols->fetchAll(PDO::FETCH_COLUMN);
            $has_data_vencimento = in_array('data_vencimento', $colunas);
            
            // Cria assinatura
            if ($has_data_vencimento) {
                $stmt = $pdo->prepare("
                    INSERT INTO saas_assinaturas 
                    (usuario_id, plano_id, status, data_inicio, data_vencimento) 
                    VALUES (?, ?, 'ativa', ?, ?)
                ");
                $stmt->execute([$usuario_id, $plano_free['id'], $data_inicio, $data_vencimento]);
            } else {
                $stmt = $pdo->prepare("
                    INSERT INTO saas_assinaturas 
                    (usuario_id, plano_id, status, data_inicio) 
                    VALUES (?, ?, 'ativa', ?)
                ");
                $stmt->execute([$usuario_id, $plano_free['id'], $data_inicio]);
            }
            
            // Marca como atribuído
            $stmt = $pdo->prepare("UPDATE usuarios SET saas_plano_free_atribuido = 1 WHERE id = ?");
            $stmt->execute([$usuario_id]);
            
            return true;
        } catch (PDOException $e) {
            error_log("Erro ao atribuir plano free: " . $e->getMessage());
            return false;
        }
    }
}

if (!function_exists('saas_check_user_access')) {
    /**
     * Verifica se o usuário tem acesso (tem plano ativo)
     * @param int $usuario_id
     * @return bool
     */
    function saas_check_user_access($usuario_id) {
        global $pdo;
        
        if (!isset($pdo) || !$usuario_id) {
            return false;
        }
        
        // Se SaaS não está habilitado, todos têm acesso
        if (!saas_enabled()) {
            return true;
        }
        
        try {
            $plano = saas_get_user_plan($usuario_id);
            return $plano !== null;
        } catch (Exception $e) {
            error_log("Erro ao verificar acesso do usuário: " . $e->getMessage());
            return false;
        }
    }
}

if (!function_exists('saas_get_plan_limits')) {
    /**
     * Retorna os limites do plano
     * @param int $plano_id
     * @return array
     */
    function saas_get_plan_limits($plano_id) {
        global $pdo;
        
        if (!isset($pdo) || !$plano_id) {
            return ['max_produtos' => null, 'max_pedidos_mes' => null];
        }
        
        try {
            $stmt = $pdo->prepare("SELECT max_produtos, max_pedidos_mes FROM saas_planos WHERE id = ?");
            $stmt->execute([$plano_id]);
            $plano = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($plano) {
                return [
                    'max_produtos' => $plano['max_produtos'],
                    'max_pedidos_mes' => $plano['max_pedidos_mes']
                ];
            }
            
            return ['max_produtos' => null, 'max_pedidos_mes' => null];
        } catch (PDOException $e) {
            error_log("Erro ao buscar limites do plano: " . $e->getMessage());
            return ['max_produtos' => null, 'max_pedidos_mes' => null];
        }
    }
}

if (!function_exists('saas_get_user_limits')) {
    /**
     * Retorna os limites do plano atual do usuário
     * @param int $usuario_id
     * @return array
     */
    function saas_get_user_limits($usuario_id) {
        $plano = saas_get_user_plan($usuario_id);
        
        if (!$plano) {
            return ['max_produtos' => null, 'max_pedidos_mes' => null];
        }
        
        return saas_get_plan_limits($plano['plano_id']);
    }
}

if (!function_exists('saas_ensure_table_structure')) {
    /**
     * Garante que a tabela saas_assinaturas tem todas as colunas necessárias
     * @return bool
     */
    function saas_ensure_table_structure() {
        global $pdo;
        
        if (!isset($pdo)) {
            error_log("SaaS: PDO não disponível em saas_ensure_table_structure");
            return false;
        }
        
        try {
            // Verificar se a tabela existe
            $stmt_table = $pdo->query("SHOW TABLES LIKE 'saas_assinaturas'");
            if ($stmt_table->rowCount() == 0) {
                error_log("SaaS: Tabela saas_assinaturas não existe");
                return false;
            }
            
            // Buscar todas as colunas de uma vez
            $stmt = $pdo->query("SHOW COLUMNS FROM saas_assinaturas");
            $existing_columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            // Verificar e criar data_vencimento se não existir
            if (!in_array('data_vencimento', $existing_columns)) {
                try {
                    // Tentar encontrar onde inserir (depois de data_inicio se existir, senão no final)
                    $after_column = in_array('data_inicio', $existing_columns) ? 'AFTER data_inicio' : '';
                    $pdo->exec("ALTER TABLE saas_assinaturas ADD COLUMN data_vencimento DATE $after_column");
                    error_log("SaaS: Coluna data_vencimento criada na tabela saas_assinaturas");
                    $existing_columns[] = 'data_vencimento'; // Atualizar lista
                } catch (PDOException $e) {
                    error_log("SaaS: Erro ao criar coluna data_vencimento: " . $e->getMessage());
                }
            }
            
            // Verificar e criar transacao_id se não existir
            if (!in_array('transacao_id', $existing_columns)) {
                try {
                    // Buscar novamente as colunas para ver se data_vencimento foi criada
                    $stmt_refresh = $pdo->query("SHOW COLUMNS FROM saas_assinaturas");
                    $refreshed_columns = $stmt_refresh->fetchAll(PDO::FETCH_COLUMN);
                    
                    if (in_array('data_vencimento', $refreshed_columns)) {
                        $after_column = 'AFTER data_vencimento';
                    } elseif (in_array('data_inicio', $refreshed_columns)) {
                        $after_column = 'AFTER data_inicio';
                    } else {
                        $after_column = '';
                    }
                    
                    $pdo->exec("ALTER TABLE saas_assinaturas ADD COLUMN transacao_id VARCHAR(255) NULL $after_column");
                    error_log("SaaS: Coluna transacao_id criada na tabela saas_assinaturas");
                    $existing_columns[] = 'transacao_id'; // Atualizar lista
                } catch (PDOException $e) {
                    error_log("SaaS: Erro ao criar coluna transacao_id: " . $e->getMessage());
                    // Tentar criar sem especificar posição
                    try {
                        $pdo->exec("ALTER TABLE saas_assinaturas ADD COLUMN transacao_id VARCHAR(255) NULL");
                        error_log("SaaS: Coluna transacao_id criada sem especificar posição");
                    } catch (PDOException $e2) {
                        error_log("SaaS: Erro ao criar coluna transacao_id (tentativa 2): " . $e2->getMessage());
                    }
                }
            }
            
            // Verificar e criar metodo_pagamento se não existir
            if (!in_array('metodo_pagamento', $existing_columns)) {
                try {
                    // Buscar novamente as colunas para ver se transacao_id foi criada
                    $stmt_refresh = $pdo->query("SHOW COLUMNS FROM saas_assinaturas");
                    $refreshed_columns = $stmt_refresh->fetchAll(PDO::FETCH_COLUMN);
                    
                    if (in_array('transacao_id', $refreshed_columns)) {
                        $after_column = 'AFTER transacao_id';
                    } elseif (in_array('data_vencimento', $refreshed_columns)) {
                        $after_column = 'AFTER data_vencimento';
                    } else {
                        $after_column = '';
                    }
                    
                    $pdo->exec("ALTER TABLE saas_assinaturas ADD COLUMN metodo_pagamento VARCHAR(50) NULL $after_column");
                    error_log("SaaS: Coluna metodo_pagamento criada na tabela saas_assinaturas");
                } catch (PDOException $e) {
                    error_log("SaaS: Erro ao criar coluna metodo_pagamento: " . $e->getMessage());
                    // Tentar criar sem especificar posição
                    try {
                        $pdo->exec("ALTER TABLE saas_assinaturas ADD COLUMN metodo_pagamento VARCHAR(50) NULL");
                        error_log("SaaS: Coluna metodo_pagamento criada sem especificar posição");
                    } catch (PDOException $e2) {
                        error_log("SaaS: Erro ao criar coluna metodo_pagamento (tentativa 2): " . $e2->getMessage());
                    }
                }
            }
            
            return true;
        } catch (PDOException $e) {
            error_log("SaaS: Erro ao verificar/criar estrutura da tabela: " . $e->getMessage());
            error_log("SaaS: Stack trace: " . $e->getTraceAsString());
            return false;
        }
    }
}

