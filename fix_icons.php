<?php
/**
 * Diagnóstico e correção de ícones Lucide
 * @author Souza Tech - https://rafaelsouzatech.com.br
 */
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Diagnóstico de Ícones</title>
    <style>
        body { font-family: Arial; background: #1a1a2e; color: #fff; padding: 20px; }
        .ok { color: #22c55e; }
        .error { color: #ef4444; }
        .warn { color: #f59e0b; }
        pre { background: #0a0a1a; padding: 10px; border-radius: 5px; }
    </style>
</head>
<body>
    <h1>🔍 Diagnóstico de Ícones Lucide</h1>
    
    <h2>1. Verificando arquivos:</h2>
    <?php
    $files_to_check = [
        'assets/js/lucide.min.js',
        'assets/js/lucide.js',
        'assets/lucide.min.js',
        'js/lucide.min.js'
    ];
    
    $found = false;
    foreach ($files_to_check as $file) {
        $path = __DIR__ . '/' . $file;
        if (file_exists($path)) {
            echo "<p class='ok'>✓ Encontrado: $file (" . round(filesize($path)/1024, 2) . " KB)</p>";
            $found = true;
        }
    }
    
    if (!$found) {
        echo "<p class='error'>✗ Nenhum arquivo lucide.js encontrado!</p>";
        echo "<p class='warn'>⚠ Você precisa copiar a pasta 'assets' completa para o novo domínio.</p>";
    }
    ?>
    
    <h2>2. Teste de carregamento:</h2>
    <p>Ícones de teste (devem aparecer como símbolos, não texto):</p>
    <div style="font-size: 24px; display: flex; gap: 20px; margin: 20px 0;">
        <span><i data-lucide="home"></i> Home</span>
        <span><i data-lucide="settings"></i> Settings</span>
        <span><i data-lucide="bell"></i> Bell</span>
        <span><i data-lucide="user"></i> User</span>
    </div>
    
    <h2>3. Solução:</h2>
    <p>Se os ícones acima aparecem como texto vazio, faça:</p>
    <ol>
        <li>Copie a pasta <code>assets/</code> completa do projeto original</li>
        <li>Verifique se o arquivo <code>assets/js/lucide.min.js</code> existe</li>
        <li>Limpe o cache do navegador (Ctrl+Shift+R)</li>
    </ol>
    
    <h2>4. Estrutura de pastas necessária:</h2>
    <pre>
/seu-dominio/
├── assets/
│   ├── js/
│   │   └── lucide.min.js  ← NECESSÁRIO
│   ├── css/
│   └── sounds/
├── config/
├── api/
└── index.php
    </pre>
    
    <!-- Tentar carregar Lucide de CDN como fallback -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
        // Tentar inicializar
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
            document.write('<p class="ok">✓ Lucide carregado via CDN!</p>');
        } else {
            document.write('<p class="error">✗ Lucide não carregou</p>');
        }
    </script>
    
    <h2>5. Correção rápida (adicionar ao index.php):</h2>
    <p>Se não conseguir copiar os arquivos, adicione esta linha no <code>&lt;head&gt;</code> do index.php:</p>
    <pre>&lt;script src="https://unpkg.com/lucide@latest"&gt;&lt;/script&gt;</pre>
    
</body>
</html>
