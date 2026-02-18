<?php
/**
 * Página de Configuração de Textos do Login - Admin
 * Autor: Rafael Souza - https://rafaelsouzatech.com.br
 * 
 * Permite personalizar os textos e cores da página de login
 */

$mensagem = '';

// Valores padrão
$defaults = [
    // Badge Superior
    'login_badge_text' => 'Plataforma #1 em Conversão',
    'login_badge_color' => '#c084fc',
    
    // Título Principal
    'login_title_line1' => 'Transforme seu conhecimento em',
    'login_title_line2' => 'resultados reais.',
    'login_title_color' => '#ffffff',
    'login_title_highlight_color1' => '#a855f7',
    'login_title_highlight_color2' => '#06b6d4',
    
    // Botão de Login
    'login_btn_color1' => '#a855f7',
    'login_btn_color2' => '#7c3aed',
    'login_btn_color3' => '#06b6d4',
    
    // Descrição
    'login_description' => 'Junte-se a produtores, afiliados e agências que já estão vendendo todos os dias usando o CheckoutPRO uma plataforma completa de área de membros + checkout próprio, criada para escalar com liberdade total.',
    'login_description_color' => '#dee4ed',
    
    // Estatísticas
    'login_stat1_value' => 'R$ 95K+',
    'login_stat1_label' => 'processados em vendas',
    'login_stat2_value' => '620+',
    'login_stat2_label' => 'usuários ativos',
    'login_stat3_value' => '99.3%',
    'login_stat3_label' => 'de estabilidade e uptime',
    'login_stats_value_color' => '#ffffff',
    'login_stats_label_color' => '#64748b',
];

// Buscar configurações atuais do banco
$config = [];
try {
    $keys = array_keys($defaults);
    $placeholders = implode(',', array_fill(0, count($keys), '?'));
    $stmt = $pdo->prepare("SELECT chave, valor FROM configuracoes WHERE chave IN ($placeholders)");
    $stmt->execute($keys);
    $results = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    
    // Mesclar com defaults
    foreach ($defaults as $key => $default) {
        $config[$key] = $results[$key] ?? $default;
    }
} catch (PDOException $e) {
    error_log("Erro ao buscar configurações de login: " . $e->getMessage());
    $config = $defaults;
}

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['salvar'])) {
        try {
            foreach ($defaults as $key => $default) {
                $value = $_POST[$key] ?? $default;
                
                // Usar INSERT ... ON DUPLICATE KEY UPDATE para evitar problemas de estrutura
                $stmt = $pdo->prepare("
                    INSERT INTO configuracoes (chave, valor) VALUES (?, ?)
                    ON DUPLICATE KEY UPDATE valor = VALUES(valor)
                ");
                $stmt->execute([$key, $value]);
                
                $config[$key] = $value;
            }
            
            $mensagem = '<div class="bg-green-900/20 border border-green-500 text-green-300 px-4 py-3 rounded-lg mb-6" role="alert">
                <i data-lucide="check-circle" class="inline w-5 h-5 mr-2"></i>Configurações salvas com sucesso!
            </div>';
        } catch (PDOException $e) {
            $mensagem = '<div class="bg-red-900/20 border border-red-500 text-red-300 px-4 py-3 rounded-lg mb-6" role="alert">
                <i data-lucide="alert-circle" class="inline w-5 h-5 mr-2"></i>Erro ao salvar: ' . htmlspecialchars($e->getMessage()) . '
            </div>';
        }
    } elseif (isset($_POST['restaurar'])) {
        try {
            foreach ($defaults as $key => $default) {
                $stmt = $pdo->prepare("UPDATE configuracoes SET valor = ? WHERE chave = ?");
                $stmt->execute([$default, $key]);
                $config[$key] = $default;
            }
            
            $mensagem = '<div class="bg-blue-900/20 border border-blue-500 text-blue-300 px-4 py-3 rounded-lg mb-6" role="alert">
                <i data-lucide="refresh-cw" class="inline w-5 h-5 mr-2"></i>Configurações restauradas para os valores padrão!
            </div>';
        } catch (PDOException $e) {
            $mensagem = '<div class="bg-red-900/20 border border-red-500 text-red-300 px-4 py-3 rounded-lg mb-6" role="alert">
                <i data-lucide="alert-circle" class="inline w-5 h-5 mr-2"></i>Erro ao restaurar: ' . htmlspecialchars($e->getMessage()) . '
            </div>';
        }
    }
}

// Função helper para escapar valores
function e($value) {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}
?>

