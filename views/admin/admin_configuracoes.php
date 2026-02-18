<?php
// Este arquivo é incluído dentro de admin.php

// Incluir helper de segurança para funções CSRF
require_once __DIR__ . '/../../helpers/security_helper.php';

// Gerar token CSRF para uso em requisições JavaScript
$csrf_token_js = generate_csrf_token();
?>

<meta name="csrf-token" content="<?php echo htmlspecialchars($csrf_token_js); ?>">
<script>
    // Variável global para token CSRF
    window.csrfToken = '<?php echo htmlspecialchars($csrf_token_js); ?>';
</script>

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-3xl font-bold text-white">Configurações do Sistema</h1>
        <p class="text-gray-400 mt-1">Personalize a aparência e identidade visual da plataforma.</p>
    </div>
    <a href="/admin?pagina=admin_dashboard" class="bg-dark-elevated text-gray-300 font-bold py-2 px-4 rounded-lg hover:bg-dark-card transition duration-300 flex items-center space-x-2 border border-dark-border">
        <i data-lucide="arrow-left" class="w-5 h-5"></i>
        <span>Voltar ao Dashboard</span>
    </a>
</div>

<div id="status-message" class="hidden px-4 py-3 rounded relative mb-4" role="alert"></div>

<!-- Seção: Cor Primária -->
<div class="bg-dark-card p-8 rounded-lg shadow-md mb-6" style="border-color: var(--accent-primary);">
    <h2 class="text-2xl font-semibold mb-6 text-white flex items-center gap-2">
        <i data-lucide="palette" class="w-6 h-6" style="color: var(--accent-primary);"></i>
        <span>Cor Primária</span>
    </h2>
    <p class="text-gray-400 mb-6">Escolha a cor primária que será aplicada em todo o sistema, incluindo botões, links e elementos de destaque.</p>
    
    <div class="flex flex-col md:flex-row items-start md:items-center gap-6">
        <div class="flex-1">
            <label for="cor_primaria" class="block text-gray-300 text-sm font-semibold mb-2">Selecione a Cor</label>
            <div class="flex items-center gap-4">
                <input type="color" id="cor_primaria" name="cor_primaria" 
                       class="w-20 h-20 rounded-lg cursor-pointer border-2 border-dark-border bg-transparent">
                <input type="text" id="cor_primaria_hex" 
                       class="px-4 py-3 bg-dark-elevated border border-dark-border rounded-lg text-white font-mono text-sm focus:outline-none focus:ring-2 transition duration-300"
                       placeholder="#32e768" maxlength="7">
            </div>
            <p class="text-xs text-gray-400 mt-2">A cor será aplicada em todos os elementos verdes do sistema.</p>
        </div>
        <div class="flex flex-col gap-2">
            <div class="w-32 h-32 rounded-lg border-2 border-dark-border flex items-center justify-center" id="color-preview" style="background-color: var(--accent-primary);">
                <span class="text-white font-bold text-lg">Preview</span>
            </div>
            <button type="button" id="save-color-btn" class="text-white font-bold py-2 px-4 rounded-lg transition duration-300" style="background-color: var(--accent-primary);" onmouseover="this.style.backgroundColor='var(--accent-primary-hover)'" onmouseout="this.style.backgroundColor='var(--accent-primary)'">
                Salvar Cor
            </button>
        </div>
    </div>
</div>

<!-- Seção: Tema (modo claro/escuro) -->
<div class="bg-dark-card p-8 rounded-lg shadow-md mb-6 border border-dark-border">
    <h2 class="text-2xl font-semibold mb-6 text-white flex items-center gap-2">
        <i data-lucide="sun" class="w-6 h-6" style="color: var(--accent-primary);"></i>
        <span>Tema (modo claro/escuro)</span>
    </h2>
    <p class="text-gray-400 mb-6">Defina o tema padrão do sistema. Os usuários podem alternar a qualquer momento pelo botão no canto superior direito.</p>
    <div class="flex flex-col md:flex-row items-start md:items-center gap-6">
        <div class="flex-1">
            <label for="tema_padrao" class="block text-gray-300 text-sm font-semibold mb-2">Tema padrão</label>
            <select id="tema_padrao" name="tema_padrao" class="w-full md:w-48 px-4 py-3 bg-dark-elevated border border-dark-border rounded-lg text-white focus:outline-none focus:ring-2 transition duration-300" style="--tw-ring-color: var(--accent-primary);">
                <option value="dark">Escuro</option>
                <option value="light">Claro</option>
            </select>
        </div>
        <button type="button" id="save-tema-padrao-btn" class="text-white font-bold py-2 px-4 rounded-lg transition duration-300" style="background-color: var(--accent-primary);" onmouseover="this.style.backgroundColor='var(--accent-primary-hover)'" onmouseout="this.style.backgroundColor='var(--accent-primary)'">
            Salvar Tema
        </button>
    </div>
