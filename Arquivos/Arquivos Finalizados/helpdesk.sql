-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 26/02/2026 às 15:10
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
(34, 12, 2, NULL, 2, 'tttttttttttt', 'tttttttttt', 'Media', 3, '2026-02-24 11:32:33', NULL, NULL, 'CH20260224-BEF7DD', 'Não', 'Não', 0),
(37, 12, 2, NULL, 3, 'Teste cliente chamad', 'fsda g ggsgf', 'Media', 3, '2026-02-24 21:27:20', NULL, NULL, 'CH20260224-D2DB16', 'Não', 'Não', 0),
(38, 12, 2, NULL, 3, 'Teste cliente chamad', 'fsda g ggsgf', 'Media', 3, '2026-02-24 21:27:25', NULL, NULL, 'CH20260224-B701AB', 'Não', 'Não', 0),
(42, 12, 2, NULL, 2, 'aaaaaaaaaabbbbbbb', 'fdsfafads', 'Media', 8, '2026-02-25 10:18:34', '2026-02-25 14:09:22', '2026-02-25 14:09:22', 'CH20260225-A559BA', 'Não', 'Não', 0),
(43, 12, 2, NULL, 2, 'aaaaaaaaaabbbbbbbccccc', 'fdsfafads', 'Media', 6, '2026-02-25 10:18:46', '2026-02-25 14:09:08', NULL, 'CH20260225-DE0B1D', 'Não', 'Não', 0);

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
  `nome` varchar(255) DEFAULT NULL,
  `enviado_por` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `chamados_anexos`
--

