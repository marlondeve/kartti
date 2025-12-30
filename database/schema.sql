-- Crear base de datos si no existe
CREATE DATABASE IF NOT EXISTS `u990790165_Digitalmenus` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE `u990790165_Digitalmenus`;

-- Importar tablas en orden
source users.sql;
source restaurantes.sql;
source categorias.sql;
source productos.sql;
source mesas.sql;
source alertas.sql; 