</div>

<!-- Seção: Logo -->
<div class="bg-dark-card p-8 rounded-lg shadow-md mb-6 border border-dark-border">
    <h2 class="text-2xl font-semibold mb-6 text-white flex items-center gap-2">
        <i data-lucide="image" class="w-6 h-6" style="color: var(--accent-primary);"></i>
        <span>Logo do Sistema</span>
    </h2>
    <p class="text-gray-400 mb-6">Faça upload da logo que será exibida no sidebar e nas telas de login.</p>
    
    <div class="flex flex-col md:flex-row items-start md:items-center gap-6">
        <div class="flex-1">
            <label for="logo_file" class="block text-gray-300 text-sm font-semibold mb-2">Upload da Logo</label>
            <div class="relative">
                <input type="file" id="logo_file" name="logo_file" accept="image/jpeg,image/png,image/webp,image/svg+xml" 
                       class="hidden">
                <label for="logo_file" class="cursor-pointer inline-flex items-center justify-center px-6 py-3 bg-dark-elevated border border-dark-border rounded-lg text-white hover:bg-dark-card transition">
                    <i data-lucide="upload" class="w-5 h-5 mr-2"></i>
                    <span>Selecionar Arquivo</span>
                </label>
                <span id="logo_filename" class="ml-4 text-gray-400 text-sm"></span>
            </div>
            <p class="text-xs text-gray-400 mt-2">Formatos aceitos: JPG, PNG, WEBP, SVG. Tamanho máximo: 2MB</p>
        </div>
        <div class="flex flex-col gap-2">
            <div class="w-32 h-32 rounded-lg border-2 border-dark-border bg-dark-elevated flex items-center justify-center overflow-hidden" id="logo-preview">
                <img id="logo-preview-img" src="" alt="Logo Preview" class="max-w-full max-h-full object-contain hidden">
                <i data-lucide="image" class="w-12 h-12 text-gray-500" id="logo-placeholder"></i>
            </div>
            <button type="button" id="upload-logo-btn" class="text-white font-bold py-2 px-4 rounded-lg transition duration-300 disabled:opacity-50 disabled:cursor-not-allowed" style="background-color: var(--accent-primary);" onmouseover="if(!this.disabled) this.style.backgroundColor='var(--accent-primary-hover)'" onmouseout="if(!this.disabled) this.style.backgroundColor='var(--accent-primary)'" disabled>
                Enviar Logo
            </button>
        </div>
    </div>
</div>

<!-- Seção: Nome da Plataforma -->
<div class="bg-dark-card p-8 rounded-lg shadow-md mb-6 border border-dark-border">
    <h2 class="text-2xl font-semibold mb-6 text-white flex items-center gap-2">
        <i data-lucide="type" class="w-6 h-6" style="color: var(--accent-primary);"></i>
        <span>Nome da Plataforma</span>
    </h2>
    <p class="text-gray-400 mb-6">Defina o nome da plataforma que será exibido no checkout e em outras áreas do sistema.</p>
    
    <div class="flex flex-col md:flex-row items-start md:items-center gap-6">
        <div class="flex-1">
            <label for="nome_plataforma" class="block text-gray-300 text-sm font-semibold mb-2">Nome da Plataforma</label>
            <input type="text" id="nome_plataforma" name="nome_plataforma" 
                   class="w-full px-4 py-3 bg-dark-elevated border border-dark-border rounded-lg text-white focus:outline-none focus:ring-2 transition duration-300" style="--tw-ring-color: var(--accent-primary);"
                   placeholder="LuraPay" maxlength="100">
            <p class="text-xs text-gray-400 mt-2">Este nome será usado no checkout e em mensagens do sistema.</p>
        </div>
        <div class="flex flex-col gap-2">
            <div class="w-32 h-32 rounded-lg border-2 border-dark-border bg-dark-elevated flex items-center justify-center">
                <span id="nome-plataforma-preview" class="text-white font-bold text-lg text-center px-2"></span>
            </div>
            <button type="button" id="save-nome-plataforma-btn" class="text-white font-bold py-2 px-4 rounded-lg transition duration-300" style="background-color: var(--accent-primary);" onmouseover="this.style.backgroundColor='var(--accent-primary-hover)'" onmouseout="this.style.backgroundColor='var(--accent-primary)'">
                Salvar Nome
            </button>
        </div>
    </div>
</div>

