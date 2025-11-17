# Patitas Felices

Sistema web para la gestión de adopciones y voluntariado de una fundación de rescate animal. Proyecto final de Pruebas y Mantenimiento

## Techstack
- HTML + Tailwind CSS
- PHP
- MySQL
- Arquitectura en 3 capas (Presentación · Aplicación · Datos)

## Estructura del proyecto
- `public/` → archivos expuestos por el servidor (landing, login, registro).
- `src/` → lógica del sistema (config, conexión, modelos, repositorios, servicios).
- `db/` → scripts SQL (`schema.sql`, `seed.sql`, `views.sql`).
- `docs/` → documentación del proyecto.

## Configuración local
1. Crear base de datos MySQL: `patitas_felices`.
2. Importar `db/schema.sql` y luego `db/seed.sql`.
3. Crear archivo local de configuración:
   ```bash
   copy src\config\config.example.php src\config\config.local.php
