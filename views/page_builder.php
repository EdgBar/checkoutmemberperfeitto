<?php
/**
 * Page Builder - Sistema de Funis de Vendas
 * Autor: Rafael Souza - https://rafaelsouzatech.com.br
 * Versão: 3.5.0
 * 
 * Changelog v3.5.0:
 * - Revertido URLs para usar /f/ (garante tracking funcional)
 * - Exemplo: seusite.com/f/meu-funil
 * 
 * Changelog v3.4.0:
 * - Tracking de visitas para páginas do funil
 * - Modal de estatísticas com visitas e visitantes únicos
 * - Filtros por período (hoje, ontem, 7 dias, 30 dias, todo período)
 * 
 * Changelog v3.3.0:
 * - URLs dos funis na raiz (removido em v3.5.0)
 * 
 * Changelog v3.2.0:
 * - Aviso sobre limitações do clone (apenas HTML e WordPress)
 * - Correção do erro 403 ao salvar scripts (codificação base64)
 * 
 * Changelog v3.1.0:
 * - Botão de Pixel e Scripts em cada funil
 * - Modal para configurar Facebook Pixel, Google Analytics e scripts personalizados
 * - API para salvar e carregar configurações do funil
 * 
 * Changelog v3.0.0:
 * - Corrigida limpeza de atributos do editor (medium-editor-index, data-height, etc)
 * - Botão para copiar URL do funil na lista Meus Funis
 * - Toast de confirmação ao copiar URL
 * 
 * Changelog v2.9.0:
 * - Removido botão Visualizar
 * 
 * Changelog v2.8.0:
 * - Colunas responsivas: empilham automaticamente em telas menores
 * - Tablet (768px): máximo 2 colunas lado a lado
 * - Mobile (480px): todas as colunas empilham verticalmente
 * - Estilos responsivos incluídos na página publicada
 * 
 * Changelog v2.7.0:
 * - Botões de visualização responsiva: Desktop, Tablet, Celular
 * - Preview em tempo real com diferentes tamanhos de tela
 * - Desktop: 100%, Tablet: 768px, Mobile: 375px
 * 
 * Changelog v2.6.0:
 * - Corrigido enquadramento das colunas com box-sizing e flex-basis
 * - Vídeo agora ocupa 100% da largura da coluna
 * - min-width: 0 para evitar overflow de conteúdo
 * 
 * Changelog v2.5.0:
 * - Botão "Visualizar" para abrir página publicada em nova aba
 * - Botão aparece apenas quando o funil está publicado
 * 
 * Changelog v2.4.0:
 * - Removidos elementos Seção e Contêiner do painel
 * - Seletor de colunas: 1, 2, 3 ou 4 colunas
 * - Placeholders removidos automaticamente ao publicar
 * - Estilos de edição das colunas limpos ao salvar
 * 
 * Changelog v2.3.0:
 * - Corrigida detecção de vídeo para containers pb-video
 * - Adicionado grupo de alinhamento no editor flutuante
 * - Botão para centralizar elemento (margin auto)
 * - Removida seção de Formulários do painel
 * 
 * Changelog v2.2.0:
 * - Painel de elementos redesenhado no estilo Elementor (grid com ícones coloridos)
 * - Novos elementos: Espaçador, Ícone, Lista
 * - Busca de elementos no painel lateral
 * 
 * Changelog v2.1.0:
 * - Sistema de Drag and Drop com estrutura Page → Section → Container → Element
 * - Painel lateral de blocos arrastáveis
 * - Editor flutuante arrastável
 * - Edição de vídeos YouTube/Vimeo
 */

require_once __DIR__ . '/../config/config.php';

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: /login");
    exit;
}

$usuario_id = $_SESSION['id'];

// Obter domínio base
$base_domain = $_SERVER['HTTP_HOST'] ?? 'seusite.com.br';
?>

