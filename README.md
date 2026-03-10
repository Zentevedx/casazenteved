# Casa Zenteved - Sistema de Gestión de Préstamos

Este sistema es una aplicación web desarrollada con Laravel (backend) y Vue.js/Inertia (frontend) para la gestión de una casa de empeños o préstamos. Permite administrar clientes, préstamos, artículos de garantía, caja chica y visualizar reportes financieros.

## Descripción del Sistema

El sistema cuenta con los siguientes módulos principales:

-   **Dashboard**: Vista general con estadísticas rápidas y accesos directos.
-   **Gestión de Clientes**: Registro, edición y visualización del historial de clientes.
-   **Gestión de Préstamos**: Creación de préstamos, cálculo de intereses y fechas de vencimiento, generación de contratos PDF.
-   **Artículos**: Inventario de artículos dejados en garantía.
-   **Caja Chica**: Control de ingresos y egresos diarios.
-   **Reportes**: Reportes financieros y estadísticas de rendimiento.
-   **Sistema (Admin)**: Herramientas de respaldo de base de datos.

## Seguridad y Roles

El sistema implementa un control de acceso basado en roles:
-   **Admin**: Tiene acceso total, incluyendo la generación de respaldos de base de datos.
-   **User**: Tiene acceso a la gestión operativa pero no a herramientas del sistema sensibles.

**Nota de Seguridad**: El registro de nuevos usuarios (`/register`) está deshabilitado públicamente. Para agregar nuevos usuarios, se debe hacer directamente en la base de datos o habilitar temporalmente la ruta.

## Copias de Seguridad (Backups)

El sistema integra `spatie/laravel-backup` para realizar copias seguridad de la base de datos.

-   **Ubicación**: Los respaldos se guardan en el servidor en la ruta `storage/app/public/Laravel`.
-   **Protección**: Los archivos ZIP generados están protegidos con contraseña (definida en las variables de entorno).
-   **Generación**: Solo los usuarios con rol `admin` pueden iniciar un respaldo desde la barra lateral ("Sistema" -> "Respaldos BD").
-   **Importante**: **NUNCA** guardes los respaldos en el repositorio Git. Asegúrate de descargarlos periódicamente mediante SFTP/SSH si necesitas guardarlos externamente.

## Instrucciones de Despliegue (Deployment)

Para poner en marcha el proyecto en un nuevo servidor, sigue estos pasos:

### 1. Requisitos del Servidor
-   PHP >= 8.2
-   Composer
-   Node.js & NPM
-   MySQL (u otro motor de base de datos soportado)
-   Extensiones PHP: `bcmath`, `ctype`, `fileinfo`, `json`, `mbstring`, `openssl`, `pdo`, `tokenizer`, `xml`, `zip`, `dom`.
-   **Importante**: Para que los respaldos funcionen, el servidor debe tener instalada la herramienta `mysqldump` (o la correspondiente a tu BD) y ser accesible desde la línea de comandos.

### 2. Instalación

```bash
# 1. Clonar el repositorio
git clone <url-del-repositorio>
cd casazenteved

# 2. Instalar dependencias de PHP
composer install --optimize-autoloader --no-dev

# 3. Instalar dependencias de JS y compilar assets
npm install
npm run build

# 4. Configurar variables de entorno
cp .env.example .env
# Edita .env y configura tu base de datos (DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD)
# Configura BACKUP_ARCHIVE_PASSWORD='tu_contraseña_secreta_para_zips'

# 5. Generar clave de aplicación
php artisan key:generate

# 6. Crear enlace simbólico para storage (opcional, si usas archivos públicos)
php artisan storage:link

# 7. Ejecutar migraciones
php artisan migrate --force
```

### 3. Configuración del Primer Admin
Por defecto, los usuarios se crean con el rol `user`. Para promover un usuario a `admin`:

1.  Regístralo (o habilita temporalmente el registro).
2.  Accede a la base de datos y ejecuta:
    ```sql
    UPDATE users SET role = 'admin' WHERE email = 'tu@email.com';
    ```

## Soporte
Para problemas técnicos, contactar al desarrollador encargado.
