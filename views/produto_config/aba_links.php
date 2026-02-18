<?php
// Aba Links - Link do checkout para copiar
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$domainName = $_SERVER['HTTP_HOST'];
$checkout_link = $protocol . $domainName . '/checkout?p=' . $produto['checkout_hash'];
?>

<div class="space-y-8">
    <!-- Seção: Link Principal do Checkout -->
    <div>
        <h2 class="text-xl font-semibold mb-4 text-white flex items-center gap-2">
            <i data-lucide="link" class="w-5 h-5 text-[#32e768]"></i>
            Link do Checkout
        </h2>
        
        <div class="bg-[#1a1f24] p-6 rounded-xl border border-[#2a2f34] shadow-lg">
            <!-- Card do Link Principal -->
            <div class="bg-[#0d1117] p-5 rounded-lg border border-[#30363d]">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-full bg-[#32e768]/20 flex items-center justify-center">
                        <i data-lucide="shopping-cart" class="w-5 h-5 text-[#32e768]"></i>
                    </div>
                    <div>
                        <h3 class="text-white font-semibold">Link Completo do Checkout</h3>
                        <p class="text-gray-500 text-xs">Compartilhe este link para vender seu produto</p>
                    </div>
                </div>
                
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                    <div class="flex-1 relative">
                        <input type="text" id="checkout-link-input" readonly value="<?php echo htmlspecialchars($checkout_link); ?>" 
                               class="w-full px-4 py-3 bg-[#161b22] border border-[#30363d] rounded-lg text-gray-300 text-sm font-mono focus:outline-none focus:border-[#32e768] transition-colors">
                        <div class="absolute right-3 top-1/2 -translate-y-1/2">
                            <i data-lucide="link-2" class="w-4 h-4 text-gray-500"></i>
                        </div>
                    </div>
                    <button type="button" id="copy-link-btn" 
                            class="bg-[#32e768] hover:bg-[#28d15e] text-white font-semibold py-3 px-6 rounded-lg transition-all duration-300 flex items-center justify-center gap-2 whitespace-nowrap shadow-lg shadow-[#32e768]/20 hover:shadow-[#32e768]/40">
                        <i data-lucide="copy" class="w-4 h-4"></i>
                        <span id="copy-link-text">Copiar Link</span>
                    </button>
                </div>
                
                <div class="mt-4 flex items-center gap-2 text-xs text-gray-500">
                    <i data-lucide="info" class="w-3.5 h-3.5"></i>
                    <span>Use este link para compartilhar o checkout do produto em suas campanhas e redes sociais.</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Links das Ofertas -->
    <?php
    // Buscar ofertas ativas do produto (com tratamento de erro caso tabela não exista)
    $ofertas = [];
    try {
        $stmt_ofertas = $pdo->prepare("SELECT * FROM produto_ofertas WHERE produto_id = ? ORDER BY created_at DESC");
        $stmt_ofertas->execute([$id_produto]);
        $ofertas = $stmt_ofertas->fetchAll(PDO::FETCH_ASSOC);
        // Filtra apenas ofertas ativas (se a coluna existir)
        $ofertas = array_filter($ofertas, function($o) {
            return !isset($o['is_active']) || $o['is_active'] == 1;
        });
    } catch (PDOException $e) {
        // Tabela pode não existir - ignora
        $ofertas = [];
    }
    ?>
    
    <?php if (!empty($ofertas)): ?>
    <!-- Seção: Links das Ofertas -->
    <div>
        <h2 class="text-xl font-semibold mb-4 text-white flex items-center gap-2">
            <i data-lucide="tag" class="w-5 h-5 text-[#32e768]"></i>
            Links das Ofertas
            <span class="ml-2 bg-[#32e768]/20 text-[#32e768] text-xs font-bold px-2 py-1 rounded-full"><?php echo count($ofertas); ?> oferta<?php echo count($ofertas) > 1 ? 's' : ''; ?></span>
        </h2>
        
        <div class="bg-[#1a1f24] p-6 rounded-xl border border-[#2a2f34] shadow-lg space-y-4">
            <?php foreach ($ofertas as $index => $oferta): 
                $oferta_link = $protocol . $domainName . '/checkout?p=' . $oferta['checkout_hash'];
            ?>
                <div class="bg-[#0d1117] p-5 rounded-lg border border-[#30363d] hover:border-[#32e768]/50 transition-colors">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-purple-500/20 flex items-center justify-center">
                                <i data-lucide="percent" class="w-5 h-5 text-purple-400"></i>
                            </div>
                            <div>
                                <div class="flex items-center gap-2 flex-wrap">
                                    <h3 class="font-semibold text-white"><?php echo htmlspecialchars($oferta['nome']); ?></h3>
                                    <span class="bg-green-500/20 text-green-400 text-xs font-bold px-2 py-0.5 rounded-full border border-green-500/30">
                                        <i data-lucide="check-circle" class="w-3 h-3 inline mr-1"></i>Ativa
                                    </span>
                                </div>
                                <p class="text-sm text-gray-500 mt-0.5">
                                    Preço: <span class="text-[#32e768] font-bold">R$ <?php echo number_format($oferta['preco'], 2, ',', '.'); ?></span>
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                        <div class="flex-1 relative">
                            <input type="text" readonly value="<?php echo htmlspecialchars($oferta_link); ?>" 
                                   class="w-full px-4 py-3 bg-[#161b22] border border-[#30363d] rounded-lg text-gray-300 text-sm font-mono focus:outline-none focus:border-[#32e768] transition-colors pr-10" 
                                   id="oferta-link-links-<?php echo $oferta['id']; ?>">
                            <div class="absolute right-3 top-1/2 -translate-y-1/2">
                                <i data-lucide="link-2" class="w-4 h-4 text-gray-500"></i>
                            </div>
                        </div>
                        <button type="button" class="copy-oferta-link-links bg-[#32e768] hover:bg-[#28d15e] text-white font-semibold py-3 px-6 rounded-lg transition-all duration-300 flex items-center justify-center gap-2 whitespace-nowrap shadow-lg shadow-[#32e768]/20 hover:shadow-[#32e768]/40" 
                                data-link-id="oferta-link-links-<?php echo $oferta['id']; ?>">
                            <i data-lucide="copy" class="w-4 h-4"></i>
                            <span class="copy-oferta-text-<?php echo $oferta['id']; ?>">Copiar Link</span>
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php else: ?>
    <!-- Seção: Sem Ofertas -->
    <div>
        <h2 class="text-xl font-semibold mb-4 text-white flex items-center gap-2">
            <i data-lucide="tag" class="w-5 h-5 text-[#32e768]"></i>
            Links das Ofertas
        </h2>
        
        <div class="bg-[#1a1f24] p-8 rounded-xl border border-[#2a2f34] shadow-lg">
            <div class="text-center">
                <div class="w-16 h-16 rounded-full bg-gray-800 flex items-center justify-center mx-auto mb-4">
                    <i data-lucide="tag" class="w-8 h-8 text-gray-600"></i>
                </div>
                <h3 class="text-white font-semibold mb-2">Nenhuma oferta criada</h3>
                <p class="text-gray-500 text-sm mb-4">Crie ofertas com preços diferenciados na aba "Geral" para ter links exclusivos.</p>
                <a href="/index?pagina=produto_config&id=<?php echo $id_produto; ?>&aba=geral" 
                   class="inline-flex items-center gap-2 text-[#32e768] hover:text-[#28d15e] font-semibold text-sm transition-colors">
                    <i data-lucide="plus-circle" class="w-4 h-4"></i>
                    Criar primeira oferta
                </a>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const copyBtn = document.getElementById('copy-link-btn');
    const copyText = document.getElementById('copy-link-text');
    const linkInput = document.getElementById('checkout-link-input');

    // Copiar link principal
    copyBtn?.addEventListener('click', () => {
        linkInput.select();
        document.execCommand('copy');
        
        const originalText = copyText.textContent;
        copyText.textContent = 'Copiado!';
        copyBtn.classList.add('bg-green-600');
        
        setTimeout(() => {
            copyText.textContent = originalText;
            copyBtn.classList.remove('bg-green-600');
        }, 2000);
    });

    // Copiar links das ofertas
    document.querySelectorAll('.copy-oferta-link-links').forEach(btn => {
        btn.addEventListener('click', function() {
            const linkId = this.getAttribute('data-link-id');
            const linkInput = document.getElementById(linkId);
            if (linkInput) {
                linkInput.select();
                document.execCommand('copy');
                
                const ofertaId = linkId.replace('oferta-link-links-', '');
                const copyText = document.querySelector('.copy-oferta-text-' + ofertaId);
                if (copyText) {
                    const originalText = copyText.textContent;
                    copyText.textContent = 'Copiado!';
                    this.classList.add('bg-green-600');
                    
                    setTimeout(() => {
                        copyText.textContent = originalText;
                        this.classList.remove('bg-green-600');
                    }, 2000);
                }
            }
        });
    });
});
</script>