<style>
    .stat-card { transition: all 0.3s ease; }
    .stat-card:hover { transform: translateY(-2px); }
    .funnel-card { transition: all 0.2s ease; }
    .funnel-card:hover { background: rgba(255,255,255,0.02); }
    .modal-overlay { backdrop-filter: blur(4px); }
    .btn-primary { background: linear-gradient(135deg, #dc2626, #b91c1c); }
    .btn-primary:hover { background: linear-gradient(135deg, #b91c1c, #991b1b); }
    #editorFrame { width: 100%; min-height: 500px; border: none; background: #fff; }
    .page-tab { cursor: pointer; transition: all 0.2s; }
    .page-tab:hover { background: rgba(255,255,255,0.1); }
    .page-tab.active { background: #3b82f6; color: white; }
    
    /* Blocos Arrastáveis - Estilo Elementor */
    .element-block {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 6px;
        padding: 12px 8px;
        background: #1f2937;
        border: 1px solid #374151;
        border-radius: 8px;
        color: #9ca3af;
        font-size: 11px;
        cursor: grab;
        transition: all 0.2s;
        user-select: none;
        text-align: center;
    }
    .element-block:hover {
        background: #374151;
        border-color: #4b5563;
        color: #fff;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.3);
    }
    .element-block:active {
        cursor: grabbing;
        transform: scale(0.95);
    }
    .element-block.dragging {
        opacity: 0.5;
        transform: scale(0.9);
    }
    .element-icon {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        color: white;
    }
    
    /* Compatibilidade com drag-block antigo */
    .drag-block {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 10px 12px;
        background: #374151;
        border: 1px solid #4b5563;
        border-radius: 6px;
        color: #e5e7eb;
        font-size: 13px;
        cursor: grab;
        transition: all 0.2s;
        user-select: none;
    }
    .drag-block:hover {
        background: #4b5563;
        border-color: #6b7280;
        transform: translateX(2px);
    }
    .drag-block:active {
        cursor: grabbing;
        transform: scale(0.98);
    }
    .drag-block.dragging {
        opacity: 0.5;
        transform: scale(0.95);
    }
    
    /* Drop Zone Indicators */
    .drop-zone-active {
        outline: 2px dashed #3b82f6 !important;
        outline-offset: -2px;
        background: rgba(59, 130, 246, 0.1) !important;
    }
    .drop-indicator {
        height: 4px;
        background: #3b82f6;
        border-radius: 2px;
        margin: 4px 0;
        animation: pulse 1s infinite;
    }
    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.5; }
    }
</style>

<div class="container mx-auto p-4 lg:p-8 max-w-7xl">
    
    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8">
        <div class="flex items-center gap-3">
            <div class="p-2 rounded-lg bg-red-600">
                <i data-lucide="layout" class="w-6 h-6 text-white"></i>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-white">Clonar Site</h1>
                <p class="text-gray-400 text-sm">Crie e gerencie seus funis de vendas com editor visual</p>
                <p class="text-yellow-500 text-xs mt-1">
                    <i data-lucide="alert-triangle" class="w-3 h-3 inline mr-1"></i>
                    Clone suporta apenas páginas HTML e WordPress. Páginas com JavaScript/React não são compatíveis.
                </p>
            </div>
        </div>
        <button onclick="openCreateFunnelModal()" class="mt-4 md:mt-0 flex items-center gap-2 px-5 py-2.5 btn-primary text-white font-semibold rounded-lg shadow-lg hover:shadow-xl transition-all">
            <i data-lucide="plus" class="w-5 h-5"></i>
            Criar Novo Funil
        </button>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="stat-card bg-dark-card rounded-xl p-5 border border-dark-border">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-400 text-sm">Total de Funis</p>
                    <p id="statTotalFunnels" class="text-3xl font-bold text-white mt-1">0</p>
                </div>
                <div class="p-3 rounded-lg bg-blue-500/20">
                    <i data-lucide="folder" class="w-6 h-6 text-blue-400"></i>
                </div>
            </div>
        </div>
        <div class="stat-card bg-dark-card rounded-xl p-5 border border-dark-border">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-400 text-sm">Publicados</p>
                    <p id="statPublished" class="text-3xl font-bold text-green-400 mt-1">0</p>
                </div>
                <div class="p-3 rounded-lg bg-green-500/20">
                    <i data-lucide="check-circle" class="w-6 h-6 text-green-400"></i>
                </div>
            </div>
        </div>
        <div class="stat-card bg-dark-card rounded-xl p-5 border border-dark-border">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-400 text-sm">Rascunhos</p>
                    <p id="statDrafts" class="text-3xl font-bold text-yellow-400 mt-1">0</p>
                </div>
                <div class="p-3 rounded-lg bg-yellow-500/20">
                    <i data-lucide="edit" class="w-6 h-6 text-yellow-400"></i>
                </div>
            </div>
        </div>
        <div class="stat-card bg-dark-card rounded-xl p-5 border border-dark-border">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-400 text-sm">Total de Páginas</p>
                    <p id="statTotalPages" class="text-3xl font-bold text-purple-400 mt-1">0</p>
                </div>
                <div class="p-3 rounded-lg bg-purple-500/20">
                    <i data-lucide="file-text" class="w-6 h-6 text-purple-400"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Funnels List -->
    <div class="bg-dark-card rounded-xl border border-dark-border overflow-hidden">
        <div class="p-5 border-b border-dark-border">
            <h2 class="text-lg font-semibold text-white">Meus Funis</h2>
        </div>
        <div id="funnelsList" class="divide-y divide-dark-border">
            <div class="p-8 text-center text-gray-400">
                <i data-lucide="loader" class="w-8 h-8 mx-auto mb-3 animate-spin"></i>
                <p>Carregando...</p>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Criar Novo Funil -->
<div id="createFunnelModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 modal-overlay">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-xl font-bold text-gray-900">Criar Novo Funil</h3>
            <button onclick="closeCreateFunnelModal()" class="text-gray-400 hover:text-gray-600">
                <i data-lucide="x" class="w-6 h-6"></i>
            </button>
        </div>
        <form id="createFunnelForm">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Nome do Funil</label>
                <input type="text" id="funnelName" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 text-gray-900" placeholder="Ex: Funil de Vendas Principal" required>
            </div>
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Slug (URL)</label>
                <div class="flex">
                    <span class="px-4 py-3 bg-gray-100 border border-r-0 border-gray-300 rounded-l-lg text-gray-500 text-sm"><?php echo $base_domain; ?>/f/</span>
                    <input type="text" id="funnelSlug" class="flex-1 px-4 py-3 border border-gray-300 rounded-r-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 text-gray-900" placeholder="meu-funil" required>
                </div>
                <p class="text-xs text-gray-500 mt-1">Apenas letras minúsculas, números e hífens</p>
            </div>
            <div class="flex gap-3">
                <button type="button" onclick="closeCreateFunnelModal()" class="flex-1 px-4 py-3 border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50 transition-colors">
                    Cancelar
                </button>
                <button type="submit" class="flex-1 px-4 py-3 btn-primary text-white font-semibold rounded-lg transition-all">
                    Criar Funil
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Pixel e Scripts -->
<div id="pixelModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 modal-overlay">
    <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full p-6">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-xl font-bold text-gray-900">
                <i data-lucide="code" class="w-6 h-6 inline mr-2 text-cyan-500"></i>
                Pixel e Scripts
            </h3>
            <button onclick="closePixelModal()" class="text-gray-400 hover:text-gray-600">
                <i data-lucide="x" class="w-6 h-6"></i>
            </button>
        </div>
        <form id="pixelForm">
            <input type="hidden" id="pixelFunnelId">
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i data-lucide="facebook" class="w-4 h-4 inline mr-1 text-blue-600"></i>
                    Facebook Pixel ID
                </label>
                <input type="text" id="facebookPixelId" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 text-gray-900" placeholder="Ex: 123456789012345">
                <p class="text-xs text-gray-500 mt-1">Apenas o ID numérico do pixel</p>
            </div>
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i data-lucide="bar-chart-2" class="w-4 h-4 inline mr-1 text-orange-500"></i>
                    Google Analytics ID
                </label>
                <input type="text" id="googleAnalyticsId" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 text-gray-900" placeholder="Ex: G-XXXXXXXXXX ou UA-XXXXXXXX-X">
            </div>
            
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i data-lucide="file-code" class="w-4 h-4 inline mr-1 text-purple-500"></i>
                    Scripts Personalizados (Head)
                </label>
                <textarea id="customHeadScripts" rows="4" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 text-gray-900 font-mono text-sm" placeholder="Cole aqui scripts que devem ser inseridos no <head>"></textarea>
                <p class="text-xs text-gray-500 mt-1">Scripts serão inseridos antes do &lt;/head&gt;</p>
            </div>
            
            <div class="flex gap-3">
                <button type="button" onclick="closePixelModal()" class="flex-1 px-4 py-3 border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50 transition-colors">
                    Cancelar
                </button>
                <button type="submit" class="flex-1 px-4 py-3 bg-cyan-600 hover:bg-cyan-700 text-white font-semibold rounded-lg transition-all">
                    Salvar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Editor Visual -->
<div id="editorModal" class="hidden fixed inset-0 z-50 bg-gray-900">
    <!-- Editor Header -->
    <div class="h-14 bg-gray-800 border-b border-gray-700 flex items-center justify-between px-4">
        <div class="flex items-center gap-4">
            <button onclick="closeEditor()" class="p-2 text-gray-400 hover:text-white hover:bg-gray-700 rounded-lg transition-colors">
                <i data-lucide="arrow-left" class="w-5 h-5"></i>
            </button>
            <div>
                <h3 id="editorFunnelName" class="text-white font-semibold">Funil</h3>
                <p id="editorSubdomain" class="text-xs text-gray-400"></p>
            </div>
        </div>
        
        <!-- Botões de Visualização Responsiva -->
        <div class="flex items-center gap-1 bg-gray-700 rounded-lg p-1">
            <button onclick="setViewport('desktop')" id="btnDesktop" class="p-2 rounded text-white bg-gray-600" title="Desktop">
                <i data-lucide="monitor" class="w-5 h-5"></i>
            </button>
            <button onclick="setViewport('tablet')" id="btnTablet" class="p-2 rounded text-gray-400 hover:text-white hover:bg-gray-600" title="Tablet">
                <i data-lucide="tablet" class="w-5 h-5"></i>
            </button>
            <button onclick="setViewport('mobile')" id="btnMobile" class="p-2 rounded text-gray-400 hover:text-white hover:bg-gray-600" title="Celular">
                <i data-lucide="smartphone" class="w-5 h-5"></i>
            </button>
        </div>
        
        <div class="flex items-center gap-2">
            <button onclick="openCloneUrlModal()" class="flex items-center gap-2 px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition-colors">
                <i data-lucide="copy" class="w-4 h-4"></i>
                Clonar URL
            </button>
        </div>
        
        <!-- Page Selector (hidden) -->
        <select id="pageSelector" class="hidden"></select>
        
        <div class="flex items-center gap-2">
            <span id="editorStatus" class="text-sm text-gray-400">Pronto</span>
            <button onclick="savePage()" class="flex items-center gap-2 px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition-colors">
                <i data-lucide="save" class="w-4 h-4"></i>
                Salvar
            </button>
            <button id="btnPublish" onclick="publishFunnel()" class="flex items-center gap-2 px-5 py-2 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg transition-colors">
                <i data-lucide="rocket" class="w-4 h-4"></i>
                <span id="btnPublishText">Publicar</span>
            </button>
        </div>
    </div>
    
    <!-- Editor Content com Painéis Laterais -->
    <div class="h-[calc(100vh-56px)] flex">
        
        <!-- Painel Esquerdo: Elementos (Estilo Elementor) -->
        <div id="blocksPanel" class="w-72 bg-gray-900 border-r border-gray-700 overflow-y-auto flex-shrink-0">
            <!-- Header com busca -->
            <div class="p-3 border-b border-gray-700">
                <div class="relative">
                    <i data-lucide="search" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-500"></i>
                    <input type="text" id="searchElements" placeholder="Buscar elementos..." class="w-full pl-9 pr-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white text-sm focus:ring-2 focus:ring-pink-500 focus:border-transparent">
                </div>
            </div>
            
            <!-- Grid de Elementos -->
            <div class="p-3">
                <p class="text-xs text-gray-500 uppercase tracking-wider mb-3">Layout</p>
                <div class="grid grid-cols-4 gap-2 mb-4" id="columnsGrid">
                    <div class="element-block" draggable="true" data-type="row-1" title="1 Coluna">
                        <div class="element-icon bg-gradient-to-br from-green-500 to-emerald-500">
                            <i data-lucide="square" class="w-5 h-5"></i>
                        </div>
                        <span>1 Col</span>
                    </div>
                    <div class="element-block" draggable="true" data-type="row-2" title="2 Colunas">
                        <div class="element-icon bg-gradient-to-br from-green-500 to-emerald-500">
                            <i data-lucide="columns" class="w-5 h-5"></i>
                        </div>
                        <span>2 Col</span>
                    </div>
                    <div class="element-block" draggable="true" data-type="row-3" title="3 Colunas">
                        <div class="element-icon bg-gradient-to-br from-green-500 to-emerald-500">
                            <i data-lucide="layout-grid" class="w-5 h-5"></i>
                        </div>
                        <span>3 Col</span>
                    </div>
                    <div class="element-block" draggable="true" data-type="row-4" title="4 Colunas">
                        <div class="element-icon bg-gradient-to-br from-green-500 to-emerald-500">
                            <i data-lucide="grid-2x2" class="w-5 h-5"></i>
                        </div>
                        <span>4 Col</span>
                    </div>
                </div>
                
                <p class="text-xs text-gray-500 uppercase tracking-wider mb-3">Elementos</p>
                <div class="grid grid-cols-3 gap-2" id="elementsGrid">
                    
                    <div class="element-block" draggable="true" data-type="heading">
                        <div class="element-icon bg-gradient-to-br from-orange-500 to-amber-500">
                            <i data-lucide="type" class="w-5 h-5"></i>
                        </div>
                        <span>Título</span>
                    </div>
                    
                    <div class="element-block" draggable="true" data-type="text">
                        <div class="element-icon bg-gradient-to-br from-sky-500 to-blue-500">
                            <i data-lucide="text" class="w-5 h-5"></i>
                        </div>
                        <span>Texto</span>
                    </div>
                    
                    <div class="element-block" draggable="true" data-type="image">
                        <div class="element-icon bg-gradient-to-br from-violet-500 to-purple-500">
                            <i data-lucide="image" class="w-5 h-5"></i>
                        </div>
                        <span>Imagem</span>
                    </div>
                    
                    <div class="element-block" draggable="true" data-type="button">
                        <div class="element-icon bg-gradient-to-br from-pink-500 to-rose-500">
                            <i data-lucide="square" class="w-5 h-5"></i>
                        </div>
                        <span>Botão</span>
                    </div>
                    
                    <div class="element-block" draggable="true" data-type="video">
                        <div class="element-icon bg-gradient-to-br from-red-500 to-orange-500">
                            <i data-lucide="play" class="w-5 h-5"></i>
                        </div>
                        <span>Vídeo</span>
                    </div>
                    
                    <div class="element-block" draggable="true" data-type="divider">
                        <div class="element-icon bg-gradient-to-br from-gray-500 to-slate-500">
                            <i data-lucide="minus" class="w-5 h-5"></i>
                        </div>
                        <span>Divisor</span>
                    </div>
                    
                    <div class="element-block" draggable="true" data-type="spacer">
                        <div class="element-icon bg-gradient-to-br from-indigo-500 to-violet-500">
                            <i data-lucide="move-vertical" class="w-5 h-5"></i>
                        </div>
                        <span>Espaçador</span>
                    </div>
                    
                    <div class="element-block" draggable="true" data-type="icon">
                        <div class="element-icon bg-gradient-to-br from-teal-500 to-green-500">
                            <i data-lucide="star" class="w-5 h-5"></i>
                        </div>
                        <span>Ícone</span>
                    </div>
                    
                    <div class="element-block" draggable="true" data-type="list">
                        <div class="element-icon bg-gradient-to-br from-cyan-500 to-teal-500">
                            <i data-lucide="list" class="w-5 h-5"></i>
                        </div>
                        <span>Lista</span>
                    </div>
                    
                </div>
            </div>
            
        </div>
        
        <!-- Área Central: Editor -->
        <div class="flex-1 bg-gray-100 overflow-auto relative">
            <div id="editorContainer" class="w-full h-full bg-white">
                <iframe id="editorFrame" class="w-full h-full border-0"></iframe>
            </div>
        </div>
        
    </div>
</div>

<!-- Modal: Nova Página -->
<div id="newPageModal" class="hidden fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/70 modal-overlay">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-xl font-bold text-gray-900">Nova Página</h3>
            <button onclick="closeNewPageModal()" class="text-gray-400 hover:text-gray-600">
                <i data-lucide="x" class="w-6 h-6"></i>
            </button>
        </div>
        <form id="newPageForm">
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Nome da Página</label>
                <input type="text" id="newPageName" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-gray-900" placeholder="Ex: Página de Vendas" required>
            </div>
            <div class="flex gap-3">
                <button type="button" onclick="closeNewPageModal()" class="flex-1 px-4 py-3 border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50">
                    Cancelar
                </button>
                <button type="submit" class="flex-1 px-4 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg">
                    Criar Página
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Clonar URL -->
<div id="cloneUrlModal" class="hidden fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/70 modal-overlay">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-xl font-bold text-gray-900">Clonar Página de URL</h3>
            <button onclick="closeCloneUrlModal()" class="text-gray-400 hover:text-gray-600">
                <i data-lucide="x" class="w-6 h-6"></i>
            </button>
        </div>
        <form id="cloneUrlForm">
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">URL da Página</label>
                <input type="text" id="cloneUrl" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 text-gray-900" placeholder="https://exemplo.com/pagina" required>
                <p class="text-xs text-gray-500 mt-1">Cole a URL completa da página que deseja clonar</p>
            </div>
            <div class="flex gap-3">
                <button type="button" onclick="closeCloneUrlModal()" class="flex-1 px-4 py-3 border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50">
                    Cancelar
                </button>
                <button type="submit" class="flex-1 px-4 py-3 bg-purple-600 hover:bg-purple-700 text-white font-semibold rounded-lg">
                    Clonar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Floating Editor Box - Editor Completo -->
<div id="floatingEditor" class="hidden fixed z-[70] bg-gray-800 rounded-xl shadow-2xl border border-gray-700 p-4 min-w-[350px] max-w-[400px]">
    <!-- Header arrastável -->
    <div id="floatingEditorHeader" class="flex items-center justify-between mb-3 pb-2 border-b border-gray-700 cursor-move select-none">
        <div class="flex items-center gap-2">
            <i data-lucide="grip-vertical" class="w-4 h-4 text-gray-500"></i>
            <span id="floatingElementType" class="text-xs font-semibold text-blue-400 uppercase tracking-wide">Elemento</span>
        </div>
        <button onclick="document.getElementById('floatingEditor').classList.add('hidden')" class="text-gray-500 hover:text-white">
            <i data-lucide="x" class="w-4 h-4"></i>
        </button>
    </div>
    
    <!-- Grupo: Texto -->
    <div id="floatingTextGroup" class="hidden mb-3">
        <label class="block text-xs font-medium text-gray-400 mb-1">Texto</label>
        <textarea id="floatingText" rows="2" class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white text-sm focus:ring-2 focus:ring-blue-500"></textarea>
    </div>
    
    <!-- Grupo: Link/URL -->
    <div id="floatingUrlGroup" class="hidden mb-3">
        <label class="block text-xs font-medium text-gray-400 mb-1">URL do Link</label>
        <input type="text" id="floatingUrl" placeholder="https://..." class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white text-sm focus:ring-2 focus:ring-blue-500">
    </div>
    
    <!-- Grupo: Imagem -->
    <div id="floatingImgGroup" class="hidden mb-3">
        <label class="block text-xs font-medium text-gray-400 mb-1">URL da Imagem</label>
        <input type="text" id="floatingImgSrc" placeholder="https://..." class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white text-sm focus:ring-2 focus:ring-blue-500" oninput="previewImage(this.value)">
        <div id="floatingImgPreview" class="mt-2 hidden">
            <img id="floatingImgPreviewImg" src="" class="max-h-24 rounded border border-gray-600">
        </div>
        <div class="mt-2 grid grid-cols-2 gap-2">
            <div>
                <label class="block text-xs text-gray-500 mb-1">Largura</label>
                <input type="text" id="floatingImgWidth" placeholder="auto" class="w-full px-2 py-1 bg-gray-700 border border-gray-600 rounded text-white text-xs">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Altura</label>
                <input type="text" id="floatingImgHeight" placeholder="auto" class="w-full px-2 py-1 bg-gray-700 border border-gray-600 rounded text-white text-xs">
            </div>
        </div>
    </div>
    
    <!-- Grupo: Vídeo -->
    <div id="floatingVideoGroup" class="hidden mb-3">
        <label class="block text-xs font-medium text-gray-400 mb-1">URL do Vídeo (YouTube/Vimeo)</label>
        <input type="text" id="floatingVideoSrc" placeholder="https://youtube.com/watch?v=..." class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white text-sm focus:ring-2 focus:ring-blue-500">
        <p class="text-xs text-gray-500 mt-1">Cole o link do YouTube ou Vimeo</p>
    </div>
    
    <!-- Grupo: Cores -->
    <div id="floatingColorGroup" class="hidden mb-3 pt-2 border-t border-gray-700">
        <label class="block text-xs font-medium text-gray-400 mb-2">Cores</label>
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="block text-xs text-gray-500 mb-1">Texto</label>
                <div class="flex items-center gap-2">
                    <input type="color" id="floatingTextColor" class="w-8 h-8 rounded cursor-pointer border-0 bg-transparent">
                    <input type="text" id="floatingTextColorHex" placeholder="#000000" class="flex-1 px-2 py-1 bg-gray-700 border border-gray-600 rounded text-white text-xs">
                </div>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Fundo</label>
                <div class="flex items-center gap-2">
                    <input type="color" id="floatingBgColor" class="w-8 h-8 rounded cursor-pointer border-0 bg-transparent">
                    <input type="text" id="floatingBgColorHex" placeholder="#ffffff" class="flex-1 px-2 py-1 bg-gray-700 border border-gray-600 rounded text-white text-xs">
                </div>
            </div>
        </div>
    </div>
    
    <!-- Grupo: Estilos de Texto -->
    <div id="floatingStyleGroup" class="hidden mb-3">
        <label class="block text-xs font-medium text-gray-400 mb-2">Estilo do Texto</label>
        <div class="flex gap-1">
            <button type="button" onclick="toggleStyle('bold')" class="style-btn px-3 py-1 bg-gray-700 hover:bg-gray-600 text-white rounded text-sm font-bold">B</button>
            <button type="button" onclick="toggleStyle('italic')" class="style-btn px-3 py-1 bg-gray-700 hover:bg-gray-600 text-white rounded text-sm italic">I</button>
            <button type="button" onclick="toggleStyle('underline')" class="style-btn px-3 py-1 bg-gray-700 hover:bg-gray-600 text-white rounded text-sm underline">U</button>
            <select id="floatingFontSize" class="px-2 py-1 bg-gray-700 border border-gray-600 rounded text-white text-xs">
                <option value="">Tamanho</option>
                <option value="12px">12px</option>
                <option value="14px">14px</option>
                <option value="16px">16px</option>
                <option value="18px">18px</option>
                <option value="20px">20px</option>
                <option value="24px">24px</option>
                <option value="28px">28px</option>
                <option value="32px">32px</option>
                <option value="36px">36px</option>
                <option value="48px">48px</option>
                <option value="64px">64px</option>
            </select>
        </div>
    </div>
    
    <!-- Grupo: Alinhamento -->
    <div id="floatingAlignGroup" class="mb-3">
        <label class="block text-xs font-medium text-gray-400 mb-2">Alinhamento</label>
        <div class="flex gap-1">
            <button type="button" onclick="setElementAlign('left')" class="flex-1 p-2 bg-gray-700 hover:bg-gray-600 text-white rounded text-sm" title="Esquerda">
                <i data-lucide="align-left" class="w-4 h-4 mx-auto"></i>
            </button>
            <button type="button" onclick="setElementAlign('center')" class="flex-1 p-2 bg-gray-700 hover:bg-gray-600 text-white rounded text-sm" title="Centralizar">
                <i data-lucide="align-center" class="w-4 h-4 mx-auto"></i>
            </button>
            <button type="button" onclick="setElementAlign('right')" class="flex-1 p-2 bg-gray-700 hover:bg-gray-600 text-white rounded text-sm" title="Direita">
                <i data-lucide="align-right" class="w-4 h-4 mx-auto"></i>
            </button>
            <button type="button" onclick="centerElement()" class="flex-1 p-2 bg-blue-600 hover:bg-blue-700 text-white rounded text-sm" title="Centralizar Elemento">
                <i data-lucide="move-horizontal" class="w-4 h-4 mx-auto"></i>
            </button>
        </div>
    </div>
    
    <!-- Botões de Ação -->
    <div class="flex gap-2 pt-2 border-t border-gray-700">
        <button onclick="applyFloatingEdit()" class="flex-1 flex items-center justify-center gap-2 px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg text-sm transition-colors">
            <i data-lucide="check" class="w-4 h-4"></i>
            Aplicar
        </button>
        <button onclick="deleteSelectedElement()" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm transition-colors" title="Excluir elemento">
            <i data-lucide="trash-2" class="w-4 h-4"></i>
        </button>
    </div>
</div>

<script>
const API = '/api/page_builder_api.php';
const BASE_DOMAIN = '<?php echo $base_domain; ?>';

let currentFunnelId = null;
let currentPageId = null;
let funnelPages = [];
let editorDoc = null;
let selectedElement = null;
let currentFunnelStatus = 'draft';

// ============================================
// INICIALIZAÇÃO
// ============================================

document.addEventListener('DOMContentLoaded', () => {
    loadStats();
    loadFunnels();
    lucide.createIcons();
});

// ============================================
// API CALLS
// ============================================

async function apiCall(action, data = {}, method = 'GET') {
    try {
        const options = { 
            method, 
            headers: { 'Content-Type': 'application/json' },
            credentials: 'same-origin'
        };
        let url = `${API}?action=${action}`;
        
        if (method === 'POST') {
            options.body = JSON.stringify(data);
        } else {
            Object.keys(data).forEach(key => url += `&${key}=${encodeURIComponent(data[key])}`);
        }
        
        const response = await fetch(url, options);
        
        // Verificar se a resposta é válida
        if (!response.ok) {
            console.error('HTTP Error:', response.status, response.statusText);
            const text = await response.text();
            console.error('Response body:', text);
            try {
                return JSON.parse(text);
            } catch (e) {
                return { success: false, error: `Erro HTTP ${response.status}: ${text.substring(0, 100)}` };
            }
        }
        
        const text = await response.text();
        try {
            return JSON.parse(text);
        } catch (e) {
            console.error('JSON Parse Error:', text);
            return { success: false, error: 'Resposta inválida do servidor' };
        }
    } catch (error) {
        console.error('API Error:', error);
        return { success: false, error: 'Erro de conexão: ' + error.message };
    }
}

// ============================================
// ESTATÍSTICAS
// ============================================

async function loadStats() {
    const data = await apiCall('get_stats');
    if (data.success) {
        document.getElementById('statTotalFunnels').textContent = data.total_funnels;
        document.getElementById('statPublished').textContent = data.published;
        document.getElementById('statDrafts').textContent = data.drafts;
        document.getElementById('statTotalPages').textContent = data.total_pages;
    }
}

// ============================================
// FUNIS
// ============================================

async function loadFunnels() {
    const data = await apiCall('get_funnels');
    const container = document.getElementById('funnelsList');
    
    if (!data.success || data.funnels.length === 0) {
        container.innerHTML = `
            <div class="p-12 text-center">
                <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-dark-elevated flex items-center justify-center">
                    <i data-lucide="folder-plus" class="w-8 h-8 text-gray-500"></i>
                </div>
                <p class="text-gray-400 font-medium">Nenhum funil criado ainda</p>
                <p class="text-gray-500 text-sm mt-1">Clique em "Criar Novo Funil" para começar</p>
            </div>
        `;
        lucide.createIcons();
        return;
    }
    
    container.innerHTML = data.funnels.map(funnel => {
        const isPublished = funnel.status === 'published';
        const statusBadge = isPublished 
            ? '<span class="px-2 py-1 rounded-full text-xs font-medium bg-green-900/30 text-green-400">Publicado</span>'
            : '<span class="px-2 py-1 rounded-full text-xs font-medium bg-yellow-900/30 text-yellow-400">Rascunho</span>';
        
        return `
            <div class="funnel-card p-5 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div class="flex-1">
                    <div class="flex items-center gap-3 mb-1">
                        <h3 class="text-lg font-semibold text-white">${funnel.name}</h3>
                        ${statusBadge}
                    </div>
                    <p class="text-sm text-gray-400">
                        <i data-lucide="link" class="w-4 h-4 inline mr-1"></i>
                        ${BASE_DOMAIN}/f/${funnel.slug}
                    </p>
                    <p class="text-xs text-gray-500 mt-1">
                        <i data-lucide="file-text" class="w-3 h-3 inline mr-1"></i>
                        ${funnel.page_count} página(s) • ${new Date(funnel.updated_at).toLocaleDateString('pt-BR')}
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <button onclick="openEditor(${funnel.id})" class="flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors">
                        <i data-lucide="edit" class="w-4 h-4"></i>
                        Editar
                    </button>
                    <button onclick="copyFunnelUrl('${BASE_DOMAIN}/f/${funnel.slug}')" class="p-2 text-purple-400 hover:bg-purple-900/20 rounded-lg transition-colors" title="Copiar URL">
                        <i data-lucide="copy" class="w-5 h-5"></i>
                    </button>
                    <button onclick="openPixelModal(${funnel.id})" class="p-2 text-cyan-400 hover:bg-cyan-900/20 rounded-lg transition-colors" title="Pixel e Scripts">
                        <i data-lucide="code" class="w-5 h-5"></i>
                    </button>
                    <button onclick="toggleFunnelStatus(${funnel.id}, '${isPublished ? 'draft' : 'published'}')" class="p-2 ${isPublished ? 'text-yellow-400 hover:bg-yellow-900/20' : 'text-green-400 hover:bg-green-900/20'} rounded-lg transition-colors" title="${isPublished ? 'Despublicar' : 'Publicar'}">
                        <i data-lucide="${isPublished ? 'eye-off' : 'rocket'}" class="w-5 h-5"></i>
                    </button>
                    <button onclick="deleteFunnel(${funnel.id})" class="p-2 text-red-400 hover:bg-red-900/20 rounded-lg transition-colors" title="Excluir">
                        <i data-lucide="trash-2" class="w-5 h-5"></i>
                    </button>
                </div>
            </div>
        `;
    }).join('');
    
    lucide.createIcons();
}

// Copiar URL do funil para a área de transferência
function copyFunnelUrl(url) {
    navigator.clipboard.writeText(url).then(() => {
        // Mostrar feedback visual
        const toast = document.createElement('div');
        toast.className = 'fixed bottom-4 right-4 bg-green-600 text-white px-4 py-2 rounded-lg shadow-lg z-50 animate-pulse';
        toast.innerHTML = '<i data-lucide="check" class="w-4 h-4 inline mr-2"></i>URL copiada!';
        document.body.appendChild(toast);
        lucide.createIcons();
        
        setTimeout(() => {
            toast.remove();
        }, 2000);
    }).catch(err => {
        // Fallback para navegadores antigos
        const textArea = document.createElement('textarea');
        textArea.value = url;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        alert('URL copiada: ' + url);
    });
}

function openCreateFunnelModal() {
    document.getElementById('createFunnelModal').classList.remove('hidden');
    document.getElementById('funnelName').value = '';
    document.getElementById('funnelSlug').value = '';
    lucide.createIcons();
}

function closeCreateFunnelModal() {
    document.getElementById('createFunnelModal').classList.add('hidden');
}

// Funções do Modal de Pixel e Scripts
async function openPixelModal(funnelId) {
    document.getElementById('pixelFunnelId').value = funnelId;
    document.getElementById('facebookPixelId').value = '';
    document.getElementById('googleAnalyticsId').value = '';
    document.getElementById('customHeadScripts').value = '';
    
    // Carregar configurações existentes
    const data = await apiCall('get_funnel_settings', { funnel_id: funnelId });
    if (data.success && data.settings) {
        document.getElementById('facebookPixelId').value = data.settings.facebook_pixel_id || '';
        document.getElementById('googleAnalyticsId').value = data.settings.google_analytics_id || '';
        document.getElementById('customHeadScripts').value = data.settings.custom_head_scripts || '';
    }
    
    document.getElementById('pixelModal').classList.remove('hidden');
    lucide.createIcons();
}

function closePixelModal() {
    document.getElementById('pixelModal').classList.add('hidden');
}

document.getElementById('pixelForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const funnelId = document.getElementById('pixelFunnelId').value;
    const facebookPixelId = document.getElementById('facebookPixelId').value.trim();
    const googleAnalyticsId = document.getElementById('googleAnalyticsId').value.trim();
    const customHeadScripts = document.getElementById('customHeadScripts').value;
    
    // Codificar scripts em base64 para evitar bloqueio do WAF/ModSecurity
    const scriptsBase64 = customHeadScripts ? btoa(unescape(encodeURIComponent(customHeadScripts))) : '';
    
    const data = await apiCall('save_funnel_settings', {
        funnel_id: funnelId,
        facebook_pixel_id: facebookPixelId,
        google_analytics_id: googleAnalyticsId,
        custom_head_scripts_b64: scriptsBase64
    }, 'POST');
    
    if (data.success) {
        closePixelModal();
        // Toast de sucesso
        const toast = document.createElement('div');
        toast.className = 'fixed bottom-4 right-4 bg-green-600 text-white px-4 py-2 rounded-lg shadow-lg z-50';
        toast.innerHTML = '<i data-lucide="check" class="w-4 h-4 inline mr-2"></i>Configurações salvas!';
        document.body.appendChild(toast);
        lucide.createIcons();
        setTimeout(() => toast.remove(), 2000);
    } else {
        alert(data.error || 'Erro ao salvar configurações');
    }
});

document.getElementById('createFunnelForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const name = document.getElementById('funnelName').value.trim();
    const slug = document.getElementById('funnelSlug').value.trim().toLowerCase().replace(/[^a-z0-9-]/g, '');
    
    if (!name || !slug) {
        alert('Preencha todos os campos');
        return;
    }
    
    const data = await apiCall('create_funnel', { name, slug }, 'POST');
    
    if (data.success) {
        closeCreateFunnelModal();
        loadStats();
        loadFunnels();
        // Abrir editor automaticamente
        setTimeout(() => openEditor(data.funnel_id), 500);
    } else {
        alert(data.error || 'Erro ao criar funil');
    }
});

async function toggleFunnelStatus(funnelId, status) {
    const data = await apiCall('toggle_funnel_status', { funnel_id: funnelId, status }, 'POST');
    if (data.success) {
        loadStats();
        loadFunnels();
    } else {
        alert(data.error || 'Erro ao atualizar status');
    }
}

async function deleteFunnel(funnelId) {
    if (!confirm('Tem certeza que deseja excluir este funil? Todas as páginas serão perdidas.')) return;
    
    const data = await apiCall('delete_funnel', { funnel_id: funnelId }, 'POST');
    if (data.success) {
        loadStats();
        loadFunnels();
    } else {
        alert(data.error || 'Erro ao excluir');
    }
}

// ============================================
// EDITOR
// ============================================

async function openEditor(funnelId) {
    currentFunnelId = funnelId;
    
    const data = await apiCall('get_funnel', { funnel_id: funnelId });
    if (!data.success) {
        alert(data.error || 'Erro ao carregar funil');
        return;
    }
    
    document.getElementById('editorFunnelName').textContent = data.funnel.name;
    document.getElementById('editorSubdomain').textContent = `${BASE_DOMAIN}/f/${data.funnel.slug}`;
    
    // Definir status atual do funil
    currentFunnelStatus = data.funnel.status || 'draft';
    updatePublishButton();
    
    funnelPages = data.pages;
    updatePageSelector();
    
    // Carregar primeira página
    if (funnelPages.length > 0) {
        loadPageInEditor(funnelPages[0].id);
    }
    
    document.getElementById('editorModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    lucide.createIcons();
}

function closeEditor() {
    document.getElementById('editorModal').classList.add('hidden');
    document.body.style.overflow = '';
    document.getElementById('floatingEditor').classList.add('hidden');
    currentFunnelId = null;
    currentPageId = null;
}

function updatePageSelector() {
    const selector = document.getElementById('pageSelector');
    selector.innerHTML = funnelPages.map(page => 
        `<option value="${page.id}" ${page.id === currentPageId ? 'selected' : ''}>${page.name}</option>`
    ).join('');
}

document.getElementById('pageSelector').addEventListener('change', (e) => {
    loadPageInEditor(parseInt(e.target.value));
});

async function loadPageInEditor(pageId) {
    currentPageId = pageId;
    updatePageSelector();
    
    const data = await apiCall('get_page', { page_id: pageId });
    if (!data.success) {
        alert(data.error || 'Erro ao carregar página');
        return;
    }
    
    initEditor(data.page.html_content || '<html><head><title>Nova Página</title></head><body><h1>Edite esta página</h1></body></html>');
}

function initEditor(htmlContent) {
    const frame = document.getElementById('editorFrame');
    
    // Usar srcdoc para evitar problemas de cross-origin
    frame.srcdoc = htmlContent;
    
    // Aguardar o iframe carregar
    frame.onload = function() {
        try {
            editorDoc = frame.contentDocument || frame.contentWindow.document;
            
            if (!editorDoc || !editorDoc.body) {
                console.error('Não foi possível acessar o documento do iframe');
                return;
            }
            
            // Adicionar estilos do editor
            const style = editorDoc.createElement('style');
            style.id = 'editor-styles';
            style.textContent = `
                * { cursor: pointer !important; box-sizing: border-box; }
                *:hover { outline: 2px dashed #3b82f6 !important; outline-offset: 2px; }
                body { min-height: 100vh; padding-bottom: 100px !important; }
                
                /* CSS Responsivo para Colunas */
                .pb-row { width: 100%; }
                .pb-column { box-sizing: border-box; }
                
                /* Tablet: 768px ou menos - 2 colunas máximo */
                @media (max-width: 768px) {
                    .pb-row { gap: 15px !important; }
                    .pb-column { 
                        flex: 1 1 calc(50% - 8px) !important; 
                        min-width: calc(50% - 8px) !important;
                    }
                }
                
                /* Mobile: 480px ou menos - 1 coluna */
                @media (max-width: 480px) {
                    .pb-row { 
                        flex-direction: column !important; 
                        gap: 10px !important; 
                    }
                    .pb-column { 
                        flex: 1 1 100% !important; 
                        min-width: 100% !important;
                        width: 100% !important;
                    }
                }
                
                /* Garantir que vídeos e imagens sejam responsivos */
                .pb-video, .pb-image, img, iframe, video {
                    max-width: 100% !important;
                }
            `;
            
            if (editorDoc.head) {
                editorDoc.head.appendChild(style);
            } else {
                editorDoc.body.insertBefore(style, editorDoc.body.firstChild);
            }
            
            // Adicionar eventos de clique em todos os elementos
            editorDoc.body.addEventListener('click', handleEditorClick, true);
            
            // Também adicionar em cada elemento individualmente para garantir
            editorDoc.querySelectorAll('*').forEach(el => {
                el.addEventListener('click', handleEditorClick);
            });
            
            // Prevenir navegação em links
            editorDoc.querySelectorAll('a').forEach(a => {
                a.onclick = (e) => { e.preventDefault(); return false; };
            });
            
            // Adicionar overlay clicável sobre iframes/videos para capturar cliques
            editorDoc.querySelectorAll('iframe, video, embed, object').forEach(media => {
                try {
                    // Verificar se já tem overlay
                    if (media.parentElement && media.parentElement.classList.contains('editor-media-wrapper')) return;
                    if (!media.parentNode) return;
                    
                    // Obter dimensões do elemento
                    const rect = media.getBoundingClientRect();
                    const width = media.offsetWidth || media.clientWidth || rect.width || '100%';
                    const height = media.offsetHeight || media.clientHeight || rect.height || '300px';
                    
                    // Criar wrapper com as mesmas dimensões
                    const wrapper = editorDoc.createElement('div');
                    wrapper.className = 'editor-media-wrapper';
                    wrapper.style.cssText = `position: relative; display: block; width: ${typeof width === 'number' ? width + 'px' : width}; height: ${typeof height === 'number' ? height + 'px' : height};`;
                    
                    // Criar overlay clicável
                    const overlay = editorDoc.createElement('div');
                    overlay.className = 'editor-media-overlay';
                    overlay.style.cssText = 'position: absolute; top: 0; left: 0; width: 100%; height: 100%; cursor: pointer; z-index: 9999; background: rgba(0,0,0,0.01);';
                    overlay.dataset.mediaType = media.tagName.toLowerCase();
                    overlay.dataset.mediaSrc = media.getAttribute('src') || '';
                    
                    // Ao clicar no overlay, abrir editor para o elemento de mídia
                    overlay.addEventListener('click', (e) => {
                        e.preventDefault();
                        e.stopPropagation();
                        console.log('Clique no overlay de mídia:', media.tagName, media.getAttribute('src'));
                        
                        // Disparar handleEditorClick com o elemento de mídia como target
                        const fakeEvent = { 
                            target: media, 
                            preventDefault: () => {}, 
                            stopPropagation: () => {},
                            type: 'click'
                        };
                        handleEditorClick(fakeEvent);
                    });
                    
                    // Inserir wrapper
                    media.parentNode.insertBefore(wrapper, media);
                    wrapper.appendChild(media);
                    wrapper.appendChild(overlay);
                    
                    // Ajustar o media para ocupar 100% do wrapper
                    media.style.width = '100%';
                    media.style.height = '100%';
                    media.style.position = 'absolute';
                    media.style.top = '0';
                    media.style.left = '0';
                    
                    console.log('Overlay adicionado para:', media.tagName, 'src:', media.getAttribute('src'));
                } catch (err) {
                    console.error('Erro ao adicionar overlay:', err);
                }
            });
            
            // Configurar drop zone para drag and drop
            setupEditorDropZone();
            
            console.log('Editor inicializado com sucesso - elementos:', editorDoc.querySelectorAll('*').length);
            
            document.getElementById('editorStatus').textContent = 'Pronto';
            document.getElementById('editorStatus').classList.remove('text-yellow-400');
            
        } catch (err) {
            console.error('Erro ao inicializar editor:', err);
        }
    };
}

function handleEditorClick(e) {
    console.log('handleEditorClick chamado', e.type, e.target.tagName);
    
    e.preventDefault();
    e.stopPropagation();
    
    // Limpar seleção anterior
    if (selectedElement) {
        selectedElement.style.outline = '';
        selectedElement.style.boxShadow = '';
    }
    
    let target = e.target;
    if (target.tagName !== 'A' && target.closest && target.closest('a')) {
        target = target.closest('a');
    }
    
    if (!target || !target.tagName || ['HTML', 'BODY'].includes(target.tagName.toUpperCase())) {
        console.log('Elemento ignorado:', target?.tagName);
        return;
    }
    
    selectedElement = target;
    target.style.outline = '3px solid #22c55e';
    target.style.boxShadow = '0 0 0 6px rgba(34, 197, 94, 0.2)';
    
    // Configurar floating editor
    const floatingEditor = document.getElementById('floatingEditor');
    const textGroup = document.getElementById('floatingTextGroup');
    const urlGroup = document.getElementById('floatingUrlGroup');
    const imgGroup = document.getElementById('floatingImgGroup');
    const videoGroup = document.getElementById('floatingVideoGroup');
    const colorGroup = document.getElementById('floatingColorGroup');
    const styleGroup = document.getElementById('floatingStyleGroup');
    const elementType = document.getElementById('floatingElementType');
    
    // Resetar todos os grupos (todos ocultos inicialmente)
    textGroup.classList.add('hidden');
    urlGroup.classList.add('hidden');
    imgGroup.classList.add('hidden');
    videoGroup.classList.add('hidden');
    colorGroup.classList.add('hidden');
    styleGroup.classList.add('hidden');
    
    // Detectar tipo de elemento
    const tagName = target.tagName.toUpperCase();
    let typeLabel = 'Elemento';
    
    if (tagName === 'IMG') {
        typeLabel = '🖼️ Imagem';
        imgGroup.classList.remove('hidden');
        colorGroup.classList.remove('hidden');
        document.getElementById('floatingImgSrc').value = target.getAttribute('src') || '';
        document.getElementById('floatingImgWidth').value = target.style.width || target.getAttribute('width') || '';
        document.getElementById('floatingImgHeight').value = target.style.height || target.getAttribute('height') || '';
        previewImage(target.getAttribute('src'));
    } else if (tagName === 'VIDEO' || tagName === 'IFRAME' || tagName === 'EMBED' || tagName === 'OBJECT' || target.classList.contains('pb-video') || target.getAttribute('data-pb-element') === 'video') {
        typeLabel = '🎬 Vídeo/Mídia';
        videoGroup.classList.remove('hidden');
        colorGroup.classList.remove('hidden');
        
        // Buscar URL do vídeo - verificar se é container pb-video ou elemento direto
        let src = '';
        let videoElement = target;
        
        // Se for um container de vídeo, buscar o iframe dentro
        if (target.classList.contains('pb-video') || target.getAttribute('data-pb-element') === 'video') {
            const iframe = target.querySelector('iframe');
            if (iframe) {
                src = iframe.getAttribute('src') || '';
                videoElement = iframe;
            }
        } else {
            src = target.getAttribute('src') || target.getAttribute('data-src') || '';
        }
        
        // Se for iframe do YouTube/Vimeo, extrair URL original
        if (src.includes('youtube.com/embed/')) {
            const videoId = src.match(/embed\/([^?&]+)/);
            if (videoId) src = `https://www.youtube.com/watch?v=${videoId[1]}`;
        } else if (src.includes('player.vimeo.com/video/')) {
            const videoId = src.match(/video\/(\d+)/);
            if (videoId) src = `https://vimeo.com/${videoId[1]}`;
        }
        
        document.getElementById('floatingVideoSrc').value = src;
        console.log('Vídeo detectado:', tagName, 'src:', src);
    } else if (tagName === 'A') {
        typeLabel = '🔗 Link';
        textGroup.classList.remove('hidden');
        urlGroup.classList.remove('hidden');
        styleGroup.classList.remove('hidden');
        colorGroup.classList.remove('hidden');
        document.getElementById('floatingText').value = target.innerText || '';
        document.getElementById('floatingUrl').value = target.getAttribute('href') || '';
    } else if (['H1','H2','H3','H4','H5','H6'].includes(tagName)) {
        typeLabel = '📝 Título ' + tagName;
        textGroup.classList.remove('hidden');
        styleGroup.classList.remove('hidden');
        colorGroup.classList.remove('hidden');
        document.getElementById('floatingText').value = target.innerText || '';
    } else if (['P','SPAN','DIV','LABEL','LI','TD','TH','STRONG','EM','B','I'].includes(tagName)) {
        typeLabel = '📄 Texto';
        textGroup.classList.remove('hidden');
        styleGroup.classList.remove('hidden');
        colorGroup.classList.remove('hidden');
        document.getElementById('floatingText').value = target.innerText || '';
    } else if (tagName === 'BUTTON') {
        typeLabel = '🔘 Botão';
        textGroup.classList.remove('hidden');
        urlGroup.classList.remove('hidden');
        styleGroup.classList.remove('hidden');
        colorGroup.classList.remove('hidden');
        document.getElementById('floatingText').value = target.innerText || '';
        document.getElementById('floatingUrl').value = target.getAttribute('onclick') || '';
    } else {
        typeLabel = `📦 ${tagName}`;
        textGroup.classList.remove('hidden');
        colorGroup.classList.remove('hidden');
        document.getElementById('floatingText').value = target.innerText || '';
    }
    
    elementType.textContent = typeLabel;
    
    // Preencher cores atuais (usar contexto do iframe)
    const iframeWindow = document.getElementById('editorFrame').contentWindow;
    const computedStyle = iframeWindow.getComputedStyle(target);
    const textColor = rgbToHex(computedStyle.color) || '#000000';
    const bgColor = rgbToHex(computedStyle.backgroundColor) || '#ffffff';
    
    document.getElementById('floatingTextColor').value = textColor;
    document.getElementById('floatingTextColorHex').value = textColor;
    document.getElementById('floatingBgColor').value = bgColor === '#00000000' ? '#ffffff' : bgColor;
    document.getElementById('floatingBgColorHex').value = bgColor === '#00000000' ? '' : bgColor;
    
    // Preencher tamanho da fonte
    document.getElementById('floatingFontSize').value = computedStyle.fontSize || '';
    
    // Debug: mostrar no console o tipo detectado
    console.log('Elemento selecionado:', tagName, typeLabel);
    
    // Posicionar floating editor
    const frame = document.getElementById('editorFrame');
    const frameRect = frame.getBoundingClientRect();
    const targetRect = target.getBoundingClientRect();
    
    let top = frameRect.top + targetRect.bottom + 10;
    let left = frameRect.left + targetRect.left;
    
    if (top + 350 > window.innerHeight) top = frameRect.top + targetRect.top - 350;
    if (left + 400 > window.innerWidth) left = window.innerWidth - 420;
    if (left < 10) left = 10;
    if (top < 10) top = 10;
    
    floatingEditor.style.top = `${top}px`;
    floatingEditor.style.left = `${left}px`;
    floatingEditor.classList.remove('hidden');
    
    lucide.createIcons();
}

// Converter RGB para HEX
function rgbToHex(rgb) {
    if (!rgb || rgb === 'transparent' || rgb === 'rgba(0, 0, 0, 0)') return '#00000000';
    const match = rgb.match(/^rgba?\((\d+),\s*(\d+),\s*(\d+)/);
    if (!match) return rgb;
    return '#' + [match[1], match[2], match[3]].map(x => {
        const hex = parseInt(x).toString(16);
        return hex.length === 1 ? '0' + hex : hex;
    }).join('');
}

// Preview de imagem
function previewImage(url) {
    const preview = document.getElementById('floatingImgPreview');
    const img = document.getElementById('floatingImgPreviewImg');
    if (url && url.startsWith('http')) {
        img.src = url;
        preview.classList.remove('hidden');
    } else {
        preview.classList.add('hidden');
    }
}

// Sincronizar color pickers com inputs hex
document.getElementById('floatingTextColor')?.addEventListener('input', (e) => {
    document.getElementById('floatingTextColorHex').value = e.target.value;
});
document.getElementById('floatingTextColorHex')?.addEventListener('input', (e) => {
    if (/^#[0-9A-Fa-f]{6}$/.test(e.target.value)) {
        document.getElementById('floatingTextColor').value = e.target.value;
    }
});
document.getElementById('floatingBgColor')?.addEventListener('input', (e) => {
    document.getElementById('floatingBgColorHex').value = e.target.value;
});
document.getElementById('floatingBgColorHex')?.addEventListener('input', (e) => {
    if (/^#[0-9A-Fa-f]{6}$/.test(e.target.value)) {
        document.getElementById('floatingBgColor').value = e.target.value;
    }
});

// Toggle estilos de texto
function toggleStyle(style) {
    if (!selectedElement) return;
    switch(style) {
        case 'bold':
            selectedElement.style.fontWeight = selectedElement.style.fontWeight === 'bold' ? 'normal' : 'bold';
            break;
        case 'italic':
            selectedElement.style.fontStyle = selectedElement.style.fontStyle === 'italic' ? 'normal' : 'italic';
            break;
        case 'underline':
            selectedElement.style.textDecoration = selectedElement.style.textDecoration === 'underline' ? 'none' : 'underline';
            break;
    }
}

// Definir alinhamento de texto do elemento
function setElementAlign(align) {
    if (!selectedElement) return;
    selectedElement.style.textAlign = align;
    document.getElementById('editorStatus').textContent = '● Alterações pendentes';
    document.getElementById('editorStatus').classList.add('text-yellow-400');
}

// Centralizar elemento na página (margin auto)
function centerElement() {
    if (!selectedElement) return;
    
    // Aplicar estilos para centralizar o elemento
    selectedElement.style.marginLeft = 'auto';
    selectedElement.style.marginRight = 'auto';
    selectedElement.style.display = 'block';
    
    // Se for imagem ou elemento inline, garantir que fique centralizado
    if (selectedElement.tagName === 'IMG' || selectedElement.tagName === 'A' || selectedElement.tagName === 'BUTTON') {
        selectedElement.style.display = 'block';
    }
    
    // Se for um container de vídeo, centralizar também
    if (selectedElement.classList.contains('pb-video') || selectedElement.getAttribute('data-pb-element') === 'video') {
        selectedElement.style.maxWidth = '100%';
    }
    
    document.getElementById('editorStatus').textContent = '● Alterações pendentes';
    document.getElementById('editorStatus').classList.add('text-yellow-400');
    
    console.log('Elemento centralizado:', selectedElement.tagName);
}

function applyFloatingEdit() {
    if (!selectedElement) return;
    
    const textGroup = document.getElementById('floatingTextGroup');
    const urlGroup = document.getElementById('floatingUrlGroup');
    const imgGroup = document.getElementById('floatingImgGroup');
    const videoGroup = document.getElementById('floatingVideoGroup');
    const colorGroup = document.getElementById('floatingColorGroup');
    const styleGroup = document.getElementById('floatingStyleGroup');
    
    // Aplicar texto
    if (!textGroup.classList.contains('hidden')) {
        selectedElement.innerText = document.getElementById('floatingText').value;
    }
    
    // Aplicar URL do link
    if (!urlGroup.classList.contains('hidden')) {
        const url = document.getElementById('floatingUrl').value;
        if (url) selectedElement.setAttribute('href', url);
    }
    
    // Aplicar imagem
    if (!imgGroup.classList.contains('hidden')) {
        const src = document.getElementById('floatingImgSrc').value;
        const width = document.getElementById('floatingImgWidth').value;
        const height = document.getElementById('floatingImgHeight').value;
        
        if (src) selectedElement.setAttribute('src', src);
        if (width) selectedElement.style.width = width.includes('px') || width.includes('%') ? width : width + 'px';
        if (height) selectedElement.style.height = height.includes('px') || height.includes('%') ? height : height + 'px';
    }
    
    // Aplicar vídeo (YouTube/Vimeo)
    if (!videoGroup.classList.contains('hidden')) {
        const videoUrl = document.getElementById('floatingVideoSrc').value;
        if (videoUrl) {
            const embedUrl = convertToEmbedUrl(videoUrl);
            // Verificar se é container pb-video ou elemento direto
            if (selectedElement.classList.contains('pb-video') || selectedElement.getAttribute('data-pb-element') === 'video') {
                const iframe = selectedElement.querySelector('iframe');
                if (iframe) iframe.setAttribute('src', embedUrl);
            } else if (selectedElement.tagName === 'IFRAME') {
                selectedElement.setAttribute('src', embedUrl);
            } else if (selectedElement.tagName === 'VIDEO') {
                selectedElement.setAttribute('src', videoUrl);
            }
        }
    }
    
    // Aplicar cores (sempre aplicar, independente do grupo estar visível)
    const textColor = document.getElementById('floatingTextColorHex').value;
    const bgColor = document.getElementById('floatingBgColorHex').value;
    
    if (textColor && textColor !== '#00000000' && textColor !== '') {
        selectedElement.style.setProperty('color', textColor, 'important');
        console.log('Cor do texto aplicada:', textColor);
    }
    if (bgColor && bgColor !== '#00000000' && bgColor !== '') {
        selectedElement.style.setProperty('background-color', bgColor, 'important');
        console.log('Cor de fundo aplicada:', bgColor);
    }
    
    // Aplicar tamanho da fonte
    if (!styleGroup.classList.contains('hidden')) {
        const fontSize = document.getElementById('floatingFontSize').value;
        if (fontSize) {
            selectedElement.style.fontSize = fontSize;
        }
    }
    
    document.getElementById('floatingEditor').classList.add('hidden');
    document.getElementById('editorStatus').textContent = '● Alterações pendentes';
    document.getElementById('editorStatus').classList.add('text-yellow-400');
}

// Converter URL do YouTube/Vimeo para embed
function convertToEmbedUrl(url) {
    // YouTube
    let match = url.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([^&\s]+)/);
    if (match) {
        return `https://www.youtube.com/embed/${match[1]}`;
    }
    
    // Vimeo
    match = url.match(/(?:vimeo\.com\/)(\d+)/);
    if (match) {
        return `https://player.vimeo.com/video/${match[1]}`;
    }
    
    return url;
}

function deleteSelectedElement() {
    if (!selectedElement || ['HTML', 'BODY'].includes(selectedElement.tagName)) return;
    
    if (confirm('Excluir este elemento?')) {
        selectedElement.remove();
        document.getElementById('floatingEditor').classList.add('hidden');
        document.getElementById('editorStatus').textContent = '● Alterações pendentes';
    }
}

function getCleanHtml() {
    if (!editorDoc) return '';
    
    const clone = editorDoc.documentElement.cloneNode(true);
    
    // Remover estilos do editor
    clone.querySelectorAll('style').forEach(s => {
        if (s.id === 'editor-styles' || s.textContent.includes('cursor: pointer')) s.remove();
    });
    
    // Remover wrappers de mídia (editor-media-wrapper) e overlays
    clone.querySelectorAll('.editor-media-wrapper').forEach(wrapper => {
        const media = wrapper.querySelector('iframe, video, embed, object');
        if (media && wrapper.parentNode) {
            // Limpar estilos que adicionamos ao media
            media.style.removeProperty('width');
            media.style.removeProperty('height');
            media.style.removeProperty('position');
            media.style.removeProperty('top');
            media.style.removeProperty('left');
            if (!media.getAttribute('style') || media.getAttribute('style').trim() === '') {
                media.removeAttribute('style');
            }
            wrapper.parentNode.insertBefore(media, wrapper);
            wrapper.remove();
        }
    });
    
    // Remover overlays órfãos
    clone.querySelectorAll('.editor-media-overlay').forEach(el => el.remove());
    
    // Remover placeholders das colunas (textos "Coluna 1", "Coluna 2", etc)
    clone.querySelectorAll('.pb-placeholder').forEach(el => el.remove());
    
    // Limpar estilos de edição das colunas (borda tracejada, fundo cinza)
    clone.querySelectorAll('.pb-column').forEach(col => {
        col.style.removeProperty('background');
        col.style.removeProperty('border');
        // Manter apenas flex e padding
        if (col.style.length === 0) {
            col.removeAttribute('style');
        }
    });
    
    // Limpar estilos de edição das rows
    clone.querySelectorAll('.pb-row').forEach(row => {
        // Manter display flex e gap, remover padding de edição se necessário
    });
    
    // Remover contenteditable
    clone.querySelectorAll('[contenteditable]').forEach(el => el.removeAttribute('contenteditable'));
    
    // Limpar outlines e atributos do editor
    clone.querySelectorAll('*').forEach(el => {
        // Remover outlines
        if (el.style.outline) {
            el.style.outline = '';
            el.style.boxShadow = '';
        }
        
        // Remover atributos data- do editor (medium-editor, slider, etc)
        const attrsToRemove = [];
        for (let attr of el.attributes) {
            if (attr.name.startsWith('data-medium-editor') || 
                attr.name.startsWith('data-placeholder') ||
                attr.name.startsWith('data-originalpos') ||
                attr.name.startsWith('data-sliderseo') ||
                attr.name.startsWith('data-height') ||
                attr.name.startsWith('data-width') ||
                attr.name.startsWith('data-aos') ||
                attr.name.startsWith('data-swiper') ||
                attr.name.startsWith('data-slick') ||
                attr.name === 'medium-editor-index' ||
                attr.name === 'data-gramm' ||
                attr.name === 'data-gramm_editor' ||
                attr.name === 'data-enable-grammarly' ||
                attr.name === 'role' ||
                attr.name === 'aria-multiline' ||
                attr.name === 'spellcheck' ||
                attr.name === 'contenteditable') {
                attrsToRemove.push(attr.name);
            }
        }
        attrsToRemove.forEach(attr => el.removeAttribute(attr));
        
        // Limpar style vazio
        if (!el.getAttribute('style') || el.getAttribute('style').trim() === '') {
            el.removeAttribute('style');
        }
    });
    
    // Adicionar estilos responsivos para a página publicada
    const responsiveStyle = clone.querySelector('head') ? clone.querySelector('head') : clone.querySelector('body');
    if (responsiveStyle) {
        const styleTag = clone.ownerDocument.createElement('style');
        styleTag.id = 'pb-responsive-styles';
        styleTag.textContent = `
            /* Page Builder - Estilos Responsivos */
            * { box-sizing: border-box; }
            .pb-row { display: flex; flex-wrap: wrap; width: 100%; }
            .pb-column { box-sizing: border-box; }
            .pb-video, .pb-image, img, iframe, video { max-width: 100%; }
            
            /* Tablet: 768px ou menos */
            @media (max-width: 768px) {
                .pb-row { gap: 15px !important; }
                .pb-column { 
                    flex: 1 1 calc(50% - 8px) !important; 
                    min-width: calc(50% - 8px) !important;
                }
            }
            
            /* Mobile: 480px ou menos */
            @media (max-width: 480px) {
                .pb-row { 
                    flex-direction: column !important; 
                    gap: 10px !important; 
                }
                .pb-column { 
                    flex: 1 1 100% !important; 
                    min-width: 100% !important;
                    width: 100% !important;
                }
            }
        `;
        responsiveStyle.appendChild(styleTag);
    }
    
    return '<!DOCTYPE html>\n' + clone.outerHTML;
}

async function savePage() {
    if (!currentPageId) {
        alert('Nenhuma página selecionada');
        return;
    }
    
    const htmlContent = getCleanHtml();
    if (!htmlContent || htmlContent.length < 50) {
        alert('Conteúdo HTML inválido ou vazio');
        return;
    }
    
    document.getElementById('editorStatus').textContent = 'Salvando...';
    document.getElementById('editorStatus').classList.add('text-yellow-400');
    
    try {
        // Codificar HTML em base64 para evitar bloqueio do ModSecurity
        const htmlBase64 = btoa(unescape(encodeURIComponent(htmlContent)));
        
        const response = await fetch(`${API}?action=save_page`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'same-origin',
            body: JSON.stringify({
                page_id: currentPageId,
                html_base64: htmlBase64
            })
        });
        
        const text = await response.text();
        let data;
        try {
            data = JSON.parse(text);
        } catch (e) {
            console.error('JSON Parse Error:', text);
            throw new Error('Resposta inválida: ' + text.substring(0, 200));
        }
        
        if (data.success) {
            document.getElementById('editorStatus').textContent = 'Salvo!';
            document.getElementById('editorStatus').classList.remove('text-yellow-400');
            document.getElementById('editorStatus').classList.add('text-green-400');
            setTimeout(() => {
                document.getElementById('editorStatus').textContent = 'Pronto';
                document.getElementById('editorStatus').classList.remove('text-green-400');
            }, 2000);
        } else {
            document.getElementById('editorStatus').textContent = 'Erro ao salvar';
            document.getElementById('editorStatus').classList.add('text-red-400');
            alert(data.error || 'Erro ao salvar página');
        }
    } catch (error) {
        console.error('Save error:', error);
        document.getElementById('editorStatus').textContent = 'Erro de conexão';
        document.getElementById('editorStatus').classList.add('text-red-400');
        alert('Erro de conexão ao salvar. Verifique sua internet.');
    }
}

// ============================================
// NOVA PÁGINA
// ============================================

function openNewPageModal() {
    document.getElementById('newPageModal').classList.remove('hidden');
    document.getElementById('newPageName').value = '';
    lucide.createIcons();
}

function closeNewPageModal() {
    document.getElementById('newPageModal').classList.add('hidden');
}

document.getElementById('newPageForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const name = document.getElementById('newPageName').value.trim();
    if (!name) return;
    
    const data = await apiCall('create_page', {
        funnel_id: currentFunnelId,
        name: name
    }, 'POST');
    
    if (data.success) {
        closeNewPageModal();
        
        // Recarregar páginas
        const funnelData = await apiCall('get_funnel', { funnel_id: currentFunnelId });
        if (funnelData.success) {
            funnelPages = funnelData.pages;
            updatePageSelector();
            loadPageInEditor(data.page_id);
        }
        
        loadStats();
    } else {
        alert(data.error || 'Erro ao criar página');
    }
});

