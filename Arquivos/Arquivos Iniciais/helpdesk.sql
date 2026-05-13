-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 24/02/2026 às 17:51
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `helpdesk`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `cargos`
--

CREATE TABLE `cargos` (
  `id` int(11) NOT NULL,
  `nome` varchar(75) DEFAULT NULL,
  `empresa` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Despejando dados para a tabela `cargos`
--

INSERT INTO `cargos` (`id`, `nome`, `empresa`) VALUES
(1, 'Administrador', 0),
(2, 'Atendente', 0),
(3, 'Comum', 0);

-- --------------------------------------------------------

--
-- Estrutura para tabela `chamados`
--

CREATE TABLE `chamados` (
  `id` int(11) NOT NULL,
  `cliente_id` int(11) DEFAULT NULL,
  `usuario_abertura_id` int(11) NOT NULL,
  `usuario_responsavel_id` int(11) DEFAULT NULL,
  `setor_id` int(11) DEFAULT NULL,
  `assunto` varchar(120) NOT NULL,
  `descricao` text NOT NULL,
  `prioridade` enum('Baixa','Media','Alta','Urgente') NOT NULL DEFAULT 'Media',
  `status_id` int(11) NOT NULL,
  `criado_em` datetime NOT NULL DEFAULT current_timestamp(),
  `atualizado_em` datetime DEFAULT NULL,
  `fechado_em` datetime DEFAULT NULL,
  `protocolo` varchar(30) DEFAULT NULL,
  `visualizado_cliente` enum('Sim','Não') NOT NULL DEFAULT 'Não',
  `visualizado_suporte` enum('Sim','Não') NOT NULL DEFAULT 'Não',
  `empresa` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `chamados`
--

INSERT INTO `chamados` (`id`, `cliente_id`, `usuario_abertura_id`, `usuario_responsavel_id`, `setor_id`, `assunto`, `descricao`, `prioridade`, `status_id`, `criado_em`, `atualizado_em`, `fechado_em`, `protocolo`, `visualizado_cliente`, `visualizado_suporte`, `empresa`) VALUES
(1, 12, 2, 2, NULL, 'Novo chamado', 'Teste\r\n\r\nAbertura', 'Media', 7, '2026-02-18 11:39:47', '2026-02-18 17:35:08', '2026-02-18 17:35:08', 'CH20260218-8B7AE8', 'Não', 'Não', NULL),
(2, 12, 2, 17, NULL, 'fdafdfas', 'fdaf dafd fdsafdasf saef', 'Urgente', 4, '2026-02-18 11:41:31', '2026-02-18 18:44:02', NULL, 'CH20260218-AF8348', 'Não', 'Não', NULL),
(3, 12, 2, 17, NULL, 'aaaaaaaa', 'fdaf dafd fdsafdasf saef  fdaf da\r\n\r\nfda fdsafadf', 'Media', 7, '2026-02-18 11:43:54', '2026-02-18 18:24:38', '2026-02-18 18:24:38', 'CH20260218-0B26F3', 'Não', 'Não', 0),
(4, 12, 2, 2, NULL, 'Testeee', 'tesstess\r\n\r\ntestesss', 'Media', 5, '2026-02-18 11:47:08', '2026-02-18 19:04:46', NULL, 'CH20260218-2F34D7', 'Não', 'Não', 0),
(5, 12, 2, 2, NULL, 'Tesssteess', 'fad fadf a fda fdas f\r\nf\r\nadfdsafdasfdafaf', 'Media', 4, '2026-02-18 11:58:06', '2026-02-18 19:10:17', NULL, 'CH20260218-536361', 'Não', 'Não', 0),
(6, 12, 2, 2, NULL, 'fasdf dfdaf af', 'fdsafdaf df', 'Media', 7, '2026-02-18 12:02:22', '2026-02-18 19:10:39', '2026-02-18 19:10:39', 'CH20260218-F1588A', 'Não', 'Não', 0),
(7, 12, 2, 2, NULL, 'fd fadsf asfda', 'fa fdaf afaf', 'Alta', 7, '2026-02-18 12:04:00', '2026-02-18 18:43:58', '2026-02-18 18:28:27', 'CH20260218-ABB4EA', 'Não', 'Não', 0),
(12, 1, 2, 17, 7, 'Testess', 'fgdsa fdaffa', 'Media', 3, '2026-02-23 12:09:10', '2026-02-23 12:09:35', NULL, 'CH20260223-48469B', 'Não', 'Não', 0),
(13, 1, 2, 17, 8, 'Teste adm aa', 'dffdsfdsa', 'Media', 3, '2026-02-23 13:29:44', '2026-02-23 14:16:42', NULL, 'CH20260223-6D620B', 'Não', 'Não', 0),
(14, 12, 2, NULL, 7, 'Testeee', 'fdsfsdf dsaf', 'Media', 3, '2026-02-23 13:31:29', '2026-02-23 19:44:17', NULL, 'CH20260223-FE8D61', 'Não', 'Não', 0),
(15, 1, 2, 12, 8, 'Teste ad', 'gd gdgfgfsgs', 'Media', 3, '2026-02-23 14:09:16', NULL, NULL, 'CH20260223-D68C74', 'Não', 'Não', 0),
(16, 12, 2, NULL, 8, 'Chamado tsstte', 'fsdg gdfg aaa', 'Media', 4, '2026-02-23 14:15:16', '2026-02-23 19:00:46', NULL, 'CH20260223-A2BB5A', 'Não', 'Não', 0),
(17, 22, 2, NULL, 8, 'Teste', 'Testessss', 'Media', 3, '2026-02-24 10:18:00', NULL, NULL, 'CH20260224-71C4B7', 'Não', 'Não', 0),
(24, 12, 2, NULL, 3, 'aaaaaaaaa', 'ff fdffafa fsdf', 'Media', 3, '2026-02-24 10:55:03', NULL, NULL, 'CH20260224-6D15AB', 'Não', 'Não', 0),
(33, 12, 2, NULL, 2, 'aaabbbb', 'fdafdfdsfa', 'Media', 5, '2026-02-24 11:30:36', NULL, NULL, 'CH20260224-5BDC41', 'Não', 'Não', 0),
(34, 12, 2, NULL, 2, 'tttttttttttt', 'tttttttttt', 'Media', 3, '2026-02-24 11:32:33', NULL, NULL, 'CH20260224-BEF7DD', 'Não', 'Não', 0);

-- --------------------------------------------------------

--
-- Estrutura para tabela `chamados_anexos`
--

CREATE TABLE `chamados_anexos` (
  `id` int(11) NOT NULL,
  `chamado_id` int(11) NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `cliente_id` int(11) DEFAULT NULL,
  `arquivo` varchar(255) NOT NULL,
  `criado_em` datetime NOT NULL DEFAULT current_timestamp(),
  `nome` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `chamados_anexos`
--

INSERT INTO `chamados_anexos` (`id`, `chamado_id`, `usuario_id`, `cliente_id`, `arquivo`, `criado_em`, `nome`) VALUES
(5, 16, 2, 1, 'ch_16_20260223_154007_021f917ef6233927.jpeg', '2026-02-23 15:40:07', 'Arquivo teste'),
(6, 16, 2, 1, 'ch_16_20260223_154021_82dbd900e31e378e.pdf', '2026-02-23 15:40:21', 'Arquivo PDF'),
(7, 16, 2, 1, 'ch_16_20260223_154520_33a07f1c3b3de067.jpeg', '2026-02-23 15:45:20', 'aaaa'),
(8, 15, 2, 1, 'ch_15_20260223_154531_13630e628330e329.pdf', '2026-02-23 15:45:31', 'teste'),
(9, 16, 2, 1, 'ch_16_20260223_184006_9177295ce5f8988a.jpg', '2026-02-23 18:40:06', 'testesss'),
(10, 16, 2, 1, 'ch_16_20260223_184014_db145af635d4a5d3.jpg', '2026-02-23 18:40:14', 'aaaaa');

-- --------------------------------------------------------

--
-- Estrutura para tabela `chamados_movimentos`
--

CREATE TABLE `chamados_movimentos` (
  `id` int(11) NOT NULL,
  `chamado_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `tipo` enum('comentario','status','anexo','sistema') NOT NULL DEFAULT 'comentario',
  `status_de` int(11) DEFAULT NULL,
  `status_para` int(11) DEFAULT NULL,
  `mensagem` text DEFAULT NULL,
  `criado_em` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `chamados_movimentos`
--

INSERT INTO `chamados_movimentos` (`id`, `chamado_id`, `usuario_id`, `tipo`, `status_de`, `status_para`, `mensagem`, `criado_em`) VALUES
(1, 1, 2, 'status', NULL, 3, 'Chamado aberto com status: Aberto', '2026-02-18 11:39:47'),
(2, 3, 2, 'status', NULL, 4, 'Chamado aberto com status: Em análise', '2026-02-18 11:43:54'),
(3, 4, 2, 'status', NULL, 3, 'Chamado aberto com status: Aberto', '2026-02-18 11:47:08'),
(4, 5, 2, 'status', NULL, 3, 'Chamado aberto com status: Aberto', '2026-02-18 11:58:06'),
(5, 6, 2, 'status', NULL, 4, 'Chamado aberto com status: Em análise', '2026-02-18 12:02:22'),
(6, 7, 2, 'status', NULL, 3, 'Chamado aberto com status: Aberto', '2026-02-18 12:04:00'),
(11, 7, 2, 'status', 3, 4, 'Status alterado para: Em análise', '2026-02-18 16:24:04'),
(12, 7, 2, 'status', 4, 5, 'Status alterado para: Em atendimento', '2026-02-18 16:24:06'),
(13, 7, 2, 'status', 5, 6, 'Status alterado para: Aguardando cliente', '2026-02-18 16:24:08'),
(14, 7, 2, 'sistema', NULL, NULL, 'Prioridade: Media → Baixa', '2026-02-18 16:24:16'),
(15, 7, 2, 'sistema', NULL, NULL, 'Prioridade: Baixa → Alta', '2026-02-18 16:24:17'),
(16, 7, 2, 'sistema', NULL, NULL, 'Prioridade: Alta → Urgente', '2026-02-18 16:24:19'),
(17, 7, 2, 'sistema', NULL, NULL, 'Prioridade: Urgente → Baixa', '2026-02-18 16:24:20'),
(18, 7, 2, 'status', 6, 5, 'Status alterado para: Em atendimento', '2026-02-18 16:24:34'),
(19, 7, 2, 'sistema', NULL, NULL, 'Prioridade: Baixa → Media', '2026-02-18 16:26:26'),
(20, 7, 2, 'status', 5, 6, 'Status alterado para: Aguardando cliente', '2026-02-18 16:37:18'),
(21, 7, 2, 'status', 6, 3, 'Status alterado para: Aberto', '2026-02-18 16:37:19'),
(22, 7, 2, 'status', 3, 5, 'Status alterado para: Em atendimento', '2026-02-18 16:37:21'),
(23, 7, 2, '', 5, 7, NULL, '2026-02-18 17:33:28'),
(24, 5, 2, '', 3, 7, NULL, '2026-02-18 17:33:44'),
(25, 4, 2, '', 3, 7, NULL, '2026-02-18 17:34:49'),
(26, 1, 2, 'status', 3, 7, 'Status alterado para: Resolvido', '2026-02-18 17:35:08'),
(30, 7, 2, 'status', 7, 6, 'Status alterado para: Aguardando cliente', '2026-02-18 18:15:43'),
(31, 7, 2, 'status', 6, 4, 'Status alterado para: Em análise', '2026-02-18 18:17:15'),
(32, 7, 2, '', 4, 7, 'Chamado encerrado (status: Resolvido)', '2026-02-18 18:24:03'),
(33, 3, 2, '', 4, 7, 'Chamado encerrado (status: Resolvido)', '2026-02-18 18:24:38'),
(34, 7, 2, 'status', 7, 5, 'Status alterado para: Em atendimento', '2026-02-18 18:28:19'),
(35, 7, 2, '', 5, 7, 'Chamado encerrado (status: Resolvido)', '2026-02-18 18:28:27'),
(36, 5, 2, 'status', 7, 5, 'Status alterado para: Em atendimento', '2026-02-18 18:43:46'),
(37, 4, 2, 'status', 7, 6, 'Status alterado para: Aguardando cliente', '2026-02-18 18:43:53'),
(38, 7, 2, 'sistema', NULL, NULL, 'Prioridade: Media → Alta', '2026-02-18 18:43:58'),
(39, 2, 2, 'sistema', NULL, NULL, 'Prioridade: Media → Urgente', '2026-02-18 18:44:02'),
(40, 4, 2, 'status', 6, 5, 'Status alterado para: Em atendimento', '2026-02-18 19:04:46'),
(41, 5, 2, 'status', 5, 4, 'Status alterado para: Em análise', '2026-02-18 19:10:17'),
(42, 6, 2, '', 4, 7, 'Chamado encerrado (status: Resolvido)', '2026-02-18 19:10:39'),
(45, 12, 2, 'status', NULL, 3, 'Chamado aberto com status: Aberto', '2026-02-23 12:09:10'),
(46, 13, 2, 'status', NULL, 3, 'Chamado aberto com status: Aberto', '2026-02-23 13:29:44'),
(47, 14, 2, 'status', NULL, 3, 'Chamado aberto com status: Aberto', '2026-02-23 13:31:29'),
(48, 15, 2, 'status', NULL, 3, 'Chamado aberto com status: Aberto', '2026-02-23 14:09:16'),
(49, 16, 2, 'status', NULL, 4, 'Chamado aberto com status: Em análise', '2026-02-23 14:15:16'),
(50, 13, 17, 'sistema', NULL, NULL, 'Assunto atualizado', '2026-02-23 14:16:22'),
(51, 16, 17, 'sistema', NULL, NULL, 'Assunto atualizado | Setor Alterado', '2026-02-23 14:20:58'),
(52, 16, 17, 'sistema', NULL, NULL, 'Assunto atualizado | Setor Alterado', '2026-02-23 14:21:25'),
(53, 16, 17, 'sistema', NULL, NULL, 'Setor Alterado', '2026-02-23 14:22:46'),
(54, 16, 17, 'sistema', NULL, NULL, 'Setor Alterado', '2026-02-23 14:22:59'),
(55, 16, 17, 'sistema', NULL, NULL, 'Descrição atualizada | Setor Alterado', '2026-02-23 14:23:08'),
(56, 16, 17, 'sistema', NULL, NULL, 'Assunto atualizado | Setor Alterado', '2026-02-23 14:25:27'),
(57, 16, 17, 'sistema', NULL, NULL, 'Assunto atualizado | 8', '2026-02-23 14:25:55'),
(58, 16, 17, 'sistema', NULL, NULL, 'Assunto atualizado | ', '2026-02-23 14:26:10'),
(59, 16, 17, 'sistema', NULL, NULL, 'Assunto atualizado | Setor Alterado', '2026-02-23 14:27:16'),
(60, 16, 17, 'sistema', NULL, NULL, 'Setor Alterado', '2026-02-23 14:27:34'),
(61, 16, 17, 'sistema', NULL, NULL, 'Assunto atualizado', '2026-02-23 14:28:24'),
(62, 16, 17, 'sistema', NULL, NULL, 'Assunto atualizado | Setor Alterado', '2026-02-23 14:28:37'),
(63, 16, 17, 'sistema', NULL, NULL, 'Setor Alterado', '2026-02-23 14:28:43'),
(64, 16, 2, 'sistema', NULL, NULL, 'Cliente alterado', '2026-02-23 19:00:46'),
(65, 14, 2, 'sistema', NULL, NULL, 'Cliente alterado', '2026-02-23 19:44:17'),
(66, 17, 2, 'status', NULL, 3, 'Chamado aberto com status: Aberto', '2026-02-24 10:18:00'),
(73, 24, 2, 'status', NULL, 3, 'Chamado aberto com status: Aberto', '2026-02-24 10:55:03'),
(82, 33, 2, 'status', NULL, 5, 'Chamado aberto com status: Em atendimento', '2026-02-24 11:30:36'),
(83, 34, 2, 'status', NULL, 3, 'Chamado aberto com status: Aberto', '2026-02-24 11:32:33');

-- --------------------------------------------------------

--
-- Estrutura para tabela `chamados_respostas`
--

CREATE TABLE `chamados_respostas` (
  `id` int(11) NOT NULL,
  `chamado_id` int(11) NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `cliente_id` int(11) DEFAULT NULL,
  `tipo_autor` enum('usuario','cliente','sistema') NOT NULL DEFAULT 'usuario',
  `mensagem` text NOT NULL,
  `visivel_cliente` enum('Sim','Nao') NOT NULL DEFAULT 'Sim',
  `criado_em` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `chamados_respostas`
--

INSERT INTO `chamados_respostas` (`id`, `chamado_id`, `usuario_id`, `cliente_id`, `tipo_autor`, `mensagem`, `visivel_cliente`, `criado_em`) VALUES
(1, 16, 2, 1, 'usuario', 'fa fd faf da fadsf ad', 'Sim', '2026-02-23 18:52:45'),
(2, 16, 2, 1, 'usuario', 'fda faffdsf df fa fdsaf', 'Sim', '2026-02-23 18:54:21'),
(4, 16, 2, 1, 'usuario', 'fda fda fsadfasdfa ffdfdafda fasdf', 'Sim', '2026-02-23 18:58:30'),
(5, 16, 2, 12, 'usuario', 'f gsgdsa ggfdsg dfsgfd', 'Sim', '2026-02-23 19:00:50'),
(6, 16, 2, 12, 'usuario', 'fda df faf fa fdsaf', 'Sim', '2026-02-23 19:02:06'),
(7, 16, 2, 12, 'usuario', 'fda fda fdafasdfsadfd afda', 'Sim', '2026-02-23 19:03:01'),
(8, 16, 2, 12, 'cliente', 'f asdfdaf dfads fda fadf', 'Sim', '2026-02-23 19:04:49'),
(10, 15, 2, 1, 'usuario', 'fdsa dfdfds afdsaf fad afadfa fadf saf adsf daf af', 'Sim', '2026-02-23 19:31:25'),
(11, 15, 2, 1, 'usuario', 'd fafafd asfa sfa', 'Sim', '2026-02-23 19:32:49'),
(12, 15, 2, 1, 'usuario', 'fad fasdf dasfadsf', 'Sim', '2026-02-23 19:33:04'),
(13, 15, 2, 1, 'usuario', 'fa fdaf asdf', 'Sim', '2026-02-23 19:33:49'),
(14, 15, 2, 1, 'usuario', 'fda fdafafda fsdaf', 'Sim', '2026-02-23 19:37:42'),
(15, 14, 2, 1, 'usuario', 'fda fdasf afdaf', 'Sim', '2026-02-23 19:42:34'),
(16, 14, 2, 1, 'usuario', 'fda fdasf afdaf fd gdfg', 'Sim', '2026-02-23 19:43:13'),
(17, 14, 2, 12, 'usuario', 'fda fadf dafadfdaf asdf', 'Sim', '2026-02-23 19:44:23'),
(18, 14, 2, 12, 'usuario', 'aaaaaaaaaaaaaaaaaaaaaaaaaaa', 'Sim', '2026-02-23 19:44:38'),
(19, 14, 2, 12, 'usuario', 'fa fdf afda fadfsda fa fs a bbbbbbbbbbb geda fdafa', 'Sim', '2026-02-23 19:45:25'),
(20, 14, 2, 12, 'usuario', 'fda faf afa fdfa eeeeeeeeeeeeeeeeee', 'Sim', '2026-02-23 19:45:52'),
(21, 15, 2, 1, 'usuario', 'fda fda fad fadfds', 'Sim', '2026-02-23 19:46:58'),
(22, 14, 2, 12, 'usuario', 'fa fdaf af asffa afd', 'Sim', '2026-02-23 19:58:15'),
(23, 15, 2, 1, 'usuario', 'fda fdsa faf df', 'Sim', '2026-02-23 19:58:31'),
(27, 14, 2, 12, 'cliente', 'fda fafdaf', 'Sim', '2026-02-23 20:48:27'),
(30, 13, 2, 1, 'usuario', 'df ggfg fsg fsg fdsgfsgsgfs', 'Sim', '2026-02-24 13:42:58');

-- --------------------------------------------------------

--
-- Estrutura para tabela `chamados_status`
--

CREATE TABLE `chamados_status` (
  `id` int(11) NOT NULL,
  `nome` varchar(60) NOT NULL,
  `cor` varchar(20) NOT NULL DEFAULT '#6c757d',
  `ativo` enum('Sim','Não') NOT NULL DEFAULT 'Sim',
  `ordem` int(11) NOT NULL DEFAULT 0,
  `padrao` enum('Sim','Não') NOT NULL DEFAULT 'Não',
  `fechado` enum('Sim','Não') NOT NULL DEFAULT 'Não',
  `criado_em` datetime NOT NULL DEFAULT current_timestamp(),
  `atualizado_em` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `chamados_status`
--

INSERT INTO `chamados_status` (`id`, `nome`, `cor`, `ativo`, `ordem`, `padrao`, `fechado`, `criado_em`, `atualizado_em`) VALUES
(3, 'Aberto', '#0d6efd', 'Sim', 1, 'Sim', 'Não', '2026-02-18 10:36:52', NULL),
(4, 'Em análise', '#6f42c1', 'Sim', 2, 'Não', 'Não', '2026-02-18 10:36:52', NULL),
(5, 'Em atendimento', '#20c997', 'Sim', 3, 'Não', 'Não', '2026-02-18 10:36:52', NULL),
(6, 'Aguardando cliente', '#fd7e14', 'Sim', 4, 'Não', 'Não', '2026-02-18 10:36:52', NULL),
(7, 'Resolvido', '#198754', 'Sim', 5, 'Não', 'Sim', '2026-02-18 10:36:52', NULL),
(8, 'Fechado', '#6c757d', 'Sim', 6, 'Não', 'Sim', '2026-02-18 10:36:52', NULL);

-- --------------------------------------------------------

--
-- Estrutura para tabela `clientes`
--

CREATE TABLE `clientes` (
  `id` int(11) NOT NULL,
  `nome` varchar(120) NOT NULL,
  `email` varchar(120) DEFAULT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `cpf_cnpj` varchar(20) DEFAULT NULL,
  `senha` varchar(255) DEFAULT NULL,
  `ativo` varchar(10) DEFAULT NULL,
  `cep` varchar(10) DEFAULT NULL,
  `endereco` varchar(150) DEFAULT NULL,
  `numero` varchar(20) DEFAULT NULL,
  `complemento` varchar(60) DEFAULT NULL,
  `bairro` varchar(80) DEFAULT NULL,
  `cidade` varchar(80) DEFAULT NULL,
  `estado` char(2) DEFAULT NULL,
  `tipo` varchar(30) DEFAULT NULL,
  `observacoes` text DEFAULT NULL,
  `ultimo_acesso` datetime DEFAULT NULL,
  `ip_ultimo_acesso` varchar(45) DEFAULT NULL,
  `data_cadastro` datetime DEFAULT NULL,
  `empresa` int(11) DEFAULT NULL,
  `foto` varchar(150) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `clientes`
--

INSERT INTO `clientes` (`id`, `nome`, `email`, `telefone`, `cpf_cnpj`, `senha`, `ativo`, `cep`, `endereco`, `numero`, `complemento`, `bairro`, `cidade`, `estado`, `tipo`, `observacoes`, `ultimo_acesso`, `ip_ultimo_acesso`, `data_cadastro`, `empresa`, `foto`) VALUES
(1, 'Cliente Teste', 'teste@teste.com.br', '(00) 00000-0000', '000.000.000-00', '$2y$10$FsgmpkbSbF.12djhPf0Q2OHgLUOiIus7iGVMnOX9lTbNfBAvmqLpK', 'Sim', '30520-150', 'Rua Titânio', NULL, NULL, 'Camargos', 'Belo Horizonte', 'MG', 'Pessoa Física', 'Teste', NULL, NULL, '2026-02-09 14:16:30', 0, 'c_20260209_141630_d4f9ee78.jpg'),
(12, 'Cliente Teste Hugo', 'contato@hugocursos.com.br', '(31) 97527-5084', '555.555.555-55', '$2y$10$q64legB2PowwYg0O0BDgSublbkivtYQ1l/K3cEsHiFgn.j2hMrKm6', 'Sim', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-10 20:52:18', 0, 'c_20260223_203722_2ec4b992.png'),
(20, 'Cliente Teste 1', 'cliente1.teste@exemplo.com', '(31) 99999-1111', '123.456.789-09', '$2y$10$tROt0G6M4gA.nr8VPBAJVe0FNrSk2pbBkNvYBWkbuL6/7m0o1Z/gm', 'Sim', '30110-012', 'Av. Afonso Pena', '1000', 'Sala 101', 'Centro', 'Belo Horizonte', 'MG', 'Física', 'Importação de teste 1', NULL, NULL, '2026-02-15 00:00:00', NULL, NULL),
(21, 'Cliente Teste 2', 'cliente2.teste@exemplo.com', '(11) 98888-2222', '12.345.678/0001-90', '$2y$10$Bg6YKlMYjx73HelQdz4cne1HygEOCXIB2pEaPtDwdAQCVjCUu5alG', 'Não', '01001-000', 'Praça da Sé', '200', NULL, 'Sé', 'São Paulo', 'SP', 'Jurídica', 'Importação de teste 2', NULL, NULL, '2026-01-10 00:00:00', NULL, NULL),
(22, 'Cli Teste 2', NULL, NULL, '87000000000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Estrutura para tabela `config`
--

CREATE TABLE `config` (
  `id` int(11) NOT NULL,
  `nome_sistema` varchar(100) DEFAULT NULL,
  `telefone_sistema` varchar(20) DEFAULT NULL,
  `email_sistema` varchar(75) DEFAULT NULL,
  `cor_primaria` varchar(25) DEFAULT NULL,
  `cor_secundaria` varchar(25) DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `icone` varchar(255) DEFAULT NULL,
  `endereco` varchar(255) DEFAULT NULL,
  `smtp_host` varchar(120) DEFAULT NULL,
  `smtp_senha` varchar(255) DEFAULT NULL,
  `smtp_porta` int(5) DEFAULT NULL,
  `smtp_seguranca` varchar(10) DEFAULT NULL,
  `empresa` int(11) DEFAULT NULL,
  `api_whatsapp` varchar(100) DEFAULT NULL,
  `token_whatsapp` text DEFAULT NULL,
  `instancia_whatsapp` varchar(150) DEFAULT NULL,
  `url_api` varchar(255) DEFAULT NULL,
  `api_ia` varchar(50) DEFAULT NULL,
  `token_ia` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Despejando dados para a tabela `config`
--

INSERT INTO `config` (`id`, `nome_sistema`, `telefone_sistema`, `email_sistema`, `cor_primaria`, `cor_secundaria`, `logo`, `icone`, `endereco`, `smtp_host`, `smtp_senha`, `smtp_porta`, `smtp_seguranca`, `empresa`, `api_whatsapp`, `token_whatsapp`, `instancia_whatsapp`, `url_api`, `api_ia`, `token_ia`) VALUES
(1, 'Sistemas com IA', '(31) 97527-5084', 'contato@hugocursos.com.br', '#667eea', '#764ba2', 'logo_20260203_194422_8c97291c.webp', 'icone_20260203_194422_8aa51489.png', 'Rua Teste 1', 'mail.hugocursos.com.br', 'o1z+XvO+CBBhBD6nLQmNCZ3PyUvyrTR1rhVQu6W96fXbMwtbKg==', 587, 'tls', 0, 'menuia', '5c2f810d-a173-4780-a51f-f0a2ef134dff', 'v9t7zpp51nsSCMeqqJWfI4lj8iGG12tyMqW8PwvBH3CojiUaHM', 'https://evo.multiatendeia.com.br', 'gemini', 'AIzaSyCRYAnGjcX4qO-XItRjHzROLKMuw7Jhnyo');

-- --------------------------------------------------------

--
-- Estrutura para tabela `logs`
--

CREATE TABLE `logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `empresa` int(11) NOT NULL DEFAULT 0,
  `usuario_id` int(11) NOT NULL DEFAULT 0,
  `acao` enum('login','logout','inserir','editar','excluir') NOT NULL,
  `entidade` varchar(50) NOT NULL DEFAULT '',
  `registro_id` bigint(20) UNSIGNED DEFAULT NULL,
  `descricao` varchar(255) DEFAULT NULL,
  `rota` varchar(120) DEFAULT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `criado_em` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `logs`
--

INSERT INTO `logs` (`id`, `empresa`, `usuario_id`, `acao`, `entidade`, `registro_id`, `descricao`, `rota`, `ip`, `user_agent`, `criado_em`) VALUES
(1, 0, 2, 'login', 'usuarios', 2, 'Login realizado com sucesso', '/helpdesk/autenticar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-13 13:58:59'),
(2, 0, 2, 'inserir', 'cargos', 8, 'Cargo \'fa fasf\' cadastrado', '/helpdesk/painel/ajax/cargos/salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-13 15:03:15'),
(3, 0, 2, 'editar', 'cargos', 8, 'Cargo \'aaaaaaaaa\' atualizado', '/helpdesk/painel/ajax/cargos/salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-13 15:03:19'),
(4, 0, 2, 'excluir', 'cargos', 8, 'Usuário excluído do sistema', '/helpdesk/painel/ajax/cargos/excluir.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-13 15:03:22'),
(5, 0, 2, 'inserir', 'cargos', 9, 'Cargo \'dfa fadf\' cadastrado', '/helpdesk/painel/ajax/cargos/salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-13 15:04:29'),
(6, 0, 2, 'inserir', 'cargos', 10, 'Cargo \'fa fadfads\' cadastrado', '/helpdesk/painel/ajax/cargos/salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-13 15:04:32'),
(7, 0, 2, 'editar', 'cargos', 10, 'Cargo \'fa fadfads fafdadsf a\' atualizado', '/helpdesk/painel/ajax/cargos/salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-13 15:06:40'),
(8, 0, 2, 'editar', 'cargos', 10, 'Cargo \'fa fadfads fafdadsf ad fa fad\' atualizado', '/helpdesk/painel/ajax/cargos/salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-13 15:06:43'),
(9, 0, 2, 'editar', 'cargos', 9, 'Cargo \'dfa fadf\' atualizado', '/helpdesk/painel/ajax/cargos/salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-13 15:06:46'),
(10, 0, 2, 'editar', 'cargos', 3, 'Cargo \'Comum\' atualizado', '/helpdesk/painel/ajax/cargos/salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-13 15:06:49'),
(11, 0, 2, 'excluir', 'cargos', 10, 'Usuário excluído do sistema', '/helpdesk/painel/ajax/cargos/excluir.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-13 15:06:52'),
(12, 0, 2, 'excluir', 'cargos', 9, 'Usuário excluído do sistema', '/helpdesk/painel/ajax/cargos/excluir.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-13 15:06:55'),
(13, 0, 2, 'inserir', 'clientes', 14, 'Cliente \'fad faf\' cadastrado', '/helpdesk/painel/ajax/clientes/salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-13 15:07:04'),
(14, 0, 2, 'inserir', 'clientes', 15, 'Cliente \'fda fadfafdaf\' cadastrado', '/helpdesk/painel/ajax/clientes/salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-13 15:07:07'),
(15, 0, 2, 'editar', 'clientes', 15, 'Cliente \'fda fadfafdaf\' atualizado', '/helpdesk/painel/ajax/clientes/salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-13 15:07:11'),
(16, 0, 2, 'excluir', 'clientes', 15, 'Usuário excluído do sistema', '/helpdesk/painel/ajax/clientes/excluir.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-13 15:07:14'),
(17, 0, 2, 'excluir', 'clientes', 14, 'Usuário excluído do sistema', '/helpdesk/painel/ajax/clientes/excluir.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-13 15:07:16'),
(18, 0, 2, 'login', 'usuarios', 2, 'Login realizado com sucesso', '/helpdesk/autenticar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-13 15:07:20'),
(19, 0, 0, 'logout', 'usuarios', 0, 'Logout realizado com sucesso', '/helpdesk/painel/logout.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-13 15:09:15'),
(20, 0, 2, 'login', 'usuarios', 2, 'Login realizado com sucesso', '/helpdesk/autenticar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-13 15:09:17'),
(21, 0, 0, 'logout', 'usuarios', 0, 'Logout realizado com sucesso', '/helpdesk/painel/logout.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-13 15:09:18'),
(22, 0, 2, 'login', 'usuarios', 2, 'Login realizado com sucesso', '/helpdesk/autenticar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-13 15:09:22'),
(23, 0, 2, 'editar', 'clientes', 12, 'Cliente \'Cliente Teste\' atualizado', '/helpdesk/painel/ajax/clientes/salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-15 20:25:13'),
(24, 0, 2, 'inserir', 'usuarios', 70, 'Usuário \'Novo Teste usu email\' cadastrado', '/helpdesk/painel/ajax/usuarios/salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-15 20:45:38'),
(25, 0, 2, 'excluir', 'usuarios', 31, 'Usuário excluído do sistema', '/helpdesk/painel/ajax/usuarios/excluir.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-15 20:47:10'),
(26, 0, 2, 'inserir', 'usuarios', 73, 'Usuário \'Novo testes usu\' cadastrado', '/helpdesk/painel/ajax/usuarios/salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-15 20:47:19'),
(27, 0, 0, 'logout', 'usuarios', 0, 'Logout realizado com sucesso', '/helpdesk/painel/logout.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-15 20:49:50'),
(28, 0, 2, 'login', 'usuarios', 2, 'Login realizado com sucesso', '/helpdesk/autenticar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-15 20:50:18'),
(29, 0, 2, 'excluir', 'usuarios', 32, 'Usuário excluído do sistema', '/helpdesk/painel/ajax/usuarios/excluir.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-15 20:51:57'),
(30, 0, 2, 'inserir', 'usuarios', 76, 'Usuário \'Novo teste usu\' cadastrado', '/helpdesk/painel/ajax/usuarios/salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-15 20:52:06'),
(31, 0, 2, 'excluir', 'usuarios', 33, 'Usuário excluído do sistema', '/helpdesk/painel/ajax/usuarios/excluir.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-15 20:52:23'),
(32, 0, 2, 'inserir', 'usuarios', 79, 'Usuário \'Novo tstttt\' cadastrado', '/helpdesk/painel/ajax/usuarios/salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-15 20:52:34'),
(33, 0, 2, 'excluir', 'usuarios', 34, 'Usuário excluído do sistema', '/helpdesk/painel/ajax/usuarios/excluir.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-15 20:53:53'),
(34, 0, 2, 'inserir', 'usuarios', 82, 'Usuário \'Novo testesss\' cadastrado', '/helpdesk/painel/ajax/usuarios/salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-15 20:54:06'),
(35, 0, 2, 'excluir', 'usuarios', 35, 'Usuário excluído do sistema', '/helpdesk/painel/ajax/usuarios/excluir.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-15 20:57:10'),
(36, 0, 2, 'inserir', 'clientes', 16, 'Cliente \'Cliente teste\' cadastrado', '/helpdesk/painel/ajax/clientes/salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-15 21:01:59'),
(37, 0, 2, 'excluir', 'clientes', 16, 'Usuário excluído do sistema', '/helpdesk/painel/ajax/clientes/excluir.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-15 21:03:39'),
(38, 0, 2, 'inserir', 'clientes', 17, 'Cliente \'Hugo Cliente\' cadastrado', '/helpdesk/painel/ajax/clientes/salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-15 21:04:00'),
(39, 0, 2, 'excluir', 'clientes', 17, 'Usuário excluído do sistema', '/helpdesk/painel/ajax/clientes/excluir.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-15 21:05:20'),
(40, 0, 2, 'inserir', 'clientes', 18, 'Cliente \'Cliente Teste 1\' importado via XLSX', '/helpdesk/painel/ajax/clientes/importar_xls.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-15 22:22:03'),
(41, 0, 2, 'inserir', 'clientes', 19, 'Cliente \'Cliente Teste 2\' importado via XLSX', '/helpdesk/painel/ajax/clientes/importar_xls.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-15 22:22:03'),
(42, 0, 0, 'logout', 'usuarios', 0, 'Logout realizado com sucesso', '/helpdesk/painel/logout.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-16 09:49:52'),
(43, 0, 27, 'login', 'usuarios', 27, 'Login realizado com sucesso', '/helpdesk/autenticar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-16 09:49:55'),
(44, 0, 0, 'logout', 'usuarios', 0, 'Logout realizado com sucesso', '/helpdesk/painel/logout.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-16 10:04:33'),
(45, 0, 27, 'login', 'usuarios', 27, 'Login realizado com sucesso', '/helpdesk/autenticar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-16 10:04:34'),
(46, 0, 0, 'logout', 'usuarios', 0, 'Logout realizado com sucesso', '/helpdesk/painel/logout.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-16 10:04:36'),
(47, 0, 2, 'login', 'usuarios', 2, 'Login realizado com sucesso', '/helpdesk/autenticar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-16 10:04:54'),
(48, 0, 2, 'excluir', 'clientes', 19, 'Usuário excluído do sistema', '/helpdesk/painel/ajax/clientes/excluir.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-16 10:21:06'),
(49, 0, 2, 'excluir', 'clientes', 18, 'Usuário excluído do sistema', '/helpdesk/painel/ajax/clientes/excluir.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-16 10:21:09'),
(50, 0, 2, 'inserir', 'clientes', 20, 'Cliente \'Cliente Teste 1\' importado via XLSX', '/helpdesk/painel/ajax/clientes/importar_xls.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-16 10:21:35'),
(51, 0, 2, 'inserir', 'clientes', 21, 'Cliente \'Cliente Teste 2\' importado via XLSX', '/helpdesk/painel/ajax/clientes/importar_xls.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-16 10:21:35'),
(52, 0, 2, 'login', 'usuarios', 2, 'Login realizado com sucesso', '/helpdesk/autenticar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-18 10:02:03'),
(53, 0, 0, 'logout', 'usuarios', 0, 'Logout realizado com sucesso', '/helpdesk/painel/logout.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-18 10:02:35'),
(54, 0, 2, 'login', 'usuarios', 2, 'Login realizado com sucesso', '/helpdesk/autenticar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-18 10:07:00'),
(55, 0, 0, 'logout', 'usuarios', 0, 'Logout realizado com sucesso', '/helpdesk/painel/logout.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-18 10:10:43'),
(56, 0, 2, 'login', 'usuarios', 2, 'Login realizado com sucesso', '/helpdesk/autenticar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-18 10:10:44'),
(57, 0, 2, 'inserir', 'chamados_status', 1, 'Status \'Status Inicial\' cadastrado', '/helpdesk/painel/ajax/status_abertura/salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-18 10:35:33'),
(58, 0, 2, 'inserir', 'chamados_status', 2, 'Status \'Teste\' cadastrado', '/helpdesk/painel/ajax/status_abertura/salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-18 10:36:01'),
(59, 0, 2, 'editar', 'chamados_status', 2, 'Status \'Teste aaaa\' atualizado', '/helpdesk/painel/ajax/status_abertura/salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-18 10:36:11'),
(60, 0, 2, 'excluir', 'chamados_status', 2, 'Status \'Teste aaaa\' excluído', '/helpdesk/painel/ajax/status_abertura/excluir.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-18 10:36:18'),
(61, 0, 2, 'editar', 'chamados_status', 1, 'Status \'Status Inicial\' atualizado', '/helpdesk/painel/ajax/status_abertura/salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-18 10:36:34'),
(62, 0, 2, 'excluir', 'chamados_status', 1, 'Status \'Status Inicial\' excluído', '/helpdesk/painel/ajax/status_abertura/excluir.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-18 10:36:37'),
(63, 0, 2, 'editar', 'clientes', 12, 'Cliente \'Cliente Teste\' atualizado', '/helpdesk/painel/ajax/clientes/salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-18 11:29:13'),
(64, 0, 2, 'editar', 'clientes', 12, 'Cliente \'Cliente Teste Hugo\' atualizado', '/helpdesk/painel/ajax/clientes/salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-18 11:29:29'),
(65, 0, 2, 'inserir', 'chamados', 1, 'Chamado \'CH20260218-8B7AE8\' aberto', '/helpdesk/painel/ajax/abertura/salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-18 11:39:47'),
(66, 0, 2, 'inserir', 'chamados', 3, 'Chamado \'CH20260218-0B26F3\' aberto', '/helpdesk/painel/ajax/abertura/salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-18 11:43:54'),
(67, 0, 2, 'inserir', 'chamados', 4, 'Chamado \'CH20260218-2F34D7\' aberto', '/helpdesk/painel/ajax/abertura/salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-18 11:47:08'),
(68, 0, 2, 'editar', 'usuarios', 2, 'Usuário \'Usuário Administrador\' atualizado', '/helpdesk/painel/ajax/usuarios/salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-18 11:57:49'),
(69, 0, 2, 'inserir', 'chamados', 5, 'Chamado \'CH20260218-536361\' aberto', '/helpdesk/painel/ajax/abertura/salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-18 11:58:06'),
(70, 0, 2, 'inserir', 'chamados', 6, 'Chamado \'CH20260218-F1588A\' aberto', '/helpdesk/painel/ajax/abertura/salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-18 12:02:22'),
(71, 0, 2, 'editar', 'usuarios', 2, 'Usuário \'Usuário Administrador\' atualizado', '/helpdesk/painel/ajax/usuarios/salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-18 12:03:44'),
(72, 0, 2, 'inserir', 'chamados', 7, 'Chamado \'CH20260218-ABB4EA\' aberto', '/helpdesk/painel/ajax/abertura/salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-18 12:04:00'),
(73, 0, 2, 'editar', 'usuarios', 2, 'Usuário \'Usuário Administrador\' atualizado', '/helpdesk/painel/ajax/usuarios/salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-18 12:04:40'),
(74, 0, 2, 'inserir', 'chamados', 8, 'Chamado \'CH20260218-47239B\' aberto', '/helpdesk/painel/ajax/abertura/salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-18 12:08:37'),
(75, 0, 0, 'logout', 'usuarios', 0, 'Logout realizado com sucesso', '/helpdesk/painel/logout.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-18 12:11:27'),
(76, 0, 2, 'login', 'usuarios', 2, 'Login realizado com sucesso', '/helpdesk/autenticar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-18 12:11:58'),
(77, 0, 0, 'logout', 'usuarios', 0, 'Logout realizado com sucesso', '/helpdesk/painel/logout.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-18 12:12:36'),
(78, 0, 2, 'login', 'usuarios', 2, 'Login realizado com sucesso', '/helpdesk/autenticar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-18 12:12:52'),
(79, 0, 2, 'inserir', 'chamados', 9, 'Chamado \'CH20260218-8DA2D9\' aberto', '/helpdesk/painel/ajax/abertura/salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-18 12:13:14'),
(80, 0, 0, 'logout', 'usuarios', 0, 'Logout realizado com sucesso', '/helpdesk/painel/logout.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-18 12:14:15'),
(81, 0, 2, 'login', 'usuarios', 2, 'Login realizado com sucesso', '/helpdesk/autenticar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-18 12:14:19'),
(82, 0, 2, 'editar', 'chamados', 9, 'Chamado \'CH20260218-8DA2D9\' atualizado', '/helpdesk/painel/ajax/abertura/salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-18 14:05:14'),
(83, 0, 2, 'editar', 'chamados', 9, 'Chamado \'CH20260218-8DA2D9\' atualizado', '/helpdesk/painel/ajax/abertura/salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-18 14:05:44'),
(84, 0, 2, 'excluir', 'chamados', 9, 'Chamado excluído: CH20260218-8DA2D9 - fa fdafasdfasd fda fsaf', '/helpdesk/painel/ajax/abertura/excluir.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-18 14:16:26'),
(85, 0, 2, 'excluir', 'chamados', 8, 'Chamado excluído: CH20260218-47239B - f afasdf af af', '/helpdesk/painel/ajax/abertura/excluir.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-18 14:16:30'),
(86, 0, 2, 'editar', 'chamados', 7, 'Chamado \'CH20260218-ABB4EA\' atualizado (rápido): status -> Em análise', '/helpdesk/painel/ajax/abertura/atualizar_rapido.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-18 16:24:04'),
(87, 0, 2, 'editar', 'chamados', 7, 'Chamado \'CH20260218-ABB4EA\' atualizado (rápido): status -> Em atendimento', '/helpdesk/painel/ajax/abertura/atualizar_rapido.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-18 16:24:06'),
(88, 0, 2, 'editar', 'chamados', 7, 'Chamado \'CH20260218-ABB4EA\' atualizado (rápido): status -> Aguardando cliente', '/helpdesk/painel/ajax/abertura/atualizar_rapido.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-18 16:24:08'),
(89, 0, 2, 'editar', 'chamados', 7, 'Chamado \'CH20260218-ABB4EA\' atualizado (rápido): prioridade -> Baixa', '/helpdesk/painel/ajax/abertura/atualizar_rapido.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-18 16:24:16'),
(90, 0, 2, 'editar', 'chamados', 7, 'Chamado \'CH20260218-ABB4EA\' atualizado (rápido): prioridade -> Alta', '/helpdesk/painel/ajax/abertura/atualizar_rapido.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-18 16:24:17'),
(91, 0, 2, 'editar', 'chamados', 7, 'Chamado \'CH20260218-ABB4EA\' atualizado (rápido): prioridade -> Urgente', '/helpdesk/painel/ajax/abertura/atualizar_rapido.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-18 16:24:19'),
(92, 0, 2, 'editar', 'chamados', 7, 'Chamado \'CH20260218-ABB4EA\' atualizado (rápido): prioridade -> Baixa', '/helpdesk/painel/ajax/abertura/atualizar_rapido.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-18 16:24:20'),
(93, 0, 2, 'editar', 'chamados', 7, 'Chamado \'CH20260218-ABB4EA\' atualizado (rápido): status -> Em atendimento', '/helpdesk/painel/ajax/abertura/atualizar_rapido.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-18 16:24:34'),
(94, 0, 2, 'editar', 'chamados', 7, 'Chamado \'CH20260218-ABB4EA\' atualizado (rápido): prioridade -> Media', '/helpdesk/painel/ajax/abertura/atualizar_rapido.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-18 16:26:26'),
(95, 0, 2, 'editar', 'chamados', 7, 'Chamado \'CH20260218-ABB4EA\' atualizado (rápido): status -> Aguardando cliente', '/helpdesk/painel/ajax/abertura/atualizar_rapido.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-18 16:37:18'),
(96, 0, 2, 'editar', 'chamados', 7, 'Chamado \'CH20260218-ABB4EA\' atualizado (rápido): status -> Aberto', '/helpdesk/painel/ajax/abertura/atualizar_rapido.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-18 16:37:19'),
(97, 0, 2, 'editar', 'chamados', 7, 'Chamado \'CH20260218-ABB4EA\' atualizado (rápido): status -> Em atendimento', '/helpdesk/painel/ajax/abertura/atualizar_rapido.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-18 16:37:21'),
(98, 0, 2, 'editar', 'chamados', 7, 'Chamado encerrado: CH20260218-ABB4EA', '/helpdesk/painel/ajax/abertura/encerrar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-18 17:33:28'),
(99, 0, 2, 'editar', 'chamados', 5, 'Chamado encerrado: CH20260218-536361', '/helpdesk/painel/ajax/abertura/encerrar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-18 17:33:44'),
(100, 0, 2, 'editar', 'chamados', 4, 'Chamado encerrado: CH20260218-2F34D7', '/helpdesk/painel/ajax/abertura/encerrar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-18 17:34:49'),
(101, 0, 2, 'editar', 'chamados', 1, 'Chamado \'CH20260218-8B7AE8\' atualizado (rápido): status -> Resolvido', '/helpdesk/painel/ajax/abertura/atualizar_rapido.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-18 17:35:08'),
(102, 0, 0, 'logout', 'usuarios', 0, 'Logout realizado com sucesso', '/helpdesk/painel/logout.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-18 18:15:32'),
(103, 0, 2, 'login', 'usuarios', 2, 'Login realizado com sucesso', '/helpdesk/autenticar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-18 18:15:34'),
(104, 0, 2, 'editar', 'chamados', 7, 'Chamado \'CH20260218-ABB4EA\' atualizado (rápido): status -> Aguardando cliente', '/helpdesk/painel/ajax/abertura/atualizar_rapido.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-18 18:15:43'),
(105, 0, 2, 'editar', 'chamados', 7, 'Chamado \'CH20260218-ABB4EA\' atualizado (rápido): status -> Em análise', '/helpdesk/painel/ajax/abertura/atualizar_rapido.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-18 18:17:15'),
(106, 0, 2, 'editar', 'chamados', 7, 'Chamado encerrado: CH20260218-ABB4EA', '/helpdesk/painel/ajax/abertura/encerrar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-18 18:24:03'),
(107, 0, 2, 'editar', 'chamados', 3, 'Chamado encerrado: CH20260218-0B26F3', '/helpdesk/painel/ajax/abertura/encerrar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-18 18:24:38'),
(108, 0, 2, 'editar', 'chamados', 7, 'Chamado \'CH20260218-ABB4EA\' atualizado (rápido): status -> Em atendimento', '/helpdesk/painel/ajax/abertura/atualizar_rapido.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-18 18:28:19'),
(109, 0, 2, 'editar', 'chamados', 7, 'Chamado encerrado: CH20260218-ABB4EA', '/helpdesk/painel/ajax/abertura/encerrar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-18 18:28:27'),
(110, 0, 0, 'logout', 'usuarios', 0, 'Logout realizado com sucesso', '/helpdesk/painel/logout.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-18 18:35:05'),
(111, 0, 2, 'login', 'usuarios', 2, 'Login realizado com sucesso', '/helpdesk/autenticar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-18 18:37:09'),
(112, 0, 2, 'login', 'usuarios', 2, 'Login realizado com sucesso', '/helpdesk/autenticar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-18 18:37:32'),
(113, 0, 0, 'logout', 'usuarios', 0, 'Logout realizado com sucesso', '/helpdesk/painel/logout.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-18 18:39:53'),
(114, 0, 2, 'login', 'usuarios', 2, 'Login realizado com sucesso', '/helpdesk/autenticar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-18 18:39:54'),
(115, 0, 2, 'login', 'usuarios', 2, 'Login realizado com sucesso', '/helpdesk/autenticar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-18 18:40:10'),
(116, 0, 2, 'editar', 'chamados', 5, 'Chamado \'CH20260218-536361\' atualizado (rápido): status -> Em atendimento', '/helpdesk/painel/ajax/abertura/atualizar_rapido.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-18 18:43:46'),
(117, 0, 2, 'editar', 'chamados', 4, 'Chamado \'CH20260218-2F34D7\' atualizado (rápido): status -> Aguardando cliente', '/helpdesk/painel/ajax/abertura/atualizar_rapido.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-18 18:43:53'),
(118, 0, 2, 'editar', 'chamados', 7, 'Chamado \'CH20260218-ABB4EA\' atualizado (rápido): prioridade -> Alta', '/helpdesk/painel/ajax/abertura/atualizar_rapido.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-18 18:43:58'),
(119, 0, 2, 'editar', 'chamados', 2, 'Chamado \'CH20260218-AF8348\' atualizado (rápido): prioridade -> Urgente', '/helpdesk/painel/ajax/abertura/atualizar_rapido.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-18 18:44:02'),
(120, 0, 2, 'editar', 'chamados', 4, 'Chamado \'CH20260218-2F34D7\' atualizado (rápido): status -> Em atendimento', '/helpdesk/painel/ajax/abertura/atualizar_rapido.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-18 19:04:46'),
(121, 0, 2, 'editar', 'chamados', 5, 'Chamado \'CH20260218-536361\' atualizado (rápido): status -> Em análise', '/helpdesk/painel/ajax/abertura/atualizar_rapido.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-18 19:10:17'),
(122, 0, 2, 'editar', 'chamados', 6, 'Chamado encerrado: CH20260218-F1588A', '/helpdesk/painel/ajax/abertura/encerrar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-18 19:10:39'),
(123, 0, 2, 'login', 'usuarios', 2, 'Login realizado com sucesso', '/helpdesk/autenticar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-19 09:55:23'),
(124, 0, 2, 'login', 'usuarios', 2, 'Login realizado com sucesso', '/helpdesk/autenticar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-23 10:13:06'),
(125, 0, 2, 'inserir', 'setores', 1, 'Setor \'Teste\' cadastrado', '/helpdesk/painel/ajax/setores/salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-23 10:23:38'),
(126, 0, 2, 'editar', 'setores', 1, 'Setor \'Teste aaa\' atualizado', '/helpdesk/painel/ajax/setores/salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-23 10:25:15'),
(127, 0, 2, 'excluir', 'setores', 1, 'Setor \'Teste aaa\' excluído', '/helpdesk/painel/ajax/setores/excluir.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-23 10:25:17'),
(128, 0, 2, 'inserir', 'setores', 2, 'Setor \'aaaa\' cadastrado', '/helpdesk/painel/ajax/setores/salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-23 10:25:31'),
(129, 0, 2, 'inserir', 'setores', 3, 'Setor \'vbbbb\' cadastrado', '/helpdesk/painel/ajax/setores/salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-23 10:25:38'),
(130, 0, 2, 'inserir', 'setores', 4, 'Setor \'ccc\' cadastrado', '/helpdesk/painel/ajax/setores/salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-23 10:25:42'),
(131, 0, 2, 'excluir', 'setores', 4, 'Setor \'ccc\' excluído', '/helpdesk/painel/ajax/setores/excluir.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-23 10:25:45'),
(132, 0, 2, 'excluir', 'setores', 3, 'Setor \'vbbbb\' excluído', '/helpdesk/painel/ajax/setores/excluir.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-23 10:25:47'),
(133, 0, 2, 'excluir', 'setores', 2, 'Setor \'aaaa\' excluído', '/helpdesk/painel/ajax/setores/excluir.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-23 10:25:49'),
(134, 0, 0, 'logout', 'usuarios', 0, 'Logout realizado com sucesso', '/helpdesk/painel/logout.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-23 11:37:39'),
(135, 0, 17, 'login', 'usuarios', 17, 'Login realizado com sucesso', '/helpdesk/autenticar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-23 11:37:41'),
(136, 0, 0, 'logout', 'usuarios', 0, 'Logout realizado com sucesso', '/helpdesk/painel/logout.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-23 11:37:50'),
(137, 0, 2, 'login', 'usuarios', 2, 'Login realizado com sucesso', '/helpdesk/autenticar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-23 11:37:57'),
(138, 0, 0, 'logout', 'usuarios', 0, 'Logout realizado com sucesso', '/helpdesk/painel/logout.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-23 11:38:14'),
(139, 0, 17, 'login', 'usuarios', 17, 'Login realizado com sucesso', '/helpdesk/autenticar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-23 11:38:17'),
(140, 0, 0, 'logout', 'usuarios', 0, 'Logout realizado com sucesso', '/helpdesk/painel/logout.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-23 11:38:26'),
(141, 0, 2, 'login', 'usuarios', 2, 'Login realizado com sucesso', '/helpdesk/autenticar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-23 11:38:31'),
(142, 0, 2, 'inserir', 'chamados', 10, 'Chamado \'CH20260223-4D27B3\' aberto', '/helpdesk/painel/ajax/abertura/salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-23 11:49:04'),
(143, 0, 2, 'inserir', 'chamados', 11, 'Chamado \'CH20260223-579063\' aberto', '/helpdesk/painel/ajax/abertura/salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-23 12:01:13'),
(144, 0, 2, 'editar', 'usuarios', 36, 'Falha ao enviar e-mail de acesso para aaaa@hotmail.com (Falha ao enviar e-mail.)', '/helpdesk/painel/ajax/usuarios/salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-23 12:05:51'),
(145, 0, 2, 'inserir', 'usuarios', 144, 'Usuário \'fafasdfas\' cadastrado', '/helpdesk/painel/ajax/usuarios/salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-23 12:05:51'),
(146, 0, 2, 'excluir', 'usuarios', 36, 'Usuário excluído do sistema', '/helpdesk/painel/ajax/usuarios/excluir.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-23 12:05:55'),
(147, 0, 2, 'editar', 'usuarios', 17, 'Usuário \'Novo Testes\' atualizado', '/helpdesk/painel/ajax/usuarios/salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-23 12:08:33'),
(148, 0, 2, 'excluir', 'chamados', 11, 'Chamado excluído: CH20260223-579063 - teste 50', '/helpdesk/painel/ajax/abertura/excluir.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-23 12:08:55'),
(149, 0, 2, 'excluir', 'chamados', 10, 'Chamado excluído: CH20260223-4D27B3 - Teste', '/helpdesk/painel/ajax/abertura/excluir.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-23 12:08:58'),
(150, 0, 2, 'inserir', 'chamados', 12, 'Chamado \'CH20260223-48469B\' aberto', '/helpdesk/painel/ajax/abertura/salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-23 12:09:10'),
(151, 0, 2, 'editar', 'chamados', 12, 'Chamado \'CH20260223-48469B\' atualizado', '/helpdesk/painel/ajax/abertura/salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-23 12:09:35'),
(152, 0, 2, 'inserir', 'chamados', 13, 'Chamado \'CH20260223-6D620B\' aberto', '/helpdesk/painel/ajax/abertura/salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-23 13:29:44'),
(153, 0, 0, 'logout', 'usuarios', 0, 'Logout realizado com sucesso', '/helpdesk/painel/logout.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-23 13:30:06'),
(154, 0, 17, 'login', 'usuarios', 17, 'Login realizado com sucesso', '/helpdesk/autenticar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-23 13:30:13'),
(155, 0, 0, 'logout', 'usuarios', 0, 'Logout realizado com sucesso', '/helpdesk/painel/logout.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-23 13:30:35'),
(156, 0, 2, 'login', 'usuarios', 2, 'Login realizado com sucesso', '/helpdesk/autenticar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-23 13:30:40'),
(157, 0, 2, 'inserir', 'chamados', 14, 'Chamado \'CH20260223-FE8D61\' aberto', '/helpdesk/painel/ajax/abertura/salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-23 13:31:29'),
(158, 0, 0, 'logout', 'usuarios', 0, 'Logout realizado com sucesso', '/helpdesk/painel/logout.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-23 13:31:40'),
(159, 0, 17, 'login', 'usuarios', 17, 'Login realizado com sucesso', '/helpdesk/autenticar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-23 13:31:44'),
(160, 0, 0, 'logout', 'usuarios', 0, 'Logout realizado com sucesso', '/helpdesk/painel/logout.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-23 13:34:11'),
(161, 0, 2, 'login', 'usuarios', 2, 'Login realizado com sucesso', '/helpdesk/autenticar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-23 13:34:14'),
(162, 0, 0, 'logout', 'usuarios', 0, 'Logout realizado com sucesso', '/helpdesk/painel/logout.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-23 14:06:55'),
(163, 0, 17, 'login', 'usuarios', 17, 'Login realizado com sucesso', '/helpdesk/autenticar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-23 14:06:58'),
(164, 0, 0, 'logout', 'usuarios', 0, 'Logout realizado com sucesso', '/helpdesk/painel/logout.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-23 14:08:48'),
(165, 0, 2, 'login', 'usuarios', 2, 'Login realizado com sucesso', '/helpdesk/autenticar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-23 14:08:53'),
(166, 0, 2, 'inserir', 'chamados', 15, 'Chamado \'CH20260223-D68C74\' aberto', '/helpdesk/painel/ajax/abertura/salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-23 14:09:16'),
(167, 0, 0, 'logout', 'usuarios', 0, 'Logout realizado com sucesso', '/helpdesk/painel/logout.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-23 14:09:40'),
(168, 0, 17, 'login', 'usuarios', 17, 'Login realizado com sucesso', '/helpdesk/autenticar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-23 14:09:45'),
(169, 0, 0, 'logout', 'usuarios', 0, 'Logout realizado com sucesso', '/helpdesk/painel/logout.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-23 14:14:57'),
(170, 0, 2, 'login', 'usuarios', 2, 'Login realizado com sucesso', '/helpdesk/autenticar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-23 14:15:01'),
(171, 0, 2, 'inserir', 'chamados', 16, 'Chamado \'CH20260223-A2BB5A\' aberto', '/helpdesk/painel/ajax/abertura/salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-23 14:15:16'),
(172, 0, 0, 'logout', 'usuarios', 0, 'Logout realizado com sucesso', '/helpdesk/painel/logout.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-23 14:15:23'),
(173, 0, 17, 'login', 'usuarios', 17, 'Login realizado com sucesso', '/helpdesk/autenticar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-23 14:15:30'),
(174, 0, 17, 'editar', 'chamados', 13, 'Chamado \'CH20260223-6D620B\' atualizado', '/helpdesk/painel/ajax/abertura/salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-23 14:16:10'),
(175, 0, 17, 'editar', 'chamados', 13, 'Chamado \'CH20260223-6D620B\' atualizado', '/helpdesk/painel/ajax/abertura/salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-23 14:16:22'),
(176, 0, 17, 'editar', 'chamados', 13, 'Chamado \'CH20260223-6D620B\' atualizado', '/helpdesk/painel/ajax/abertura/salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-23 14:16:42'),
(177, 0, 17, 'editar', 'chamados', 16, 'Chamado \'CH20260223-A2BB5A\' atualizado', '/helpdesk/painel/ajax/abertura/salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-23 14:20:58'),
(178, 0, 17, 'editar', 'chamados', 16, 'Chamado \'CH20260223-A2BB5A\' atualizado', '/helpdesk/painel/ajax/abertura/salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-23 14:21:25'),
(179, 0, 17, 'editar', 'chamados', 16, 'Chamado \'CH20260223-A2BB5A\' atualizado', '/helpdesk/painel/ajax/abertura/salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-23 14:22:46'),
(180, 0, 17, 'editar', 'chamados', 16, 'Chamado \'CH20260223-A2BB5A\' atualizado', '/helpdesk/painel/ajax/abertura/salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-23 14:22:59'),
(181, 0, 17, 'editar', 'chamados', 16, 'Chamado \'CH20260223-A2BB5A\' atualizado', '/helpdesk/painel/ajax/abertura/salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-23 14:23:08'),
(182, 0, 17, 'editar', 'chamados', 16, 'Chamado \'CH20260223-A2BB5A\' atualizado', '/helpdesk/painel/ajax/abertura/salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-23 14:25:27'),
(183, 0, 17, 'editar', 'chamados', 16, 'Chamado \'CH20260223-A2BB5A\' atualizado', '/helpdesk/painel/ajax/abertura/salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-23 14:25:55'),
(184, 0, 17, 'editar', 'chamados', 16, 'Chamado \'CH20260223-A2BB5A\' atualizado', '/helpdesk/painel/ajax/abertura/salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-23 14:26:10'),
(185, 0, 17, 'editar', 'chamados', 16, 'Chamado \'CH20260223-A2BB5A\' atualizado', '/helpdesk/painel/ajax/abertura/salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-23 14:27:16'),
(186, 0, 17, 'editar', 'chamados', 16, 'Chamado \'CH20260223-A2BB5A\' atualizado', '/helpdesk/painel/ajax/abertura/salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-23 14:27:34'),
(187, 0, 17, 'editar', 'chamados', 16, 'Chamado \'CH20260223-A2BB5A\' atualizado', '/helpdesk/painel/ajax/abertura/salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-23 14:28:10'),
(188, 0, 17, 'editar', 'chamados', 16, 'Chamado \'CH20260223-A2BB5A\' atualizado', '/helpdesk/painel/ajax/abertura/salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-23 14:28:24'),
(189, 0, 17, 'editar', 'chamados', 16, 'Chamado \'CH20260223-A2BB5A\' atualizado', '/helpdesk/painel/ajax/abertura/salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-23 14:28:37'),
(190, 0, 17, 'editar', 'chamados', 16, 'Chamado \'CH20260223-A2BB5A\' atualizado', '/helpdesk/painel/ajax/abertura/salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-23 14:28:43'),
(191, 0, 0, 'logout', 'usuarios', 0, 'Logout realizado com sucesso', '/helpdesk/painel/logout.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-23 14:38:18'),
(192, 0, 2, 'login', 'usuarios', 2, 'Login realizado com sucesso', '/helpdesk/autenticar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-23 14:38:23'),
(193, 0, 2, 'inserir', 'chamados_anexos', 1, 'Anexo enviado no chamado CH20260223-A2BB5A', '/helpdesk/painel/ajax/abertura/anexos_salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-23 15:16:48'),
(194, 0, 2, 'inserir', 'chamados_anexos', 2, 'Anexo enviado no chamado CH20260223-A2BB5A', '/helpdesk/painel/ajax/abertura/anexos_salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-23 15:23:29'),
(195, 0, 2, 'inserir', 'chamados_anexos', 3, 'Anexo enviado no chamado CH20260223-A2BB5A', '/helpdesk/painel/ajax/abertura/anexos_salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-23 15:23:59'),
(196, 0, 2, 'inserir', 'chamados_anexos', 4, 'Anexo enviado no chamado CH20260223-A2BB5A', '/helpdesk/painel/ajax/abertura/anexos_salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-23 15:25:45'),
(197, 0, 2, 'excluir', 'chamados_anexos', 4, 'Anexo excluído (Chamado CH20260223-A2BB5A) ch_16_20260223_152545_b8c5d2f8bddb6ca8.pdf aaaaaaaaaaa', '/helpdesk/painel/ajax/abertura/anexos_excluir.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-23 15:39:47'),
(198, 0, 2, 'excluir', 'chamados_anexos', 3, 'Anexo excluído (Chamado CH20260223-A2BB5A) ch_16_20260223_152359_8bcbe3c10f28f4a8.jpeg Tesaata', '/helpdesk/painel/ajax/abertura/anexos_excluir.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-23 15:39:50'),
(199, 0, 2, 'excluir', 'chamados_anexos', 2, 'Anexo excluído (Chamado CH20260223-A2BB5A) ch_16_20260223_152329_8dfb62c52bd3058f.jpeg Tesaata', '/helpdesk/painel/ajax/abertura/anexos_excluir.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-23 15:39:53'),
(200, 0, 2, 'excluir', 'chamados_anexos', 1, 'Anexo excluído (Chamado CH20260223-A2BB5A) ch_16_20260223_151648_c43cd5c50d09eaca.jpeg ', '/helpdesk/painel/ajax/abertura/anexos_excluir.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-23 15:39:55'),
(201, 0, 2, 'inserir', 'chamados_anexos', 5, 'Anexo enviado no chamado CH20260223-A2BB5A', '/helpdesk/painel/ajax/abertura/anexos_salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-23 15:40:07'),
(202, 0, 2, 'inserir', 'chamados_anexos', 6, 'Anexo enviado no chamado CH20260223-A2BB5A', '/helpdesk/painel/ajax/abertura/anexos_salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-23 15:40:21'),
(203, 0, 2, 'inserir', 'chamados_anexos', 7, 'Anexo enviado no chamado CH20260223-A2BB5A', '/helpdesk/painel/ajax/abertura/anexos_salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-23 15:45:20'),
(204, 0, 2, 'inserir', 'chamados_anexos', 8, 'Anexo enviado no chamado CH20260223-D68C74', '/helpdesk/painel/ajax/abertura/anexos_salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-23 15:45:31'),
(205, 0, 2, 'login', 'usuarios', 2, 'Login realizado com sucesso', '/helpdesk/autenticar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-23 18:18:45'),
(206, 0, 2, 'inserir', 'chamados_anexos', 9, 'Anexo enviado no chamado CH20260223-A2BB5A', '/helpdesk/painel/ajax/abertura/anexos_salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-23 18:40:06');
INSERT INTO `logs` (`id`, `empresa`, `usuario_id`, `acao`, `entidade`, `registro_id`, `descricao`, `rota`, `ip`, `user_agent`, `criado_em`) VALUES
(207, 0, 2, 'inserir', 'chamados_anexos', 10, 'Anexo enviado no chamado CH20260223-A2BB5A', '/helpdesk/painel/ajax/abertura/anexos_salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-23 18:40:14'),
(208, 0, 2, 'inserir', 'chamados_respostas', 1, 'Resposta no chamado CH20260223-A2BB5A', '/helpdesk/painel/ajax/abertura/respostas_salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-23 18:52:45'),
(209, 0, 2, 'inserir', 'chamados_respostas', 2, 'Resposta no chamado CH20260223-A2BB5A', '/helpdesk/painel/ajax/abertura/respostas_salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-23 18:54:21'),
(210, 0, 2, 'inserir', 'chamados_respostas', 3, 'Resposta no chamado CH20260223-D68C74', '/helpdesk/painel/ajax/abertura/respostas_salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-23 18:54:25'),
(211, 0, 2, 'inserir', 'chamados_respostas', 4, 'Resposta no chamado CH20260223-A2BB5A', '/helpdesk/painel/ajax/abertura/respostas_salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-23 18:58:30'),
(212, 0, 2, 'editar', 'chamados', 16, 'Chamado \'CH20260223-A2BB5A\' atualizado', '/helpdesk/painel/ajax/abertura/salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-23 19:00:46'),
(213, 0, 2, 'inserir', 'chamados_respostas', 5, 'Resposta no chamado CH20260223-A2BB5A', '/helpdesk/painel/ajax/abertura/respostas_salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-23 19:00:50'),
(214, 0, 2, 'inserir', 'chamados_respostas', 6, 'Resposta no chamado CH20260223-A2BB5A', '/helpdesk/painel/ajax/abertura/respostas_salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-23 19:02:07'),
(215, 0, 2, 'inserir', 'chamados_respostas', 7, 'Resposta no chamado CH20260223-A2BB5A', '/helpdesk/painel/ajax/abertura/respostas_salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-23 19:03:01'),
(216, 0, 2, 'inserir', 'chamados_respostas', 8, 'Resposta no chamado CH20260223-A2BB5A', '/helpdesk/painel/ajax/abertura/respostas_salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-23 19:04:49'),
(217, 0, 2, 'inserir', 'chamados_respostas', 9, 'Resposta no chamado CH20260223-D68C74', '/helpdesk/painel/ajax/abertura/respostas_salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-23 19:30:51'),
(218, 0, 2, 'inserir', 'chamados_respostas', 10, 'Resposta no chamado CH20260223-D68C74', '/helpdesk/painel/ajax/abertura/respostas_salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-23 19:31:25'),
(219, 0, 2, 'inserir', 'chamados_respostas', 11, 'Resposta no chamado CH20260223-D68C74', '/helpdesk/painel/ajax/abertura/respostas_salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-23 19:32:49'),
(220, 0, 2, 'inserir', 'chamados_respostas', 12, 'Resposta no chamado CH20260223-D68C74', '/helpdesk/painel/ajax/abertura/respostas_salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-23 19:33:04'),
(221, 0, 2, 'inserir', 'chamados_respostas', 13, 'Resposta no chamado CH20260223-D68C74', '/helpdesk/painel/ajax/abertura/respostas_salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-23 19:33:49'),
(222, 0, 2, 'inserir', 'chamados_respostas', 14, 'Resposta no chamado CH20260223-D68C74', '/helpdesk/painel/ajax/abertura/respostas_salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-23 19:37:42'),
(223, 0, 2, 'inserir', 'chamados_respostas', 15, 'Resposta no chamado CH20260223-FE8D61', '/helpdesk/painel/ajax/abertura/respostas_salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-23 19:42:34'),
(224, 0, 2, 'inserir', 'chamados_respostas', 16, 'Resposta no chamado CH20260223-FE8D61', '/helpdesk/painel/ajax/abertura/respostas_salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-23 19:43:13'),
(225, 0, 2, 'editar', 'chamados', 14, 'Chamado \'CH20260223-FE8D61\' atualizado', '/helpdesk/painel/ajax/abertura/salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-23 19:44:17'),
(226, 0, 2, 'inserir', 'chamados_respostas', 17, 'Resposta no chamado CH20260223-FE8D61', '/helpdesk/painel/ajax/abertura/respostas_salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-23 19:44:23'),
(227, 0, 2, 'inserir', 'chamados_respostas', 18, 'Resposta no chamado CH20260223-FE8D61', '/helpdesk/painel/ajax/abertura/respostas_salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-23 19:44:38'),
(228, 0, 2, 'inserir', 'chamados_respostas', 19, 'Resposta no chamado CH20260223-FE8D61', '/helpdesk/painel/ajax/abertura/respostas_salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-23 19:45:25'),
(229, 0, 2, 'inserir', 'chamados_respostas', 20, 'Resposta no chamado CH20260223-FE8D61', '/helpdesk/painel/ajax/abertura/respostas_salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-23 19:45:52'),
(230, 0, 2, 'inserir', 'chamados_respostas', 21, 'Resposta no chamado CH20260223-D68C74', '/helpdesk/painel/ajax/abertura/respostas_salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-23 19:46:58'),
(231, 0, 2, 'inserir', 'chamados_respostas', 22, 'Resposta no chamado CH20260223-FE8D61', '/helpdesk/painel/ajax/abertura/respostas_salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-23 19:58:15'),
(232, 0, 2, 'inserir', 'chamados_respostas', 23, 'Resposta no chamado CH20260223-D68C74', '/helpdesk/painel/ajax/abertura/respostas_salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-23 19:58:31'),
(233, 0, 2, 'inserir', 'chamados_respostas', 24, 'Resposta no chamado CH20260223-D68C74', '/helpdesk/painel/ajax/abertura/respostas_salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-23 19:58:36'),
(234, 0, 2, 'inserir', 'chamados_respostas', 25, 'Resposta no chamado CH20260223-D68C74', '/helpdesk/painel/ajax/abertura/respostas_salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-23 20:02:44'),
(235, 0, 2, 'editar', 'clientes', 12, 'Cliente \'Cliente Teste Hugo\' atualizado', '/helpdesk/painel/ajax/clientes/salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-23 20:37:22'),
(236, 0, 2, 'inserir', 'chamados_respostas', 26, 'Resposta no chamado CH20260223-D68C74', '/helpdesk/painel/ajax/abertura/respostas_salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-23 20:37:59'),
(237, 0, 2, 'inserir', 'chamados_respostas', 27, 'Resposta no chamado CH20260223-FE8D61', '/helpdesk/painel/ajax/abertura/respostas_salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-23 20:48:27'),
(238, 0, 2, 'inserir', 'chamados_respostas', 28, 'Resposta no chamado CH20260223-D68C74', '/helpdesk/painel/ajax/abertura/respostas_salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-23 20:52:34'),
(239, 0, 2, 'inserir', 'chamados_respostas', 29, 'Resposta no chamado CH20260223-D68C74', '/helpdesk/painel/ajax/abertura/respostas_salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-23 20:52:39'),
(240, 0, 2, 'excluir', 'chamados_respostas', 29, 'Resposta do chamado excluída', '/helpdesk/painel/ajax/abertura/respostas_excluir.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-23 20:53:33'),
(241, 0, 2, 'excluir', 'chamados_respostas', 28, 'Resposta do chamado excluída', '/helpdesk/painel/ajax/abertura/respostas_excluir.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-23 20:53:35'),
(242, 0, 2, 'excluir', 'chamados_respostas', 26, 'Resposta do chamado excluída', '/helpdesk/painel/ajax/abertura/respostas_excluir.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-23 20:53:38'),
(243, 0, 2, 'excluir', 'chamados_respostas', 25, 'Resposta do chamado excluída', '/helpdesk/painel/ajax/abertura/respostas_excluir.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-23 20:53:41'),
(244, 0, 2, 'excluir', 'chamados_respostas', 24, 'Resposta do chamado excluída', '/helpdesk/painel/ajax/abertura/respostas_excluir.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-23 20:53:43'),
(245, 0, 2, 'excluir', 'chamados_respostas', 3, 'Resposta do chamado excluída', '/helpdesk/painel/ajax/abertura/respostas_excluir.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-23 20:53:47'),
(246, 0, 2, 'excluir', 'chamados_respostas', 9, 'Resposta do chamado excluída', '/helpdesk/painel/ajax/abertura/respostas_excluir.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-23 20:53:51'),
(247, 0, 2, 'editar', 'clientes', 12, 'Cliente \'Cliente Teste Hugo\' atualizado', '/helpdesk/painel/ajax/clientes/salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 10:15:42'),
(248, 0, 2, 'inserir', 'chamados', 17, 'Chamado \'CH20260224-71C4B7\' aberto', '/helpdesk/painel/ajax/abertura/salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 10:18:00'),
(249, 0, 2, 'inserir', 'chamados', 18, 'Chamado \'CH20260224-338BC7\' aberto', '/helpdesk/painel/ajax/abertura/salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 10:41:08'),
(250, 0, 2, 'excluir', 'chamados', 18, 'Chamado excluído: CH20260224-338BC7 - Teste 50', '/helpdesk/painel/ajax/abertura/excluir.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 10:41:45'),
(251, 0, 2, 'excluir', 'clientes', 23, 'Usuário excluído do sistema', '/helpdesk/painel/ajax/clientes/excluir.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 10:41:49'),
(252, 0, 2, 'inserir', 'chamados', 19, 'Chamado \'CH20260224-9ACFF3\' aberto', '/helpdesk/painel/ajax/abertura/salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 10:45:29'),
(253, 0, 2, 'inserir', 'chamados', 20, 'Chamado \'CH20260224-3CE36E\' aberto', '/helpdesk/painel/ajax/abertura/salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 10:46:29'),
(254, 0, 2, 'excluir', 'chamados', 20, 'Chamado excluído: CH20260224-3CE36E - Testessss', '/helpdesk/painel/ajax/abertura/excluir.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 10:46:42'),
(255, 0, 2, 'excluir', 'clientes', 24, 'Usuário excluído do sistema', '/helpdesk/painel/ajax/clientes/excluir.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 10:46:53'),
(256, 0, 2, 'editar', 'clientes', 12, 'Cliente \'Cliente Teste Hugo\' atualizado', '/helpdesk/painel/ajax/clientes/salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 10:47:00'),
(257, 0, 2, 'inserir', 'chamados', 21, 'Chamado \'CH20260224-55CD3C\' aberto', '/helpdesk/painel/ajax/abertura/salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 10:47:16'),
(258, 0, 2, 'inserir', 'chamados', 22, 'Chamado \'CH20260224-E29074\' aberto', '/helpdesk/painel/ajax/abertura/salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 10:49:23'),
(259, 0, 2, 'inserir', 'chamados', 23, 'Chamado \'CH20260224-E2D132\' aberto', '/helpdesk/painel/ajax/abertura/salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 10:50:07'),
(260, 0, 2, 'excluir', 'chamados', 23, 'Chamado excluído: CH20260224-E2D132 - afdsafafdasfsa', '/helpdesk/painel/ajax/abertura/excluir.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 10:54:41'),
(261, 0, 2, 'excluir', 'chamados', 22, 'Chamado excluído: CH20260224-E29074 - fdaf dfafadf', '/helpdesk/painel/ajax/abertura/excluir.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 10:54:44'),
(262, 0, 2, 'excluir', 'chamados', 21, 'Chamado excluído: CH20260224-55CD3C - aaaaaaaaa', '/helpdesk/painel/ajax/abertura/excluir.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 10:54:47'),
(263, 0, 2, 'excluir', 'chamados', 19, 'Chamado excluído: CH20260224-9ACFF3 - aaaaaaaaaaa', '/helpdesk/painel/ajax/abertura/excluir.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 10:54:50'),
(264, 0, 2, 'inserir', 'chamados', 24, 'Chamado \'CH20260224-6D15AB\' aberto', '/helpdesk/painel/ajax/abertura/salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 10:55:03'),
(265, 0, 2, 'inserir', 'chamados', 25, 'Chamado \'CH20260224-3B408B\' aberto', '/helpdesk/painel/ajax/abertura/salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 10:56:19'),
(266, 0, 2, 'inserir', 'chamados', 26, 'Chamado \'CH20260224-6726BC\' aberto', '/helpdesk/painel/ajax/abertura/salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 10:56:42'),
(267, 0, 2, 'inserir', 'chamados', 27, 'Chamado \'CH20260224-4ACAA4\' aberto', '/helpdesk/painel/ajax/abertura/salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 10:57:54'),
(268, 0, 2, 'inserir', 'chamados', 28, 'Chamado \'CH20260224-B5580E\' aberto', '/helpdesk/painel/ajax/abertura/salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 11:00:55'),
(269, 0, 2, 'inserir', 'chamados', 29, 'Chamado \'CH20260224-A2FCEB\' aberto', '/helpdesk/painel/ajax/abertura/salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 11:02:42'),
(270, 0, 2, 'inserir', 'chamados', 30, 'Chamado \'CH20260224-EED23A\' aberto', '/helpdesk/painel/ajax/abertura/salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 11:08:13'),
(271, 0, 2, 'inserir', 'chamados', 31, 'Chamado \'CH20260224-3121F9\' aberto', '/helpdesk/painel/ajax/abertura/salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 11:09:44'),
(272, 0, 2, 'excluir', 'chamados', 31, 'Chamado excluído: CH20260224-3121F9 - ttttttt', '/helpdesk/painel/ajax/abertura/excluir.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 11:14:14'),
(273, 0, 2, 'excluir', 'chamados', 30, 'Chamado excluído: CH20260224-EED23A - fffffffff', '/helpdesk/painel/ajax/abertura/excluir.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 11:14:17'),
(274, 0, 2, 'excluir', 'chamados', 29, 'Chamado excluído: CH20260224-A2FCEB - fdafdafafadsf', '/helpdesk/painel/ajax/abertura/excluir.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 11:14:19'),
(275, 0, 2, 'excluir', 'chamados', 28, 'Chamado excluído: CH20260224-B5580E - fdasfdafdaf ddd', '/helpdesk/painel/ajax/abertura/excluir.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 11:14:22'),
(276, 0, 2, 'excluir', 'chamados', 27, 'Chamado excluído: CH20260224-4ACAA4 - fdasfdafdaf', '/helpdesk/painel/ajax/abertura/excluir.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 11:14:24'),
(277, 0, 2, 'excluir', 'chamados', 26, 'Chamado excluído: CH20260224-6726BC - fdasfdafdaf', '/helpdesk/painel/ajax/abertura/excluir.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 11:14:28'),
(278, 0, 2, 'excluir', 'chamados', 25, 'Chamado excluído: CH20260224-3B408B - aaaaaaaa', '/helpdesk/painel/ajax/abertura/excluir.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 11:14:30'),
(279, 0, 2, 'inserir', 'chamados', 32, 'Chamado \'CH20260224-17EC08\' aberto', '/helpdesk/painel/ajax/abertura/salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 11:14:46'),
(280, 0, 2, 'editar', 'clientes', 12, 'Cliente \'Cliente Teste Hugo\' atualizado', '/helpdesk/painel/ajax/clientes/salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 11:15:25'),
(281, 0, 2, 'excluir', 'chamados', 32, 'Chamado excluído: CH20260224-17EC08 - bbbbbbbb', '/helpdesk/painel/ajax/abertura/excluir.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 11:30:22'),
(282, 0, 2, 'inserir', 'chamados', 33, 'Chamado \'CH20260224-5BDC41\' aberto', '/helpdesk/painel/ajax/abertura/salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 11:30:36'),
(283, 0, 2, 'inserir', 'chamados', 34, 'Chamado \'CH20260224-BEF7DD\' aberto', '/helpdesk/painel/ajax/abertura/salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 11:32:33'),
(284, 0, 2, 'inserir', 'chamados_respostas', 30, 'Resposta no chamado CH20260223-6D620B', '/helpdesk/painel/ajax/abertura/respostas_salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 13:42:58');

-- --------------------------------------------------------

--
-- Estrutura para tabela `recuperacao_senha`
--

CREATE TABLE `recuperacao_senha` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `token_hash` varchar(255) NOT NULL,
  `expira_em` datetime NOT NULL,
  `usado_em` datetime DEFAULT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `criado_em` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `recuperacao_senha`
--

INSERT INTO `recuperacao_senha` (`id`, `usuario_id`, `token_hash`, `expira_em`, `usado_em`, `ip`, `user_agent`, `criado_em`) VALUES
(14, 2, '$2y$10$twISLP6r5F/P5g7CJCmBteUsnYtdu.bgGSkPRAAsZMYLi4sJVs8Q.', '2026-02-03 18:42:00', '2026-02-03 18:12:29', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-03 18:12:00'),
(15, 2, '$2y$10$GHMZ8mil1ZjcfiQ45M4N5ul5C/9sUnQrC9Y3mo71XiLyO7soELeA6', '2026-02-15 21:19:54', '2026-02-18 12:11:30', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-15 20:49:54'),
(16, 2, '$2y$10$zsd/8O1FxFfh7yChJmRPP.LEaVH84eJ7vzNO6mxQelih8NW1vOqPK', '2026-02-18 12:41:30', '2026-02-18 12:12:39', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-18 12:11:30'),
(17, 2, '$2y$10$AOF9kzcT.Oahlc6nZ/AsbODzNcCHJh2ndzF6RN/zPEhfZN6JuQodW', '2026-02-18 12:42:39', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-18 12:12:39');

-- --------------------------------------------------------

--
-- Estrutura para tabela `setores`
--

CREATE TABLE `setores` (
  `id` int(11) NOT NULL,
  `empresa` int(11) DEFAULT NULL,
  `nome` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `setores`
--

INSERT INTO `setores` (`id`, `empresa`, `nome`) VALUES
(1, NULL, 'Suporte Técnico'),
(2, NULL, 'Financeiro'),
(3, NULL, 'Comercial'),
(4, NULL, 'Recursos Humanos'),
(5, NULL, 'Tecnologia da Informação'),
(6, NULL, 'Atendimento ao Cliente'),
(7, NULL, 'Infraestrutura'),
(8, NULL, 'Administrativo');

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) DEFAULT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `senha` varchar(255) DEFAULT NULL,
  `cpf` varchar(14) DEFAULT NULL,
  `endereco` varchar(200) DEFAULT NULL,
  `numero` varchar(10) DEFAULT NULL,
  `complemento` varchar(100) DEFAULT NULL,
  `bairro` varchar(100) DEFAULT NULL,
  `cidade` varchar(100) DEFAULT NULL,
  `estado` char(2) DEFAULT NULL,
  `cep` varchar(10) DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `nivel` varchar(50) DEFAULT 'comum',
  `ativo` varchar(5) DEFAULT NULL,
  `empresa` int(11) DEFAULT 0,
  `data_cadastro` datetime DEFAULT current_timestamp(),
  `data_atualizacao` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  `assinatura` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Despejando dados para a tabela `usuarios`
--

INSERT INTO `usuarios` (`id`, `nome`, `telefone`, `email`, `senha`, `cpf`, `endereco`, `numero`, `complemento`, `bairro`, `cidade`, `estado`, `cep`, `foto`, `nivel`, `ativo`, `empresa`, `data_cadastro`, `data_atualizacao`, `assinatura`) VALUES
(2, 'Usuário Administrador', '(31) 99534-8118', 'contato@hugocursos.com.br', '$2y$10$exORLq4U96CBg76UU2C6peohQoQ8CbLZ5l748esJ8Cu.IIzLYuX62', NULL, 'Rua Boa Vista', NULL, NULL, 'Cabana do Pai Tomás', 'Belo Horizonte', 'MG', '30512-660', 'u2_1770141461.jpg', 'Administrador', 'Sim', 0, '2026-02-03 12:35:19', '2026-02-18 12:04:40', 'assinatura_usu_ario_administrador_2_20260216_102004.png'),
(12, 'Usuário teste', '(00) 00000-0000', 'teste@hotmail.com', '$2y$10$2Xbttb/IjcAjQCWbvFjndOJKVQaloHmwVM4FX7eHs0PoieYa7BaIO', NULL, NULL, NULL, NULL, NULL, NULL, 'PB', NULL, 'sem_foto.webp', 'Comum', 'Sim', 0, '2026-02-04 11:43:21', '2026-02-09 14:44:12', NULL),
(17, 'Novo Testes', NULL, 'novoteste@hotmail.com', '$2y$10$/w9iroJK8C8/A53mf5FdVOZ4Cw5dhdFMXW2R4WGCRGGUxOf95bfpK', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'sem_foto.webp', 'Atendente', 'Sim', 0, '2026-02-09 19:30:57', '2026-02-23 12:08:33', 'assinatura_novo_teste_17_20260215_225423.png'),
(27, 'teste novo', '(31) 97527-5084', 'testenovoss@hotmail.com', '$2y$10$boAxKNRaz.A3/77bZGIo1.0luEscCsJSkEyeWqUq4CTuGiY5gfPAG', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'sem_foto.webp', 'Administrador', 'Sim', 0, '2026-02-10 20:50:08', '2026-02-16 10:04:07', 'assinatura_teste_novo_27_20260216_100407.png');

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios_acoes`
--

CREATE TABLE `usuarios_acoes` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `acao` varchar(30) NOT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `usuarios_acoes`
--

INSERT INTO `usuarios_acoes` (`id`, `usuario_id`, `acao`, `criado_em`) VALUES
(3, 17, 'criar', '2026-02-23 13:51:15'),
(4, 17, 'editar', '2026-02-23 13:51:15');

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios_permissoes`
--

CREATE TABLE `usuarios_permissoes` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `menu_id` varchar(50) NOT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `usuarios_permissoes`
--

INSERT INTO `usuarios_permissoes` (`id`, `usuario_id`, `menu_id`, `criado_em`) VALUES
(3, 17, 'dashboard', '2026-02-23 13:51:15'),
(4, 17, 'abertura', '2026-02-23 13:51:15');

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios_setores`
--

CREATE TABLE `usuarios_setores` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `setor_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `usuarios_setores`
--

INSERT INTO `usuarios_setores` (`id`, `usuario_id`, `setor_id`) VALUES
(4, 17, 3),
(5, 17, 4),
(3, 17, 8);

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `cargos`
--
ALTER TABLE `cargos`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `chamados`
--
ALTER TABLE `chamados`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_status` (`status_id`),
  ADD KEY `idx_cliente` (`cliente_id`),
  ADD KEY `idx_responsavel` (`usuario_responsavel_id`),
  ADD KEY `idx_criado` (`criado_em`),
  ADD KEY `fk_chamados_usuario_abertura` (`usuario_abertura_id`),
  ADD KEY `fk_chamados_setor` (`setor_id`);

--
-- Índices de tabela `chamados_anexos`
--
ALTER TABLE `chamados_anexos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `chamado_id` (`chamado_id`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `cliente_id` (`cliente_id`);

--
-- Índices de tabela `chamados_movimentos`
--
ALTER TABLE `chamados_movimentos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_chamado` (`chamado_id`),
  ADD KEY `idx_tipo` (`tipo`),
  ADD KEY `fk_mov_usuario` (`usuario_id`);

--
-- Índices de tabela `chamados_respostas`
--
ALTER TABLE `chamados_respostas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_chamado` (`chamado_id`),
  ADD KEY `idx_usuario` (`usuario_id`),
  ADD KEY `idx_cliente` (`cliente_id`),
  ADD KEY `idx_criado` (`criado_em`);

--
-- Índices de tabela `chamados_status`
--
ALTER TABLE `chamados_status`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_status_nome` (`nome`);

--
-- Índices de tabela `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `config`
--
ALTER TABLE `config`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `logs`
--
ALTER TABLE `logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_empresa_data` (`empresa`,`criado_em`),
  ADD KEY `idx_usuario_data` (`usuario_id`,`criado_em`),
  ADD KEY `idx_acao_data` (`acao`,`criado_em`),
  ADD KEY `idx_entidade_registro` (`entidade`,`registro_id`);

--
-- Índices de tabela `recuperacao_senha`
--
ALTER TABLE `recuperacao_senha`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_usuario` (`usuario_id`),
  ADD KEY `idx_expira` (`expira_em`),
  ADD KEY `idx_usado` (`usado_em`);

--
-- Índices de tabela `setores`
--
ALTER TABLE `setores`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `usuarios_acoes`
--
ALTER TABLE `usuarios_acoes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_usuario_acao` (`usuario_id`,`acao`),
  ADD KEY `idx_usuario` (`usuario_id`);

--
-- Índices de tabela `usuarios_permissoes`
--
ALTER TABLE `usuarios_permissoes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_usuario_menu` (`usuario_id`,`menu_id`),
  ADD KEY `idx_usuario` (`usuario_id`),
  ADD KEY `idx_menu` (`menu_id`);

--
-- Índices de tabela `usuarios_setores`
--
ALTER TABLE `usuarios_setores`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_usuario_setor` (`usuario_id`,`setor_id`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `cargos`
--
ALTER TABLE `cargos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de tabela `chamados`
--
ALTER TABLE `chamados`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT de tabela `chamados_anexos`
--
ALTER TABLE `chamados_anexos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de tabela `chamados_movimentos`
--
ALTER TABLE `chamados_movimentos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=84;

--
-- AUTO_INCREMENT de tabela `chamados_respostas`
--
ALTER TABLE `chamados_respostas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT de tabela `chamados_status`
--
ALTER TABLE `chamados_status`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de tabela `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT de tabela `config`
--
ALTER TABLE `config`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `logs`
--
ALTER TABLE `logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=285;

--
-- AUTO_INCREMENT de tabela `recuperacao_senha`
--
ALTER TABLE `recuperacao_senha`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT de tabela `setores`
--
ALTER TABLE `setores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT de tabela `usuarios_acoes`
--
ALTER TABLE `usuarios_acoes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de tabela `usuarios_permissoes`
--
ALTER TABLE `usuarios_permissoes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `usuarios_setores`
--
ALTER TABLE `usuarios_setores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `chamados`
--
ALTER TABLE `chamados`
  ADD CONSTRAINT `fk_chamados_setor` FOREIGN KEY (`setor_id`) REFERENCES `setores` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_chamados_status` FOREIGN KEY (`status_id`) REFERENCES `chamados_status` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_chamados_usuario_abertura` FOREIGN KEY (`usuario_abertura_id`) REFERENCES `usuarios` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_chamados_usuario_responsavel` FOREIGN KEY (`usuario_responsavel_id`) REFERENCES `usuarios` (`id`) ON UPDATE CASCADE;

--
-- Restrições para tabelas `chamados_anexos`
--
ALTER TABLE `chamados_anexos`
  ADD CONSTRAINT `fk_anexo_chamado` FOREIGN KEY (`chamado_id`) REFERENCES `chamados` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_anexo_cliente` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_anexo_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;

--
-- Restrições para tabelas `chamados_movimentos`
--
ALTER TABLE `chamados_movimentos`
  ADD CONSTRAINT `fk_mov_chamado` FOREIGN KEY (`chamado_id`) REFERENCES `chamados` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_mov_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON UPDATE CASCADE;

--
-- Restrições para tabelas `chamados_respostas`
--
ALTER TABLE `chamados_respostas`
  ADD CONSTRAINT `fk_resp_chamado` FOREIGN KEY (`chamado_id`) REFERENCES `chamados` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_resp_cliente` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_resp_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;

--
-- Restrições para tabelas `recuperacao_senha`
--
ALTER TABLE `recuperacao_senha`
  ADD CONSTRAINT `fk_recuperacao_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
