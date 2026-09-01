-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Versión del servidor:         8.4.3 - MySQL Community Server - GPL
-- SO del servidor:              Win64
-- HeidiSQL Versión:             12.8.0.6908
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Volcando estructura de base de datos para u553580668_sigejmm4
CREATE DATABASE IF NOT EXISTS `u553580668_sigejmm4` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `u553580668_sigejmm4`;

-- Volcando estructura para tabla sigejmm.bonos_evaluacion
CREATE TABLE IF NOT EXISTS `bonos_evaluacion` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `empleado_id` bigint unsigned NOT NULL,
  `anio` int NOT NULL,
  `cuatrimestre` int NOT NULL,
  `calificacion` decimal(5,2) NOT NULL,
  `dias_otorgados` int NOT NULL,
  `dias_usados` int NOT NULL DEFAULT '0',
  `fecha_expiracion` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `bonos_evaluacion_empleado_id_foreign` (`empleado_id`),
  CONSTRAINT `bonos_evaluacion_empleado_id_foreign` FOREIGN KEY (`empleado_id`) REFERENCES `empleados` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla sigejmm.cache
CREATE TABLE IF NOT EXISTS `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla sigejmm.cache_locks
CREATE TABLE IF NOT EXISTS `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla sigejmm.capitulos
CREATE TABLE IF NOT EXISTS `capitulos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `codigo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Código del capítulo (ej: 2000)',
  `nombre` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci COMMENT 'Descripción detallada',
  `activo` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Capítulo activo o inactivo',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `capitulos_codigo_unique` (`codigo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla sigejmm.captures
CREATE TABLE IF NOT EXISTS `captures` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `date` date NOT NULL,
  `year` int NOT NULL,
  `community_id` bigint unsigned NOT NULL,
  `firefighter_id` bigint unsigned NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `commission` decimal(10,2) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `rounding_commission` decimal(10,2) NOT NULL,
  `rounding_total` decimal(10,2) NOT NULL,
  `requirement_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `requirement_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'bomberos',
  `assignment_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `captures_community_id_foreign` (`community_id`),
  KEY `captures_firefighter_id_foreign` (`firefighter_id`),
  KEY `captures_requirement_number_index` (`requirement_number`),
  KEY `idx_requirement_lookup` (`requirement_type`,`year`,`requirement_number`),
  CONSTRAINT `captures_community_id_foreign` FOREIGN KEY (`community_id`) REFERENCES `communities` (`id`),
  CONSTRAINT `captures_firefighter_id_foreign` FOREIGN KEY (`firefighter_id`) REFERENCES `firefighters` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla sigejmm.communities
CREATE TABLE IF NOT EXISTS `communities` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `geolocation` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla sigejmm.configuracion
CREATE TABLE IF NOT EXISTS `configuracion` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nombre_empresa` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'COMISION DE AGUA POTABLE Y ALCANTARILLADO DEL ESTADO DE QUINTANA ROO' COMMENT 'Nombre de la empresa',
  `nombre_organismo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ORGANISMO OPERADOR JOSE MARIA MORELOS' COMMENT 'Nombre del organismo operador',
  `logo` text COLLATE utf8mb4_unicode_ci COMMENT 'Ruta del logo',
  `iva` decimal(5,2) NOT NULL DEFAULT '16.00' COMMENT 'Porcentaje de IVA',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla sigejmm.detalles_solicitud
CREATE TABLE IF NOT EXISTS `detalles_solicitud` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `solicitud_id` bigint unsigned NOT NULL,
  `origen_tipo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `origen_id` bigint unsigned DEFAULT NULL,
  `dias_tomados` int NOT NULL,
  `numero_oficio` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `detalles_solicitud_solicitud_id_foreign` (`solicitud_id`),
  CONSTRAINT `detalles_solicitud_solicitud_id_foreign` FOREIGN KEY (`solicitud_id`) REFERENCES `solicitudes_vacaciones` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla sigejmm.empleados
