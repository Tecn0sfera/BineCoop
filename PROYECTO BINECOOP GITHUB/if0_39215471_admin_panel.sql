-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Servidor: sql211.infinityfree.com
-- Tiempo de generación: 18-11-2025 a las 19:21:36
-- Versión del servidor: 11.4.7-MariaDB
-- Versión de PHP: 7.2.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `if0_39215471_admin_panel`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `admin_logs`
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
-- Estructura de tabla para la tabla `aportes`
--

CREATE TABLE `aportes` (
  `id` int(11) NOT NULL,
  `socio_id` int(11) NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `fecha` date NOT NULL,
  `descripcion` text DEFAULT NULL,
  `comprobante_id` varchar(255) DEFAULT NULL,
  `creado_por` int(11) DEFAULT NULL,
  `creado_en` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `aportes`
--

INSERT INTO `aportes` (`id`, `socio_id`, `monto`, `fecha`, `descripcion`, `comprobante_id`, `creado_por`, `creado_en`) VALUES
(1, 11, '11.00', '2025-11-18', '', '', 1, '2025-11-18 22:04:20');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `comprobantes_pago`
--

CREATE TABLE `comprobantes_pago` (
  `id` varchar(50) NOT NULL,
  `trabajador_id` int(11) NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `nombre_archivo` varchar(255) NOT NULL,
  `ruta_archivo` varchar(500) NOT NULL,
  `tamano_archivo` int(11) NOT NULL DEFAULT 0,
  `fecha_envio` datetime NOT NULL,
  `estado` enum('pendiente','aprobado','rechazado') NOT NULL DEFAULT 'pendiente',
  `fecha_aprobacion` datetime DEFAULT NULL,
  `fecha_rechazo` datetime DEFAULT NULL,
  `aprobado_por` int(11) DEFAULT NULL,
  `rechazado_por` int(11) DEFAULT NULL,
  `razon_rechazo` text DEFAULT NULL,
  `creado_en` timestamp NULL DEFAULT current_timestamp(),
  `actualizado_en` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `comprobantes_pago`
--

INSERT INTO `comprobantes_pago` (`id`, `trabajador_id`, `titulo`, `descripcion`, `nombre_archivo`, `ruta_archivo`, `tamano_archivo`, `fecha_envio`, `estado`, `fecha_aprobacion`, `fecha_rechazo`, `aprobado_por`, `rechazado_por`, `razon_rechazo`, `creado_en`, `actualizado_en`) VALUES
('comp_691d01930f8766.48870225', 11, 'Comprobante de Prueba - 2025-11-18 18:30:27', 'Este es un comprobante de prueba generado automáticamente', 'test_comprobante.pdf', '/uploads/test/test_comprobante.pdf', 1024, '2025-11-18 18:30:27', 'rechazado', NULL, '2025-11-18 15:51:13', NULL, 1, '', '2025-11-18 23:30:27', '2025-11-18 23:51:13'),
('comp_691d0756926685.97387324', 11, 'Comprobante de Prueba - 2025-11-18 18:55:02', 'Este es un comprobante de prueba desde htdocscop', 'test_comprobante.pdf', '/uploads/test/test_comprobante.pdf', 1024, '2025-11-18 18:55:02', 'rechazado', NULL, '2025-11-18 16:10:36', NULL, 1, '', '2025-11-18 23:55:02', '2025-11-19 00:10:36'),
('comp_691d07ff8a7f53.79399376', 11, 'Comprobante de Prueba - 2025-11-18 18:57:51', 'Este es un comprobante de prueba desde htdocscop', 'test_comprobante.pdf', '/uploads/test/test_comprobante.pdf', 1024, '2025-11-18 18:57:51', 'rechazado', NULL, '2025-11-18 15:58:37', NULL, 1, '', '2025-11-18 23:57:51', '2025-11-18 23:58:37'),
('comp_691d08b0436ca1.44761883', 1, 'aaa', 'aa', 'Guia_Repaso_Matematica_Detallada.pdf', '/home/vol19_1/infinityfree.com/if0_39532356/htdocs/uploads/reports/report_1_2025-11-18_19-00-48_691d08b042dcd.pdf', 10151, '2025-11-18 19:00:48', 'pendiente', NULL, NULL, NULL, NULL, NULL, '2025-11-19 00:00:48', '2025-11-19 00:00:48'),
('comp_691d0b1b41df96.68251402', 11, 'aaaaa', 'aaaaaaa', 'asd1.pdf', '/home/vol19_1/infinityfree.com/if0_39532356/htdocs/uploads/reports/report_11_2025-11-18_19-11-07_691d0b1b40fe7.pdf', 1486192, '2025-11-18 19:11:07', 'pendiente', NULL, NULL, NULL, NULL, NULL, '2025-11-19 00:11:07', '2025-11-19 00:11:07');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `logs_admin`
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
-- Estructura de tabla para la tabla `notificaciones`
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
-- Volcado de datos para la tabla `notificaciones`
--

INSERT INTO `notificaciones` (`id`, `titulo`, `mensaje`, `tipo`, `usuario_id`, `leida`, `created_at`, `updated_at`) VALUES
(1, 'Bienvenido al sistema', 'Su cuenta ha sido activada correctamente', 'success', NULL, 0, '2025-09-14 06:03:45', '2025-09-14 06:03:45'),
(2, 'Mantenimiento programado', 'El sistema estará en mantenimiento el próximo domingo de 2:00 AM a 6:00 AM', 'warning', NULL, 0, '2025-09-14 06:03:45', '2025-09-14 06:03:45'),
(3, 'Nueva funcionalidad', 'Se ha agregado el módulo de reportes avanzados', 'info', NULL, 0, '2025-09-14 06:03:45', '2025-09-14 06:03:45');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notificaciones_lecturas`
--

CREATE TABLE `notificaciones_lecturas` (
  `id` int(11) NOT NULL,
  `notificacion_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `leida_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Volcado de datos para la tabla `notificaciones_lecturas`
--

INSERT INTO `notificaciones_lecturas` (`id`, `notificacion_id`, `usuario_id`, `leida_at`) VALUES
(1, 1, 1, '2025-09-14 06:09:28'),
(2, 2, 1, '2025-09-14 06:09:28'),
(3, 3, 1, '2025-09-14 06:09:28');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reportes_pdf`
--

CREATE TABLE `reportes_pdf` (
  `id` int(11) NOT NULL,
  `trabajador_id` int(11) NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `nombre_archivo` varchar(255) NOT NULL,
  `ruta_archivo` varchar(500) NOT NULL,
  `tamano_archivo` int(11) NOT NULL,
  `contenido_pdf` longblob DEFAULT NULL,
  `fecha_envio` timestamp NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Volcado de datos para la tabla `reportes_pdf`
--

INSERT INTO `reportes_pdf` (`id`, `trabajador_id`, `titulo`, `descripcion`, `nombre_archivo`, `ruta_archivo`, `tamano_archivo`, `contenido_pdf`, `fecha_envio`) VALUES
(1, 7, 'asdasd', 'asd', 'Punto 1 — Planificación estratégica.pdf', '/home/vol19_1/infinityfree.com/if0_39532356/htdocs/uploads/reports/report_7_2025-09-14_05-23-28_68c6899041d12.pdf', 84981, NULL, '2025-09-14 09:23:28'),
(2, 7, 'asdasd', 'asd', 'Punto 1 — Planificación estratégica.pdf', '/home/vol19_1/infinityfree.com/if0_39532356/htdocs/uploads/reports/report_7_2025-09-14_05-25-00_68c689ec7bc2c.pdf', 84981, NULL, '2025-09-14 09:25:00'),
(3, 7, 'asd', '352', 'Punto 1 — Planificación estratégica.pdf', '/home/vol19_1/infinityfree.com/if0_39532356/htdocs/uploads/reports/report_7_2025-09-14_05-28-52_68c68ad4ed5f9.pdf', 84981, NULL, '2025-09-14 09:28:52'),
(4, 9, 'fdag', 'fdsag', 'Documento sin título.pdf', '/home/vol19_1/infinityfree.com/if0_39532356/htdocs/uploads/reports/report_9_2025-10-09_08-59-22_68e7b1aa354e0.pdf', 90662, NULL, '2025-10-09 12:59:22'),
(5, 11, 'hola', 'hola', 'Guía de Repaso - Matemática CTS y Cálculo-1 (3).PDF', '/home/vol19_1/infinityfree.com/if0_39532356/htdocs/uploads/reports/report_11_2025-11-18_14-41-48_691ccbfce7f7c.pdf', 597007, NULL, '2025-11-18 19:41:48'),
(6, 11, 's', 's', 'Guia_Repaso_Matematic (gráficos).pdf', '/home/vol19_1/infinityfree.com/if0_39532356/htdocs/uploads/reports/report_11_2025-11-18_16-52-15_691cea8fceb67.pdf', 131251, NULL, '2025-11-18 21:52:15'),
(7, 11, 're', 're', 'Guía de Repaso - Matemática CTS y Cálculo-1 (3).PDF', '/home/vol19_1/infinityfree.com/if0_39532356/htdocs/uploads/reports/report_11_2025-11-18_17-14-08_691cefb070172.pdf', 597007, NULL, '2025-11-18 22:14:08'),
(8, 11, 'asd', 'asd', 'asd1.pdf', '/home/vol19_1/infinityfree.com/if0_39532356/htdocs/uploads/reports/report_11_2025-11-18_17-36-17_691cf4e167506.pdf', 1486192, NULL, '2025-11-18 22:36:17'),
(9, 11, 'asd', 'asd', 'Guia_Repaso_Matematica_Detallada.pdf', '/home/vol19_1/infinityfree.com/if0_39532356/htdocs/uploads/reports/report_11_2025-11-18_18-05-45_691cfbc9aa070.pdf', 10151, NULL, '2025-11-18 23:05:46'),
(10, 11, 'asdasd', 'asdasdasd', 'Guía de Repaso - Matemática CTS y Cálculo-1 (3).PDF', '/home/vol19_1/infinityfree.com/if0_39532356/htdocs/uploads/reports/report_11_2025-11-18_18-21-04_691cff601166c.pdf', 597007, NULL, '2025-11-18 23:21:04'),
(11, 11, 'aaa', 'aa', 'asd1.pdf', '/home/vol19_1/infinityfree.com/if0_39532356/htdocs/uploads/reports/report_11_2025-11-18_18-24-38_691d0036592ad.pdf', 1486192, NULL, '2025-11-18 23:24:38'),
(12, 11, 'aaa', 'aaa', 'Guía de Repaso - Matemática CTS y Cálculo-1 (3).PDF', '/home/vol19_1/infinityfree.com/if0_39532356/htdocs/uploads/reports/report_11_2025-11-18_18-26-03_691d008bafe3e.pdf', 597007, NULL, '2025-11-18 23:26:03'),
(13, 11, 'asdasd', 'asdasd', 'Guia_Repaso_Matematica_Detallada.pdf', '/home/vol19_1/infinityfree.com/if0_39532356/htdocs/uploads/reports/report_11_2025-11-18_18-31-01_691d01b536168.pdf', 10151, NULL, '2025-11-18 23:31:01'),
(14, 11, 'asdasdas', 'asdasd', 'Guía de Repaso - Matemática CTS y Cálculo-1 (3).PDF', '/home/vol19_1/infinityfree.com/if0_39532356/htdocs/uploads/reports/report_11_2025-11-18_18-31-58_691d01ee40bb6.pdf', 597007, NULL, '2025-11-18 23:31:58'),
(15, 11, 'aa', 'aaaa', 'asd1.pdf', '/home/vol19_1/infinityfree.com/if0_39532356/htdocs/uploads/reports/report_11_2025-11-18_18-34-59_691d02a3accde.pdf', 1486192, NULL, '2025-11-18 23:34:59'),
(16, 11, 'aa', 'aa', 'asd1.pdf', '/home/vol19_1/infinityfree.com/if0_39532356/htdocs/uploads/reports/report_11_2025-11-18_18-43-41_691d04ad882c7.pdf', 1486192, NULL, '2025-11-18 23:43:41'),
(17, 11, 'aaa', 'aaaaa', 'asd1.pdf', '/home/vol19_1/infinityfree.com/if0_39532356/htdocs/uploads/reports/report_11_2025-11-18_18-47-48_691d05a4a0a36.pdf', 1486192, NULL, '2025-11-18 23:47:48'),
(18, 11, '56456', '56456', 'asd1.pdf', '/home/vol19_1/infinityfree.com/if0_39532356/htdocs/uploads/reports/report_11_2025-11-18_18-53-03_691d06dfdfdc1.pdf', 1486192, NULL, '2025-11-18 23:53:03'),
(19, 1, 'meptor', 'neptor', 'asd1.pdf', '/home/vol19_1/infinityfree.com/if0_39532356/htdocs/uploads/reports/report_1_2025-11-18_18-57-40_691d07f478d9e.pdf', 1486192, NULL, '2025-11-18 23:57:40'),
(20, 1, 'aaaaa', 'aaaaaaaaa', 'Guia_Repaso_Matematica_Detallada.pdf', '/home/vol19_1/infinityfree.com/if0_39532356/htdocs/uploads/reports/report_1_2025-11-18_18-58-50_691d083a1d43f.pdf', 10151, NULL, '2025-11-18 23:58:50');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `socios`
--

CREATE TABLE `socios` (
  `id` int(11) NOT NULL,
  `visitante_id` int(11) NOT NULL,
  `apellido` varchar(100) DEFAULT NULL,
  `ci` varchar(20) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `fecha_ingreso` date DEFAULT NULL,
  `vivienda_id` int(11) DEFAULT NULL,
  `tipo_asignacion` enum('casa','departamento') DEFAULT NULL,
  `creado_en` timestamp NULL DEFAULT current_timestamp(),
  `actualizado_en` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `socios`
--

INSERT INTO `socios` (`id`, `visitante_id`, `apellido`, `ci`, `telefono`, `fecha_ingreso`, `vivienda_id`, `tipo_asignacion`, `creado_en`, `actualizado_en`) VALUES
(1, 11, NULL, NULL, NULL, '2025-11-18', NULL, 'departamento', '2025-11-18 23:06:30', '2025-11-18 23:06:30');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tiempo_trabajado`
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
-- Volcado de datos para la tabla `tiempo_trabajado`
--

INSERT INTO `tiempo_trabajado` (`id`, `trabajador_id`, `descripcion`, `horas`, `fecha_inicio`, `fecha_fin`, `estado`, `fecha_creacion`, `fecha_aprobacion`, `fecha_rechazo`, `aprobado_por`, `rechazado_por`, `razon_rechazo`) VALUES
(1, 7, 'muchas cosas xdasds', '3.00', '2025-09-14 08:00:00', '2025-09-14 11:00:00', 'aprobado', '2025-09-14 09:23:14', '2025-11-18 01:43:46', NULL, 1, NULL, NULL),
(2, 7, 'muchas cosas w', '1.00', '2025-09-14 13:22:00', '2025-09-14 14:22:00', 'aprobado', '2025-09-14 09:25:33', '2025-11-18 01:43:46', NULL, 1, NULL, NULL),
(3, 9, 'dsfa', '3.00', '2025-10-09 05:02:00', '2025-10-09 08:02:00', 'aprobado', '2025-10-09 12:57:49', '2025-11-18 01:43:46', NULL, 1, NULL, NULL),
(4, 11, 'seeee', '8.00', '2025-11-18 12:00:00', '2025-11-18 20:00:00', 'aprobado', '2025-11-18 19:37:10', '2025-11-18 20:26:36', NULL, 1, NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
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
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `username`, `email`, `password`, `role`, `created_at`, `last_login`, `is_active`, `rol`, `activo`) VALUES
(1, 'Admin', 'admin@correo.com', '$2y$10$lYFd.ULelN0ybgUaNHdhh.dkAGqDhX7LtgtgDvs4uM1E2MnM0DmP6', 'admin', '2025-07-25 17:09:17', '2025-07-25 17:22:01', 1, 'usuario', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `visitantes`
--

CREATE TABLE `visitantes` (
  `id` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `ci` varchar(20) DEFAULT NULL,
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
-- Volcado de datos para la tabla `visitantes`
--

INSERT INTO `visitantes` (`id`, `nombre`, `email`, `telefono`, `ci`, `password`, `fecha_registro`, `estado_aprobacion`, `fecha_aprobacion`, `aprobado_por`, `activo`, `ultimo_acceso`, `token_reset`, `token_reset_expira`, `created_at`, `updated_at`) VALUES
(1, 'aaa aaa', 'asiofj2@goku.com', NULL, NULL, '$2y$10$6NPt.rllGihQcPfjqp94heuy8ajU8nDPHmVE7iYBIc5/x9j39AGvG', '2025-09-12 22:02:08', 'rechazado', '2025-09-12 20:02:24', 1, 0, NULL, NULL, NULL, '2025-09-13 02:02:07', '2025-11-18 01:45:34'),
(2, 'goku', 'son.goku@goku.com', NULL, NULL, '$2y$10$Sk2NmI0fFUQAe.8D7Xl4rOZiGOIxaR5Xu/OkhNeck8MxBacdTo8Hm', '2025-09-13 12:26:06', 'rechazado', '2025-09-13 09:28:06', 1, 0, NULL, NULL, NULL, '2025-09-13 16:26:06', '2025-09-13 16:28:06'),
(3, 'Mario Bros', 'asiof3j2@goku.com', NULL, NULL, '$2y$10$GtQ26BNXHNzh25BG.erDV.HiW/TFnIZ/PNGWPg4v1lS20EeaCwqSK', '2025-09-13 13:43:57', 'rechazado', '2025-09-13 10:44:09', 1, 0, NULL, NULL, NULL, '2025-09-13 17:43:57', '2025-11-18 01:08:04'),
(4, 'Santiago Martinez Martinez Martinez', 'martin.martinez@martin.com', NULL, NULL, '$2y$10$3itGCQ9N2bVQs/Hze2T14uu0BlgTPcEhwFJcYckrHIhV3gjfY/KKy', '2025-09-13 17:53:33', 'rechazado', '2025-09-13 14:55:08', 1, 0, NULL, NULL, NULL, '2025-09-13 21:53:33', '2025-09-13 21:55:08'),
(5, 'Balatro Balatrez', 'pacuando.balatrito@gmail.com', NULL, NULL, '$2y$10$EFAgE8cd3kdU.3Crxw/.q.9zqztEvt5Svxgxr9WkPvdamnOh44kom', '2025-09-13 17:56:00', 'rechazado', '2025-09-13 14:56:52', 1, 0, NULL, NULL, NULL, '2025-09-13 21:56:00', '2025-11-18 01:07:53'),
(6, 'Sebitas Martines', 'gabrielcome.trabas@gmail.com', NULL, NULL, '$2y$10$hWmaPAHxwV5czbJhFHWI7.fqFj6vLqsTEp.Yz6V3vTQKYRYQmUp76', '2025-09-13 17:57:53', 'rechazado', '2025-09-13 14:59:42', 1, 0, NULL, NULL, NULL, '2025-09-13 21:57:53', '2025-09-13 21:59:42'),
(7, 'Roman Romanez', 'romanez777@gmail.com', NULL, NULL, '$2y$10$wNyoYQ9fcNfJU3L1J2xaK.xSZIYBS0S/PClxQ7Fgjz./.f9m.gmrW', '2025-09-14 02:01:20', 'rechazado', '2025-09-13 23:01:41', 1, 0, '2025-09-14 02:29:08', NULL, NULL, '2025-09-14 06:01:20', '2025-11-18 01:07:56'),
(8, 'Nose', 'hsjsjsjsjsjs@gmail.com', NULL, NULL, '$2y$10$SvQ2myfCnkMCWAIg1Gwsk.KLRfQjAxtZn.dv8ie8d31AoB6r5USi.', '2025-09-19 18:54:55', 'rechazado', '2025-10-09 05:57:03', 1, 0, NULL, NULL, NULL, '2025-09-19 22:54:55', '2025-10-09 12:57:03'),
(9, 'Manuel Loquendero', 'manuelorsi@gaymail.com', NULL, NULL, '$2y$10$Lm7LsXWHmIkVnm5heKWO5.SkvKFl4JATF1WQnzNAq1tBQqFDjmmWS', '2025-10-09 08:56:23', 'rechazado', '2025-10-09 05:57:00', 1, 0, '2025-10-09 05:57:32', NULL, NULL, '2025-10-09 12:56:23', '2025-11-18 01:07:46'),
(10, 'hola hola', 'adioshola@gmail.com', NULL, NULL, '$2y$10$hdqugi0ZvFa6.RqgGhjlgO9nghxfI1mt9fNLXEZqvggPQVjAilUjS', '2025-10-10 08:44:24', 'rechazado', '2025-10-10 05:45:03', 1, 0, '2025-10-10 05:45:20', NULL, NULL, '2025-10-10 12:44:24', '2025-11-18 01:07:44'),
(11, 'Facundo Facundez', 'facundo.facundez@gmail.com', '099123456', '12345678', '$2y$10$exFCCRPXidBDH6p4nQWb.e2RibEXhaxllo/Xl.K9LNFiRvZpK1qPq', '2025-11-17 21:19:47', 'aprobado', '2025-11-17 18:23:16', 1, 1, '2025-11-18 16:10:58', NULL, NULL, '2025-11-18 02:19:47', '2025-11-19 00:10:58'),
(12, 'Rodrigo Rodriguez', 'rodrigo.rodriguez@gmail.com', NULL, NULL, '$2y$10$MCwmk95vht7Oh3aBmvT4leAsx39cvnJnkYDBX1CrhHtpZ.6fiaKlG', '2025-11-17 21:22:24', 'aprobado', '2025-11-17 18:40:17', 1, 1, '2025-11-18 12:19:55', NULL, NULL, '2025-11-18 02:22:24', '2025-11-18 20:19:55'),
(13, 'Ramiro Ramirez', 'ramiro.ramirez22@gmail.com', NULL, NULL, '$2y$10$y6QXnmX0SlQdePo.q7mJQOvNBow7jrEHoO9nqaNDtn/eYdoPrIzbm', '2025-11-18 09:20:48', 'aprobado', '2025-11-18 12:24:54', 1, 1, NULL, NULL, NULL, '2025-11-18 14:20:48', '2025-11-18 20:24:54'),
(14, 'Joaquin', 'joaquinjoaquinez@gmail.com', NULL, NULL, '$2y$10$i7iG.jCgSvdGobIGYA0zgeFRgegznzgxnzlOPBTGB1WTJukUgEvb2', '2025-11-18 17:54:56', 'aprobado', NULL, NULL, 1, NULL, NULL, NULL, '2025-11-18 22:54:56', '2025-11-18 22:54:56');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `viviendas`
--

CREATE TABLE `viviendas` (
  `id` int(11) NOT NULL,
  `numero` varchar(50) NOT NULL,
  `bloque` varchar(50) DEFAULT NULL,
  `tipo` enum('departamento','casa') NOT NULL DEFAULT 'departamento',
  `metros_cuadrados` decimal(10,2) DEFAULT NULL,
  `estado` enum('libre','ocupada','mantenimiento') NOT NULL DEFAULT 'libre',
  `descripcion` text DEFAULT NULL,
  `creado_en` timestamp NULL DEFAULT current_timestamp(),
  `actualizado_en` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `viviendas`
--

INSERT INTO `viviendas` (`id`, `numero`, `bloque`, `tipo`, `metros_cuadrados`, `estado`, `descripcion`, `creado_en`, `actualizado_en`) VALUES
(1, '101', 'A', 'departamento', '65.50', 'ocupada', 'Departamento 2 habitaciones, 1 baño', '2025-11-18 22:35:31', '2025-11-18 22:35:31'),
(2, '102', 'A', 'departamento', '65.50', 'ocupada', 'Departamento 2 habitaciones, 1 baño', '2025-11-18 22:35:31', '2025-11-18 22:35:31'),
(3, '103', 'A', 'departamento', '75.00', 'libre', 'Departamento 3 habitaciones, 2 baños', '2025-11-18 22:35:31', '2025-11-18 22:35:31'),
(4, '104', 'A', 'departamento', '75.00', 'ocupada', 'Departamento 3 habitaciones, 2 baños', '2025-11-18 22:35:31', '2025-11-18 22:35:31'),
(5, '201', 'A', 'departamento', '65.50', 'libre', 'Departamento 2 habitaciones, 1 baño', '2025-11-18 22:35:31', '2025-11-18 22:35:31'),
(6, '202', 'A', 'departamento', '65.50', 'ocupada', 'Departamento 2 habitaciones, 1 baño', '2025-11-18 22:35:31', '2025-11-18 22:35:31'),
(7, '203', 'A', 'departamento', '85.00', 'libre', 'Departamento 3 habitaciones, 2 baños, balcón', '2025-11-18 22:35:31', '2025-11-18 22:35:31'),
(8, '204', 'A', 'departamento', '85.00', 'mantenimiento', 'Departamento 3 habitaciones, 2 baños, balcón', '2025-11-18 22:35:31', '2025-11-18 22:35:31'),
(9, '301', 'A', 'departamento', '75.00', 'ocupada', 'Departamento 3 habitaciones, 2 baños', '2025-11-18 22:35:31', '2025-11-18 22:35:31'),
(10, '302', 'A', 'departamento', '75.00', 'libre', 'Departamento 3 habitaciones, 2 baños', '2025-11-18 22:35:31', '2025-11-18 22:35:31'),
(11, '101', 'B', 'departamento', '70.00', 'ocupada', 'Departamento 2 habitaciones, 1 baño', '2025-11-18 22:35:31', '2025-11-18 22:35:31'),
(12, '102', 'B', 'departamento', '70.00', 'libre', 'Departamento 2 habitaciones, 1 baño', '2025-11-18 22:35:31', '2025-11-18 22:35:31'),
(13, '103', 'B', 'departamento', '80.00', 'ocupada', 'Departamento 3 habitaciones, 2 baños', '2025-11-18 22:35:31', '2025-11-18 22:35:31'),
(14, '201', 'B', 'departamento', '70.00', 'libre', 'Departamento 2 habitaciones, 1 baño', '2025-11-18 22:35:31', '2025-11-18 22:35:31'),
(15, '202', 'B', 'departamento', '90.00', 'ocupada', 'Departamento 3 habitaciones, 2 baños, terraza', '2025-11-18 22:35:31', '2025-11-18 22:35:31'),
(16, '301', 'B', 'departamento', '80.00', 'libre', 'Departamento 3 habitaciones, 2 baños', '2025-11-18 22:35:31', '2025-11-18 22:35:31'),
(17, 'Casa 1', NULL, 'casa', '120.00', 'ocupada', 'Casa 3 habitaciones, 2 baños, patio', '2025-11-18 22:35:31', '2025-11-18 22:35:31'),
(18, 'Casa 2', NULL, 'casa', '120.00', 'libre', 'Casa 3 habitaciones, 2 baños, patio', '2025-11-18 22:35:31', '2025-11-18 22:35:31'),
(19, 'Casa 3', NULL, 'casa', '150.00', 'ocupada', 'Casa 4 habitaciones, 3 baños, patio grande', '2025-11-18 22:35:31', '2025-11-18 22:35:31'),
(20, 'Casa 4', NULL, 'casa', '150.00', 'libre', 'Casa 4 habitaciones, 3 baños, patio grande', '2025-11-18 22:35:31', '2025-11-18 22:35:31'),
(21, 'Casa 5', NULL, 'casa', '100.00', 'ocupada', 'Casa 2 habitaciones, 1 baño, patio pequeño', '2025-11-18 22:35:31', '2025-11-18 22:35:31'),
(22, 'Casa 6', NULL, 'casa', '100.00', 'libre', 'Casa 2 habitaciones, 1 baño, patio pequeño', '2025-11-18 22:35:31', '2025-11-18 22:35:31'),
(23, 'Casa 7', NULL, 'casa', '130.00', 'ocupada', 'Casa 3 habitaciones, 2 baños, garaje', '2025-11-18 22:35:31', '2025-11-18 22:35:31'),
(24, 'Casa 8', NULL, 'casa', '130.00', 'mantenimiento', 'Casa 3 habitaciones, 2 baños, garaje', '2025-11-18 22:35:31', '2025-11-18 22:35:31'),
(25, 'Casa 9', NULL, 'casa', '140.00', 'libre', 'Casa 4 habitaciones, 2 baños, patio y garaje', '2025-11-18 22:35:31', '2025-11-18 22:35:31'),
(26, 'Casa 10', NULL, 'casa', '140.00', 'ocupada', 'Casa 4 habitaciones, 2 baños, patio y garaje', '2025-11-18 22:35:31', '2025-11-18 22:35:31');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `admin_logs`
--
ALTER TABLE `admin_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_admin_id` (`admin_id`),
  ADD KEY `idx_target` (`target_table`,`target_id`),
  ADD KEY `idx_fecha` (`fecha`);

--
-- Indices de la tabla `aportes`
--
ALTER TABLE `aportes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_socio` (`socio_id`),
  ADD KEY `idx_fecha` (`fecha`);

--
-- Indices de la tabla `comprobantes_pago`
--
ALTER TABLE `comprobantes_pago`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_trabajador` (`trabajador_id`),
  ADD KEY `idx_estado` (`estado`),
  ADD KEY `idx_fecha_envio` (`fecha_envio`);

--
-- Indices de la tabla `logs_admin`
--
ALTER TABLE `logs_admin`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_admin_fecha` (`admin_id`,`fecha`),
  ADD KEY `idx_accion` (`accion`),
  ADD KEY `idx_fecha` (`fecha`);

--
-- Indices de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_usuario_leida` (`usuario_id`,`leida`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indices de la tabla `notificaciones_lecturas`
--
ALTER TABLE `notificaciones_lecturas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_notification` (`notificacion_id`,`usuario_id`);

--
-- Indices de la tabla `reportes_pdf`
--
ALTER TABLE `reportes_pdf`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `socios`
--
ALTER TABLE `socios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_visitante_id` (`visitante_id`),
  ADD UNIQUE KEY `unique_ci` (`ci`),
  ADD KEY `idx_vivienda` (`vivienda_id`),
  ADD KEY `idx_fecha_ingreso` (`fecha_ingreso`);

--
-- Indices de la tabla `tiempo_trabajado`
--
ALTER TABLE `tiempo_trabajado`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indices de la tabla `visitantes`
--
ALTER TABLE `visitantes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_estado_aprobacion` (`estado_aprobacion`),
  ADD KEY `idx_fecha_registro` (`fecha_registro`);

--
-- Indices de la tabla `viviendas`
--
ALTER TABLE `viviendas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_numero_bloque` (`numero`,`bloque`),
  ADD KEY `idx_tipo` (`tipo`),
  ADD KEY `idx_estado` (`estado`),
  ADD KEY `idx_bloque` (`bloque`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `admin_logs`
--
ALTER TABLE `admin_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `aportes`
--
ALTER TABLE `aportes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `logs_admin`
--
ALTER TABLE `logs_admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `notificaciones_lecturas`
--
ALTER TABLE `notificaciones_lecturas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `reportes_pdf`
--
ALTER TABLE `reportes_pdf`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT de la tabla `socios`
--
ALTER TABLE `socios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `tiempo_trabajado`
--
ALTER TABLE `tiempo_trabajado`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `visitantes`
--
ALTER TABLE `visitantes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de la tabla `viviendas`
--
ALTER TABLE `viviendas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