<!-- Seção: Upload de vídeo na plataforma -->
<div class="bg-dark-card p-8 rounded-lg shadow-md mb-6 border border-dark-border">
    <h2 class="text-2xl font-semibold mb-6 text-white flex items-center gap-2">
        <i data-lucide="video" class="w-6 h-6" style="color: var(--accent-primary);"></i>
        <span>Upload de vídeo na plataforma</span>
    </h2>
    <p class="text-gray-400 mb-6">Se ativado, os infoprodutores poderão fazer upload de vídeos em MP4 diretamente na plataforma ao adicionar/editar aulas (além do link do YouTube). Se desativado, apenas o link do YouTube será exibido.</p>
    
    <div class="flex flex-col md:flex-row items-start md:items-center gap-6">
        <div class="flex-1">
            <label class="flex items-center gap-3 cursor-pointer">
                <input type="checkbox" id="allow_video_upload" name="allow_video_upload" value="1" 
                       class="w-5 h-5 rounded border-dark-border bg-dark-elevated text-[var(--accent-primary)] focus:ring-[var(--accent-primary)]">
                <span class="text-gray-300 font-medium">Permitir upload de vídeo direto na plataforma (MP4)</span>
            </label>
            <p class="text-xs text-gray-400 mt-2">Quando desativado, nas aulas dos infoprodutores aparece apenas a opção de URL do YouTube.</p>
        </div>
        <button type="button" id="save-allow-video-upload-btn" class="text-white font-bold py-2 px-4 rounded-lg transition duration-300" style="background-color: var(--accent-primary);" onmouseover="this.style.backgroundColor='var(--accent-primary-hover)'" onmouseout="this.style.backgroundColor='var(--accent-primary)'">
            Salvar
        </button>
    </div>
</div>

<!-- Seção: Logo do Checkout -->
<div class="bg-dark-card p-8 rounded-lg shadow-md mb-6 border border-dark-border">
    <h2 class="text-2xl font-semibold mb-6 text-white flex items-center gap-2">
        <i data-lucide="shopping-cart" class="w-6 h-6" style="color: var(--accent-primary);"></i>
        <span>Logo do Checkout</span>
    </h2>
    <p class="text-gray-400 mb-6">Faça upload da logo que será exibida exclusivamente no checkout. Se não configurada, será usada a logo padrão do sistema.</p>
    
    <div class="flex flex-col md:flex-row items-start md:items-center gap-6">
        <div class="flex-1">
            <label for="logo_checkout_file" class="block text-gray-300 text-sm font-semibold mb-2">Upload da Logo do Checkout</label>
            <div class="relative">
                <input type="file" id="logo_checkout_file" name="logo_checkout_file" accept="image/jpeg,image/png,image/webp,image/svg+xml" 
                       class="hidden">
                <label for="logo_checkout_file" class="cursor-pointer inline-flex items-center justify-center px-6 py-3 bg-dark-elevated border border-dark-border rounded-lg text-white hover:bg-dark-card transition">
                    <i data-lucide="upload" class="w-5 h-5 mr-2"></i>
                    <span>Selecionar Arquivo</span>
                </label>
                <span id="logo_checkout_filename" class="ml-4 text-gray-400 text-sm"></span>
            </div>
            <p class="text-xs text-gray-400 mt-2">Formatos aceitos: JPG, PNG, WEBP, SVG. Tamanho máximo: 2MB</p>
        </div>
        <div class="flex flex-col gap-2">
            <div class="w-32 h-32 rounded-lg border-2 border-dark-border bg-dark-elevated flex items-center justify-center overflow-hidden" id="logo-checkout-preview">
                <img id="logo-checkout-preview-img" src="" alt="Logo Checkout Preview" class="max-w-full max-h-full object-contain hidden">
                <i data-lucide="image" class="w-12 h-12 text-gray-500" id="logo-checkout-placeholder"></i>
            </div>
            <button type="button" id="upload-logo-checkout-btn" class="text-white font-bold py-2 px-4 rounded-lg transition duration-300 disabled:opacity-50 disabled:cursor-not-allowed" style="background-color: var(--accent-primary);" onmouseover="if(!this.disabled) this.style.backgroundColor='var(--accent-primary-hover)'" onmouseout="if(!this.disabled) this.style.backgroundColor='var(--accent-primary)'" disabled>
                Enviar Logo
            </button>
        </div>
    </div>
</div>