// ============================================
// CLONAR URL
// ============================================

function openCloneUrlModal() {
    document.getElementById('cloneUrlModal').classList.remove('hidden');
    document.getElementById('cloneUrl').value = '';
    lucide.createIcons();
}

function closeCloneUrlModal() {
    document.getElementById('cloneUrlModal').classList.add('hidden');
}

document.getElementById('cloneUrlForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const url = document.getElementById('cloneUrl').value.trim();
    if (!url || !currentPageId) return;
    
    const btn = e.target.querySelector('button[type="submit"]');
    btn.disabled = true;
    btn.textContent = 'Clonando...';
    
    const data = await apiCall('clone_url', {
        page_id: currentPageId,
        url: url
    }, 'POST');
    
    btn.disabled = false;
    btn.textContent = 'Clonar';
    
    if (data.success) {
        closeCloneUrlModal();
        initEditor(data.html_content);
        document.getElementById('editorStatus').textContent = '● Alterações pendentes';
    } else {
        alert(data.error || 'Erro ao clonar');
    }
});

// ============================================
// PUBLICAR FUNIL
// ============================================

async function publishFunnel() {
    if (!currentFunnelId) return;
    
    // Primeiro salvar a página atual
    if (currentPageId && editorDoc) {
        document.getElementById('editorStatus').textContent = 'Salvando...';
        const htmlContent = getCleanHtml();
        const htmlBase64 = btoa(unescape(encodeURIComponent(htmlContent)));
        await apiCall('save_page', {
            page_id: currentPageId,
            html_base64: htmlBase64
        }, 'POST');
    }
    
    const newStatus = currentFunnelStatus === 'published' ? 'draft' : 'published';
    const btnText = document.getElementById('btnPublishText');
    btnText.textContent = 'Publicando...';
    
    const data = await apiCall('toggle_funnel_status', {
        funnel_id: currentFunnelId,
        status: newStatus
    }, 'POST');
    
    if (data.success) {
        currentFunnelStatus = newStatus;
        updatePublishButton();
        document.getElementById('editorStatus').textContent = newStatus === 'published' ? 'Publicado!' : 'Despublicado';
        loadStats();
        loadFunnels();
        
        if (newStatus === 'published') {
            const subdomain = document.getElementById('editorSubdomain').textContent;
            alert(`Funil publicado com sucesso!\n\nAcesse: ${subdomain}`);
        }
    } else {
        btnText.textContent = currentFunnelStatus === 'published' ? 'Despublicar' : 'Publicar';
        alert(data.error || 'Erro ao publicar');
    }
}