INSERT INTO `chamados_anexos` (`id`, `chamado_id`, `usuario_id`, `cliente_id`, `arquivo`, `criado_em`, `nome`, `enviado_por`) VALUES
(5, 16, 2, 1, 'ch_16_20260223_154007_021f917ef6233927.jpeg', '2026-02-23 15:40:07', 'Arquivo teste', NULL),
(6, 16, 2, 1, 'ch_16_20260223_154021_82dbd900e31e378e.pdf', '2026-02-23 15:40:21', 'Arquivo PDF', NULL),
(7, 16, 2, 1, 'ch_16_20260223_154520_33a07f1c3b3de067.jpeg', '2026-02-23 15:45:20', 'aaaa', NULL),
(8, 15, 2, 1, 'ch_15_20260223_154531_13630e628330e329.pdf', '2026-02-23 15:45:31', 'teste', NULL),
(9, 16, 2, 1, 'ch_16_20260223_184006_9177295ce5f8988a.jpg', '2026-02-23 18:40:06', 'testesss', NULL),
(10, 16, 2, 1, 'ch_16_20260223_184014_db145af635d4a5d3.jpg', '2026-02-23 18:40:14', 'aaaaa', NULL),
(17, 43, NULL, 12, 'ch_43_20260225_104323_d99eaf615fc3ffa1.jpeg', '2026-02-25 10:43:23', 'aaaa', 'cliente'),
(18, 43, 2, 12, 'ch_43_20260225_104355_8b30844dbb6f6670.pdf', '2026-02-25 10:43:55', 'pdf adm', NULL),
(19, 43, NULL, 12, 'ch_43_20260225_124630_07ed47ba69056a14.jpeg', '2026-02-25 12:46:30', 'bbbbb', 'cliente'),
(20, 43, NULL, 12, 'ch_43_20260225_124756_38930cfd7e5e2965.pdf', '2026-02-25 12:47:56', 'fd afdsafdasfadf', 'cliente');

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
(83, 34, 2, 'status', NULL, 3, 'Chamado aberto com status: Aberto', '2026-02-24 11:32:33'),
(87, 37, 2, 'status', NULL, 3, 'Chamado aberto com status: Aberto', '2026-02-24 21:27:20'),
(88, 38, 2, 'status', NULL, 3, 'Chamado aberto com status: Aberto', '2026-02-24 21:27:25'),
(91, 42, 2, 'status', NULL, 3, 'Chamado aberto pelo cliente com status: Aberto', '2026-02-25 10:18:34'),
(92, 43, 2, 'status', NULL, 3, 'Chamado aberto pelo cliente com status: Aberto', '2026-02-25 10:18:46'),
(93, 43, 2, 'status', 3, 6, 'Status alterado para: Aguardando cliente', '2026-02-25 14:09:08'),
(94, 42, 2, 'status', 3, 8, 'Status alterado para: Fechado', '2026-02-25 14:09:22');

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
(30, 13, 2, 1, 'usuario', 'df ggfg fsg fsg fdsgfsgsgfs', 'Sim', '2026-02-24 13:42:58'),
(31, 34, 2, 12, 'usuario', 'TESTSE', 'Sim', '2026-02-24 14:01:33'),
(34, 34, 2, 12, 'usuario', 'fda fafafdf', 'Sim', '2026-02-24 20:06:27'),
(35, 33, 2, 12, 'usuario', 'fdf fdffasf saf fdsf', 'Sim', '2026-02-24 20:18:58'),
(36, 24, 2, 12, 'usuario', 'vcgdfgg ggfds gf', 'Sim', '2026-02-24 21:06:29'),
(37, 43, 12, 12, 'cliente', 'f dfa faf adf a fdfa fad ffa', 'Sim', '2026-02-25 12:50:45'),
(38, 43, 2, 12, 'usuario', 'fd fdfafadf ad fafaf', 'Sim', '2026-02-25 12:51:24'),
(39, 43, 12, 12, 'cliente', 'fda fafafffafa', 'Sim', '2026-02-25 12:51:46'),
(40, 42, 12, 12, 'cliente', 'gd ggfgsgfsgfsg', 'Sim', '2026-02-25 12:59:04'),
(41, 43, 2, 12, 'usuario', 'fda fadf afda', 'Sim', '2026-02-25 13:07:02'),
(42, 43, 12, 12, 'cliente', 'aaaa', 'Sim', '2026-02-25 17:54:57');

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
(12, 'Cliente Teste Hugo', 'contato@hugocursos.com.br', '(31) 97527-5084', '555.555.555-55', '$2y$10$BBtV6RX9LleG03a4wDZi0eREGJ1iAMsVqhPNxs14HVyfyy5rdwUrC', 'Sim', '30512-660', 'Rua Teste', NULL, NULL, 'Cabana do Pai Tomás', 'Belo Horizonte', 'MG', NULL, NULL, '2026-02-25 17:50:51', '127.0.0.1', '2026-02-10 20:52:18', 0, 'c12_20260224_195825_eb383724.jpg'),
(20, 'Cliente Teste 1', 'cliente1.teste@exemplo.com', '(31) 99999-1111', '123.456.789-09', '$2y$10$tROt0G6M4gA.nr8VPBAJVe0FNrSk2pbBkNvYBWkbuL6/7m0o1Z/gm', 'Sim', '30110-012', 'Av. Afonso Pena', '1000', 'Sala 101', 'Centro', 'Belo Horizonte', 'MG', 'Física', 'Importação de teste 1', NULL, NULL, '2026-02-15 00:00:00', NULL, NULL),
(21, 'Cliente Teste 2', 'cliente2.teste@exemplo.com', '(11) 98888-2222', '12.345.678/0001-90', '$2y$10$Bg6YKlMYjx73HelQdz4cne1HygEOCXIB2pEaPtDwdAQCVjCUu5alG', 'Não', '01001-000', 'Praça da Sé', '200', NULL, 'Sé', 'São Paulo', 'SP', 'Jurídica', 'Importação de teste 2', NULL, NULL, '2026-01-10 00:00:00', NULL, NULL),
(22, 'Cli Teste 2', NULL, NULL, '87000000000', '$2y$10$54STPMtIrOy5DCneLAvbMOYAymrMtHBOzdWrZQsPDr6qRqrHrah96', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(31, 'Cliente Ficticio', 'contato@sistemashugo.com.br', '(31) 99534-8118', NULL, '$2y$10$EGkPmtE/5b3ybZBEmCb39OA03SakH4JHhLc1r00NiZMYC5gyzAxoe', 'Sim', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-26 09:37:32', 0, 'sem_foto.webp');

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
  `token_ia` varchar(255) DEFAULT NULL,
  `dias_excluir_logs` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Despejando dados para a tabela `config`
--

INSERT INTO `config` (`id`, `nome_sistema`, `telefone_sistema`, `email_sistema`, `cor_primaria`, `cor_secundaria`, `logo`, `icone`, `endereco`, `smtp_host`, `smtp_senha`, `smtp_porta`, `smtp_seguranca`, `empresa`, `api_whatsapp`, `token_whatsapp`, `instancia_whatsapp`, `url_api`, `api_ia`, `token_ia`, `dias_excluir_logs`) VALUES
(1, 'Sistemas com IA', '(31) 97527-5084', 'contato@sistemashugo.com.br', '#667eea', '#764ba2', 'logo_20260203_194422_8c97291c.webp', 'icone_20260203_194422_8aa51489.png', 'Rua Boa Vista', 'mail.sistemashugo.com.br', 'T+MlSvapgmnHpdDNW2pErRf01s3esEsu37D1FPNBDaIqkENLvdY=', 587, 'tls', 0, 'Nenhuma', 'f6a13060-7e35-486a-b70c-9ffde7684217', 'v9t7zpp51nsSCMeqqJWfI4lj8iGG12tyMqW8PwvBH3CojiUaHM', 'https://evo.multiatendeia.com.br', 'gemini', 'AIzaSyCRYAnGjcX4qO-XItRjHzROLKMuw7Jhnyo', 30);

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
(206, 0, 2, 'inserir', 'chamados_anexos', 9, 'Anexo enviado no chamado CH20260223-A2BB5A', '/helpdesk/painel/ajax/abertura/anexos_salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-23 18:40:06'),
(207, 0, 2, 'inserir', 'chamados_anexos', 10, 'Anexo enviado no chamado CH20260223-A2BB5A', '/helpdesk/painel/ajax/abertura/anexos_salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-23 18:40:14'),
(208, 0, 2, 'inserir', 'chamados_respostas', 1, 'Resposta no chamado CH20260223-A2BB5A', '/helpdesk/painel/ajax/abertura/respostas_salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-23 18:52:45'),
(209, 0, 2, 'inserir', 'chamados_respostas', 2, 'Resposta no chamado CH20260223-A2BB5A', '/helpdesk/painel/ajax/abertura/respostas_salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-23 18:54:21');
INSERT INTO `logs` (`id`, `empresa`, `usuario_id`, `acao`, `entidade`, `registro_id`, `descricao`, `rota`, `ip`, `user_agent`, `criado_em`) VALUES
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
(284, 0, 2, 'inserir', 'chamados_respostas', 30, 'Resposta no chamado CH20260223-6D620B', '/helpdesk/painel/ajax/abertura/respostas_salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 13:42:58'),
(285, 0, 2, 'inserir', 'chamados_respostas', 31, 'Resposta no chamado CH20260224-BEF7DD', '/helpdesk/painel/ajax/abertura/respostas_salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 14:01:33'),
(286, 0, 2, 'inserir', 'chamados', 35, 'Chamado \'CH20260224-96E29B\' aberto', '/helpdesk/painel/ajax/abertura/salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 14:04:09'),
(287, 0, 2, 'inserir', 'chamados_respostas', 32, 'Resposta no chamado CH20260224-96E29B', '/helpdesk/painel/ajax/abertura/respostas_salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 14:04:57'),
(288, 0, 2, 'inserir', 'backup', 0, 'Gerou backup do banco helpdesk (backup_helpdesk_2026-02-24_14-53-22.sql)', '/helpdesk/painel/ajax/backup/gerar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 14:53:22'),
(289, 0, 2, 'inserir', 'cargos', 11, 'Cargo \'fdaf adfdasf dsa\' cadastrado', '/helpdesk/painel/ajax/cargos/salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 15:02:05'),
(290, 0, 2, 'inserir', 'cargos', 12, 'Cargo \'aaaaaaaaa\' cadastrado', '/helpdesk/painel/ajax/cargos/salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 15:02:09'),
(291, 0, 2, 'excluir', 'cargos', 11, 'Cargo \'fdaf adfdasf dsa\' excluído (seleção múltipla)', '/helpdesk/painel/ajax/cargos/excluir_selecionados.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 15:02:17'),
(292, 0, 2, 'excluir', 'cargos', 12, 'Cargo \'aaaaaaaaa\' excluído (seleção múltipla)', '/helpdesk/painel/ajax/cargos/excluir_selecionados.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 15:02:17'),
(293, 0, 2, 'inserir', 'cargos', 13, 'Cargo \'fa fadf dafdasf\' cadastrado', '/helpdesk/painel/ajax/cargos/salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 15:09:18'),
(294, 0, 2, 'inserir', 'cargos', 14, 'Cargo \'fa fa faf\' cadastrado', '/helpdesk/painel/ajax/cargos/salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 15:09:22'),
(295, 0, 2, 'editar', 'cargos', 14, 'Cargo \'aaaaaaaaaaaaa\' atualizado', '/helpdesk/painel/ajax/cargos/salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 15:09:26'),
(296, 0, 2, 'editar', 'cargos', 14, 'Cargo \'xxxxxxxxxxx\' atualizado', '/helpdesk/painel/ajax/cargos/salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 15:09:31'),
(297, 0, 2, 'excluir', 'cargos', 14, 'Usuário excluído do sistema', '/helpdesk/painel/ajax/cargos/excluir.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 15:09:34'),
(298, 0, 2, 'inserir', 'cargos', 15, 'Cargo \'fda fdafadfaf\' cadastrado', '/helpdesk/painel/ajax/cargos/salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 15:09:38'),
(299, 0, 2, 'inserir', 'cargos', 16, 'Cargo \'aafa fafa\' cadastrado', '/helpdesk/painel/ajax/cargos/salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 15:10:04'),
(300, 0, 2, 'editar', 'cargos', 16, 'Cargo \'aaaaaaaaaa\' atualizado', '/helpdesk/painel/ajax/cargos/salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 15:10:11'),
(301, 0, 2, 'excluir', 'cargos', 16, 'Usuário excluído do sistema', '/helpdesk/painel/ajax/cargos/excluir.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 15:10:15'),
(302, 0, 2, 'excluir', 'cargos', 13, 'Cargo \'fa fadf dafdasf\' excluído (seleção múltipla)', '/helpdesk/painel/ajax/cargos/excluir_selecionados.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 15:10:21'),
(303, 0, 2, 'excluir', 'cargos', 15, 'Cargo \'fda fdafadfaf\' excluído (seleção múltipla)', '/helpdesk/painel/ajax/cargos/excluir_selecionados.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 15:10:21'),
(304, 0, 2, 'inserir', 'chamados_status', 9, 'Status \'fa fadf dsaf\' cadastrado', '/helpdesk/painel/ajax/status_abertura/salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 15:16:54'),
(305, 0, 2, 'inserir', 'chamados_status', 10, 'Status \'aaaaaaaa\' cadastrado', '/helpdesk/painel/ajax/status_abertura/salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 15:16:58'),
(306, 0, 2, 'excluir', 'chamados_status', 9, 'Status \'fa fadf dsaf\' excluído (seleção múltipla)', '/helpdesk/painel/ajax/status_abertura/excluir_selecionados.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 15:17:03'),
(307, 0, 2, 'excluir', 'chamados_status', 10, 'Status \'aaaaaaaa\' excluído (seleção múltipla)', '/helpdesk/painel/ajax/status_abertura/excluir_selecionados.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 15:17:03'),
(308, 0, 2, 'inserir', 'setores', 9, 'Setor \'aaaa\' cadastrado', '/helpdesk/painel/ajax/setores/salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 15:20:40'),
(309, 0, 2, 'inserir', 'setores', 10, 'Setor \'bbb\' cadastrado', '/helpdesk/painel/ajax/setores/salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 15:20:43'),
(310, 0, 2, 'excluir', 'setores', 9, 'Setor \'aaaa\' excluído (seleção múltipla)', '/helpdesk/painel/ajax/setores/excluir_selecionados.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 15:21:03'),
(311, 0, 2, 'excluir', 'setores', 10, 'Setor \'bbb\' excluído (seleção múltipla)', '/helpdesk/painel/ajax/setores/excluir_selecionados.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 15:21:03'),
(312, 0, 2, 'inserir', 'clientes', 25, 'Cliente \'fda fafdsa\' cadastrado', '/helpdesk/painel/ajax/clientes/salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 15:22:42'),
(313, 0, 2, 'inserir', 'clientes', 26, 'Cliente \'fdsaf dfafdas\' cadastrado', '/helpdesk/painel/ajax/clientes/salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 15:22:56'),
(314, 0, 2, 'excluir', 'clientes', 25, 'Usuário excluído do sistema (seleção múltipla)', '/helpdesk/painel/ajax/clientes/excluir_selecionados.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 15:23:22'),
(315, 0, 2, 'excluir', 'clientes', 26, 'Usuário excluído do sistema (seleção múltipla)', '/helpdesk/painel/ajax/clientes/excluir_selecionados.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 15:23:22'),
(316, 0, 2, 'inserir', 'usuarios', 10, 'Usuário \'afda fadsf\' cadastrado', '/helpdesk/painel/ajax/usuarios/salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 15:25:49'),
(317, 0, 2, 'excluir', 'usuarios', 37, 'Usuário excluído do sistema (seleção múltipla)', '/helpdesk/painel/ajax/usuarios/excluir_selecionados.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 15:25:53'),
(318, 0, 0, 'logout', 'usuarios', 0, 'Logout realizado com sucesso', '/helpdesk/painel/logout.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 15:31:03'),
(319, 0, 2, 'login', 'usuarios', 2, 'Login realizado com sucesso', '/helpdesk/autenticar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 15:31:19'),
(320, 0, 2, 'editar', 'chamados', 35, 'Chamado \'CH20260224-96E29B\' atualizado', '/helpdesk/painel/ajax/abertura/salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 15:32:54'),
(321, 0, 2, 'inserir', 'chamados_respostas', 33, 'Resposta no chamado CH20260224-96E29B', '/helpdesk/painel/ajax/abertura/respostas_salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 15:33:03'),
(322, 0, 2, 'inserir', 'chamados_anexos', 11, 'Anexo enviado no chamado CH20260224-96E29B', '/helpdesk/painel/ajax/abertura/anexos_salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 15:33:38'),
(323, 0, 2, 'excluir', 'chamados', 35, 'Chamado excluído: CH20260224-96E29B - zzzzzzzzzzz (com dados relacionados)', '/helpdesk/painel/ajax/abertura/excluir.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 15:34:14'),
(324, 0, 0, 'logout', 'usuarios', 0, 'Logout realizado com sucesso', '/helpdesk/painel/logout.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 15:35:42'),
(325, 0, 2, 'login', 'usuarios', 2, 'Login realizado com sucesso', '/helpdesk/autenticar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 15:35:51'),
(326, 0, 0, 'logout', 'usuarios', 0, 'Logout realizado com sucesso', '/helpdesk/painel/logout.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 15:35:53'),
(327, 0, 2, 'login', 'usuarios', 2, 'Login realizado com sucesso', '/helpdesk/autenticar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 15:36:10'),
(328, 0, 0, 'logout', 'usuarios', 0, 'Logout realizado com sucesso', '/helpdesk/painel/logout.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 15:49:09'),
(329, 0, 2, 'login', 'usuarios', 2, 'Login realizado com sucesso', '/helpdesk/autenticar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 15:50:41'),
(330, 0, 2, 'editar', 'usuarios', 27, 'Senha do usuário resetada para padrão', '/helpdesk/painel/ajax/usuarios/resetar_senha.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 15:55:29'),
(331, 0, 0, 'logout', 'usuarios', 0, 'Logout realizado com sucesso', '/helpdesk/painel/logout.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 15:55:32'),
(332, 0, 27, 'login', 'usuarios', 27, 'Login realizado com sucesso', '/helpdesk/autenticar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 15:55:35'),
(333, 0, 0, 'logout', 'usuarios', 0, 'Logout realizado com sucesso', '/helpdesk/painel/logout.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 15:55:51'),
(334, 0, 27, 'login', 'usuarios', 27, 'Login realizado com sucesso', '/helpdesk/autenticar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 15:56:01'),
(335, 0, 0, 'logout', 'usuarios', 0, 'Logout realizado com sucesso', '/helpdesk/painel/logout.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 15:56:04'),
(336, 0, 2, 'login', 'usuarios', 2, 'Login realizado com sucesso', '/helpdesk/autenticar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 15:56:11'),
(337, 0, 2, 'editar', 'usuarios', 27, 'Senha do usuário resetada para padrão', '/helpdesk/painel/ajax/usuarios/resetar_senha.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 15:56:18'),
(338, 0, 0, 'logout', 'usuarios', 0, 'Logout realizado com sucesso', '/helpdesk/painel/logout.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 15:56:23'),
(339, 0, 27, 'login', 'usuarios', 27, 'Login realizado com sucesso', '/helpdesk/autenticar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 15:56:26'),
(340, 0, 0, 'logout', 'usuarios', 0, 'Logout realizado com sucesso', '/helpdesk/painel/logout.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 15:56:30'),
(341, 0, 2, 'login', 'usuarios', 2, 'Login realizado com sucesso', '/helpdesk/autenticar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 15:56:37'),
(342, 0, 2, 'editar', 'clientes', 22, 'Senha do cliente \'Cli Teste 2\' resetada para padrão', '/helpdesk/painel/ajax/clientes/resetar_senha.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 15:58:58'),
(343, 0, 2, 'login', 'clientes', 12, 'Login do cliente realizado com sucesso', '/helpdesk/autenticar_cliente.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 17:58:50'),
(344, 0, 2, 'login', 'clientes', 12, 'Login do cliente realizado com sucesso', '/helpdesk/autenticar_cliente.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 18:03:10'),
(345, 0, 2, 'login', 'clientes', 12, 'Login do cliente realizado com sucesso', '/helpdesk/autenticar_cliente.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 18:03:24'),
(346, 0, 2, 'login', 'clientes', 12, 'Login do cliente realizado com sucesso', '/helpdesk/autenticar_cliente.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 18:05:04'),
(347, 0, 2, 'login', 'clientes', 12, 'Login do cliente realizado com sucesso', '/helpdesk/autenticar_cliente.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 18:14:24'),
(348, 0, 0, 'logout', 'usuarios', 0, 'Logout realizado com sucesso', '/helpdesk/painel/logout.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 18:52:47'),
(349, 0, 2, 'login', 'usuarios', 2, 'Login realizado com sucesso', '/helpdesk/autenticar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 18:53:03'),
(350, 0, 0, 'login', 'clientes', 12, 'Login do cliente realizado com sucesso', '/helpdesk/autenticar_cliente.php', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-24 19:02:26'),
(351, 0, 2, 'login', 'clientes', 12, 'Login do cliente realizado com sucesso', '/helpdesk/autenticar_cliente.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 19:03:55'),
(352, 0, 0, 'logout', 'usuarios', 0, 'Logout realizado com sucesso', '/helpdesk/painel_cliente/logout.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 19:07:59'),
(353, 0, 0, 'login', 'clientes', 12, 'Login do cliente realizado com sucesso', '/helpdesk/autenticar_cliente.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 19:08:08'),
(354, 0, 0, 'login', 'clientes', 12, 'Login do cliente realizado com sucesso', '/helpdesk/autenticar_cliente.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 19:08:22'),
(355, 0, 0, 'login', 'clientes', 12, 'Login do cliente realizado com sucesso', '/helpdesk/autenticar_cliente.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 19:08:38'),
(356, 0, 0, 'login', 'clientes', 12, 'Login do cliente realizado com sucesso', '/helpdesk/autenticar_cliente.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 19:08:53'),
(357, 0, 0, 'login', 'clientes', 12, 'Login do cliente realizado com sucesso', '/helpdesk/autenticar_cliente.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 19:09:59'),
(358, 0, 0, 'login', 'clientes', 12, 'Login do cliente realizado com sucesso', '/helpdesk/autenticar_cliente.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 19:10:04'),
(359, 0, 0, 'login', 'clientes', 12, 'Login do cliente realizado com sucesso', '/helpdesk/autenticar_cliente.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 19:10:20'),
(360, 0, 0, 'login', 'clientes', 12, 'Login do cliente realizado com sucesso', '/helpdesk/autenticar_cliente.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 19:10:24'),
(361, 0, 0, 'login', 'clientes', 12, 'Login do cliente realizado com sucesso', '/helpdesk/autenticar_cliente.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 19:12:03'),
(362, 0, 0, 'logout', 'usuarios', 0, 'Logout realizado com sucesso', '/helpdesk/painel_cliente/logout.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 19:17:13'),
(363, 0, 0, 'login', 'clientes', 12, 'Login do cliente realizado com sucesso', '/helpdesk/autenticar_cliente.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 19:17:19'),
(364, 0, 0, 'logout', 'usuarios', 0, 'Logout realizado com sucesso', '/helpdesk/painel_cliente/logout.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 19:18:23'),
(365, 0, 0, 'login', 'clientes', 12, 'Login do cliente realizado com sucesso', '/helpdesk/autenticar_cliente.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 19:18:25'),
(366, 0, 0, 'logout', 'usuarios', 0, 'Logout realizado com sucesso', '/helpdesk/painel_cliente/logout.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 19:18:33'),
(367, 0, 0, 'login', 'clientes', 12, 'Login do cliente realizado com sucesso', '/helpdesk/autenticar_cliente.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 19:18:34'),
(368, 0, 0, 'logout', 'usuarios', 0, 'Logout realizado com sucesso', '/helpdesk/painel_cliente/logout.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 19:46:27'),
(369, 0, 0, 'login', 'clientes', 12, 'Login do cliente realizado com sucesso', '/helpdesk/autenticar_cliente.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 19:46:30'),
(370, 0, 0, 'logout', 'usuarios', 0, 'Logout realizado com sucesso', '/helpdesk/painel_cliente/logout.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 19:51:15'),
(371, 0, 0, 'login', 'clientes', 12, 'Login do cliente realizado com sucesso', '/helpdesk/autenticar_cliente.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 19:51:16'),
(372, 0, 0, 'logout', 'usuarios', 0, 'Logout realizado com sucesso', '/helpdesk/painel_cliente/logout.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 19:58:33'),
(373, 0, 2, 'login', 'usuarios', 2, 'Login realizado com sucesso', '/helpdesk/autenticar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 19:58:38'),
(374, 0, 0, 'logout', 'usuarios', 0, 'Logout realizado com sucesso', '/helpdesk/painel/logout.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 19:58:42'),
(375, 0, 0, 'login', 'clientes', 12, 'Login do cliente realizado com sucesso', '/helpdesk/autenticar_cliente.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 19:58:50'),
(376, 0, 2, 'login', 'usuarios', 2, 'Login realizado com sucesso', '/helpdesk/autenticar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 20:06:11'),
(377, 0, 2, 'inserir', 'chamados_respostas', 34, 'Resposta no chamado CH20260224-BEF7DD', '/helpdesk/painel/ajax/abertura/respostas_salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 20:06:27'),
(378, 0, 2, 'inserir', 'chamados_respostas', 35, 'Resposta no chamado CH20260224-5BDC41', '/helpdesk/painel/ajax/abertura/respostas_salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 20:18:58'),
(379, 0, 2, 'inserir', 'chamados_respostas', 36, 'Resposta no chamado CH20260224-6D15AB', '/helpdesk/painel_cliente/ajax/abertura/respostas_salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 21:06:29'),
(380, 0, 2, 'inserir', 'chamados', 36, 'Chamado \'CH20260224-D86D29\' aberto', '/helpdesk/painel_cliente/ajax/abertura/salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 21:25:38'),
(381, 0, 2, 'inserir', 'chamados', 37, 'Chamado \'CH20260224-D2DB16\' aberto', '/helpdesk/painel_cliente/ajax/abertura/salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 21:27:20'),
(382, 0, 2, 'inserir', 'chamados', 38, 'Chamado \'CH20260224-B701AB\' aberto', '/helpdesk/painel_cliente/ajax/abertura/salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 21:27:25'),
(383, 0, 2, 'inserir', 'chamados', 39, 'Chamado \'CH20260224-14AE95\' aberto', '/helpdesk/painel_cliente/ajax/abertura/salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 21:33:41'),
(384, 0, 0, 'logout', 'usuarios', 0, 'Logout realizado com sucesso', '/helpdesk/painel_cliente/logout.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-25 10:05:09'),
(385, 0, 2, 'login', 'usuarios', 2, 'Login realizado com sucesso', '/helpdesk/autenticar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-25 10:05:15'),
(386, 0, 0, 'logout', 'usuarios', 0, 'Logout realizado com sucesso', '/helpdesk/painel/logout.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-25 10:07:08'),
(387, 0, 0, 'login', 'clientes', 12, 'Login do cliente realizado com sucesso', '/helpdesk/autenticar_cliente.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-25 10:07:35'),
(388, 0, 0, 'logout', 'usuarios', 0, 'Logout realizado com sucesso', '/helpdesk/painel_cliente/logout.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-25 10:12:37'),
(389, 0, 0, 'login', 'clientes', 12, 'Login do cliente realizado com sucesso', '/helpdesk/autenticar_cliente.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-25 10:12:39'),
(390, 0, 0, 'logout', 'usuarios', 0, 'Logout realizado com sucesso', '/helpdesk/painel_cliente/logout.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-25 10:12:45'),
(391, 0, 2, 'login', 'usuarios', 2, 'Login realizado com sucesso', '/helpdesk/autenticar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-25 10:12:50'),
(392, 0, 0, 'logout', 'usuarios', 0, 'Logout realizado com sucesso', '/helpdesk/painel/logout.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-25 10:12:56'),
(393, 0, 0, 'login', 'clientes', 12, 'Login do cliente realizado com sucesso', '/helpdesk/autenticar_cliente.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-25 10:13:00'),
(394, 0, 2, 'login', 'usuarios', 2, 'Login realizado com sucesso', '/helpdesk/autenticar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-25 10:29:22'),
(395, 0, 2, 'inserir', 'chamados_anexos', 16, 'Anexo enviado no chamado CH20260225-DE0B1D', '/helpdesk/painel/ajax/abertura/anexos_salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-25 10:29:41'),
(396, 0, 2, 'excluir', 'chamados_anexos', 15, 'Anexo excluído (Chamado CH20260225-DE0B1D) ch_43_20260225_102654_93ed8b90be68797d.jpeg fda fad adf', '/helpdesk/painel_cliente/ajax/abertura/anexos_excluir.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-25 10:30:03'),
(397, 0, 2, 'login', 'clientes', 12, 'Login do cliente realizado com sucesso', '/helpdesk/autenticar_cliente.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-25 10:41:58'),
(398, 0, 2, 'excluir', 'chamados_anexos', 16, 'Anexo excluído (Chamado CH20260225-DE0B1D) ch_43_20260225_102941_ff7e95950dc64fe1.pdf PDF adm', '/helpdesk/painel_cliente/ajax/abertura/anexos_excluir.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-25 10:43:38'),
(399, 0, 2, 'excluir', 'chamados_anexos', 14, 'Anexo excluído (Chamado CH20260225-DE0B1D) ch_43_20260225_102509_f29dc160bdee4b7c.pdf PDF', '/helpdesk/painel_cliente/ajax/abertura/anexos_excluir.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-25 10:43:40'),
(400, 0, 2, 'excluir', 'chamados_anexos', 13, 'Anexo excluído (Chamado CH20260225-DE0B1D) ch_43_20260225_102438_e1103516dd51dbc9.png Teste', '/helpdesk/painel_cliente/ajax/abertura/anexos_excluir.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-25 10:43:42'),
(401, 0, 2, 'inserir', 'chamados_anexos', 18, 'Anexo enviado no chamado CH20260225-DE0B1D', '/helpdesk/painel/ajax/abertura/anexos_salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-25 10:43:55'),
(402, 0, 2, 'inserir', 'chamados_respostas', 38, 'Resposta no chamado CH20260225-DE0B1D', '/helpdesk/painel/ajax/abertura/respostas_salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-25 12:51:24'),
(403, 0, 2, 'inserir', 'chamados_respostas', 41, 'Resposta no chamado CH20260225-DE0B1D', '/helpdesk/painel/ajax/abertura/respostas_salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-25 13:07:02'),
(404, 0, 0, 'logout', 'usuarios', 0, 'Logout realizado com sucesso', '/helpdesk/painel_cliente/logout.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-25 13:14:25'),
(405, 0, 2, 'login', 'usuarios', 2, 'Login realizado com sucesso', '/helpdesk/autenticar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-25 13:14:29'),
(406, 0, 0, 'logout', 'usuarios', 0, 'Logout realizado com sucesso', '/helpdesk/painel/logout.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-25 13:54:03'),
(407, 0, 17, 'login', 'usuarios', 17, 'Login realizado com sucesso', '/helpdesk/autenticar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-25 13:54:08'),
(408, 0, 0, 'logout', 'usuarios', 0, 'Logout realizado com sucesso', '/helpdesk/painel/logout.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-25 13:54:42'),
(409, 0, 2, 'login', 'usuarios', 2, 'Login realizado com sucesso', '/helpdesk/autenticar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-25 13:54:50'),
(410, 0, 2, 'editar', 'chamados', 43, 'Chamado \'CH20260225-DE0B1D\' atualizado (rápido): status -> Aguardando cliente', '/helpdesk/painel/ajax/abertura/atualizar_rapido.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-25 14:09:08'),
(411, 0, 2, 'editar', 'chamados', 42, 'Chamado \'CH20260225-A559BA\' atualizado (rápido): status -> Fechado', '/helpdesk/painel/ajax/abertura/atualizar_rapido.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-25 14:09:22');
INSERT INTO `logs` (`id`, `empresa`, `usuario_id`, `acao`, `entidade`, `registro_id`, `descricao`, `rota`, `ip`, `user_agent`, `criado_em`) VALUES
(412, 0, 0, 'logout', 'usuarios', 0, 'Logout realizado com sucesso', '/helpdesk/painel/logout.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-25 16:03:15'),
(413, 0, 2, 'login', 'usuarios', 2, 'Login realizado com sucesso', '/helpdesk/autenticar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-25 16:54:02'),
(414, 0, 0, 'logout', 'usuarios', 0, 'Logout realizado com sucesso', '/helpdesk/painel/logout.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-25 17:43:09'),
(415, 0, 0, 'login', 'clientes', 12, 'Login do cliente realizado com sucesso', '/helpdesk/autenticar_cliente.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-25 17:43:15'),
(416, 0, 0, 'logout', 'usuarios', 0, 'Logout realizado com sucesso', '/helpdesk/painel_cliente/logout.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-25 17:43:59'),
(417, 0, 0, 'login', 'clientes', 12, 'Login do cliente realizado com sucesso', '/helpdesk/autenticar_cliente.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-25 17:50:52'),
(418, 0, 0, 'logout', 'usuarios', 0, 'Logout realizado com sucesso', '/helpdesk/painel_cliente/logout.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-25 17:59:21'),
(419, 0, 2, 'login', 'usuarios', 2, 'Login realizado com sucesso', '/helpdesk/autenticar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-25 18:11:00'),
(420, 0, 2, 'inserir', 'chamados', 44, 'Chamado \'CH20260225-749F71\' aberto', '/helpdesk/painel/ajax/abertura/salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-25 18:12:40'),
(421, 0, 2, 'inserir', 'chamados', 45, 'Chamado \'CH20260225-C7B420\' aberto', '/helpdesk/painel/ajax/abertura/salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-25 18:12:57'),
(422, 0, 2, 'excluir', 'clientes', 27, 'Usuário excluído do sistema (seleção múltipla)', '/helpdesk/painel/ajax/clientes/excluir_selecionados.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-25 18:13:10'),
(423, 0, 2, 'excluir', 'clientes', 28, 'Usuário excluído do sistema (seleção múltipla)', '/helpdesk/painel/ajax/clientes/excluir_selecionados.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-25 18:13:10'),
(424, 0, 2, 'excluir', 'clientes', 29, 'Usuário excluído do sistema (seleção múltipla)', '/helpdesk/painel/ajax/clientes/excluir_selecionados.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-25 18:13:10'),
(425, 0, 2, 'excluir', 'clientes', 30, 'Usuário excluído do sistema (seleção múltipla)', '/helpdesk/painel/ajax/clientes/excluir_selecionados.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-25 18:13:10'),
(426, 0, 2, 'excluir', 'chamados', 45, 'Chamado excluído: CH20260225-C7B420 - dgfsgsfgsgfsg gfgfsg (com dados relacionados)', '/helpdesk/painel/ajax/abertura/excluir.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-25 18:13:38'),
(427, 0, 2, 'excluir', 'chamados', 44, 'Chamado excluído: CH20260225-749F71 - Teesssttt (com dados relacionados)', '/helpdesk/painel/ajax/abertura/excluir.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-25 18:13:40'),
(428, 0, 0, 'logout', 'usuarios', 0, 'Logout realizado com sucesso', '/helpdesk/painel/logout.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-25 19:38:11'),
(429, 0, 2, 'login', 'usuarios', 2, 'Login realizado com sucesso', '/helpdesk/autenticar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-25 19:40:25'),
(430, 0, 0, 'logout', 'usuarios', 0, 'Logout realizado com sucesso', '/helpdesk/painel/logout.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-25 19:52:51'),
(431, 0, 2, 'login', 'usuarios', 2, 'Login realizado com sucesso', '/helpdesk/autenticar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-25 19:53:57'),
(432, 0, 0, 'logout', 'usuarios', 0, 'Logout realizado com sucesso', '/helpdesk/painel/logout.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-25 19:54:16'),
(433, 0, 2, 'login', 'usuarios', 2, 'Login realizado com sucesso', '/helpdesk/autenticar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-25 19:54:41'),
(434, 0, 2, 'login', 'usuarios', 2, 'Login realizado com sucesso', '/helpdesk/autenticar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-26 08:27:23'),
(435, 0, 2, 'login', 'usuarios', 2, 'Login realizado com sucesso', '/helpdesk/autenticar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-26 09:26:51'),
(436, 0, 2, 'inserir', 'clientes', 31, 'Cliente \'Cliente Ficticio\' cadastrado', '/helpdesk/painel/ajax/clientes/salvar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-26 09:37:40'),
(437, 0, 2, 'login', 'usuarios', 2, 'Login realizado com sucesso', '/helpdesk/autenticar.php', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-26 10:31:02');

-- --------------------------------------------------------

--
-- Estrutura para tabela `recuperacao_senha`
--

CREATE TABLE `recuperacao_senha` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `cliente_id` int(11) DEFAULT NULL,
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

INSERT INTO `recuperacao_senha` (`id`, `usuario_id`, `cliente_id`, `token_hash`, `expira_em`, `usado_em`, `ip`, `user_agent`, `criado_em`) VALUES
(14, 2, NULL, '$2y$10$twISLP6r5F/P5g7CJCmBteUsnYtdu.bgGSkPRAAsZMYLi4sJVs8Q.', '2026-02-03 18:42:00', '2026-02-03 18:12:29', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-03 18:12:00'),
(15, 2, NULL, '$2y$10$GHMZ8mil1ZjcfiQ45M4N5ul5C/9sUnQrC9Y3mo71XiLyO7soELeA6', '2026-02-15 21:19:54', '2026-02-18 12:11:30', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-15 20:49:54'),
(16, 2, NULL, '$2y$10$zsd/8O1FxFfh7yChJmRPP.LEaVH84eJ7vzNO6mxQelih8NW1vOqPK', '2026-02-18 12:41:30', '2026-02-18 12:12:39', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-18 12:11:30'),
(17, 2, NULL, '$2y$10$AOF9kzcT.Oahlc6nZ/AsbODzNcCHJh2ndzF6RN/zPEhfZN6JuQodW', '2026-02-18 12:42:39', '2026-02-24 18:44:49', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-18 12:12:39'),
(18, 2, NULL, '$2y$10$KHYMgBn7.Tag7khu4p5TdOCuZVSkYMIcuMdV8HhzT7ysImSGpGxtC', '2026-02-24 19:14:49', '2026-02-24 18:52:49', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 18:44:49'),
(19, NULL, 12, '$2y$10$HbpivVtns85as6ICb2t69eyZl1MhwlbZnqENWwRkGEO901Aq/FWHC', '2026-02-24 19:15:30', '2026-02-24 18:46:41', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 18:45:30'),
(20, NULL, 12, '$2y$10$qnhFcOERmaJGyWFp.NoBwuHFBJcoLyTT3UCPY7C9fvxse5tnpganm', '2026-02-24 19:16:41', '2026-02-24 18:56:21', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 18:46:41'),
(21, 2, NULL, '$2y$10$yy4icEnkydZQTPyR6/m.c.MouwArVe9IF6WIQUti8FD/UUssXElKW', '2026-02-24 19:22:49', '2026-02-25 16:03:17', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 18:52:49'),
(22, NULL, 12, '$2y$10$S8LDaWfICzhBhV3TfF695OwvxsIynmDFoVx8ZTczMmyMLEOQwOZUq', '2026-02-24 19:26:21', '2026-02-24 19:01:28', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 18:56:21'),
(23, NULL, 12, '$2y$10$SjdOsUo5Dv3ftVLkmAy/yuJMSWRgjL.C.tLw7l8Eh2teTX4m8E9Vu', '2026-02-24 19:31:55', '2026-02-24 19:02:14', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-24 19:01:55'),
(24, NULL, 12, '$2y$10$NjECeOhuGzeAKobi7rSJUOjqlk8IOuzHZ79V0W3c/MZ5Qal6TnezS', '2026-02-24 19:33:08', '2026-02-24 19:03:27', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-24 19:03:08'),
(25, 2, NULL, '$2y$10$kvFvZT2TmjXI0rAvfDPWF.xE314YPJm4OlNIikC0xBL4uQT7N6ubS', '2026-02-25 16:33:17', '2026-02-25 16:03:49', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-25 16:03:17'),
(26, 2, NULL, '$2y$10$R6aUBRLrNSsSWE2rQZ1tmO245rba/.esdu./iIT6o49W4pPS2NJNq', '2026-02-25 16:33:49', '2026-02-25 19:38:14', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-25 16:03:49'),
(27, 2, NULL, '$2y$10$pKcBUsbAREH5I22GJzMUeuruEeRELjR/Q/ZNw5QPaQoZfQrQUxugy', '2026-02-25 20:08:14', '2026-02-25 19:54:19', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-25 19:38:14'),
(28, 2, NULL, '$2y$10$bslzUoDmXEeVOJXwpkZmluRGOPeYUo7Chv5eLAgoYn5NAikfwwRri', '2026-02-25 20:24:19', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-25 19:54:19');

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
(2, 'Usuário Administrador', '(31) 99534-8118', 'contato@hugocursos.com.br', '$2y$10$CF8eWvhSmTgK2Mxgbv.LpOpW.QSSRsgzialj2zjWW0rISXtvOJ2cm', '', 'Rua Boa Vista', '', '', 'Cabana do Pai Tomás', 'Belo Horizonte', 'MG', '30512660', 'u2_1770141461.jpg', 'Administrador', 'Sim', 0, '2026-02-03 12:35:19', '2026-02-25 19:54:09', 'assinatura_usu_ario_administrador_2_20260216_102004.png'),
(12, 'Usuário teste', '(00) 00000-0000', 'teste@hotmail.com', '$2y$10$2Xbttb/IjcAjQCWbvFjndOJKVQaloHmwVM4FX7eHs0PoieYa7BaIO', NULL, NULL, NULL, NULL, NULL, NULL, 'PB', NULL, 'sem_foto.webp', 'Comum', 'Sim', 0, '2026-02-04 11:43:21', '2026-02-09 14:44:12', NULL),
(17, 'Novo Testes', NULL, 'novoteste@hotmail.com', '$2y$10$/w9iroJK8C8/A53mf5FdVOZ4Cw5dhdFMXW2R4WGCRGGUxOf95bfpK', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'sem_foto.webp', 'Atendente', 'Sim', 0, '2026-02-09 19:30:57', '2026-02-23 12:08:33', 'assinatura_novo_teste_17_20260215_225423.png'),
(27, 'teste novo', '(31) 97527-5084', 'testenovoss@hotmail.com', '$2y$10$S64YoCO1ODMTFPXEhAjGO.zQz66154wS0k6sdFWw9mk/C7wcgASW6', '', '', '', '', '', '', '', '', 'sem_foto.webp', 'Administrador', 'Sim', 0, '2026-02-10 20:50:08', '2026-02-24 15:56:18', 'assinatura_teste_novo_27_20260216_100407.png');

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
  ADD KEY `idx_usado` (`usado_em`),
  ADD KEY `idx_cliente_id` (`cliente_id`),
  ADD KEY `idx_usuario_id` (`usuario_id`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT de tabela `chamados`
--
ALTER TABLE `chamados`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT de tabela `chamados_anexos`
--
ALTER TABLE `chamados_anexos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT de tabela `chamados_movimentos`
--
ALTER TABLE `chamados_movimentos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=97;

--
-- AUTO_INCREMENT de tabela `chamados_respostas`
--
ALTER TABLE `chamados_respostas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT de tabela `chamados_status`
--
ALTER TABLE `chamados_status`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de tabela `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT de tabela `config`
--
ALTER TABLE `config`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `logs`
--
ALTER TABLE `logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=438;

--
-- AUTO_INCREMENT de tabela `recuperacao_senha`
--
ALTER TABLE `recuperacao_senha`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT de tabela `setores`
--
ALTER TABLE `setores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT de tabela `usuarios_acoes`
--
ALTER TABLE `usuarios_acoes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

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