<!-- Seção: Fundo do Login (Imagem ou Vídeo via URL) -->
<div class="bg-dark-card p-8 rounded-lg shadow-md mb-6 border border-dark-border">
    <h2 class="text-2xl font-semibold mb-6 text-white flex items-center gap-2">
        <i data-lucide="film" class="w-6 h-6" style="color: var(--accent-primary);"></i>
        <span>Fundo do Login (Imagem ou Vídeo)</span>
    </h2>
    <p class="text-gray-400 mb-6">Insira a URL de uma imagem ou vídeo que será exibido como fundo na tela de login. Vídeos serão reproduzidos em loop automaticamente.</p>
    
    <div class="flex flex-col md:flex-row items-start gap-6">
        <div class="flex-1">
            <label for="login_bg_url" class="block text-gray-300 text-sm font-semibold mb-2">URL da Imagem ou Vídeo de Fundo</label>
            <input type="url" id="login_bg_url" name="login_bg_url" 
                   class="w-full px-4 py-3 bg-dark-elevated border border-dark-border rounded-lg text-white focus:outline-none focus:ring-2 transition duration-300" style="--tw-ring-color: var(--accent-primary);"
                   placeholder="https://exemplo.com/video.mp4">
            <p class="text-xs text-gray-400 mt-2">Suporta imagens (.jpg, .png, .webp) ou vídeos (.mp4, .webm) em loop</p>
        </div>
        <div class="flex flex-col gap-2">
            <div class="w-48 h-32 rounded-lg border-2 border-dark-border bg-dark-elevated flex items-center justify-center overflow-hidden" id="login-bg-url-preview">
                <img id="login-bg-url-preview-img" src="" alt="Preview" class="w-full h-full object-cover hidden">
                <video id="login-bg-url-preview-video" src="" class="w-full h-full object-cover hidden" muted loop autoplay playsinline></video>
                <i data-lucide="film" class="w-12 h-12 text-gray-500" id="login-bg-url-placeholder"></i>
            </div>
            <button type="button" id="save-login-bg-url-btn" class="text-white font-bold py-2 px-4 rounded-lg transition duration-300" style="background-color: var(--accent-primary);" onmouseover="this.style.backgroundColor='var(--accent-primary-hover)'" onmouseout="this.style.backgroundColor='var(--accent-primary)'">
                Salvar URL
            </button>
        </div>
    </div>
</div>