function updatePublishButton() {
    const btn = document.getElementById('btnPublish');
    const btnText = document.getElementById('btnPublishText');
    
    if (currentFunnelStatus === 'published') {
        btn.className = 'flex items-center gap-2 px-5 py-2 bg-yellow-600 hover:bg-yellow-700 text-white font-semibold rounded-lg transition-colors';
        btnText.textContent = 'Despublicar';
    } else {
        btn.className = 'flex items-center gap-2 px-5 py-2 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg transition-colors';
        btnText.textContent = 'Publicar';
    }
    lucide.createIcons();
}

// Definir viewport responsivo (Desktop, Tablet, Mobile)
let currentViewport = 'desktop';

function setViewport(viewport) {
    currentViewport = viewport;
    const frame = document.getElementById('editorFrame');
    const container = document.getElementById('editorContainer');
    
    // Resetar estilos
    const btnDesktop = document.getElementById('btnDesktop');
    const btnTablet = document.getElementById('btnTablet');
    const btnMobile = document.getElementById('btnMobile');
    
    [btnDesktop, btnTablet, btnMobile].forEach(btn => {
        btn.className = 'p-2 rounded text-gray-400 hover:text-white hover:bg-gray-600';
    });
    
    // Aplicar viewport selecionado
    switch(viewport) {
        case 'desktop':
            btnDesktop.className = 'p-2 rounded text-white bg-gray-600';
            container.style.maxWidth = '100%';
            container.style.margin = '0';
            container.style.boxShadow = 'none';
            frame.style.width = '100%';
            break;
        case 'tablet':
            btnTablet.className = 'p-2 rounded text-white bg-gray-600';
            container.style.maxWidth = '768px';
            container.style.margin = '0 auto';
            container.style.boxShadow = '0 0 20px rgba(0,0,0,0.3)';
            frame.style.width = '100%';
            break;
        case 'mobile':
            btnMobile.className = 'p-2 rounded text-white bg-gray-600';
            container.style.maxWidth = '375px';
            container.style.margin = '0 auto';
            container.style.boxShadow = '0 0 20px rgba(0,0,0,0.3)';
            frame.style.width = '100%';
            break;
    }
    
    lucide.createIcons();
}

