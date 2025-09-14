-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Host: sql211.infinityfree.com
-- Generation Time: Sep 14, 2025 at 05:46 AM
-- Server version: 11.4.7-MariaDB
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
-- Database: `if0_39215471_admin_panel`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_logs`
--

CREATE TABLE `admin_logs` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `action` varchar(50) NOT NULL,
  `target_table` varchar(50) NOT NULL,
  `target_id` int(11) NOT NULL,
  `description` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `fecha` timestamp NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `logs_admin`
--

CREATE TABLE `logs_admin` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `accion` varchar(100) NOT NULL,
  `detalles` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `fecha` datetime DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notificaciones`
--

CREATE TABLE `notificaciones` (
  `id` int(11) NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `mensaje` text NOT NULL,
  `tipo` enum('info','success','warning','error') DEFAULT 'info',
  `usuario_id` int(11) DEFAULT NULL,
  `leida` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `notificaciones`
--

INSERT INTO `notificaciones` (`id`, `titulo`, `mensaje`, `tipo`, `usuario_id`, `leida`, `created_at`, `updated_at`) VALUES
(1, 'Bienvenido al sistema', 'Su cuenta ha sido activada correctamente', 'success', NULL, 0, '2025-09-14 06:03:45', '2025-09-14 06:03:45'),
(2, 'Mantenimiento programado', 'El sistema estará en mantenimiento el próximo domingo de 2:00 AM a 6:00 AM', 'warning', NULL, 0, '2025-09-14 06:03:45', '2025-09-14 06:03:45'),
(3, 'Nueva funcionalidad', 'Se ha agregado el módulo de reportes avanzados', 'info', NULL, 0, '2025-09-14 06:03:45', '2025-09-14 06:03:45');

-- --------------------------------------------------------

--
-- Table structure for table `notificaciones_lecturas`
--

CREATE TABLE `notificaciones_lecturas` (
  `id` int(11) NOT NULL,
  `notificacion_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `leida_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `notificaciones_lecturas`
--

INSERT INTO `notificaciones_lecturas` (`id`, `notificacion_id`, `usuario_id`, `leida_at`) VALUES
(1, 1, 1, '2025-09-14 06:09:28'),
(2, 2, 1, '2025-09-14 06:09:28'),
(3, 3, 1, '2025-09-14 06:09:28');

-- --------------------------------------------------------

--
-- Table structure for table `reportes_pdf`
--

CREATE TABLE `reportes_pdf` (
  `id` int(11) NOT NULL,
  `trabajador_id` int(11) NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `nombre_archivo` varchar(255) NOT NULL,
  `ruta_archivo` varchar(500) NOT NULL,
  `tamano_archivo` int(11) NOT NULL,
  `fecha_envio` timestamp NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `reportes_pdf`
--

INSERT INTO `reportes_pdf` (`id`, `trabajador_id`, `titulo`, `descripcion`, `nombre_archivo`, `ruta_archivo`, `tamano_archivo`, `fecha_envio`) VALUES
(1, 7, 'asdasd', 'asd', 'Punto 1 — Planificación estratégica.pdf', '/home/vol19_1/infinityfree.com/if0_39532356/htdocs/uploads/reports/report_7_2025-09-14_05-23-28_68c6899041d12.pdf', 84981, '2025-09-14 09:23:28'),
(2, 7, 'asdasd', 'asd', 'Punto 1 — Planificación estratégica.pdf', '/home/vol19_1/infinityfree.com/if0_39532356/htdocs/uploads/reports/report_7_2025-09-14_05-25-00_68c689ec7bc2c.pdf', 84981, '2025-09-14 09:25:00'),
(3, 7, 'asd', '352', 'Punto 1 — Planificación estratégica.pdf', '/home/vol19_1/infinityfree.com/if0_39532356/htdocs/uploads/reports/report_7_2025-09-14_05-28-52_68c68ad4ed5f9.pdf', 84981, '2025-09-14 09:28:52');

-- --------------------------------------------------------

--
-- Table structure for table `tiempo_trabajado`
--

CREATE TABLE `tiempo_trabajado` (
  `id` int(11) NOT NULL,
  `trabajador_id` int(11) NOT NULL,
  `descripcion` text NOT NULL,
  `horas` decimal(5,2) NOT NULL,
  `fecha_inicio` datetime NOT NULL,
  `fecha_fin` datetime DEFAULT NULL,
  `estado` enum('pendiente','aprobado','rechazado') DEFAULT 'pendiente',
  `fecha_creacion` timestamp NULL DEFAULT current_timestamp(),
  `fecha_aprobacion` timestamp NULL DEFAULT NULL,
  `fecha_rechazo` timestamp NULL DEFAULT NULL,
  `aprobado_por` int(11) DEFAULT NULL,
  `rechazado_por` int(11) DEFAULT NULL,
  `razon_rechazo` text DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tiempo_trabajado`
--

INSERT INTO `tiempo_trabajado` (`id`, `trabajador_id`, `descripcion`, `horas`, `fecha_inicio`, `fecha_fin`, `estado`, `fecha_creacion`, `fecha_aprobacion`, `fecha_rechazo`, `aprobado_por`, `rechazado_por`, `razon_rechazo`) VALUES
(1, 7, 'muchas cosas xdasds', '3.00', '2025-09-14 08:00:00', '2025-09-14 11:00:00', 'pendiente', '2025-09-14 09:23:14', NULL, NULL, NULL, NULL, NULL),
(2, 7, 'muchas cosas w', '1.00', '2025-09-14 13:22:00', '2025-09-14 14:22:00', 'pendiente', '2025-09-14 09:25:33', NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(20) NOT NULL DEFAULT 'user',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `last_login` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `rol` enum('admin','trabajador','usuario') DEFAULT 'usuario',
  `activo` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `usuarios`
--

INSERT INTO `usuarios` (`id`, `username`, `email`, `password`, `role`, `created_at`, `last_login`, `is_active`, `rol`, `activo`) VALUES
(1, 'Admin', 'admin@correo.com', '$2y$10$lYFd.ULelN0ybgUaNHdhh.dkAGqDhX7LtgtgDvs4uM1E2MnM0DmP6', 'admin', '2025-07-25 17:09:17', '2025-07-25 17:22:01', 1, 'usuario', 1);

-- --------------------------------------------------------

--
-- Table structure for table `visitantes`
--

CREATE TABLE `visitantes` (
  `id` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `fecha_registro` datetime NOT NULL,
  `estado_aprobacion` enum('pendiente','aprobado','rechazado') DEFAULT 'pendiente',
  `fecha_aprobacion` datetime DEFAULT NULL,
  `aprobado_por` int(11) DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `ultimo_acceso` datetime DEFAULT NULL,
  `token_reset` varchar(255) DEFAULT NULL,
  `token_reset_expira` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `visitantes`
--

INSERT INTO `visitantes` (`id`, `nombre`, `email`, `password`, `fecha_registro`, `estado_aprobacion`, `fecha_aprobacion`, `aprobado_por`, `activo`, `ultimo_acceso`, `token_reset`, `token_reset_expira`, `created_at`, `updated_at`) VALUES
(1, 'aaa aaa', 'asiofj2@goku.com', '$2y$10$6NPt.rllGihQcPfjqp94heuy8ajU8nDPHmVE7iYBIc5/x9j39AGvG', '2025-09-12 22:02:08', 'aprobado', '2025-09-12 20:02:24', 1, 1, NULL, NULL, NULL, '2025-09-13 02:02:07', '2025-09-13 03:02:24'),
(2, 'goku', 'son.goku@goku.com', '$2y$10$Sk2NmI0fFUQAe.8D7Xl4rOZiGOIxaR5Xu/OkhNeck8MxBacdTo8Hm', '2025-09-13 12:26:06', 'rechazado', '2025-09-13 09:28:06', 1, 0, NULL, NULL, NULL, '2025-09-13 16:26:06', '2025-09-13 16:28:06'),
(3, 'Mario Bros', 'asiof3j2@goku.com', '$2y$10$GtQ26BNXHNzh25BG.erDV.HiW/TFnIZ/PNGWPg4v1lS20EeaCwqSK', '2025-09-13 13:43:57', 'aprobado', '2025-09-13 10:44:09', 1, 1, NULL, NULL, NULL, '2025-09-13 17:43:57', '2025-09-13 17:44:09'),
(4, 'Santiago Martinez Martinez Martinez', 'martin.martinez@martin.com', '$2y$10$3itGCQ9N2bVQs/Hze2T14uu0BlgTPcEhwFJcYckrHIhV3gjfY/KKy', '2025-09-13 17:53:33', 'rechazado', '2025-09-13 14:55:08', 1, 0, NULL, NULL, NULL, '2025-09-13 21:53:33', '2025-09-13 21:55:08'),
(5, 'Balatro Balatrez', 'pacuando.balatrito@gmail.com', '$2y$10$EFAgE8cd3kdU.3Crxw/.q.9zqztEvt5Svxgxr9WkPvdamnOh44kom', '2025-09-13 17:56:00', 'aprobado', '2025-09-13 14:56:52', 1, 1, NULL, NULL, NULL, '2025-09-13 21:56:00', '2025-09-13 21:56:52'),
(6, 'Sebitas Martines', 'gabrielcome.trabas@gmail.com', '$2y$10$hWmaPAHxwV5czbJhFHWI7.fqFj6vLqsTEp.Yz6V3vTQKYRYQmUp76', '2025-09-13 17:57:53', 'rechazado', '2025-09-13 14:59:42', 1, 0, NULL, NULL, NULL, '2025-09-13 21:57:53', '2025-09-13 21:59:42'),
(7, 'Roman Romanez', 'romanez777@gmail.com', '$2y$10$wNyoYQ9fcNfJU3L1J2xaK.xSZIYBS0S/PClxQ7Fgjz./.f9m.gmrW', '2025-09-14 02:01:20', 'aprobado', '2025-09-13 23:01:41', 1, 1, '2025-09-14 02:29:08', NULL, NULL, '2025-09-14 06:01:20', '2025-09-14 09:29:08');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_logs`
--
ALTER TABLE `admin_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_admin_id` (`admin_id`),
  ADD KEY `idx_target` (`target_table`,`target_id`),
  ADD KEY `idx_fecha` (`fecha`);

--
-- Indexes for table `logs_admin`
--
ALTER TABLE `logs_admin`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_admin_fecha` (`admin_id`,`fecha`),
  ADD KEY `idx_accion` (`accion`),
  ADD KEY `idx_fecha` (`fecha`);

--
-- Indexes for table `notificaciones`
--
ALTER TABLE `notificaciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_usuario_leida` (`usuario_id`,`leida`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `notificaciones_lecturas`
--
ALTER TABLE `notificaciones_lecturas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_notification` (`notificacion_id`,`usuario_id`);

--
-- Indexes for table `reportes_pdf`
--
ALTER TABLE `reportes_pdf`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tiempo_trabajado`
--
ALTER TABLE `tiempo_trabajado`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `visitantes`
--
ALTER TABLE `visitantes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_estado_aprobacion` (`estado_aprobacion`),
  ADD KEY `idx_fecha_registro` (`fecha_registro`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_logs`
--
ALTER TABLE `admin_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `logs_admin`
--
ALTER TABLE `logs_admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notificaciones`
--
ALTER TABLE `notificaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `notificaciones_lecturas`
--
ALTER TABLE `notificaciones_lecturas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `reportes_pdf`
--
ALTER TABLE `reportes_pdf`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `tiempo_trabajado`
--
ALTER TABLE `tiempo_trabajado`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `visitantes`
--
ALTER TABLE `visitantes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