<!-- Seção: Favicon -->
<div class="bg-dark-card p-8 rounded-lg shadow-md mb-6 border border-dark-border">
    <h2 class="text-2xl font-semibold mb-6 text-white flex items-center gap-2">
        <i data-lucide="image" class="w-6 h-6" style="color: var(--accent-primary);"></i>
        <span>Favicon</span>
    </h2>
    <p class="text-gray-400 mb-6">Faça upload do favicon que será exibido na aba do navegador em todas as páginas do sistema.</p>
    
    <div class="flex flex-col md:flex-row items-start md:items-center gap-6">
        <div class="flex-1">
            <label for="favicon_file" class="block text-gray-300 text-sm font-semibold mb-2">Upload do Favicon</label>
            <div class="relative">
                <input type="file" id="favicon_file" name="favicon_file" accept="image/x-icon,image/vnd.microsoft.icon,image/png,image/svg+xml" 
                       class="hidden">
                <label for="favicon_file" class="cursor-pointer inline-flex items-center justify-center px-6 py-3 bg-dark-elevated border border-dark-border rounded-lg text-white hover:bg-dark-card transition">
                    <i data-lucide="upload" class="w-5 h-5 mr-2"></i>
                    <span>Selecionar Arquivo</span>
                </label>
                <span id="favicon_filename" class="ml-4 text-gray-400 text-sm"></span>
            </div>
            <p class="text-xs text-gray-400 mt-2">Formatos aceitos: ICO, PNG, SVG. Tamanho máximo: 2MB</p>
        </div>
        <div class="flex flex-col gap-2">
            <div class="w-32 h-32 rounded-lg border-2 border-dark-border bg-dark-elevated flex items-center justify-center overflow-hidden" id="favicon-preview">
                <img id="favicon-preview-img" src="" alt="Favicon Preview" class="max-w-full max-h-full object-contain hidden">
                <i data-lucide="image" class="w-12 h-12 text-gray-500" id="favicon-placeholder"></i>
            </div>
            <button type="button" id="upload-favicon-btn" class="text-white font-bold py-2 px-4 rounded-lg transition duration-300 disabled:opacity-50 disabled:cursor-not-allowed" style="background-color: var(--accent-primary);" onmouseover="if(!this.disabled) this.style.backgroundColor='var(--accent-primary-hover)'" onmouseout="if(!this.disabled) this.style.backgroundColor='var(--accent-primary)'" disabled>
                Enviar Favicon
            </button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    lucide.createIcons();
    
    const statusMessage = document.getElementById('status-message');
    const corPrimariaInput = document.getElementById('cor_primaria');
    const corPrimariaHex = document.getElementById('cor_primaria_hex');
    const colorPreview = document.getElementById('color-preview');
    const saveColorBtn = document.getElementById('save-color-btn');
    
    const logoFile = document.getElementById('logo_file');
    const logoFilename = document.getElementById('logo_filename');
    const logoPreview = document.getElementById('logo-preview-img');
    const logoPlaceholder = document.getElementById('logo-placeholder');
    const uploadLogoBtn = document.getElementById('upload-logo-btn');
    
    
    const nomePlataformaInput = document.getElementById('nome_plataforma');
    const nomePlataformaPreview = document.getElementById('nome-plataforma-preview');
    const saveNomePlataformaBtn = document.getElementById('save-nome-plataforma-btn');
    
    const logoCheckoutFile = document.getElementById('logo_checkout_file');
    const logoCheckoutFilename = document.getElementById('logo_checkout_filename');
    const logoCheckoutPreview = document.getElementById('logo-checkout-preview-img');
    const logoCheckoutPlaceholder = document.getElementById('logo-checkout-placeholder');
    const uploadLogoCheckoutBtn = document.getElementById('upload-logo-checkout-btn');
    
    const faviconFile = document.getElementById('favicon_file');
    const faviconFilename = document.getElementById('favicon_filename');
    const faviconPreview = document.getElementById('favicon-preview-img');
    const faviconPlaceholder = document.getElementById('favicon-placeholder');
    const uploadFaviconBtn = document.getElementById('upload-favicon-btn');
    
    // Fundo do Login (URL)
    const loginBgUrlInput = document.getElementById('login_bg_url');
    const loginBgUrlPreviewImg = document.getElementById('login-bg-url-preview-img');
    const loginBgUrlPreviewVideo = document.getElementById('login-bg-url-preview-video');
    const loginBgUrlPlaceholder = document.getElementById('login-bg-url-placeholder');
    const saveLoginBgUrlBtn = document.getElementById('save-login-bg-url-btn');
    
    // Carregar configurações atuais
    async function loadSettings() {
        try {
            const response = await fetch('/api/admin_api.php?action=get_system_settings');
            const result = await response.json();
            
            if (result.success && result.data) {
                // Cor primária
                if (result.data.cor_primaria) {
                    const cor = result.data.cor_primaria;
                    corPrimariaInput.value = cor;
                    corPrimariaHex.value = cor;
                    colorPreview.style.backgroundColor = cor;
                    // Atualizar focus ring do input hex
                    corPrimariaHex.style.setProperty('--tw-ring-color', cor);
                }
                
                // Logo
                if (result.data.logo_url) {
                    logoPreview.src = result.data.logo_url;
                    logoPreview.classList.remove('hidden');
                    logoPlaceholder.classList.add('hidden');
                } else {
                    logoPreview.classList.add('hidden');
                    logoPlaceholder.classList.remove('hidden');
                }
                
                // Nome da Plataforma
                if (result.data.nome_plataforma) {
                    nomePlataformaInput.value = result.data.nome_plataforma;
                    nomePlataformaPreview.textContent = result.data.nome_plataforma;
                }
                
                // Logo do Checkout
                if (result.data.logo_checkout_url) {
                    logoCheckoutPreview.src = result.data.logo_checkout_url;
                    logoCheckoutPreview.classList.remove('hidden');
                    logoCheckoutPlaceholder.classList.add('hidden');
                } else {
                    logoCheckoutPreview.classList.add('hidden');
                    logoCheckoutPlaceholder.classList.remove('hidden');
                }
                
                // Favicon
                if (result.data.favicon_url) {
                    faviconPreview.src = result.data.favicon_url;
                    faviconPreview.classList.remove('hidden');
                    faviconPlaceholder.classList.add('hidden');
                } else {
                    faviconPreview.classList.add('hidden');
                    faviconPlaceholder.classList.remove('hidden');
                }
                
                // URL de Fundo do Login
                if (result.data.login_bg_url) {
                    loginBgUrlInput.value = result.data.login_bg_url;
                    updateLoginBgPreview(result.data.login_bg_url);
                }
                
                // Upload de vídeo na plataforma
                const allowVideoUploadCheck = document.getElementById('allow_video_upload');
                if (allowVideoUploadCheck) {
                    allowVideoUploadCheck.checked = (result.data.allow_video_upload === '1' || result.data.allow_video_upload === true);
                }
                
                // Tema padrão (claro/escuro)
                const temaPadraoSelect = document.getElementById('tema_padrao');
                if (temaPadraoSelect && result.data.tema_padrao) {
                    temaPadraoSelect.value = result.data.tema_padrao === 'light' ? 'light' : 'dark';
                }
            }
        } catch (error) {
            console.error('Erro ao carregar configurações:', error);
        }
    }
    
    // Função para atualizar preview do fundo do login
    function updateLoginBgPreview(url) {
        if (!url) {
            loginBgUrlPreviewImg.classList.add('hidden');
            loginBgUrlPreviewVideo.classList.add('hidden');
            loginBgUrlPlaceholder.classList.remove('hidden');
            return;
        }
        
        const isVideo = /\.(mp4|webm)$/i.test(url);
        
        if (isVideo) {
            loginBgUrlPreviewVideo.src = url;
            loginBgUrlPreviewVideo.classList.remove('hidden');
            loginBgUrlPreviewImg.classList.add('hidden');
            loginBgUrlPlaceholder.classList.add('hidden');
        } else {
            loginBgUrlPreviewImg.src = url;
            loginBgUrlPreviewImg.classList.remove('hidden');
            loginBgUrlPreviewVideo.classList.add('hidden');
            loginBgUrlPlaceholder.classList.add('hidden');
        }
    }
    
    function showMessage(message, type = 'success') {
        // Remove hidden primeiro
        statusMessage.classList.remove('hidden');
        // Reset classes e adiciona novas
        statusMessage.className = 'px-4 py-3 rounded relative mb-4';
        if (type === 'success') {
            statusMessage.classList.add('bg-green-900/20', 'border', 'border-green-500/30', 'text-green-300');
        } else {
            statusMessage.classList.add('bg-red-900/20', 'border', 'border-red-500/30', 'text-red-300');
        }
        statusMessage.textContent = message;
        statusMessage.scrollIntoView({ behavior: 'smooth', block: 'start' });
        setTimeout(() => {
            statusMessage.classList.add('hidden');
        }, 5000);
    }
    
    // Sincronizar color picker e input hex
    corPrimariaInput.addEventListener('input', function() {
        const cor = this.value;
        corPrimariaHex.value = cor;
        colorPreview.style.backgroundColor = cor;
        // Atualizar focus ring do input hex
        corPrimariaHex.style.setProperty('--tw-ring-color', cor);
    });
    
    corPrimariaHex.addEventListener('input', function() {
        let hex = this.value.trim();
        // Garante que tem o #
        if (hex && hex[0] !== '#') {
            hex = '#' + hex;
            this.value = hex;
        }
        if (/^#[0-9A-Fa-f]{6}$/.test(hex)) {
            corPrimariaInput.value = hex;
            colorPreview.style.backgroundColor = hex;
            // Atualizar focus ring
            this.style.setProperty('--tw-ring-color', hex);
        }
    });
    
    // Salvar cor primária
    saveColorBtn.addEventListener('click', async function() {
        let cor = corPrimariaHex.value.trim();
        // Garante que tem o #
        if (cor && cor[0] !== '#') {
            cor = '#' + cor;
        }
        if (!/^#[0-9A-Fa-f]{6}$/.test(cor)) {
            showMessage('Cor inválida. Use o formato hexadecimal (#RRGGBB)', 'error');
            return;
        }
        
        saveColorBtn.disabled = true;
        saveColorBtn.textContent = 'Salvando...';
        
        try {
            const response = await fetch('/api/admin_api.php?action=save_system_settings', {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': window.csrfToken || ''
                },
                body: JSON.stringify({ 
                    cor_primaria: cor,
                    csrf_token: window.csrfToken || ''
                })
            });
            const result = await response.json();
            
            if (result.success) {
                showMessage('Cor primária salva com sucesso! A página será recarregada para aplicar as mudanças.', 'success');
                setTimeout(() => {
                    window.location.reload();
                }, 2000);
            } else {
                showMessage('Erro ao salvar cor: ' + (result.error || 'Erro desconhecido'), 'error');
                saveColorBtn.disabled = false;
                saveColorBtn.textContent = 'Salvar Cor';
            }
        } catch (error) {
            console.error('Erro:', error);
            showMessage('Erro de comunicação com o servidor: ' + error.message, 'error');
            saveColorBtn.disabled = false;
            saveColorBtn.textContent = 'Salvar Cor';
        }
    });
    
    // Preview logo
    logoFile.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            logoFilename.textContent = this.files[0].name;
            uploadLogoBtn.disabled = false;
            
            const reader = new FileReader();
            reader.onload = function(e) {
                logoPreview.src = e.target.result;
                logoPreview.classList.remove('hidden');
                logoPlaceholder.classList.add('hidden');
            };
            reader.readAsDataURL(this.files[0]);
        }
    });
    
    // Upload logo
    uploadLogoBtn.addEventListener('click', async function() {
        if (!logoFile.files || !logoFile.files[0]) {
            showMessage('Selecione um arquivo primeiro', 'error');
            return;
        }
        
        const formData = new FormData();
        formData.append('logo', logoFile.files[0]);
        formData.append('csrf_token', window.csrfToken || '');
        
        uploadLogoBtn.disabled = true;
        uploadLogoBtn.textContent = 'Enviando...';
        
        try {
            const response = await fetch('/api/admin_api.php?action=upload_logo', {
                method: 'POST',
                headers: {
                    'X-CSRF-Token': window.csrfToken || ''
                },
                body: formData
            });
            const result = await response.json();
            
            if (result.success) {
                showMessage('Logo enviada com sucesso!', 'success');
                logoFile.value = '';
                logoFilename.textContent = '';
                uploadLogoBtn.disabled = true;
            } else {
                showMessage('Erro ao enviar logo: ' + (result.error || 'Erro desconhecido'), 'error');
                uploadLogoBtn.disabled = false;
            }
        } catch (error) {
            console.error('Erro:', error);
            showMessage('Erro de comunicação com o servidor', 'error');
            uploadLogoBtn.disabled = false;
        } finally {
            uploadLogoBtn.textContent = 'Enviar Logo';
        }
    });
    
        // Nome da Plataforma - Preview em tempo real
        nomePlataformaInput.addEventListener('input', function() {
            nomePlataformaPreview.textContent = this.value || 'CheckoutPRO';
        });

        // Salvar Nome da Plataforma
        saveNomePlataformaBtn.addEventListener('click', async function() {
            const nome = nomePlataformaInput.value.trim();
            if (!nome) {
                showMessage('Por favor, insira um nome para a plataforma.', 'error');
                return;
            }

            saveNomePlataformaBtn.disabled = true;
            saveNomePlataformaBtn.textContent = 'Salvando...';

            try {
                console.log('Enviando nome_plataforma:', nome);
                const response = await fetch('/api/admin_api.php?action=save_system_settings', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': window.csrfToken || ''
                    },
                    body: JSON.stringify({ 
                        nome_plataforma: nome,
                        csrf_token: window.csrfToken || ''
                    })
                });

                const result = await response.json();
                console.log('Resposta do servidor:', result);
                
                if (result.success) {
                    showMessage('Nome da plataforma salvo com sucesso!', 'success');
                    nomePlataformaPreview.textContent = nome;
                } else {
                    showMessage(result.error || 'Erro ao salvar nome da plataforma.', 'error');
                    console.error('Erro ao salvar:', result);
                }
            } catch (error) {
                console.error('Erro na requisição:', error);
                showMessage('Erro ao salvar nome da plataforma: ' + error.message, 'error');
            } finally {
                saveNomePlataformaBtn.disabled = false;
                saveNomePlataformaBtn.textContent = 'Salvar Nome';
            }
        });

        // Salvar opção de upload de vídeo na plataforma
        const saveAllowVideoUploadBtn = document.getElementById('save-allow-video-upload-btn');
        const allowVideoUploadCheck = document.getElementById('allow_video_upload');
        if (saveAllowVideoUploadBtn && allowVideoUploadCheck) {
            saveAllowVideoUploadBtn.addEventListener('click', async function() {
                saveAllowVideoUploadBtn.disabled = true;
                saveAllowVideoUploadBtn.textContent = 'Salvando...';
                try {
                    const response = await fetch('/api/admin_api.php?action=save_system_settings', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': window.csrfToken || '' },
                        body: JSON.stringify({
                            allow_video_upload: allowVideoUploadCheck.checked ? '1' : '0',
                            csrf_token: window.csrfToken || ''
                        })
                    });
                    const result = await response.json();
                    if (result.success) {
                        showMessage('Configuração de upload de vídeo salva com sucesso!', 'success');
                    } else {
                        showMessage(result.error || 'Erro ao salvar.', 'error');
                    }
                } catch (error) {
                    showMessage('Erro de comunicação: ' + error.message, 'error');
                } finally {
                    saveAllowVideoUploadBtn.disabled = false;
                    saveAllowVideoUploadBtn.textContent = 'Salvar';
                }
            });
        }

        // Upload Logo do Checkout
        logoCheckoutFile.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                logoCheckoutFilename.textContent = this.files[0].name;
                uploadLogoCheckoutBtn.disabled = false;
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    logoCheckoutPreview.src = e.target.result;
                    logoCheckoutPreview.classList.remove('hidden');
                    logoCheckoutPlaceholder.classList.add('hidden');
                };
                reader.readAsDataURL(this.files[0]);
            }
        });

        // Upload logo do checkout
        uploadLogoCheckoutBtn.addEventListener('click', async function() {
            if (!logoCheckoutFile.files || !logoCheckoutFile.files[0]) {
                showMessage('Selecione um arquivo primeiro', 'error');
                return;
            }
            
            const formData = new FormData();
            formData.append('logo_checkout', logoCheckoutFile.files[0]);
            formData.append('csrf_token', window.csrfToken || '');
            
            uploadLogoCheckoutBtn.disabled = true;
            uploadLogoCheckoutBtn.textContent = 'Enviando...';
            
            try {
                const response = await fetch('/api/admin_api.php?action=upload_logo_checkout', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-Token': window.csrfToken || ''
                    },
                    body: formData
                });
                const result = await response.json();
                
                if (result.success) {
                    showMessage('Logo do checkout enviada com sucesso!', 'success');
                    logoCheckoutPreview.src = result.url;
                    logoCheckoutPreview.classList.remove('hidden');
                    logoCheckoutPlaceholder.classList.add('hidden');
                } else {
                    showMessage(result.error || 'Erro ao enviar logo do checkout', 'error');
                }
            } catch (error) {
                console.error('Erro:', error);
                showMessage('Erro de comunicação com o servidor', 'error');
                uploadLogoCheckoutBtn.disabled = false;
            } finally {
                uploadLogoCheckoutBtn.textContent = 'Enviar Logo';
            }
        });

    // Preview favicon
    faviconFile.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            faviconFilename.textContent = this.files[0].name;
            uploadFaviconBtn.disabled = false;
            
            const reader = new FileReader();
            reader.onload = function(e) {
                faviconPreview.src = e.target.result;
                faviconPreview.classList.remove('hidden');
                faviconPlaceholder.classList.add('hidden');
            };
            reader.readAsDataURL(this.files[0]);
        }
    });

    // Upload favicon
    uploadFaviconBtn.addEventListener('click', async function() {
        if (!faviconFile.files || !faviconFile.files[0]) {
            showMessage('Selecione um arquivo primeiro', 'error');
            return;
        }
        
        const formData = new FormData();
        formData.append('favicon', faviconFile.files[0]);
        formData.append('csrf_token', window.csrfToken || '');
        
        uploadFaviconBtn.disabled = true;
        uploadFaviconBtn.textContent = 'Enviando...';
        
        try {
            const response = await fetch('/api/admin_api.php?action=upload_favicon', {
                method: 'POST',
                headers: {
                    'X-CSRF-Token': window.csrfToken || ''
                },
                body: formData
            });
            const result = await response.json();
            
            if (result.success) {
                showMessage('Favicon enviado com sucesso!', 'success');
                faviconPreview.src = result.url;
                faviconPreview.classList.remove('hidden');
                faviconPlaceholder.classList.add('hidden');
                faviconFile.value = '';
                faviconFilename.textContent = '';
                uploadFaviconBtn.disabled = true;
            } else {
                showMessage(result.error || 'Erro ao enviar favicon', 'error');
                uploadFaviconBtn.disabled = false;
            }
        } catch (error) {
            console.error('Erro:', error);
            showMessage('Erro de comunicação com o servidor', 'error');
            uploadFaviconBtn.disabled = false;
        } finally {
            uploadFaviconBtn.textContent = 'Enviar Favicon';
        }
    });
    
    // Preview em tempo real do URL de fundo do login
    loginBgUrlInput.addEventListener('input', function() {
        updateLoginBgPreview(this.value);
    });
    
    // Salvar URL de fundo do login
    saveLoginBgUrlBtn.addEventListener('click', async function() {
        const url = loginBgUrlInput.value.trim();
        
        saveLoginBgUrlBtn.disabled = true;
        saveLoginBgUrlBtn.textContent = 'Salvando...';
        
        try {
            const response = await fetch('/api/admin_api.php?action=save_system_settings', {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': window.csrfToken || ''
                },
                body: JSON.stringify({ 
                    login_bg_url: url,
                    csrf_token: window.csrfToken || ''
                })
            });
            const result = await response.json();
            
            if (result.success) {
                showMessage('URL de fundo do login salva com sucesso!', 'success');
                updateLoginBgPreview(url);
            } else {
                showMessage('Erro ao salvar URL: ' + (result.error || 'Erro desconhecido'), 'error');
            }
        } catch (error) {
            console.error('Erro:', error);
            showMessage('Erro de comunicação com o servidor: ' + error.message, 'error');
        } finally {
            saveLoginBgUrlBtn.disabled = false;
            saveLoginBgUrlBtn.textContent = 'Salvar URL';
        }
    });
    
    // Salvar tema padrão (claro/escuro)
    const saveTemaPadraoBtn = document.getElementById('save-tema-padrao-btn');
    if (saveTemaPadraoBtn) {
        saveTemaPadraoBtn.addEventListener('click', async function() {
            const temaSelect = document.getElementById('tema_padrao');
            if (!temaSelect) return;
            const tema = temaSelect.value === 'light' ? 'light' : 'dark';
            saveTemaPadraoBtn.disabled = true;
            saveTemaPadraoBtn.textContent = 'Salvando...';
            try {
                const response = await fetch('/api/admin_api.php?action=save_system_settings', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': window.csrfToken || '' },
                    body: JSON.stringify({ tema_padrao: tema, csrf_token: window.csrfToken || '' })
                });
                const result = await response.json();
                if (result.success) {
                    showMessage('Tema padrão salvo. Novas abas usarão este tema; o botão no header continua alternando na sessão atual.', 'success');
                } else {
                    showMessage(result.error || 'Erro ao salvar tema', 'error');
                }
            } catch (error) {
                console.error('Erro:', error);
                showMessage('Erro de comunicação com o servidor', 'error');
            } finally {
                saveTemaPadraoBtn.disabled = false;
                saveTemaPadraoBtn.textContent = 'Salvar Tema';
            }
        });
    }
    
    // Carregar configurações ao iniciar
    loadSettings();
});
</script>