// Fechar floating editor ao clicar fora
document.addEventListener('click', (e) => {
    const floating = document.getElementById('floatingEditor');
    const frame = document.getElementById('editorFrame');
    if (!floating.contains(e.target) && e.target !== frame) {
        floating.classList.add('hidden');
    }
});

// ============================================
// ARRASTAR EDITOR FLUTUANTE
// ============================================
(function() {
    const floatingEditor = document.getElementById('floatingEditor');
    const header = document.getElementById('floatingEditorHeader');
    
    let isDragging = false;
    let offsetX = 0;
    let offsetY = 0;
    
    header.addEventListener('mousedown', (e) => {
        // Ignorar clique no botão de fechar
        if (e.target.closest('button')) return;
        
        isDragging = true;
        offsetX = e.clientX - floatingEditor.offsetLeft;
        offsetY = e.clientY - floatingEditor.offsetTop;
        
        floatingEditor.style.transition = 'none';
        header.style.cursor = 'grabbing';
    });
    
    document.addEventListener('mousemove', (e) => {
        if (!isDragging) return;
        
        e.preventDefault();
        
        let newX = e.clientX - offsetX;
        let newY = e.clientY - offsetY;
        
        // Limitar dentro da tela
        const maxX = window.innerWidth - floatingEditor.offsetWidth - 10;
        const maxY = window.innerHeight - floatingEditor.offsetHeight - 10;
        
        newX = Math.max(10, Math.min(newX, maxX));
        newY = Math.max(10, Math.min(newY, maxY));
        
        floatingEditor.style.left = newX + 'px';
        floatingEditor.style.top = newY + 'px';
    });
    
    document.addEventListener('mouseup', () => {
        if (isDragging) {
            isDragging = false;
            floatingEditor.style.transition = '';
            header.style.cursor = 'move';
        }
    });
    
    // Suporte para touch (mobile/tablet)
    header.addEventListener('touchstart', (e) => {
        if (e.target.closest('button')) return;
        
        isDragging = true;
        const touch = e.touches[0];
        offsetX = touch.clientX - floatingEditor.offsetLeft;
        offsetY = touch.clientY - floatingEditor.offsetTop;
    }, { passive: true });
    
    document.addEventListener('touchmove', (e) => {
        if (!isDragging) return;
        
        const touch = e.touches[0];
        let newX = touch.clientX - offsetX;
        let newY = touch.clientY - offsetY;
        
        const maxX = window.innerWidth - floatingEditor.offsetWidth - 10;
        const maxY = window.innerHeight - floatingEditor.offsetHeight - 10;
        
        newX = Math.max(10, Math.min(newX, maxX));
        newY = Math.max(10, Math.min(newY, maxY));
        
        floatingEditor.style.left = newX + 'px';
        floatingEditor.style.top = newY + 'px';
    }, { passive: true });
    
    document.addEventListener('touchend', () => {
        isDragging = false;
    });
})();

