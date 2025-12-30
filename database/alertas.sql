-- Tabla de alertas
CREATE TABLE IF NOT EXISTS `alertas` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `estado` text NOT NULL,
  `qr_id` bigint(20) DEFAULT NULL,
  `fecha_creacion` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `qr_id` (`qr_id`),
  CONSTRAINT `alertas_ibfk_1` FOREIGN KEY (`qr_id`) REFERENCES `qr_codes` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; 