-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 14-09-2026 a las 00:21:55
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `mesas_disponibles`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `restaurants`
--

CREATE TABLE `restaurants` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `is_open` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `restaurants`
--

INSERT INTO `restaurants` (`id`, `user_id`, `name`, `address`, `phone`, `description`, `is_open`, `created_at`, `updated_at`) VALUES
(1, 1, 'Parrilla El Fogón', 'Av. Belgrano 123, Crespo, Entre Ríos', '343-555-1234', 'Especialidad en asado a la leña y comidas caseras.', 1, '2026-09-11 13:33:56', '2026-09-11 13:33:56'),
(2, 1, 'Fritz', 'San Martín 450, Crespo', '343-555-1111', 'Comida rápida y lomos artesanales.', 1, '2026-09-11 14:11:17', '2026-09-11 14:11:17'),
(3, 1, 'Andale', '9 de Julio 890, Crespo', '343-555-2222', 'Auténtica comida mexicana y tragos.', 1, '2026-09-11 14:11:17', '2026-09-11 14:11:17'),
(4, 1, 'Hans', 'Av. Independencia 300, Crespo', '343-555-3333', 'Cervecería y platos alemanes tradicionales.', 0, '2026-09-11 14:11:17', '2026-09-11 14:11:17');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tables`
--

CREATE TABLE `tables` (
  `id` int(11) NOT NULL,
  `restaurant_id` int(11) NOT NULL,
  `table_number` int(11) NOT NULL,
  `details` varchar(100) NOT NULL,
  `chairs` int(11) NOT NULL,
  `status_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tables`
--

INSERT INTO `tables` (`id`, `restaurant_id`, `table_number`, `details`, `chairs`, `status_id`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'Ventanal', 4, 1, '2026-09-11 13:33:56', '2026-09-11 13:58:12'),
(2, 1, 2, 'Centro', 2, 2, '2026-09-11 13:33:56', '2026-09-11 13:58:12'),
(3, 1, 3, 'Patio Trasero', 6, 1, '2026-09-11 13:33:56', '2026-09-11 13:58:12'),
(4, 1, 4, 'VIP', 8, 3, '2026-09-11 13:33:56', '2026-09-11 13:58:12'),
(5, 1, 5, 'Barra', 2, 1, '2026-09-11 13:33:56', '2026-09-11 13:58:12'),
(6, 2, 1, 'Frente a la calle', 4, 1, '2026-09-11 14:11:17', '2026-09-11 14:11:17'),
(7, 2, 2, 'Salón central', 2, 2, '2026-09-11 14:11:17', '2026-09-11 14:11:17'),
(8, 2, 3, 'Barra principal', 1, 1, '2026-09-11 14:11:17', '2026-09-11 14:11:17'),
(9, 2, 4, 'Patio', 4, 3, '2026-09-11 14:11:17', '2026-09-11 14:11:17'),
(10, 3, 1, 'Sector Mariachi', 6, 1, '2026-09-11 14:11:17', '2026-09-11 14:11:17'),
(11, 3, 2, 'Ventana colorida', 2, 1, '2026-09-11 14:11:17', '2026-09-11 14:11:17'),
(12, 3, 3, 'Terraza', 4, 2, '2026-09-11 14:11:17', '2026-09-11 14:11:17'),
(13, 4, 1, 'Rincón cervecero', 4, 2, '2026-09-11 14:11:17', '2026-09-11 14:11:17'),
(14, 4, 2, 'Salón principal', 8, 2, '2026-09-11 14:11:17', '2026-09-11 14:11:17');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `table_statuses`
--

CREATE TABLE `table_statuses` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `table_statuses`
--

INSERT INTO `table_statuses` (`id`, `name`) VALUES
(1, 'Disponible'),
(2, 'Ocupada'),
(3, 'Reservada');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `created_at`, `updated_at`) VALUES
(1, 'Admin Prueba', 'admin@prueba.com', '$2y$10$P2N5Z/AOnRhyL2t.7jZ9c.j2hO6T6VzRzM3g8jF39c.j2hO6T6VzR', '2026-09-11 13:33:56', '2026-09-11 13:33:56');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `restaurants`
--
ALTER TABLE `restaurants`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indices de la tabla `tables`
--
ALTER TABLE `tables`
  ADD PRIMARY KEY (`id`),
  ADD KEY `restaurant_id` (`restaurant_id`),
  ADD KEY `status_id` (`status_id`);

--
-- Indices de la tabla `table_statuses`
--
ALTER TABLE `table_statuses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `restaurants`
--
ALTER TABLE `restaurants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `tables`
--
ALTER TABLE `tables`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de la tabla `table_statuses`
--
ALTER TABLE `table_statuses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `restaurants`
--
ALTER TABLE `restaurants`
  ADD CONSTRAINT `restaurants_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `tables`
--
ALTER TABLE `tables`
  ADD CONSTRAINT `tables_ibfk_1` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tables_ibfk_2` FOREIGN KEY (`status_id`) REFERENCES `table_statuses` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