// ============================================
// SISTEMA DE DRAG AND DROP
// ============================================
let draggedBlockType = null;
let dropIndicator = null;

// Templates HTML para cada tipo de bloco
const blockTemplates = {
    // 1 Coluna
    'row-1': `<div class="pb-row" style="display: flex; flex-wrap: wrap; gap: 20px; padding: 10px; width: 100%; box-sizing: border-box;">
        <div class="pb-column" style="flex: 1 1 100%; min-width: 0; padding: 15px; background: #f9fafb; border: 1px dashed #e5e7eb; box-sizing: border-box;">
            <p class="pb-placeholder" style="color: #999; text-align: center;">Arraste elementos aqui</p>
        </div>
    </div>`,
    
    // 2 Colunas
    'row-2': `<div class="pb-row" style="display: flex; flex-wrap: wrap; gap: 20px; padding: 10px; width: 100%; box-sizing: border-box;">
        <div class="pb-column" style="flex: 1 1 calc(50% - 10px); min-width: 0; padding: 15px; background: #f9fafb; border: 1px dashed #e5e7eb; box-sizing: border-box;">
            <p class="pb-placeholder" style="color: #999; text-align: center;">Coluna 1</p>
        </div>
        <div class="pb-column" style="flex: 1 1 calc(50% - 10px); min-width: 0; padding: 15px; background: #f9fafb; border: 1px dashed #e5e7eb; box-sizing: border-box;">
            <p class="pb-placeholder" style="color: #999; text-align: center;">Coluna 2</p>
        </div>
    </div>`,
    
    // 3 Colunas
    'row-3': `<div class="pb-row" style="display: flex; flex-wrap: wrap; gap: 20px; padding: 10px; width: 100%; box-sizing: border-box;">
        <div class="pb-column" style="flex: 1 1 calc(33.333% - 14px); min-width: 0; padding: 15px; background: #f9fafb; border: 1px dashed #e5e7eb; box-sizing: border-box;">
            <p class="pb-placeholder" style="color: #999; text-align: center;">Coluna 1</p>
        </div>
        <div class="pb-column" style="flex: 1 1 calc(33.333% - 14px); min-width: 0; padding: 15px; background: #f9fafb; border: 1px dashed #e5e7eb; box-sizing: border-box;">
            <p class="pb-placeholder" style="color: #999; text-align: center;">Coluna 2</p>
        </div>
        <div class="pb-column" style="flex: 1 1 calc(33.333% - 14px); min-width: 0; padding: 15px; background: #f9fafb; border: 1px dashed #e5e7eb; box-sizing: border-box;">
            <p class="pb-placeholder" style="color: #999; text-align: center;">Coluna 3</p>
        </div>
    </div>`,
    
    // 4 Colunas
    'row-4': `<div class="pb-row" style="display: flex; flex-wrap: wrap; gap: 20px; padding: 10px; width: 100%; box-sizing: border-box;">
        <div class="pb-column" style="flex: 1 1 calc(25% - 15px); min-width: 0; padding: 15px; background: #f9fafb; border: 1px dashed #e5e7eb; box-sizing: border-box;">
            <p class="pb-placeholder" style="color: #999; text-align: center;">Col 1</p>
        </div>
        <div class="pb-column" style="flex: 1 1 calc(25% - 15px); min-width: 0; padding: 15px; background: #f9fafb; border: 1px dashed #e5e7eb; box-sizing: border-box;">
            <p class="pb-placeholder" style="color: #999; text-align: center;">Col 2</p>
        </div>
        <div class="pb-column" style="flex: 1 1 calc(25% - 15px); min-width: 0; padding: 15px; background: #f9fafb; border: 1px dashed #e5e7eb; box-sizing: border-box;">
            <p class="pb-placeholder" style="color: #999; text-align: center;">Col 3</p>
        </div>
        <div class="pb-column" style="flex: 1 1 calc(25% - 15px); min-width: 0; padding: 15px; background: #f9fafb; border: 1px dashed #e5e7eb; box-sizing: border-box;">
            <p class="pb-placeholder" style="color: #999; text-align: center;">Col 4</p>
        </div>
    </div>`,
    
    heading: `<h2 class="pb-heading" style="font-size: 32px; font-weight: bold; margin: 0; padding: 10px 0;">Título Principal</h2>`,
    
    text: `<p class="pb-text" style="font-size: 16px; line-height: 1.6; margin: 0; padding: 10px 0;">Digite seu texto aqui. Clique para editar o conteúdo deste parágrafo.</p>`,
    
    image: `<img class="pb-image" src="https://via.placeholder.com/600x400?text=Imagem" alt="Imagem" style="max-width: 100%; height: auto; display: block;">`,
    
    button: `<a class="pb-button" href="#" style="display: inline-block; padding: 12px 24px; background: #3b82f6; color: white; text-decoration: none; border-radius: 6px; font-weight: 600;">Clique Aqui</a>`,
    
    video: `<div class="pb-video" data-pb-element="video" style="position: relative; width: 100%; padding-bottom: 56.25%; height: 0; overflow: hidden;">
        <iframe src="https://www.youtube.com/embed/dQw4w9WgXcQ" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: 0;" allowfullscreen></iframe>
    </div>`,
    
    divider: `<hr class="pb-divider" style="border: none; border-top: 1px solid #e5e7eb; margin: 20px 0;">`,
    
    spacer: `<div class="pb-spacer" style="height: 50px;"></div>`,
    
    icon: `<div class="pb-icon" style="text-align: center; padding: 10px;">
        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
    </div>`,
    
    list: `<ul class="pb-list" style="padding-left: 20px; margin: 10px 0;">
        <li style="margin-bottom: 8px;">Item da lista 1</li>
        <li style="margin-bottom: 8px;">Item da lista 2</li>
        <li style="margin-bottom: 8px;">Item da lista 3</li>
    </ul>`,
    
    form: `<form class="pb-form" style="padding: 20px; background: #f9fafb; border-radius: 8px;">
        <div style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px; font-weight: 500;">Nome</label>
            <input type="text" placeholder="Seu nome" style="width: 100%; padding: 10px; border: 1px solid #e5e7eb; border-radius: 6px;">
        </div>
        <div style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px; font-weight: 500;">E-mail</label>
            <input type="email" placeholder="seu@email.com" style="width: 100%; padding: 10px; border: 1px solid #e5e7eb; border-radius: 6px;">
        </div>
        <button type="submit" style="width: 100%; padding: 12px; background: #3b82f6; color: white; border: none; border-radius: 6px; font-weight: 600; cursor: pointer;">Enviar</button>
    </form>`,
    
    input: `<div class="pb-input" style="margin-bottom: 15px;">
        <label style="display: block; margin-bottom: 5px; font-weight: 500;">Campo</label>
        <input type="text" placeholder="Digite aqui..." style="width: 100%; padding: 10px; border: 1px solid #e5e7eb; border-radius: 6px;">
    </div>`,
    
    textarea: `<div class="pb-textarea" style="margin-bottom: 15px;">
        <label style="display: block; margin-bottom: 5px; font-weight: 500;">Mensagem</label>
        <textarea placeholder="Digite sua mensagem..." rows="4" style="width: 100%; padding: 10px; border: 1px solid #e5e7eb; border-radius: 6px; resize: vertical;"></textarea>
    </div>`
};

