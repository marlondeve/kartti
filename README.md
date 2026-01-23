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

## Migraciones de base de datos

Hemos añadido una migración que agrega las columnas necesarias para la nueva vista de configuración (`call_waiter_enabled`, `whatsapp`) en `database/migrations/20260105_add_restaurant_settings.sql`.

Aplica la migración con tu cliente MySQL (por ejemplo, desde la terminal):

```bash
mysql -u <user> -p <database> < database/migrations/20260105_add_restaurant_settings.sql
```

Después, actualiza los archivos JSON de `public/json/` para que incluyan los nuevos ajustes (si quieres que la carta los use sin consultar la base de datos). Ejecuta el script PHP de migración de JSON:

```bash
php database/migrations/20260105_populate_restaurant_json.php
```

Asegúrate de crear copia de seguridad de tu base de datos y de los archivos `public/json/` antes de aplicar migraciones en producción.

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