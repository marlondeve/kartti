# Digital Menus

Sistema de gestión de menús digitales para restaurantes.

## Configuración

### Configuración de Apache

El proyecto utiliza diferentes configuraciones de Apache para desarrollo y producción:

- `.htaccess`: Archivo de configuración para desarrollo local, ubicado en la raíz del proyecto
- `config/apache/.htaccess.production`: Archivo de configuración para producción con configuraciones más estrictas

Para desplegar la configuración correcta según el entorno:

```bash
php config/apache/deploy_htaccess.php
```

Este script detectará automáticamente el entorno y:
- En desarrollo: No realizará cambios, manteniendo la configuración de desarrollo
- En producción: Copiará la configuración de producción a la raíz del proyecto

### Configuración de la Base de Datos

1. Copia el archivo `config/config.example.php` a `config/config.php`
2. Actualiza las credenciales de la base de datos en `config/config.php`

## Instalación

1. Clona el repositorio
2. Configura la base de datos
3. Despliega la configuración de Apache
4. Accede a la aplicación a través del navegador

## Estructura del Proyecto

```
Digitalmenus/
├── api/              # Endpoints de la API
├── config/           # Archivos de configuración
│   └── apache/      # Configuraciones de Apache
├── database/         # Scripts de base de datos
├── includes/         # Archivos PHP reutilizables
├── logs/            # Archivos de registro
├── public/          # Archivos públicos (CSS, JS, imágenes)
├── routes/          # Definición de rutas
├── tests/           # Archivos de prueba
└── views/           # Vistas de la aplicación
```

## Pruebas

Consulta el archivo `tests/README.md` para información sobre las pruebas disponibles.