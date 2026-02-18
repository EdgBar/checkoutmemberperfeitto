-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Tempo de geração: 24/01/2026 às 18:42
-- Versão do servidor: 11.8.3-MariaDB-log
-- Versão do PHP: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `u733823283_v3`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `alunos`
--

CREATE TABLE `alunos` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `curso_id` int(11) NOT NULL,
  `produto_id` int(11) DEFAULT NULL,
  `venda_id` int(11) DEFAULT NULL,
  `nome` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `cpf` varchar(14) DEFAULT NULL,
  `senha` varchar(255) DEFAULT NULL,
  `status` enum('ativo','inativo','pendente','cancelado') DEFAULT 'ativo',
  `data_inscricao` datetime DEFAULT current_timestamp(),
  `data_expiracao` date DEFAULT NULL,
  `ultimo_acesso` datetime DEFAULT NULL,
  `password_setup_token` varchar(100) DEFAULT NULL,
  `password_setup_expires` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `alunos_acessos`
--

CREATE TABLE `alunos_acessos` (
  `id` int(11) NOT NULL,
  `aluno_email` varchar(255) NOT NULL,
  `produto_id` int(11) NOT NULL,
  `data_concessao` datetime DEFAULT current_timestamp(),
  `data_expiracao` datetime DEFAULT NULL,
  `tipo_plano` varchar(50) DEFAULT 'vitalicio',
  `data_inicio` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `aluno_progresso`
--

CREATE TABLE `aluno_progresso` (
  `id` int(11) NOT NULL,
  `aluno_email` varchar(255) NOT NULL,
  `aula_id` int(11) NOT NULL,
  `data_conclusao` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `aulas`
--

CREATE TABLE `aulas` (
  `id` int(11) NOT NULL,
  `modulo_id` int(11) NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `url_video` text DEFAULT NULL,
  `descricao` text DEFAULT NULL,
  `ordem` int(11) DEFAULT 0,
  `release_days` int(11) DEFAULT 0,
  `tipo_conteudo` enum('video','texto','quiz','arquivo') DEFAULT 'video',
  `duracao` int(11) DEFAULT 0,
  `tipo_video` enum('youtube','vimeo','url','upload') DEFAULT 'youtube',
  `materiais` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `aula_arquivos`
--

CREATE TABLE `aula_arquivos` (
  `id` int(11) NOT NULL,
  `aula_id` int(11) NOT NULL,
  `nome_original` varchar(255) NOT NULL COMMENT 'Nome original do arquivo',
  `nome_salvo` varchar(255) NOT NULL COMMENT 'Nome do arquivo salvo no servidor',
  `caminho_arquivo` varchar(255) NOT NULL COMMENT 'Caminho completo do arquivo no servidor',
  `tipo_mime` varchar(100) DEFAULT NULL COMMENT 'Tipo MIME do arquivo',
  `tamanho_bytes` int(11) DEFAULT NULL,
  `ordem` int(11) NOT NULL DEFAULT 0 COMMENT 'Ordem de exibição do arquivo dentro da aula',
  `data_upload` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `brand_settings`
--

CREATE TABLE `brand_settings` (
  `id` int(11) NOT NULL,
  `logo_url` varchar(500) DEFAULT 'https://i.ibb.co/2YRWNQw7/1757909548831-Photoroom.png',
  `login_bg_image` varchar(500) DEFAULT NULL,
  `primary_color` varchar(7) DEFAULT '#f97316',
  `secondary_color` varchar(7) DEFAULT NULL,
  `use_gradient` tinyint(1) DEFAULT 0,
  `gradient_direction` varchar(50) DEFAULT 'to right',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `icon_color` varchar(20) DEFAULT '#ef4444'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `brand_settings`
--

INSERT INTO `brand_settings` (`id`, `logo_url`, `login_bg_image`, `primary_color`, `secondary_color`, `use_gradient`, `gradient_direction`, `updated_at`, `icon_color`) VALUES
(1, 'https://checkout.digitalavance.com.br/uploads/config/logo_d68832efd510810bba206eace33298f5_1769194721.png', 'https://checkout.digitalavance.com.br/uploads/videologin.mp4', '#ff0000', '#eb0000', 0, 'to right', '2026-01-24 18:38:36', '#ef4444');

-- --------------------------------------------------------

--
-- Estrutura para tabela `checkout_sessions`
--

CREATE TABLE `checkout_sessions` (
  `id` int(11) NOT NULL,
  `uuid` varchar(100) NOT NULL,
  `produto_id` int(11) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `nome` varchar(255) DEFAULT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `cpf` varchar(14) DEFAULT NULL,
  `utm_source` varchar(255) DEFAULT NULL,
  `utm_medium` varchar(255) DEFAULT NULL,
  `utm_campaign` varchar(255) DEFAULT NULL,
  `utm_content` varchar(255) DEFAULT NULL,
  `utm_term` varchar(255) DEFAULT NULL,
  `src` varchar(255) DEFAULT NULL,
  `sck` varchar(255) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `status` enum('started','form_filled','payment_pending','completed','abandoned') DEFAULT 'started',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `cloned_sites`
--

CREATE TABLE `cloned_sites` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `original_url` varchar(2048) NOT NULL,
  `title` varchar(255) DEFAULT 'Site Clonado',
  `slug` varchar(255) DEFAULT NULL,
  `original_html` longtext DEFAULT NULL,
  `edited_html` longtext DEFAULT NULL,
  `status` enum('draft','published') DEFAULT 'draft',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `cloned_site_settings`
--

CREATE TABLE `cloned_site_settings` (
  `id` int(11) NOT NULL,
  `cloned_site_id` int(11) NOT NULL,
  `facebook_pixel_id` varchar(255) DEFAULT NULL,
  `google_analytics_id` varchar(255) DEFAULT NULL,
  `custom_head_scripts` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `configuracoes`
--

CREATE TABLE `configuracoes` (
  `chave` varchar(255) NOT NULL,
  `valor` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `configuracoes`
--

INSERT INTO `configuracoes` (`chave`, `valor`) VALUES
('brand_logo', 'https://checkout.digitalavance.com.br/uploads/config/logo_d68832efd510810bba206eace33298f5_1769194721.png'),
('brand_name', 'CheckoutPRO'),
('brand_primary_color', '#3b82f6'),
('brand_secondary_color', '#1e40af'),
('default_member_password', 'SuaNovaSenha@'),
('email_template_delivery_html', ''),
('email_template_delivery_subject', ''),
('login_badge_color', '#ffffff'),
('login_badge_text', 'Plataforma #1 em Conversão'),
('login_bg_image', ''),
('login_btn_color1', '#30a1f8'),
('login_btn_color2', '#36b441'),
('login_btn_color3', '#029749'),
('login_description', 'Junte-se a produtores, afiliados e agências que já estão vendendo todos os dias usando o CheckoutPRO uma plataforma completa de área de membros + checkout próprio, criada para escalar com liberdade total.'),
('login_description_color', '#dee4ed'),
('login_stat1_label', 'processados em vendas'),
('login_stat1_value', 'R$ 95K+'),
('login_stat2_label', 'usuários ativos'),
('login_stat2_value', '620+'),
('login_stat3_label', 'de estabilidade e uptime'),
('login_stat3_value', '99.3%'),
('login_stats_label_color', '#64748b'),
('login_stats_value_color', '#ffffff'),
('login_title_color', '#ffffff'),
('login_title_highlight_color1', '#30a1f8'),
('login_title_highlight_color2', '#36b441'),
('login_title_line1', 'Transforme seu conhecimento em'),
('login_title_line2', 'resultados reais.'),
('member_area_login_url', ''),
('mercado_pago_enable_credit_card', '1'),
('mercado_pago_enable_pix', '1'),
('mercado_pago_max_installments', '24'),
('site_url', 'contato@flixmembers.com.br'),
('smtp_encryption', 'ssl'),
('smtp_from_email', 'contato@digitalavance.com.br'),
('smtp_from_name', 'CheckoutPRO'),
('smtp_host', 'smtp.hostinger.com'),
('smtp_password', '@Thmpv77d6f'),
('smtp_port', '465'),
('smtp_username', 'contato@digitalavance.com.br');

-- --------------------------------------------------------

--
-- Estrutura para tabela `configuracoes_sistema`
--

CREATE TABLE `configuracoes_sistema` (
  `id` int(11) NOT NULL,
  `chave` varchar(100) NOT NULL,
  `valor` text DEFAULT NULL,
  `tipo` enum('string','number','boolean','json') DEFAULT 'string',
  `descricao` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `configuracoes_sistema`
--

INSERT INTO `configuracoes_sistema` (`id`, `chave`, `valor`, `tipo`, `descricao`, `created_at`, `updated_at`) VALUES
(1, 'cor_primaria', '#0643d0', 'string', NULL, '2026-01-14 03:39:05', '2026-01-23 09:23:07'),
(2, 'login_bg_url', 'https://checkout.digitalavance.com.br/uploads/videologin.mp4', 'string', NULL, '2026-01-14 19:21:04', '2026-01-23 10:04:13'),
(3, 'logo_url', 'uploads/config/logo_d68832efd510810bba206eace33298f5_1769194721.png', 'string', NULL, '2026-01-23 09:27:55', '2026-01-23 18:58:41'),
(4, 'logo_checkout_url', 'uploads/config/logo_checkout_644d756737d1485b859e2c1810f102bb_1769160537.png', 'string', NULL, '2026-01-23 09:28:57', '2026-01-23 09:28:57'),
(5, 'nome_plataforma', 'CheckoutPRO', 'string', NULL, '2026-01-23 09:29:23', '2026-01-23 09:29:23');

-- --------------------------------------------------------

--
-- Estrutura para tabela `cursos`
--

CREATE TABLE `cursos` (
  `id` int(11) NOT NULL,
  `produto_id` int(11) NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `descricao` text DEFAULT NULL,
  `imagem_url` varchar(500) DEFAULT NULL,
  `banner_url` varchar(500) DEFAULT NULL,
  `data_criacao` datetime DEFAULT current_timestamp(),
  `imagem_capa` varchar(500) DEFAULT NULL,
  `tipo` enum('curso','area_membros') DEFAULT 'curso',
  `ativo` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `email_campaigns`
--

CREATE TABLE `email_campaigns` (
  `id` int(11) NOT NULL,
  `nome` varchar(255) NOT NULL,
  `assunto` varchar(255) NOT NULL,
  `conteudo_html` longtext NOT NULL,
  `filtro_status` enum('todos','ativos','inativos','pendentes','rejeitadas','canceladas') DEFAULT 'todos',
  `total_destinatarios` int(11) DEFAULT 0,
  `total_enviados` int(11) DEFAULT 0,
  `total_abertos` int(11) DEFAULT 0,
  `total_erros` int(11) DEFAULT 0,
  `status` enum('rascunho','enviando','concluida','pausada','erro') DEFAULT 'rascunho',
  `criado_por` int(11) NOT NULL,
  `criado_em` datetime DEFAULT current_timestamp(),
  `enviado_em` datetime DEFAULT NULL,
  `concluido_em` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `email_campaign_tracking`
--

CREATE TABLE `email_campaign_tracking` (
  `id` int(11) NOT NULL,
  `campaign_id` int(11) NOT NULL,
  `email_destinatario` varchar(255) NOT NULL,
  `nome_destinatario` varchar(255) DEFAULT NULL,
  `tracking_id` varchar(64) NOT NULL,
  `enviado_em` datetime DEFAULT current_timestamp(),
  `aberto_em` datetime DEFAULT NULL,
  `total_aberturas` int(11) DEFAULT 0,
  `status` enum('pendente','enviado','aberto','erro') DEFAULT 'pendente',
  `erro_mensagem` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `email_queue`
--

CREATE TABLE `email_queue` (
  `id` int(11) NOT NULL,
  `to_email` varchar(255) NOT NULL,
  `to_name` varchar(255) DEFAULT NULL,
  `subject` varchar(500) NOT NULL,
  `body` text NOT NULL,
  `status` enum('pending','sent','failed') DEFAULT 'pending',
  `attempts` int(11) DEFAULT 0,
  `error_message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `sent_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `email_templates`
--

CREATE TABLE `email_templates` (
  `id` int(11) NOT NULL,
  `nome` varchar(255) NOT NULL,
  `assunto` varchar(255) NOT NULL,
  `conteudo_html` longtext NOT NULL,
  `is_default` tinyint(1) DEFAULT 0,
  `criado_em` datetime DEFAULT current_timestamp(),
  `atualizado_em` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `funnels`
--

CREATE TABLE `funnels` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `status` enum('draft','published') DEFAULT 'draft',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `funnel_pages`
--

CREATE TABLE `funnel_pages` (
  `id` int(11) NOT NULL,
  `funnel_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `html_content` longtext DEFAULT NULL,
  `is_homepage` tinyint(1) DEFAULT 0,
  `order_index` int(11) DEFAULT 0,
  `status` enum('draft','published') DEFAULT 'draft',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `funnel_page_views`
--

CREATE TABLE `funnel_page_views` (
  `id` int(11) NOT NULL,
  `funnel_id` int(11) NOT NULL,
  `page_id` int(11) DEFAULT NULL,
  `session_id` varchar(64) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `referer` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Despejando dados para a tabela `funnel_page_views`
--

INSERT INTO `funnel_page_views` (`id`, `funnel_id`, `page_id`, `session_id`, `ip_address`, `user_agent`, `referer`, `created_at`) VALUES
(1, 17, 18, '860a5277a25e1c5d4d33d3de25900d83', '2804:50b0:102:e171:bdb3:2ffb:bfa0:d189', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, '2026-01-13 23:23:24'),
(2, 17, 18, 'f1838e18de2ad13f6a25cd7af5745aab', '2804:50b0:102:e171:bdb3:2ffb:bfa0:d189', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, '2026-01-14 00:25:31'),
(3, 19, 20, 'bcca7af4fd9aa3934cbb148f75ab86a2', '2804:50b0:102:e171:bdb3:2ffb:bfa0:d189', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, '2026-01-14 00:55:03'),
(4, 21, 22, 'bcca7af4fd9aa3934cbb148f75ab86a2', '2804:50b0:102:e171:bdb3:2ffb:bfa0:d189', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, '2026-01-14 01:08:05'),
(5, 21, 22, '860a5277a25e1c5d4d33d3de25900d83', '2804:50b0:102:e171:bdb3:2ffb:bfa0:d189', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, '2026-01-14 01:08:47'),
(6, 21, 22, 'fa82f0b2e287ee979f711d024c490e0a', '2804:50b0:102:e171:38cd:2f77:1e25:f87b', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Mobile Safari/537.36', NULL, '2026-01-14 01:17:34'),
(7, 21, 22, '860a5277a25e1c5d4d33d3de25900d83', '2804:50b0:102:e171:bdb3:2ffb:bfa0:d189', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, '2026-01-14 01:42:27');

-- --------------------------------------------------------

--
-- Estrutura para tabela `funnel_settings`
--

CREATE TABLE `funnel_settings` (
  `id` int(11) NOT NULL,
  `funnel_id` int(11) NOT NULL,
  `facebook_pixel_id` varchar(50) DEFAULT NULL,
  `google_analytics_id` varchar(50) DEFAULT NULL,
  `custom_head_scripts` longtext DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `login_attempts`
--

CREATE TABLE `login_attempts` (
  `id` int(11) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `action` varchar(50) NOT NULL,
  `username` varchar(255) DEFAULT NULL,
  `attempted_at` datetime DEFAULT current_timestamp(),
  `blocked_until` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `login_texts`
--

CREATE TABLE `login_texts` (
  `id` int(11) NOT NULL DEFAULT 1,
  `badge_text` varchar(255) DEFAULT 'Plataforma #1 em Conversão',
  `badge_color` varchar(20) DEFAULT '#c084fc',
  `title_line1` varchar(255) DEFAULT 'Transforme seu conhecimento em',
  `title_line2` varchar(255) DEFAULT 'resultados reais.',
  `title_color` varchar(20) DEFAULT '#ffffff',
  `title_highlight_color` varchar(20) DEFAULT '#a855f7',
  `title_highlight_color2` varchar(20) DEFAULT '#06b6d4',
  `btn_gradient_color1` varchar(20) DEFAULT '#a855f7',
  `btn_gradient_color2` varchar(20) DEFAULT '#7c3aed',
  `btn_gradient_color3` varchar(20) DEFAULT '#06b6d4',
  `description` text DEFAULT NULL,
  `description_color` varchar(20) DEFAULT '#94a3b8',
  `stat1_value` varchar(50) DEFAULT 'R$ 2.5M+',
  `stat1_label` varchar(50) DEFAULT 'Processados',
  `stat2_value` varchar(50) DEFAULT '15K+',
  `stat2_label` varchar(50) DEFAULT 'Usuários',
  `stat3_value` varchar(50) DEFAULT '99.9%',
  `stat3_label` varchar(50) DEFAULT 'Uptime',
  `stats_value_color` varchar(20) DEFAULT '#ffffff',
  `stats_label_color` varchar(20) DEFAULT '#64748b'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Despejando dados para a tabela `login_texts`
--

INSERT INTO `login_texts` (`id`, `badge_text`, `badge_color`, `title_line1`, `title_line2`, `title_color`, `title_highlight_color`, `title_highlight_color2`, `btn_gradient_color1`, `btn_gradient_color2`, `btn_gradient_color3`, `description`, `description_color`, `stat1_value`, `stat1_label`, `stat2_value`, `stat2_label`, `stat3_value`, `stat3_label`, `stats_value_color`, `stats_label_color`) VALUES
(1, 'Plataforma #1 em Conversão', '#c084fc', 'Transforme seu conhecimento em', 'resultados reais.', '#ffffff', '#a855f7', '#06b6d4', '#a855f7', '#7c3aed', '#06b6d4', NULL, '#94a3b8', 'R$ 2.5M+', 'Processados', '15K+', 'Usuários', '99.9%', 'Uptime', '#ffffff', '#64748b');

-- --------------------------------------------------------

--
-- Estrutura para tabela `modulos`
--

CREATE TABLE `modulos` (
  `id` int(11) NOT NULL,
  `curso_id` int(11) NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `imagem_capa_url` varchar(500) DEFAULT NULL,
  `ordem` int(11) DEFAULT 0,
  `release_days` int(11) DEFAULT 0,
  `is_paid_module` tinyint(1) DEFAULT 0,
  `linked_product_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `notificacoes`
--

CREATE TABLE `notificacoes` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `tipo` varchar(50) NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `mensagem` text DEFAULT NULL,
  `lida` tinyint(1) DEFAULT 0,
  `data_notificacao` datetime DEFAULT current_timestamp(),
  `link` varchar(500) DEFAULT NULL,
  `venda_id_fk` int(11) DEFAULT NULL,
  `displayed_live` tinyint(1) DEFAULT 0,
  `metodo_pagamento` varchar(50) DEFAULT NULL,
  `link_acao` varchar(255) DEFAULT NULL,
  `valor` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `order_bumps`
--

CREATE TABLE `order_bumps` (
  `id` int(11) NOT NULL,
  `main_product_id` int(11) NOT NULL,
  `offer_product_id` int(11) NOT NULL,
  `headline` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `ordem` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `token` varchar(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `plugins`
--

CREATE TABLE `plugins` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `descricao` text DEFAULT NULL,
  `versao` varchar(20) DEFAULT '1.0.0',
  `autor` varchar(100) DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT 0,
  `configuracoes` longtext DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `product_exclusive_offers`
--

CREATE TABLE `product_exclusive_offers` (
  `id` int(11) NOT NULL,
  `source_product_id` int(11) NOT NULL,
  `offer_product_id` int(11) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `produtos`
--

CREATE TABLE `produtos` (
  `id` int(11) NOT NULL,
  `nome` varchar(255) NOT NULL,
  `descricao` text DEFAULT NULL,
  `preco` decimal(10,2) NOT NULL,
  `foto` varchar(500) DEFAULT NULL,
  `checkout_hash` varchar(64) DEFAULT NULL,
  `usuario_id` int(11) NOT NULL,
  `data_criacao` datetime DEFAULT current_timestamp(),
  `preco_anterior` decimal(10,2) DEFAULT NULL,
  `tipo_entrega` enum('area_membros','link_externo','arquivo','manual') DEFAULT 'area_membros',
  `conteudo_entrega` text DEFAULT NULL,
  `gateway` varchar(50) DEFAULT 'mercadopago',
  `tipo_produto` enum('produto_digital','assinatura','area_membros') DEFAULT 'produto_digital',
  `curso_id` int(11) DEFAULT NULL,
  `arquivo_entrega` varchar(500) DEFAULT NULL,
  `link_entrega` varchar(500) DEFAULT NULL,
  `email_template` text DEFAULT NULL,
  `email_assunto` varchar(255) DEFAULT NULL,
  `redirect_url` varchar(500) DEFAULT NULL,
  `pixel_facebook` text DEFAULT NULL,
  `pixel_google` text DEFAULT NULL,
  `pixel_tiktok` text DEFAULT NULL,
  `pixel_taboola` text DEFAULT NULL,
  `pix_enabled` tinyint(1) DEFAULT 1,
  `card_enabled` tinyint(1) DEFAULT 1,
  `boleto_enabled` tinyint(1) DEFAULT 0,
  `gateway_preferido` varchar(50) DEFAULT 'mercadopago',
  `checkout_style` longtext DEFAULT NULL,
  `bump_produto_id` int(11) DEFAULT NULL,
  `bump_titulo` varchar(255) DEFAULT NULL,
  `bump_descricao` text DEFAULT NULL,
  `bump_preco` decimal(10,2) DEFAULT NULL,
  `bump_ativo` tinyint(1) DEFAULT 0,
  `checkout_config` longtext DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `produto_ofertas`
--

CREATE TABLE `produto_ofertas` (
  `id` int(11) NOT NULL,
  `produto_id` int(11) NOT NULL,
  `nome` varchar(255) NOT NULL,
  `preco` decimal(10,2) NOT NULL,
  `preco_anterior` decimal(10,2) DEFAULT NULL,
  `checkout_hash` varchar(64) DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `progresso_aulas`
--

CREATE TABLE `progresso_aulas` (
  `id` int(11) NOT NULL,
  `aluno_id` int(11) NOT NULL,
  `aula_id` int(11) NOT NULL,
  `concluida` tinyint(1) DEFAULT 0,
  `progresso_segundos` int(11) DEFAULT 0,
  `ultima_visualizacao` datetime DEFAULT current_timestamp(),
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `pwa_config`
--

CREATE TABLE `pwa_config` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `app_name` varchar(255) DEFAULT NULL,
  `short_name` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `theme_color` varchar(20) DEFAULT '#000000',
  `background_color` varchar(20) DEFAULT '#ffffff',
  `icon_192` varchar(500) DEFAULT NULL,
  `icon_512` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `vapid_public_key` text DEFAULT NULL,
  `vapid_private_key` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `pwa_push_notifications`
--

CREATE TABLE `pwa_push_notifications` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `body` text DEFAULT NULL,
  `icon` varchar(500) DEFAULT NULL,
  `url` varchar(500) DEFAULT NULL,
  `sent_at` timestamp NULL DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `pwa_push_subscriptions`
--

CREATE TABLE `pwa_push_subscriptions` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `endpoint` text NOT NULL,
  `p256dh` varchar(255) NOT NULL,
  `auth` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `rate_limits`
--

CREATE TABLE `rate_limits` (
  `id` int(11) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `endpoint` varchar(255) NOT NULL,
  `requests` int(11) DEFAULT 1,
  `window_start` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `reembolsos`
--

CREATE TABLE `reembolsos` (
  `id` int(11) NOT NULL,
  `venda_id` int(11) NOT NULL,
  `produto_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `valor` decimal(10,2) NOT NULL,
  `motivo` text DEFAULT NULL,
  `status` enum('pendente','aprovado','rejeitado') DEFAULT 'pendente',
  `data_solicitacao` datetime DEFAULT current_timestamp(),
  `data_processamento` datetime DEFAULT NULL,
  `observacoes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `saas_admin_gateways`
--

CREATE TABLE `saas_admin_gateways` (
  `id` int(11) NOT NULL,
  `gateway` varchar(50) NOT NULL,
  `mp_access_token` text DEFAULT NULL,
  `mp_public_key` text DEFAULT NULL,
  `efi_client_id` text DEFAULT NULL,
  `efi_client_secret` text DEFAULT NULL,
  `efi_pix_key` varchar(255) DEFAULT NULL,
  `efi_payee_code` varchar(255) DEFAULT NULL,
  `efi_certificate_path` varchar(255) DEFAULT NULL,
  `pushinpay_token` text DEFAULT NULL,
  `beehive_secret_key` text DEFAULT NULL,
  `beehive_public_key` text DEFAULT NULL,
  `hypercash_secret_key` text DEFAULT NULL,
  `hypercash_public_key` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Despejando dados para a tabela `saas_admin_gateways`
--

INSERT INTO `saas_admin_gateways` (`id`, `gateway`, `mp_access_token`, `mp_public_key`, `efi_client_id`, `efi_client_secret`, `efi_pix_key`, `efi_payee_code`, `efi_certificate_path`, `pushinpay_token`, `beehive_secret_key`, `beehive_public_key`, `hypercash_secret_key`, `hypercash_public_key`, `created_at`, `updated_at`) VALUES
(1, 'mercadopago', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-14 18:47:53', '2026-01-14 18:53:18');

-- --------------------------------------------------------

--
-- Estrutura para tabela `saas_assinaturas`
--

CREATE TABLE `saas_assinaturas` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `plano_id` int(11) NOT NULL,
  `status` enum('ativa','cancelada','suspensa','trial') DEFAULT 'trial',
  `data_inicio` datetime DEFAULT current_timestamp(),
  `data_fim` datetime DEFAULT NULL,
  `gateway_subscription_id` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `saas_config`
--

CREATE TABLE `saas_config` (
  `id` int(11) NOT NULL,
  `chave` varchar(100) NOT NULL,
  `valor` text DEFAULT NULL,
  `descricao` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `saas_config`
--

INSERT INTO `saas_config` (`id`, `chave`, `valor`, `descricao`, `created_at`, `updated_at`) VALUES
(1, 'enabled', '1', 'Sistema SaaS habilitado', '2026-01-14 18:14:38', '2026-01-15 16:58:25');

-- --------------------------------------------------------

--
-- Estrutura para tabela `saas_config_admin`
--

CREATE TABLE `saas_config_admin` (
  `id` int(11) NOT NULL,
  `chave` varchar(100) NOT NULL,
  `valor` text DEFAULT NULL,
  `tipo` varchar(50) DEFAULT 'string',
  `descricao` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `saas_contadores_mensais`
--

CREATE TABLE `saas_contadores_mensais` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `mes_ano` varchar(7) NOT NULL,
  `vendas_count` int(11) DEFAULT 0,
  `produtos_count` int(11) DEFAULT 0,
  `alunos_count` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `saas_limites_uso`
--

CREATE TABLE `saas_limites_uso` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `produtos_criados` int(11) DEFAULT 0,
  `vendas_mes` int(11) DEFAULT 0,
  `alunos_ativos` int(11) DEFAULT 0,
  `mes_referencia` varchar(7) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `saas_payment_methods`
--

CREATE TABLE `saas_payment_methods` (
  `id` int(11) NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `gateway` varchar(50) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Despejando dados para a tabela `saas_payment_methods`
--

INSERT INTO `saas_payment_methods` (`id`, `payment_method`, `gateway`, `created_at`, `updated_at`) VALUES
(1, 'pix', 'mercadopago', '2026-01-14 18:47:38', '2026-01-14 18:48:52'),
(2, 'credit_card', 'mercadopago', '2026-01-14 18:47:38', '2026-01-14 18:48:52');

-- --------------------------------------------------------

--
-- Estrutura para tabela `saas_planos`
--

CREATE TABLE `saas_planos` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `descricao` text DEFAULT NULL,
  `preco_mensal` decimal(10,2) NOT NULL,
  `preco_anual` decimal(10,2) DEFAULT NULL,
  `limite_produtos` int(11) DEFAULT -1,
  `limite_vendas_mes` int(11) DEFAULT -1,
  `limite_alunos` int(11) DEFAULT -1,
  `recursos` longtext DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `preco` decimal(10,2) NOT NULL DEFAULT 0.00,
  `periodo` varchar(20) DEFAULT 'mensal',
  `max_produtos` int(11) DEFAULT NULL,
  `max_pedidos_mes` int(11) DEFAULT NULL,
  `is_free` tinyint(1) DEFAULT 0,
  `ordem` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `smtp_settings`
--

CREATE TABLE `smtp_settings` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `smtp_host` varchar(255) DEFAULT NULL,
  `smtp_port` int(11) DEFAULT 587,
  `smtp_user` varchar(255) DEFAULT NULL,
  `smtp_pass` varchar(255) DEFAULT NULL,
  `smtp_encryption` enum('tls','ssl','none') DEFAULT 'tls',
  `smtp_from_email` varchar(255) DEFAULT NULL,
  `smtp_from_name` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `starfy_tracking_events`
--

CREATE TABLE `starfy_tracking_events` (
  `id` int(11) NOT NULL,
  `tracking_product_id` int(11) NOT NULL,
  `session_id` varchar(100) NOT NULL,
  `event_type` varchar(50) NOT NULL,
  `event_data` longtext DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `referer` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `starfy_tracking_products`
--

CREATE TABLE `starfy_tracking_products` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `produto_id` int(11) NOT NULL,
  `tracking_id` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `usuario` varchar(255) NOT NULL,
  `nome` varchar(255) DEFAULT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `senha` varchar(255) NOT NULL,
  `tipo` enum('admin','infoprodutor','usuario') DEFAULT 'usuario',
  `mp_public_key` varchar(255) DEFAULT NULL,
  `mp_access_token` varchar(255) DEFAULT NULL,
  `foto_perfil` varchar(500) DEFAULT NULL,
  `ultima_visualizacao_notificacoes` datetime DEFAULT NULL,
  `pushinpay_token` varchar(255) DEFAULT NULL,
  `efi_client_id` varchar(255) DEFAULT NULL,
  `efi_client_secret` varchar(255) DEFAULT NULL,
  `efi_certificate_path` varchar(500) DEFAULT NULL,
  `efi_pix_key` varchar(255) DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `beehive_secret_key` varchar(255) DEFAULT NULL,
  `beehive_public_key` varchar(255) DEFAULT NULL,
  `hypercash_secret_key` varchar(255) DEFAULT NULL,
  `hypercash_public_key` varchar(255) DEFAULT NULL,
  `efi_payee_code` varchar(255) DEFAULT NULL,
  `password_reset_token` varchar(100) DEFAULT NULL,
  `password_reset_expires` datetime DEFAULT NULL,
  `password_setup_token` varchar(100) DEFAULT NULL,
  `password_setup_expires` datetime DEFAULT NULL,
  `saas_plano_free_atribuido` tinyint(1) DEFAULT 0,
  `remember_token_expires` datetime DEFAULT NULL,
  `senha_seguranca` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `usuarios`
--

INSERT INTO `usuarios` (`id`, `usuario`, `nome`, `telefone`, `senha`, `tipo`, `mp_public_key`, `mp_access_token`, `foto_perfil`, `ultima_visualizacao_notificacoes`, `pushinpay_token`, `efi_client_id`, `efi_client_secret`, `efi_certificate_path`, `efi_pix_key`, `remember_token`, `beehive_secret_key`, `beehive_public_key`, `hypercash_secret_key`, `hypercash_public_key`, `efi_payee_code`, `password_reset_token`, `password_reset_expires`, `password_setup_token`, `password_setup_expires`, `saas_plano_free_atribuido`, `remember_token_expires`, `senha_seguranca`) VALUES
(1, 'admin@gmail.com', 'WEVERTON ADM', '', '$2y$10$C/Umn4YHt/hKs2wFM5SvuO9Zsc4zTpFlmUBlcBvIm5RNA5rR82z4y', 'admin', NULL, NULL, NULL, '2025-09-15 16:35:10', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL);

-- --------------------------------------------------------

--
-- Estrutura para tabela `utmfy_integrations`
--

CREATE TABLE `utmfy_integrations` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `api_token` varchar(255) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `event_approved` tinyint(1) DEFAULT 1,
  `event_pending` tinyint(1) DEFAULT 0,
  `event_rejected` tinyint(1) DEFAULT 0,
  `event_refunded` tinyint(1) DEFAULT 0,
  `event_charged_back` tinyint(1) DEFAULT 0,
  `event_initiate_checkout` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `vendas`
--

CREATE TABLE `vendas` (
  `id` int(11) NOT NULL,
  `produto_id` int(11) NOT NULL,
  `valor` decimal(10,2) NOT NULL,
  `status_pagamento` varchar(50) DEFAULT 'pending',
  `data_venda` datetime DEFAULT current_timestamp(),
  `comprador_email` varchar(255) DEFAULT NULL,
  `comprador_nome` varchar(255) DEFAULT NULL,
  `comprador_cpf` varchar(14) DEFAULT NULL,
  `comprador_telefone` varchar(20) DEFAULT NULL,
  `transacao_id` varchar(255) DEFAULT NULL,
  `metodo_pagamento` varchar(50) DEFAULT NULL,
  `checkout_session_uuid` varchar(100) DEFAULT NULL,
  `email_entrega_enviado` tinyint(1) DEFAULT 0,
  `comprador_cep` varchar(10) DEFAULT NULL,
  `comprador_logradouro` varchar(255) DEFAULT NULL,
  `comprador_numero` varchar(20) DEFAULT NULL,
  `comprador_complemento` varchar(100) DEFAULT NULL,
  `comprador_bairro` varchar(100) DEFAULT NULL,
  `comprador_cidade` varchar(100) DEFAULT NULL,
  `comprador_estado` varchar(2) DEFAULT NULL,
  `recovery_email_sent` tinyint(1) DEFAULT 0,
  `utm_source` varchar(255) DEFAULT NULL,
  `utm_campaign` varchar(255) DEFAULT NULL,
  `utm_medium` varchar(255) DEFAULT NULL,
  `utm_content` varchar(255) DEFAULT NULL,
  `utm_term` varchar(255) DEFAULT NULL,
  `src` varchar(255) DEFAULT NULL,
  `sck` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `webhooks`
--

CREATE TABLE `webhooks` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `produto_id` int(11) DEFAULT NULL,
  `url` varchar(2048) NOT NULL,
  `event_approved` tinyint(1) DEFAULT 0,
  `event_pending` tinyint(1) DEFAULT 0,
  `event_rejected` tinyint(1) DEFAULT 0,
  `event_refunded` tinyint(1) DEFAULT 0,
  `event_charged_back` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `alunos`
--
ALTER TABLE `alunos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_usuario` (`usuario_id`),
  ADD KEY `idx_curso` (`curso_id`),
  ADD KEY `idx_produto` (`produto_id`),
  ADD KEY `idx_venda` (`venda_id`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_status` (`status`);

--
-- Índices de tabela `alunos_acessos`
--
ALTER TABLE `alunos_acessos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `aluno_produto_unico` (`aluno_email`,`produto_id`),
  ADD KEY `idx_produto_id` (`produto_id`),
  ADD KEY `idx_aluno_email` (`aluno_email`);

--
-- Índices de tabela `aluno_progresso`
--
ALTER TABLE `aluno_progresso`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `aluno_aula_unico` (`aluno_email`,`aula_id`),
  ADD KEY `idx_aula_id` (`aula_id`);

--
-- Índices de tabela `aulas`
--
ALTER TABLE `aulas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_modulo_id` (`modulo_id`);

--
-- Índices de tabela `aula_arquivos`
--
ALTER TABLE `aula_arquivos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_aula_arquivos_aula` (`aula_id`);

--
-- Índices de tabela `brand_settings`
--
ALTER TABLE `brand_settings`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `checkout_sessions`
--
ALTER TABLE `checkout_sessions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uuid` (`uuid`),
  ADD KEY `idx_uuid` (`uuid`),
  ADD KEY `idx_produto` (`produto_id`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_created` (`created_at`);

--
-- Índices de tabela `cloned_sites`
--
ALTER TABLE `cloned_sites`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `status` (`status`);

--
-- Índices de tabela `cloned_site_settings`
--
ALTER TABLE `cloned_site_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `cloned_site_id` (`cloned_site_id`);

--
-- Índices de tabela `configuracoes`
--
ALTER TABLE `configuracoes`
  ADD PRIMARY KEY (`chave`);

--
-- Índices de tabela `configuracoes_sistema`
--
ALTER TABLE `configuracoes_sistema`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `chave` (`chave`);

--
-- Índices de tabela `cursos`
--
ALTER TABLE `cursos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_produto_id_cursos` (`produto_id`);

--
-- Índices de tabela `email_campaigns`
--
ALTER TABLE `email_campaigns`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_criado_em` (`criado_em`);

--
-- Índices de tabela `email_campaign_tracking`
--
ALTER TABLE `email_campaign_tracking`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `tracking_id` (`tracking_id`),
  ADD KEY `idx_campaign` (`campaign_id`),
  ADD KEY `idx_tracking` (`tracking_id`),
  ADD KEY `idx_email` (`email_destinatario`),
  ADD KEY `idx_status` (`status`);

--
-- Índices de tabela `email_queue`
--
ALTER TABLE `email_queue`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `email_templates`
--
ALTER TABLE `email_templates`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `funnels`
--
ALTER TABLE `funnels`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_subdomain` (`slug`),
  ADD KEY `idx_usuario` (`usuario_id`);

--
-- Índices de tabela `funnel_pages`
--
ALTER TABLE `funnel_pages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_funnel` (`funnel_id`);

--
-- Índices de tabela `funnel_page_views`
--
ALTER TABLE `funnel_page_views`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_funnel_id` (`funnel_id`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_session_funnel` (`session_id`,`funnel_id`);

--
-- Índices de tabela `funnel_settings`
--
ALTER TABLE `funnel_settings`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `login_attempts`
--
ALTER TABLE `login_attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ip` (`ip_address`),
  ADD KEY `idx_action` (`action`),
  ADD KEY `idx_attempted` (`attempted_at`);

--
-- Índices de tabela `login_texts`
--
ALTER TABLE `login_texts`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `modulos`
--
ALTER TABLE `modulos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_curso_id` (`curso_id`),
  ADD KEY `fk_modulos_linked_product` (`linked_product_id`),
  ADD KEY `idx_linked_product` (`linked_product_id`);

--
-- Índices de tabela `notificacoes`
--
ALTER TABLE `notificacoes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_usuario_id_notificacoes` (`usuario_id`),
  ADD KEY `fk_notificacoes_venda` (`venda_id_fk`);

--
-- Índices de tabela `order_bumps`
--
ALTER TABLE `order_bumps`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_main_product_id` (`main_product_id`),
  ADD KEY `fk_order_bumps_offer_product` (`offer_product_id`);

--
-- Índices de tabela `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `idx_token` (`token`),
  ADD KEY `idx_usuario` (`usuario_id`),
  ADD KEY `idx_expires` (`expires_at`);

--
-- Índices de tabela `plugins`
--
ALTER TABLE `plugins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Índices de tabela `product_exclusive_offers`
--
ALTER TABLE `product_exclusive_offers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_unique_product_offer` (`source_product_id`,`offer_product_id`),
  ADD KEY `fk_offer_target_product` (`offer_product_id`);

--
-- Índices de tabela `produtos`
--
ALTER TABLE `produtos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_usuario_id` (`usuario_id`);

--
-- Índices de tabela `produto_ofertas`
--
ALTER TABLE `produto_ofertas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_produto_ofertas_produto` (`produto_id`);

--
-- Índices de tabela `progresso_aulas`
--
ALTER TABLE `progresso_aulas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_aluno_aula` (`aluno_id`,`aula_id`),
  ADD KEY `idx_aluno` (`aluno_id`),
  ADD KEY `idx_aula` (`aula_id`);

--
-- Índices de tabela `pwa_config`
--
ALTER TABLE `pwa_config`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `pwa_push_notifications`
--
ALTER TABLE `pwa_push_notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`);

--
-- Índices de tabela `pwa_push_subscriptions`
--
ALTER TABLE `pwa_push_subscriptions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Índices de tabela `rate_limits`
--
ALTER TABLE `rate_limits`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `reembolsos`
--
ALTER TABLE `reembolsos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `venda_id` (`venda_id`),
  ADD KEY `produto_id` (`produto_id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Índices de tabela `saas_admin_gateways`
--
ALTER TABLE `saas_admin_gateways`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `gateway` (`gateway`);

--
-- Índices de tabela `saas_assinaturas`
--
ALTER TABLE `saas_assinaturas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `plano_id` (`plano_id`);

--
-- Índices de tabela `saas_config`
--
ALTER TABLE `saas_config`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `chave` (`chave`);

--
-- Índices de tabela `saas_config_admin`
--
ALTER TABLE `saas_config_admin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `chave` (`chave`);

--
-- Índices de tabela `saas_contadores_mensais`
--
ALTER TABLE `saas_contadores_mensais`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Índices de tabela `saas_limites_uso`
--
ALTER TABLE `saas_limites_uso`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Índices de tabela `saas_payment_methods`
--
ALTER TABLE `saas_payment_methods`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `payment_method` (`payment_method`);

--
-- Índices de tabela `saas_planos`
--
ALTER TABLE `saas_planos`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `smtp_settings`
--
ALTER TABLE `smtp_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `usuario_id` (`usuario_id`),
  ADD KEY `idx_usuario` (`usuario_id`);

--
-- Índices de tabela `starfy_tracking_events`
--
ALTER TABLE `starfy_tracking_events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_tracking_events_product` (`tracking_product_id`);

--
-- Índices de tabela `starfy_tracking_products`
--
ALTER TABLE `starfy_tracking_products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_unique_tracking_id` (`tracking_id`),
  ADD UNIQUE KEY `idx_unique_usuario_produto_rastreado` (`usuario_id`,`produto_id`),
  ADD KEY `fk_tracking_products_produto` (`produto_id`);

--
-- Índices de tabela `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `usuario` (`usuario`);

--
-- Índices de tabela `utmfy_integrations`
--
ALTER TABLE `utmfy_integrations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_utmfy_integrations_usuario` (`usuario_id`),
  ADD KEY `fk_utmfy_integrations_produto` (`product_id`);

--
-- Índices de tabela `vendas`
--
ALTER TABLE `vendas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_produto_id_vendas` (`produto_id`),
  ADD KEY `idx_checkout_session_uuid` (`checkout_session_uuid`);

--
-- Índices de tabela `webhooks`
--
ALTER TABLE `webhooks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_webhooks_usuario` (`usuario_id`),
  ADD KEY `fk_webhooks_produto` (`produto_id`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `alunos`
--
ALTER TABLE `alunos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `alunos_acessos`
--
ALTER TABLE `alunos_acessos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=112;

--
-- AUTO_INCREMENT de tabela `aluno_progresso`
--
ALTER TABLE `aluno_progresso`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT de tabela `aulas`
--
ALTER TABLE `aulas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT de tabela `aula_arquivos`
--
ALTER TABLE `aula_arquivos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `brand_settings`
--
ALTER TABLE `brand_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `checkout_sessions`
--
ALTER TABLE `checkout_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `cloned_sites`
--
ALTER TABLE `cloned_sites`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de tabela `cloned_site_settings`
--
ALTER TABLE `cloned_site_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `configuracoes_sistema`
--
ALTER TABLE `configuracoes_sistema`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de tabela `cursos`
--
ALTER TABLE `cursos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT de tabela `email_campaigns`
--
ALTER TABLE `email_campaigns`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `email_campaign_tracking`
--
ALTER TABLE `email_campaign_tracking`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `email_queue`
--
ALTER TABLE `email_queue`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `email_templates`
--
ALTER TABLE `email_templates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `funnels`
--
ALTER TABLE `funnels`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT de tabela `funnel_pages`
--
ALTER TABLE `funnel_pages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT de tabela `funnel_page_views`
--
ALTER TABLE `funnel_page_views`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de tabela `funnel_settings`
--
ALTER TABLE `funnel_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `login_attempts`
--
ALTER TABLE `login_attempts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `modulos`
--
ALTER TABLE `modulos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT de tabela `notificacoes`
--
ALTER TABLE `notificacoes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=434;

--
-- AUTO_INCREMENT de tabela `order_bumps`
--
ALTER TABLE `order_bumps`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=202;

--
-- AUTO_INCREMENT de tabela `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `plugins`
--
ALTER TABLE `plugins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `product_exclusive_offers`
--
ALTER TABLE `product_exclusive_offers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de tabela `produtos`
--
ALTER TABLE `produtos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT de tabela `produto_ofertas`
--
ALTER TABLE `produto_ofertas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `progresso_aulas`
--
ALTER TABLE `progresso_aulas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `pwa_config`
--
ALTER TABLE `pwa_config`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `pwa_push_notifications`
--
ALTER TABLE `pwa_push_notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `pwa_push_subscriptions`
--
ALTER TABLE `pwa_push_subscriptions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `rate_limits`
--
ALTER TABLE `rate_limits`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `reembolsos`
--
ALTER TABLE `reembolsos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `saas_admin_gateways`
--
ALTER TABLE `saas_admin_gateways`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `saas_assinaturas`
--
ALTER TABLE `saas_assinaturas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `saas_config`
--
ALTER TABLE `saas_config`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `saas_config_admin`
--
ALTER TABLE `saas_config_admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `saas_contadores_mensais`
--
ALTER TABLE `saas_contadores_mensais`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `saas_limites_uso`
--
ALTER TABLE `saas_limites_uso`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `saas_payment_methods`
--
ALTER TABLE `saas_payment_methods`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de tabela `saas_planos`
--
ALTER TABLE `saas_planos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `smtp_settings`
--
ALTER TABLE `smtp_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `starfy_tracking_events`
--
ALTER TABLE `starfy_tracking_events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=301;

--
-- AUTO_INCREMENT de tabela `starfy_tracking_products`
--
ALTER TABLE `starfy_tracking_products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=84;

--
-- AUTO_INCREMENT de tabela `utmfy_integrations`
--
ALTER TABLE `utmfy_integrations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de tabela `vendas`
--
ALTER TABLE `vendas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=353;

--
-- AUTO_INCREMENT de tabela `webhooks`
--
ALTER TABLE `webhooks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `alunos_acessos`
--
ALTER TABLE `alunos_acessos`
  ADD CONSTRAINT `fk_alunos_acessos_produto` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `aluno_progresso`
--
ALTER TABLE `aluno_progresso`
  ADD CONSTRAINT `fk_aluno_progresso_aula` FOREIGN KEY (`aula_id`) REFERENCES `aulas` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `aulas`
--
ALTER TABLE `aulas`
  ADD CONSTRAINT `fk_aulas_modulo` FOREIGN KEY (`modulo_id`) REFERENCES `modulos` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `cloned_sites`
--
ALTER TABLE `cloned_sites`
  ADD CONSTRAINT `fk_cloned_sites_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `cloned_site_settings`
--
ALTER TABLE `cloned_site_settings`
  ADD CONSTRAINT `fk_cloned_site_settings_site` FOREIGN KEY (`cloned_site_id`) REFERENCES `cloned_sites` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `cursos`
--
ALTER TABLE `cursos`
  ADD CONSTRAINT `fk_cursos_produto` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `modulos`
--
ALTER TABLE `modulos`
  ADD CONSTRAINT `fk_modulos_curso` FOREIGN KEY (`curso_id`) REFERENCES `cursos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_modulos_linked_product` FOREIGN KEY (`linked_product_id`) REFERENCES `produtos` (`id`) ON DELETE SET NULL;

--
-- Restrições para tabelas `notificacoes`
--
ALTER TABLE `notificacoes`
  ADD CONSTRAINT `fk_notificacoes_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_notificacoes_venda` FOREIGN KEY (`venda_id_fk`) REFERENCES `vendas` (`id`) ON DELETE SET NULL;

--
-- Restrições para tabelas `order_bumps`
--
ALTER TABLE `order_bumps`
  ADD CONSTRAINT `fk_order_bumps_main_product` FOREIGN KEY (`main_product_id`) REFERENCES `produtos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_order_bumps_offer_product` FOREIGN KEY (`offer_product_id`) REFERENCES `produtos` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `product_exclusive_offers`
--
ALTER TABLE `product_exclusive_offers`
  ADD CONSTRAINT `fk_offer_source_product` FOREIGN KEY (`source_product_id`) REFERENCES `produtos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_offer_target_product` FOREIGN KEY (`offer_product_id`) REFERENCES `produtos` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `produtos`
--
ALTER TABLE `produtos`
  ADD CONSTRAINT `fk_produtos_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `produto_ofertas`
--
ALTER TABLE `produto_ofertas`
  ADD CONSTRAINT `fk_produto_ofertas_produto` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `pwa_push_notifications`
--
ALTER TABLE `pwa_push_notifications`
  ADD CONSTRAINT `pwa_push_notifications_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;

--
-- Restrições para tabelas `pwa_push_subscriptions`
--
ALTER TABLE `pwa_push_subscriptions`
  ADD CONSTRAINT `pwa_push_subscriptions_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `reembolsos`
--
ALTER TABLE `reembolsos`
  ADD CONSTRAINT `reembolsos_ibfk_1` FOREIGN KEY (`venda_id`) REFERENCES `vendas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reembolsos_ibfk_2` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reembolsos_ibfk_3` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `saas_assinaturas`
--
ALTER TABLE `saas_assinaturas`
  ADD CONSTRAINT `saas_assinaturas_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `saas_assinaturas_ibfk_2` FOREIGN KEY (`plano_id`) REFERENCES `saas_planos` (`id`);

--
-- Restrições para tabelas `saas_contadores_mensais`
--
ALTER TABLE `saas_contadores_mensais`
  ADD CONSTRAINT `saas_contadores_mensais_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `saas_limites_uso`
--
ALTER TABLE `saas_limites_uso`
  ADD CONSTRAINT `saas_limites_uso_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `starfy_tracking_events`
--
ALTER TABLE `starfy_tracking_events`
  ADD CONSTRAINT `fk_tracking_events_product` FOREIGN KEY (`tracking_product_id`) REFERENCES `starfy_tracking_products` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `starfy_tracking_products`
--
ALTER TABLE `starfy_tracking_products`
  ADD CONSTRAINT `fk_tracking_products_produto` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_tracking_products_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `utmfy_integrations`
--
ALTER TABLE `utmfy_integrations`
  ADD CONSTRAINT `fk_utmfy_integrations_produto` FOREIGN KEY (`product_id`) REFERENCES `produtos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_utmfy_integrations_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `vendas`
--
ALTER TABLE `vendas`
  ADD CONSTRAINT `fk_vendas_produto` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `webhooks`
--
ALTER TABLE `webhooks`
  ADD CONSTRAINT `fk_webhooks_produto` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_webhooks_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