// Inicializar drag and drop dos blocos
function initDragAndDrop() {
    // Selecionar tanto .drag-block quanto .element-block
    const blocks = document.querySelectorAll('.drag-block, .element-block');
    
    blocks.forEach(block => {
        block.addEventListener('dragstart', (e) => {
            draggedBlockType = block.dataset.type;
            block.classList.add('dragging');
            e.dataTransfer.effectAllowed = 'copy';
            e.dataTransfer.setData('text/plain', block.dataset.type);
            console.log('Arrastando bloco:', draggedBlockType);
        });
        
        block.addEventListener('dragend', () => {
            block.classList.remove('dragging');
            draggedBlockType = null;
            removeDropIndicators();
        });
    });
    
    // Adicionar busca de elementos
    const searchInput = document.getElementById('searchElements');
    if (searchInput) {
        searchInput.addEventListener('input', (e) => {
            const query = e.target.value.toLowerCase();
            document.querySelectorAll('.element-block').forEach(block => {
                const text = block.textContent.toLowerCase();
                block.style.display = text.includes(query) ? '' : 'none';
            });
        });
    }
}

// Configurar drop zone no iframe do editor
function setupEditorDropZone() {
    if (!editorDoc || !editorDoc.body) return;
    
    // Adicionar estilos de drop zone no iframe
    const dropStyles = editorDoc.createElement('style');
    dropStyles.id = 'drop-zone-styles';
    dropStyles.textContent = `
        .drop-zone-hover { outline: 2px dashed #3b82f6 !important; outline-offset: -2px; background: rgba(59, 130, 246, 0.05) !important; }
        .drop-indicator { height: 4px; background: #3b82f6; border-radius: 2px; margin: 4px 0; pointer-events: none; }
        .pb-section, .pb-container, .pb-row, .pb-column { min-height: 50px; transition: outline 0.2s; }
        .pb-section:empty::before, .pb-container:empty::before, .pb-row:empty::before, .pb-column:empty::before {
            content: 'Arraste elementos aqui';
            display: block;
            padding: 20px;
            text-align: center;
            color: #9ca3af;
            font-size: 14px;
        }
    `;
    if (editorDoc.head) editorDoc.head.appendChild(dropStyles);
    
    // Eventos de drag no body do editor
    editorDoc.body.addEventListener('dragover', handleDragOver);
    editorDoc.body.addEventListener('dragleave', handleDragLeave);
    editorDoc.body.addEventListener('drop', handleDrop);
    
    console.log('Drop zone configurada no editor');
}