CREATE TABLE IF NOT EXISTS `empleados` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `clave` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Clave única del empleado',
  `nombre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Nombre completo del empleado',
  `puesto` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Puesto del empleado',
  `departamento` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Departamento al que pertenece',
  `rfc` varchar(13) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'RFC del empleado',
  `categoria` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Categoría del empleado',
  `fecha_alta` date DEFAULT NULL COMMENT 'Fecha de alta',
  `fecha_nacimiento` date DEFAULT NULL COMMENT 'Fecha de nacimiento para cálculo de onomástico',
  `nivel` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `salario_diario` decimal(10,2) DEFAULT NULL COMMENT 'Salario diario',
  `salario_mensual` decimal(10,2) DEFAULT NULL COMMENT 'Salario mensual',
  `curp` varchar(18) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'CURP del empleado',
  `nss` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `afiliacion` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'e.g. ISSSTE, IMSS',
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Correo electrónico',
  `telefono` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Teléfono de contacto',
  `numero_empleado` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Número de empleado',
  `fotografia` text COLLATE utf8mb4_unicode_ci COMMENT 'Ruta de la fotografía',
  `fecha_baja` date DEFAULT NULL COMMENT 'Fecha de baja (si aplica)',
  `activo` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Empleado activo o inactivo',
  `es_sindicalizado` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Indica si el empleado pertenece al sindicato',
  `es_gerente` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `empleados_clave_unique` (`clave`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla sigejmm.failed_jobs
CREATE TABLE IF NOT EXISTS `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla sigejmm.firefighters
CREATE TABLE IF NOT EXISTS `firefighters` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `community_id` bigint unsigned NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `contact_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `credential_photo_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `geolocation` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `previous_firefighter` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `change_date` date DEFAULT NULL,
  `max_rounding_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `firefighters_community_id_foreign` (`community_id`),
  CONSTRAINT `firefighters_community_id_foreign` FOREIGN KEY (`community_id`) REFERENCES `communities` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla sigejmm.firefighter_settings
CREATE TABLE IF NOT EXISTS `firefighter_settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `firefighter_settings_key_unique` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla sigejmm.invoices
CREATE TABLE IF NOT EXISTS `invoices` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `rfc_emisor` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre_emisor` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reg_emis` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rfc_receptor` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre_receptor` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reg_recep` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tipo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `numero_factura` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha` date NOT NULL,
  `subtotal` decimal(15,2) NOT NULL DEFAULT '0.00',
  `ieps` decimal(15,2) NOT NULL DEFAULT '0.00',
  `descuento` decimal(15,2) NOT NULL DEFAULT '0.00',
  `base_16` decimal(15,2) NOT NULL DEFAULT '0.00',
  `base_8` decimal(15,2) NOT NULL DEFAULT '0.00',
  `base_0` decimal(15,2) NOT NULL DEFAULT '0.00',
  `iva_16` decimal(15,2) NOT NULL DEFAULT '0.00',
  `iva_8` decimal(15,2) NOT NULL DEFAULT '0.00',
  `isr_ret` decimal(15,2) NOT NULL DEFAULT '0.00',
  `iva_ret` decimal(15,2) NOT NULL DEFAULT '0.00',
  `total` decimal(15,2) NOT NULL DEFAULT '0.00',
  `uso` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `forma_pago` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `metodo_pago` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `oi` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `concepto` text COLLATE utf8mb4_unicode_ci,
  `uuid_relacionado` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tiporel` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `f_pago` date DEFAULT NULL,
  `num_op` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cta_ordenante` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cta_beneficiario` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `parc` int DEFAULT NULL,
  `s_anterior` decimal(15,2) NOT NULL DEFAULT '0.00',
  `imp_pagado` decimal(15,2) NOT NULL DEFAULT '0.00',
  `saldo_insoluto` decimal(15,2) NOT NULL DEFAULT '0.00',
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'PPD',
  `is_used` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `invoices_uuid_unique` (`uuid`)
) ENGINE=InnoDB AUTO_INCREMENT=638 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla sigejmm.jobs
CREATE TABLE IF NOT EXISTS `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla sigejmm.job_batches
CREATE TABLE IF NOT EXISTS `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla sigejmm.leyendas
CREATE TABLE IF NOT EXISTS `leyendas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned DEFAULT NULL,
  `anio` year NOT NULL COMMENT 'Año de la leyenda',
  `texto` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Texto de la leyenda',
  `activa` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Leyenda activa para usar por defecto',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `leyendas_anio_unique` (`anio`),
  KEY `leyendas_user_id_foreign` (`user_id`),
  CONSTRAINT `leyendas_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla sigejmm.materials
CREATE TABLE IF NOT EXISTS `materials` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `articulo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cantidad` int NOT NULL DEFAULT '1',
  `unidad_medida_id` bigint unsigned DEFAULT NULL,
  `es_default` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `materials_unidad_medida_id_foreign` (`unidad_medida_id`),
  CONSTRAINT `materials_unidad_medida_id_foreign` FOREIGN KEY (`unidad_medida_id`) REFERENCES `unidad_medidas` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla sigejmm.migrations
CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=58 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla sigejmm.model_has_permissions
CREATE TABLE IF NOT EXISTS `model_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla sigejmm.model_has_roles
CREATE TABLE IF NOT EXISTS `model_has_roles` (
  `role_id` bigint unsigned NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla sigejmm.partidas
CREATE TABLE IF NOT EXISTS `partidas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `capitulo_id` bigint unsigned NOT NULL,
  `subcapitulo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `partida_generica` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `codigo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Código de la partida (ej: 29,601)',
  `nombre` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci COMMENT 'Descripción detallada',
  `activo` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Partida activa o inactiva',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `partidas_codigo_unique` (`codigo`),
  KEY `partidas_capitulo_id_codigo_index` (`capitulo_id`,`codigo`),
  CONSTRAINT `partidas_capitulo_id_foreign` FOREIGN KEY (`capitulo_id`) REFERENCES `capitulos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla sigejmm.password_reset_tokens
CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla sigejmm.periodos_vacacionales
CREATE TABLE IF NOT EXISTS `periodos_vacacionales` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `empleado_id` bigint unsigned NOT NULL,
  `anio` int NOT NULL,
  `numero_periodo` int NOT NULL,
  `fecha_inicio` date DEFAULT NULL,
  `fecha_fin` date DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `periodos_vacacionales_empleado_id_anio_numero_periodo_unique` (`empleado_id`,`anio`,`numero_periodo`),
  CONSTRAINT `periodos_vacacionales_empleado_id_foreign` FOREIGN KEY (`empleado_id`) REFERENCES `empleados` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla sigejmm.permissions
CREATE TABLE IF NOT EXISTS `permissions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla sigejmm.reporte_bitacoras
CREATE TABLE IF NOT EXISTS `reporte_bitacoras` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `empleado_id` bigint unsigned DEFAULT NULL,
  `fecha_reporte` date NOT NULL,
  `destinatario_nombre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `destinatario_cargo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `solicitante_nombre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `solicitante_cargo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `solicitante_departamento` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `materiales` json NOT NULL,
  `datos_completos` json NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `reporte_bitacoras_fecha_reporte_index` (`fecha_reporte`),
  KEY `reporte_bitacoras_user_id_index` (`user_id`),
  KEY `reporte_bitacoras_empleado_id_index` (`empleado_id`),
  CONSTRAINT `reporte_bitacoras_empleado_id_foreign` FOREIGN KEY (`empleado_id`) REFERENCES `empleados` (`id`) ON DELETE SET NULL,
  CONSTRAINT `reporte_bitacoras_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla sigejmm.roles
CREATE TABLE IF NOT EXISTS `roles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla sigejmm.role_has_permissions
CREATE TABLE IF NOT EXISTS `role_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `role_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`role_id`),
  KEY `role_has_permissions_role_id_foreign` (`role_id`),
  CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla sigejmm.saldos_vacaciones
CREATE TABLE IF NOT EXISTS `saldos_vacaciones` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `periodo_vacacional_id` bigint unsigned NOT NULL,
  `tipo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_dias` int NOT NULL,
  `dias_usados` int NOT NULL DEFAULT '0',
  `dias_pendientes` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `saldos_vacaciones_periodo_vacacional_id_foreign` (`periodo_vacacional_id`),
  CONSTRAINT `saldos_vacaciones_periodo_vacacional_id_foreign` FOREIGN KEY (`periodo_vacacional_id`) REFERENCES `periodos_vacacionales` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla sigejmm.sessions
CREATE TABLE IF NOT EXISTS `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla sigejmm.settings
CREATE TABLE IF NOT EXISTS `settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` text COLLATE utf8mb4_unicode_ci,
  `group` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `settings_key_unique` (`key`),
  KEY `settings_group_index` (`group`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla sigejmm.solicitudes_vacaciones
CREATE TABLE IF NOT EXISTS `solicitudes_vacaciones` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `empleado_id` bigint unsigned NOT NULL,
  `tipo_solicitud` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'VACACION',
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL,
  `dias_solicitados` int NOT NULL,
  `motivo` text COLLATE utf8mb4_unicode_ci,
  `estado` enum('PENDIENTE','APROBADA','RECHAZADA','CANCELADA') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'PENDIENTE',
  `aprobado_por` bigint unsigned DEFAULT NULL,
  `fecha_aprobacion` datetime DEFAULT NULL,
  `comentarios_rechazo` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `solicitudes_vacaciones_empleado_id_foreign` (`empleado_id`),
  KEY `solicitudes_vacaciones_aprobado_por_foreign` (`aprobado_por`),
  CONSTRAINT `solicitudes_vacaciones_aprobado_por_foreign` FOREIGN KEY (`aprobado_por`) REFERENCES `users` (`id`),
  CONSTRAINT `solicitudes_vacaciones_empleado_id_foreign` FOREIGN KEY (`empleado_id`) REFERENCES `empleados` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla sigejmm.unidad_medidas
CREATE TABLE IF NOT EXISTS `unidad_medidas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unidad_medidas_nombre_unique` (`nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla sigejmm.users
CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `empleado_id` bigint unsigned DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `username` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  UNIQUE KEY `users_username_unique` (`username`),
  KEY `users_empleado_id_foreign` (`empleado_id`),
  CONSTRAINT `users_empleado_id_foreign` FOREIGN KEY (`empleado_id`) REFERENCES `empleados` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla sigejmm.user_material_defaults
CREATE TABLE IF NOT EXISTS `user_material_defaults` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `material_id` bigint unsigned NOT NULL,
  `cantidad` decimal(10,2) NOT NULL DEFAULT '1.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_material_defaults_user_id_material_id_unique` (`user_id`,`material_id`),
  KEY `user_material_defaults_material_id_foreign` (`material_id`),
  CONSTRAINT `user_material_defaults_material_id_foreign` FOREIGN KEY (`material_id`) REFERENCES `materials` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_material_defaults_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