<div class="max-w-4xl mx-auto">
    <!-- Header -->
    <div class="flex items-center justify-between mb-8">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-cyan-500 rounded-xl flex items-center justify-center">
                <i data-lucide="type" class="w-6 h-6 text-white"></i>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-white">Textos do Login</h1>
                <p class="text-gray-400 text-sm">Personalize os textos e cores da página de login</p>
            </div>
        </div>
        <a href="?pagina=configuracoes" class="flex items-center gap-2 px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-lg transition-colors">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            Voltar
        </a>
    </div>

    <?php echo $mensagem; ?>

    <form method="POST" class="space-y-6">
        
        <!-- Badge Superior -->
        <div class="bg-gray-800/50 rounded-xl p-6 border border-gray-700">
            <h2 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                <i data-lucide="circle" class="w-5 h-5 text-purple-400"></i>
                Badge Superior
            </h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Texto do Badge</label>
                    <input type="text" name="login_badge_text" value="<?php echo e($config['login_badge_text']); ?>" 
                           class="w-full px-4 py-3 bg-gray-900 border border-gray-600 rounded-lg text-white focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Cor do Badge</label>
                    <div class="flex gap-2">
                        <input type="color" name="login_badge_color" value="<?php echo e($config['login_badge_color']); ?>" 
                               class="w-12 h-12 rounded-lg cursor-pointer border-0 bg-transparent">
                        <input type="text" value="<?php echo e($config['login_badge_color']); ?>" 
                               class="flex-1 px-4 py-3 bg-gray-900 border border-gray-600 rounded-lg text-white"
                               oninput="this.previousElementSibling.value = this.value" 
                               onchange="this.previousElementSibling.value = this.value">
                    </div>
                </div>
            </div>
        </div>

        <!-- Título Principal -->
        <div class="bg-gray-800/50 rounded-xl p-6 border border-gray-700">
            <h2 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                <i data-lucide="heading" class="w-5 h-5 text-purple-400"></i>
                Título Principal
            </h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Primeira Linha</label>
                    <input type="text" name="login_title_line1" value="<?php echo e($config['login_title_line1']); ?>" 
                           class="w-full px-4 py-3 bg-gray-900 border border-gray-600 rounded-lg text-white focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Segunda Linha (Destaque)</label>
                    <input type="text" name="login_title_line2" value="<?php echo e($config['login_title_line2']); ?>" 
                           class="w-full px-4 py-3 bg-gray-900 border border-gray-600 rounded-lg text-white focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Cor do Título</label>
                    <div class="flex gap-2">
                        <input type="color" name="login_title_color" value="<?php echo e($config['login_title_color']); ?>" 
                               class="w-12 h-12 rounded-lg cursor-pointer border-0 bg-transparent">
                        <input type="text" value="<?php echo e($config['login_title_color']); ?>" 
                               class="flex-1 px-4 py-3 bg-gray-900 border border-gray-600 rounded-lg text-white"
                               oninput="this.previousElementSibling.value = this.value">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Cor do Destaque 1 (Início do Gradiente)</label>
                    <div class="flex gap-2">
                        <input type="color" name="login_title_highlight_color1" value="<?php echo e($config['login_title_highlight_color1']); ?>" 
                               class="w-12 h-12 rounded-lg cursor-pointer border-0 bg-transparent">
                        <input type="text" value="<?php echo e($config['login_title_highlight_color1']); ?>" 
                               class="flex-1 px-4 py-3 bg-gray-900 border border-gray-600 rounded-lg text-white"
                               oninput="this.previousElementSibling.value = this.value">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Cor do Destaque 2 (Fim do Gradiente)</label>
                    <div class="flex gap-2">
                        <input type="color" name="login_title_highlight_color2" value="<?php echo e($config['login_title_highlight_color2']); ?>" 
                               class="w-12 h-12 rounded-lg cursor-pointer border-0 bg-transparent">
                        <input type="text" value="<?php echo e($config['login_title_highlight_color2']); ?>" 
                               class="flex-1 px-4 py-3 bg-gray-900 border border-gray-600 rounded-lg text-white"
                               oninput="this.previousElementSibling.value = this.value">
                    </div>
                </div>
            </div>
        </div>

        <!-- Botão de Login -->
        <div class="bg-gray-800/50 rounded-xl p-6 border border-gray-700">
            <h2 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                <i data-lucide="mouse-pointer-click" class="w-5 h-5 text-purple-400"></i>
                Botão de Login
            </h2>
            <p class="text-gray-400 text-sm mb-4">Configure as cores do gradiente do botão "Entrar na Plataforma"</p>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Cor 1 (Início)</label>
                    <div class="flex gap-2">
                        <input type="color" name="login_btn_color1" value="<?php echo e($config['login_btn_color1']); ?>" 
                               class="w-12 h-12 rounded-lg cursor-pointer border-0 bg-transparent" id="btn_color1">
                        <input type="text" value="<?php echo e($config['login_btn_color1']); ?>" 
                               class="flex-1 px-4 py-3 bg-gray-900 border border-gray-600 rounded-lg text-white"
                               oninput="document.getElementById('btn_color1').value = this.value; updateBtnPreview();">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Cor 2 (Meio)</label>
                    <div class="flex gap-2">
                        <input type="color" name="login_btn_color2" value="<?php echo e($config['login_btn_color2']); ?>" 
                               class="w-12 h-12 rounded-lg cursor-pointer border-0 bg-transparent" id="btn_color2">
                        <input type="text" value="<?php echo e($config['login_btn_color2']); ?>" 
                               class="flex-1 px-4 py-3 bg-gray-900 border border-gray-600 rounded-lg text-white"
                               oninput="document.getElementById('btn_color2').value = this.value; updateBtnPreview();">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Cor 3 (Fim)</label>
                    <div class="flex gap-2">
                        <input type="color" name="login_btn_color3" value="<?php echo e($config['login_btn_color3']); ?>" 
                               class="w-12 h-12 rounded-lg cursor-pointer border-0 bg-transparent" id="btn_color3">
                        <input type="text" value="<?php echo e($config['login_btn_color3']); ?>" 
                               class="flex-1 px-4 py-3 bg-gray-900 border border-gray-600 rounded-lg text-white"
                               oninput="document.getElementById('btn_color3').value = this.value; updateBtnPreview();">
                    </div>
                </div>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Prévia do Botão:</label>
                <button type="button" id="btnPreview" class="px-6 py-3 rounded-lg text-white font-semibold transition-all"
                        style="background: linear-gradient(to right, <?php echo e($config['login_btn_color1']); ?>, <?php echo e($config['login_btn_color2']); ?>, <?php echo e($config['login_btn_color3']); ?>);">
                    Entrar na Plataforma →
                </button>
            </div>
        </div>

        <!-- Descrição -->
        <div class="bg-gray-800/50 rounded-xl p-6 border border-gray-700">
            <h2 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                <i data-lucide="align-left" class="w-5 h-5 text-purple-400"></i>
                Descrição
            </h2>
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-300 mb-2">Texto da Descrição</label>
                <textarea name="login_description" rows="3" 
                          class="w-full px-4 py-3 bg-gray-900 border border-gray-600 rounded-lg text-white focus:ring-2 focus:ring-purple-500 focus:border-transparent resize-none"><?php echo e($config['login_description']); ?></textarea>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Cor da Descrição</label>
                <div class="flex gap-2 max-w-xs">
                    <input type="color" name="login_description_color" value="<?php echo e($config['login_description_color']); ?>" 
                           class="w-12 h-12 rounded-lg cursor-pointer border-0 bg-transparent">
                    <input type="text" value="<?php echo e($config['login_description_color']); ?>" 
                           class="flex-1 px-4 py-3 bg-gray-900 border border-gray-600 rounded-lg text-white"
                           oninput="this.previousElementSibling.value = this.value">
                </div>
            </div>
        </div>

        <!-- Estatísticas Flutuantes -->
        <div class="bg-gray-800/50 rounded-xl p-6 border border-gray-700">
            <h2 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                <i data-lucide="bar-chart-3" class="w-5 h-5 text-purple-400"></i>
                Estatísticas Flutuantes
            </h2>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <!-- Estatística 1 -->
                <div class="bg-gray-900/50 rounded-lg p-4 border border-gray-600">
                    <h3 class="text-sm font-medium text-purple-400 mb-3">Estatística 1</h3>
                    <div class="space-y-3">
                        <div>
                            <label class="block text-xs text-gray-400 mb-1">Valor</label>
                            <input type="text" name="login_stat1_value" value="<?php echo e($config['login_stat1_value']); ?>" 
                                   class="w-full px-3 py-2 bg-gray-800 border border-gray-600 rounded-lg text-white text-sm">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-400 mb-1">Rótulo</label>
                            <input type="text" name="login_stat1_label" value="<?php echo e($config['login_stat1_label']); ?>" 
                                   class="w-full px-3 py-2 bg-gray-800 border border-gray-600 rounded-lg text-white text-sm">
                        </div>
                    </div>
                </div>
                
                <!-- Estatística 2 -->
                <div class="bg-gray-900/50 rounded-lg p-4 border border-gray-600">
                    <h3 class="text-sm font-medium text-cyan-400 mb-3">Estatística 2</h3>
                    <div class="space-y-3">
                        <div>
                            <label class="block text-xs text-gray-400 mb-1">Valor</label>
                            <input type="text" name="login_stat2_value" value="<?php echo e($config['login_stat2_value']); ?>" 
                                   class="w-full px-3 py-2 bg-gray-800 border border-gray-600 rounded-lg text-white text-sm">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-400 mb-1">Rótulo</label>
                            <input type="text" name="login_stat2_label" value="<?php echo e($config['login_stat2_label']); ?>" 
                                   class="w-full px-3 py-2 bg-gray-800 border border-gray-600 rounded-lg text-white text-sm">
                        </div>
                    </div>
                </div>
                
                <!-- Estatística 3 -->
                <div class="bg-gray-900/50 rounded-lg p-4 border border-gray-600">
                    <h3 class="text-sm font-medium text-green-400 mb-3">Estatística 3</h3>
                    <div class="space-y-3">
                        <div>
                            <label class="block text-xs text-gray-400 mb-1">Valor</label>
                            <input type="text" name="login_stat3_value" value="<?php echo e($config['login_stat3_value']); ?>" 
                                   class="w-full px-3 py-2 bg-gray-800 border border-gray-600 rounded-lg text-white text-sm">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-400 mb-1">Rótulo</label>
                            <input type="text" name="login_stat3_label" value="<?php echo e($config['login_stat3_label']); ?>" 
                                   class="w-full px-3 py-2 bg-gray-800 border border-gray-600 rounded-lg text-white text-sm">
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Cor dos Valores</label>
                    <div class="flex gap-2">
                        <input type="color" name="login_stats_value_color" value="<?php echo e($config['login_stats_value_color']); ?>" 
                               class="w-12 h-12 rounded-lg cursor-pointer border-0 bg-transparent">
                        <input type="text" value="<?php echo e($config['login_stats_value_color']); ?>" 
                               class="flex-1 px-4 py-3 bg-gray-900 border border-gray-600 rounded-lg text-white"
                               oninput="this.previousElementSibling.value = this.value">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Cor dos Rótulos</label>
                    <div class="flex gap-2">
                        <input type="color" name="login_stats_label_color" value="<?php echo e($config['login_stats_label_color']); ?>" 
                               class="w-12 h-12 rounded-lg cursor-pointer border-0 bg-transparent">
                        <input type="text" value="<?php echo e($config['login_stats_label_color']); ?>" 
                               class="flex-1 px-4 py-3 bg-gray-900 border border-gray-600 rounded-lg text-white"
                               oninput="this.previousElementSibling.value = this.value">
                    </div>
                </div>
            </div>
        </div>

        <!-- Botões de Ação -->
        <div class="flex flex-wrap gap-4 justify-between items-center pt-4">
            <button type="submit" name="restaurar" 
                    class="flex items-center gap-2 px-6 py-3 bg-gray-700 hover:bg-gray-600 text-white rounded-lg transition-colors"
                    onclick="return confirm('Tem certeza que deseja restaurar os valores padrão?');">
                <i data-lucide="refresh-cw" class="w-5 h-5"></i>
                Restaurar Padrões
            </button>
            
            <button type="submit" name="salvar" 
                    class="flex items-center gap-2 px-8 py-3 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors font-semibold">
                <i data-lucide="save" class="w-5 h-5"></i>
                Salvar Alterações
            </button>
        </div>
    </form>
</div>

<script>
// Atualizar prévia do botão em tempo real
function updateBtnPreview() {
    const color1 = document.getElementById('btn_color1').value;
    const color2 = document.getElementById('btn_color2').value;
    const color3 = document.getElementById('btn_color3').value;
    const btn = document.getElementById('btnPreview');
    btn.style.background = `linear-gradient(to right, ${color1}, ${color2}, ${color3})`;
}

// Sincronizar inputs de cor
document.querySelectorAll('input[type="color"]').forEach(colorInput => {
    colorInput.addEventListener('input', function() {
        const textInput = this.nextElementSibling;
        if (textInput && textInput.type === 'text') {
            textInput.value = this.value;
        }
        if (this.id && this.id.startsWith('btn_color')) {
            updateBtnPreview();
        }
    });
});

// Reinicializar ícones Lucide
if (typeof lucide !== 'undefined') {
    lucide.createIcons();
}
</script>