function handleDragOver(e) {
    e.preventDefault();
    e.dataTransfer.dropEffect = 'copy';
    
    const target = getDropTarget(e.target);
    if (target) {
        // Remover hover de outros elementos
        editorDoc.querySelectorAll('.drop-zone-hover').forEach(el => el.classList.remove('drop-zone-hover'));
        target.classList.add('drop-zone-hover');
    }
}

function handleDragLeave(e) {
    const target = e.target;
    if (target && target.classList) {
        target.classList.remove('drop-zone-hover');
    }
}

function handleDrop(e) {
    e.preventDefault();
    e.stopPropagation();
    
    // Remover indicadores visuais
    editorDoc.querySelectorAll('.drop-zone-hover').forEach(el => el.classList.remove('drop-zone-hover'));
    
    if (!draggedBlockType) {
        draggedBlockType = e.dataTransfer.getData('text/plain');
    }
    
    if (!draggedBlockType || !blockTemplates[draggedBlockType]) {
        console.log('Tipo de bloco inválido:', draggedBlockType);
        return;
    }
    
    const target = getDropTarget(e.target);
    const template = blockTemplates[draggedBlockType];
    
    // Criar elemento temporário para converter HTML em elemento DOM
    const temp = editorDoc.createElement('div');
    temp.innerHTML = template;
    const newElement = temp.firstElementChild;
    
    // Adicionar atributo para identificar elementos do page builder
    newElement.setAttribute('data-pb-element', draggedBlockType);
    
    // Inserir no local correto
    if (target && target !== editorDoc.body) {
        // Se o target é um container de estrutura, inserir dentro
        if (isStructureElement(target)) {
            // Remover placeholder text se existir
            const placeholder = target.querySelector('p[style*="color: #999"]');
            if (placeholder) placeholder.remove();
            target.appendChild(newElement);
        } else {
            // Inserir após o elemento
            target.parentNode.insertBefore(newElement, target.nextSibling);
        }
    } else {
        // Inserir no body
        editorDoc.body.appendChild(newElement);
    }
    
    // Adicionar evento de clique no novo elemento
    newElement.addEventListener('click', handleEditorClick);
    
    // Marcar como alterado
    document.getElementById('editorStatus').textContent = '● Alterações pendentes';
    document.getElementById('editorStatus').classList.add('text-yellow-400');
    
    console.log('Bloco inserido:', draggedBlockType);
    
    // Selecionar o novo elemento
    setTimeout(() => {
        const fakeEvent = { target: newElement, preventDefault: () => {}, stopPropagation: () => {}, type: 'click' };
        handleEditorClick(fakeEvent);
    }, 100);
}

function getDropTarget(element) {
    if (!element || element === editorDoc.body || element === editorDoc.documentElement) {
        return editorDoc.body;
    }
    
    // Se é um elemento de estrutura, retornar ele mesmo
    if (isStructureElement(element)) {
        return element;
    }
    
    // Procurar o elemento pai mais próximo que seja de estrutura
    let parent = element.parentElement;
    while (parent && parent !== editorDoc.body) {
        if (isStructureElement(parent)) {
            return parent;
        }
        parent = parent.parentElement;
    }
    
    return element;
}

function isStructureElement(element) {
    if (!element || !element.classList) return false;
    return element.classList.contains('pb-section') || 
           element.classList.contains('pb-container') || 
           element.classList.contains('pb-row') || 
           element.classList.contains('pb-column') ||
           element.tagName === 'SECTION' ||
           element.tagName === 'DIV';
}

function removeDropIndicators() {
    if (editorDoc) {
        editorDoc.querySelectorAll('.drop-zone-hover').forEach(el => el.classList.remove('drop-zone-hover'));
        editorDoc.querySelectorAll('.drop-indicator').forEach(el => el.remove());
    }
}

// Inicializar drag and drop quando a página carregar
document.addEventListener('DOMContentLoaded', () => {
    initDragAndDrop();
    lucide.createIcons();
});
</script>
