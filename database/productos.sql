-- Tabla de productos (reemplaza a la antigua tabla 'platos')
-- Este archivo es principalmente para referencia, la tabla se crea desde update_schema.php
CREATE TABLE IF NOT EXISTS `productos` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `nombre` text NOT NULL,
  `descripcion` text DEFAULT NULL,
  `precio` decimal(10) NOT NULL,
  `imagen` varchar(255) DEFAULT NULL,
  `categoria_id` bigint(20) NOT NULL,
  `posicion` int(11) NOT NULL DEFAULT 0,
  `estado` varchar(45) DEFAULT 'activo',
  PRIMARY KEY (`id`),
  KEY `categoria_id` (`categoria_id`),
  CONSTRAINT `productos_ibfk_1` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; 