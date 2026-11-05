-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Host: sql209.infinityfree.com
-- Generation Time: Sep 25, 2026 at 05:35 PM
-- Server version: 11.4.13-MariaDB
-- PHP Version: 7.2.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `if0_42685142_flowgrid`
--

-- --------------------------------------------------------

--
-- Table structure for table `documentos`
--

CREATE TABLE `documentos` (
  `id_documento` int(11) NOT NULL,
  `nome_documento` varchar(100) DEFAULT NULL,
  `prioridade` enum('Baixa','Média','Alta','Urgente') DEFAULT 'Média',
  `data_inicio` date DEFAULT NULL,
  `data_entrega` date DEFAULT NULL,
  `status_documento` enum('Pendente','Em Andamento','Concluido') DEFAULT 'Pendente',
  `descricao` varchar(2000) DEFAULT NULL,
  `arquivo` longblob DEFAULT NULL,
  `responsavel` int(11) DEFAULT NULL,
  `projeto` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `documentos`
--

INSERT INTO `documentos` (`id_documento`, `nome_documento`, `prioridade`, `data_inicio`, `data_entrega`, `status_documento`, `descricao`, `arquivo`, `responsavel`, `projeto`) VALUES
(1, 'alo', 'Urgente', '2026-09-23', '2026-09-30', 'Pendente', 'trbrbtbrtbr', NULL, 1, 3);

-- --------------------------------------------------------

--
-- Table structure for table `empresa`
--

CREATE TABLE `empresa` (
  `id_empresa` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `empresa`
--

INSERT INTO `empresa` (`id_empresa`) VALUES
(1);

-- --------------------------------------------------------

--
-- Table structure for table `equipes`
--

CREATE TABLE `equipes` (
  `id_equipe` int(11) NOT NULL,
  `setor` varchar(100) NOT NULL,
  `descricao_equipe` varchar(1000) DEFAULT NULL,
  `projeto_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `equipe_projeto`
--

CREATE TABLE `equipe_projeto` (
  `id_equipe_projeto` int(11) NOT NULL,
  `equipe_id` int(11) NOT NULL,
  `projeto_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `projetos`
--

CREATE TABLE `projetos` (
  `id_projeto` int(11) NOT NULL,
  `nome_projeto` varchar(100) DEFAULT NULL,
  `status_projeto` enum('Pendente','Em Andamento','Concluido') DEFAULT 'Pendente',
  `responsavel_id` int(11) DEFAULT NULL,
  `data_criacao` datetime DEFAULT current_timestamp(),
  `data_entrega` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `projetos`
--

INSERT INTO `projetos` (`id_projeto`, `nome_projeto`, `status_projeto`, `responsavel_id`, `data_criacao`, `data_entrega`) VALUES
(2, 'cu', 'Pendente', 1, '2026-09-23 04:43:39', NULL),
(3, 'Eu to puto', 'Pendente', 2, '2026-09-23 15:35:03', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `relatorios`
--

CREATE TABLE `relatorios` (
  `id_relatorio` int(11) NOT NULL,
  `data_relatorio` date DEFAULT NULL,
  `relatorio` longblob DEFAULT NULL,
  `status` enum('Pendente','Em Andamento','Concluido') DEFAULT 'Pendente',
  `projeto` int(11) DEFAULT NULL,
  `dono` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `responsavel`
--

CREATE TABLE `responsavel` (
  `responsavel_id` int(11) NOT NULL,
  `usuario_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `responsavel`
--

INSERT INTO `responsavel` (`responsavel_id`, `usuario_id`) VALUES
(1, 1),
(2, 2);

-- --------------------------------------------------------

--
-- Table structure for table `usuarios`
--

CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL,
  `nome_usuario` varchar(100) NOT NULL,
  `user_usuario` varchar(50) NOT NULL,
  `senha_usuario` varchar(255) NOT NULL,
  `ocupacao` int(1) NOT NULL DEFAULT 3,
  `empresa_cod` int(11) DEFAULT NULL,
  `equipe_id` int(11) DEFAULT NULL,
  `data_cadastro` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `nome_usuario`, `user_usuario`, `senha_usuario`, `ocupacao`, `empresa_cod`, `equipe_id`, `data_cadastro`) VALUES
(1, 'joao', 'joao', '$2y$10$V5cJSmoRM4x55OTINu.eJ.oler88Qp1OSrjlI5D.IwQI6XjzcD2OK', 3, 1, NULL, '2026-09-21 10:28:48'),
(2, 'ser', 'sa', '$2y$10$xLuM2phYa4I7MidO1LzShe3R1c7SJOGB0S4CP.vTzJUec6YBlWdtK', 1, 1, NULL, '2026-09-21 10:31:22');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `documentos`
--
ALTER TABLE `documentos`
  ADD PRIMARY KEY (`id_documento`),
  ADD KEY `idx_documentos_responsavel` (`responsavel`),
  ADD KEY `idx_documentos_projeto` (`projeto`);

--
-- Indexes for table `empresa`
--
ALTER TABLE `empresa`
  ADD PRIMARY KEY (`id_empresa`);

--
-- Indexes for table `equipes`
--
ALTER TABLE `equipes`
  ADD PRIMARY KEY (`id_equipe`),
  ADD KEY `idx_equipes_projeto` (`projeto_id`);

--
-- Indexes for table `equipe_projeto`
--
ALTER TABLE `equipe_projeto`
  ADD PRIMARY KEY (`id_equipe_projeto`),
  ADD UNIQUE KEY `unique_equipe_projeto` (`equipe_id`,`projeto_id`),
  ADD KEY `idx_equipe_projeto_equipe` (`equipe_id`),
  ADD KEY `idx_equipe_projeto_projeto` (`projeto_id`);

--
-- Indexes for table `projetos`
--
ALTER TABLE `projetos`
  ADD PRIMARY KEY (`id_projeto`),
  ADD KEY `idx_projetos_responsavel` (`responsavel_id`),
  ADD KEY `idx_projetos_status` (`status_projeto`);

--
-- Indexes for table `relatorios`
--
ALTER TABLE `relatorios`
  ADD PRIMARY KEY (`id_relatorio`),
  ADD KEY `idx_relatorios_status` (`status`),
  ADD KEY `idx_relatorios_projeto` (`projeto`),
  ADD KEY `idx_relatorios_dono` (`dono`);

--
-- Indexes for table `responsavel`
--
ALTER TABLE `responsavel`
  ADD PRIMARY KEY (`responsavel_id`),
  ADD KEY `idx_responsavel_usuario` (`usuario_id`);

--
-- Indexes for table `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_usuario`),
  ADD KEY `idx_usuarios_empresa` (`empresa_cod`),
  ADD KEY `idx_usuarios_equipe` (`equipe_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `documentos`
--
ALTER TABLE `documentos`
  MODIFY `id_documento` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `empresa`
--
ALTER TABLE `empresa`
  MODIFY `id_empresa` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `equipes`
--
ALTER TABLE `equipes`
  MODIFY `id_equipe` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `equipe_projeto`
--
ALTER TABLE `equipe_projeto`
  MODIFY `id_equipe_projeto` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `projetos`
--
ALTER TABLE `projetos`
  MODIFY `id_projeto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `relatorios`
--
ALTER TABLE `relatorios`
  MODIFY `id_relatorio` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `responsavel`
--
ALTER TABLE `responsavel`
  MODIFY `responsavel_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `documentos`
--
ALTER TABLE `documentos`
  ADD CONSTRAINT `FK_documentos_projetos` FOREIGN KEY (`projeto`) REFERENCES `projetos` (`id_projeto`),
  ADD CONSTRAINT `FK_documentos_responsavel` FOREIGN KEY (`responsavel`) REFERENCES `responsavel` (`responsavel_id`);

--
-- Constraints for table `equipes`
--
ALTER TABLE `equipes`
  ADD CONSTRAINT `FK_equipes_projetos` FOREIGN KEY (`projeto_id`) REFERENCES `projetos` (`id_projeto`);

--
-- Constraints for table `projetos`
--
ALTER TABLE `projetos`
  ADD CONSTRAINT `FK_projetos_responsavel` FOREIGN KEY (`responsavel_id`) REFERENCES `responsavel` (`responsavel_id`);

--
-- Constraints for table `relatorios`
--
ALTER TABLE `relatorios`
  ADD CONSTRAINT `FK_relatorios_projetos` FOREIGN KEY (`projeto`) REFERENCES `projetos` (`id_projeto`),
  ADD CONSTRAINT `FK_relatorios_usuarios` FOREIGN KEY (`dono`) REFERENCES `usuarios` (`id_usuario`);

--
-- Constraints for table `responsavel`
--
ALTER TABLE `responsavel`
  ADD CONSTRAINT `FK_responsavel_usuarios` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id_usuario`);

--
-- Constraints for table `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `FK_usuarios_empresa` FOREIGN KEY (`empresa_cod`) REFERENCES `empresa` (`id_empresa`),
  ADD CONSTRAINT `FK_usuarios_equipes` FOREIGN KEY (`equipe_id`) REFERENCES `equipes` (`id_equipe`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
