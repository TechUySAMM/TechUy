# Manual de instalación — S.I.G.S.M.

Configuración del entorno de desarrollo local, compatible con el entorno
de producción del DTI (GNU/Linux Debian).

## Paso 1 — Instalación de XAMPP (entorno local)

- Descargar XAMPP 8.x desde el sitio oficial de Apache Friends.
- Instalar habilitando los módulos Apache, MySQL/MariaDB y PHP.
- Iniciar los servicios Apache y MySQL desde el panel de control de XAMPP.

## Paso 2 — Instalación de Visual Studio Code

- Descargar e instalar VS Code desde code.visualstudio.com.
- Instalar las extensiones: PHP Intelephense, SQLTools, GitLens, Live Server
  y Prettier.
- Configurar el intérprete de PHP apuntando a la carpeta `php/` dentro de la
  instalación de XAMPP.

## Paso 3 — Configuración de la base de datos

- Acceder a phpMyAdmin (`http://localhost/phpmyadmin`).
- Crear la base de datos `hc_gestion_documental` con codificación `utf8mb4`
  (el script de creación ya la incluye).
- Ejecutar `database/ddl_3fn.sql` para crear las tablas (esquema 3FN).
- Ejecutar `database/datos_ejemplo.sql` para cargar datos de ejemplo
  (nunca datos de pacientes reales) para pruebas funcionales.

## Paso 4 — Clonado y configuración del repositorio

- Clonar el repositorio: `git clone <URL-del-repositorio>`.
- Copiar el archivo `.env.example` a `.env` y configurar las credenciales
  locales de base de datos.
- Verificar el correcto funcionamiento accediendo a
  `http://localhost/techuy/prototipo/`.

## Paso 5 — Verificación del entorno

- Confirmar versión de PHP >= 8.1 mediante `php -v`.
- Confirmar conexión a MySQL/MariaDB desde SQLTools en VS Code.
- Ejecutar el prototipo estático (carpeta `/prototipo`) mediante Live Server
  para validar el diseño responsivo.
