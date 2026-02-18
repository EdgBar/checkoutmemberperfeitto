# Backup e reversão do Dark/Light Mode

Este documento descreve o estado **anterior** à implementação do modo claro/escuro e como **desfazer** a alteração, voltando ao tema escuro fixo.

---

## Estado original (antes do dark mode)

### 1. Body (index.php e admin.php)

- **index.php** (linha do `<body>`):
  ```html
  <body class="font-sans flex flex-col min-h-screen" style="background-color: #07090d;">
  ```
- **admin.php** (linha do `<body>`):
  ```html
  <body class="font-sans flex flex-col min-h-screen" style="background-color: #07090d;">
  ```

Ou seja: fundo fixo `#07090d` (escuro), sem atributo `data-theme` no `<html>`.

### 2. style.css

- Havia apenas variáveis em `:root` (tema escuro). **Não** existia o bloco `[data-theme="light"]` nem as regras `.theme-toggle-btn`.
- O arquivo **style.css** não tinha:
  - Variáveis para tema claro
  - Estilos para `[data-theme="light"]`
  - Classe do botão de toggle

### 3. Header (index.php e admin.php)

- No header **não** existia o botão "Darkmode" (ícone lua/sol) no canto superior direito.
- Controles do header: apenas notificação + perfil (index) ou apenas logout (admin).

### 4. Configurações (admin)

- Na página **Configurações** (admin_configuracoes) **não** existia a seção "Tema (modo claro/escuro)" nem o campo para salvar `tema_padrao` no banco.

### 5. Script de tema

- **Não** existia script que:
  - Define `data-theme` em `document.documentElement` com base em `localStorage` ou configuração do sistema.
  - Troca o tema ao clicar no botão.

---

## Como desfazer (reverter para tema escuro fixo)

Siga estes passos para voltar ao comportamento anterior.

1. **Remover o script de tema no `<head>`**
   - Em **index.php**: apague o bloco `<script>` que contém `document.documentElement.setAttribute('data-theme'` (logo após `load_settings.php`).
   - Em **admin.php**: idem.

2. **Restaurar o `<body>`**
   - Em **index.php** e **admin.php**: no `<body>`, remova qualquer `id` ou classe relacionada a tema e deixe apenas:
     ```html
     <body class="font-sans flex flex-col min-h-screen" style="background-color: #07090d;">
     ```

3. **Remover o botão Darkmode do header**
   - Em **index.php**: apague o elemento do botão de toggle (o que chama `themeToggle()` ou tem `id="theme-toggle"`), mantendo apenas o sininho e o dropdown de perfil.
   - Em **admin.php**: apague o mesmo botão, mantendo apenas o link de logout.

4. **Remover o bloco de tema em style.css**
   - Apague todo o bloco `[data-theme="light"] { ... }` e as regras associadas ao tema claro (por exemplo `.theme-toggle-btn`), se existirem.
   - Opcional: se tiver criado um arquivo separado (ex.: `theme-toggle.js`), remova a inclusão dele nas páginas.

5. **Remover a seção "Tema" em Configurações**
   - Em **views/admin/admin_configuracoes.php**: apague a seção "Tema (modo claro/escuro)" e o script que salva `tema_padrao` (chamada à API ou formulário).
   - Opcional no banco: pode deixar a chave `tema_padrao` em `configuracoes_sistema`; ela não afetará nada se o script e o botão forem removidos.

6. **Remover endpoint de salvamento do tema (se houver)**
   - Se foi criado um endpoint (ex.: em `api/admin_api.php`) que grava `tema_padrao`, remova ou ignore essa parte.

Após isso, o sistema voltará a exibir apenas o tema escuro, sem botão de alternância e sem uso de `data-theme` ou `localStorage` para tema.

---

## Arquivos modificados nesta implementação

- **config/config.php** — Licença comentada; credenciais do banco atualizadas.
- **style.css** — Variáveis `[data-theme="light"]`, `.theme-toggle-btn`, `body { background-color: var(--dark-base); }`, overrides para tema claro.
- **index.php** — Script de tema no `<head>`, `<body style="background-color: var(--dark-base);">`, botão `#theme-toggle`, script de toggle.
- **admin.php** — Mesmo script no `<head>`, body com `var(--dark-base)`, botão `#theme-toggle`, script de toggle.
- **views/admin/admin_configuracoes.php** — Seção "Tema (modo claro/escuro)", select `#tema_padrao`, botão "Salvar Tema", `loadSettings` e handler de save.
- **api/admin_api.php** — `get_system_settings` retorna `tema_padrao`; `save_system_settings` aceita e grava `tema_padrao`.

## Data da implementação

Implementação do dark/light mode registrada neste backup para permitir reversão futura.
