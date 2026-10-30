# S.I.G.S.M. — Sistema Informático de Gestión de Servicios Médicos

Módulo de **gestión y acceso digital a documentación para pacientes** y **encuestas de satisfacción** del Hospital de Clínicas.
Proyecto de Egreso BT Tecnologías de la Información — ISBO 2026 — Grupo **TechUY**.

- Los pacientes escanean **un único código QR** y llegan a la portada del portal, desde donde abren documentos (PDF) y responden encuestas anónimas, sin iniciar sesión.
- El personal del hospital ingresa con su cédula y contraseña para cargar y administrar los documentos; el administrador gestiona además usuarios, servicios, encuestas, resultados y auditoría.

> Todos los datos y documentos del proyecto son **ficticios**.

---

## 1. Requisitos

| Componente | Versión probada |
|---|---|
| XAMPP (Apache + PHP + MariaDB) | 8.2.12 |
| PHP | 8.1 o superior (extensiones `pdo_mysql`, `fileinfo`, `mbstring`) |
| MariaDB / MySQL | MariaDB 10.4 / MySQL 8 |
| Navegador | Chrome, Firefox o Edge actualizados |

En producción (servidor del DTI): Debian 12/13 con Apache 2.4, PHP 8.2 y MariaDB 10.11.

## 2. Instalación (Windows + XAMPP)

1. Descargar o clonar este repositorio y copiar su carpeta `sigsm` dentro de `C:\xampp\htdocs\` (debe quedar `C:\xampp\htdocs\sigsm`).
2. Iniciar **Apache** y **MySQL** desde el panel de control de XAMPP.
3. Crear la base y cargar los datos de prueba desde phpMyAdmin → Importar, o por consola desde la carpeta `sigsm`:

   ```bash
   C:\xampp\mysql\bin\mysql.exe -u root --default-character-set=utf8mb4 < database\01_estructura.sql
   C:\xampp\mysql\bin\mysql.exe -u root --default-character-set=utf8mb4 < database\02_datos_prueba.sql
   ```

   El script 01 crea la base `hc_gestion_documental`, sus 10 tablas y el usuario de aplicación `sigsm_app` (solo `SELECT`, `INSERT`, `UPDATE`).
4. Verificar que existan los PDF de prueba en `sigsm/documentos/` (vienen incluidos, uno por cada registro de la tabla `documento`).
5. Abrir **http://localhost/sigsm/**

### Instalación en Debian (resumen)

```bash
sudo apt install apache2 mariadb-server php php-mysql php-mbstring
sudo cp -r sigsm /var/www/
sudo mysql < /var/www/sigsm/database/01_estructura.sql
sudo mysql < /var/www/sigsm/database/02_datos_prueba.sql
sudo chown -R www-data:www-data /var/www/sigsm/documentos /var/www/sigsm/logs
sudo a2enmod headers rewrite && sudo systemctl reload apache2
```

Habilitar `AllowOverride All` para `/var/www/sigsm` para que se apliquen los `.htaccess`.

## 3. Configuración

Todo se configura en `sigsm/config/config.php`:

| Constante | Uso | Valor por defecto |
|---|---|---|
| `BD_HOST`, `BD_NOMBRE` | Servidor y base | `localhost`, `hc_gestion_documental` |
| `BD_USUARIO`, `BD_CLAVE` | Usuario de aplicación (no root) | `sigsm_app` |
| `URL_BASE` | Carpeta del sistema en el servidor | `/sigsm` |
| `PDF_TAMANO_MAX` | Tamaño máximo de PDF | 5 MB |
| `LOGIN_MAX_INTENTOS` / `_IP` | Intentos fallidos antes de bloquear | 5 por cédula / 20 por IP |
| `LOGIN_MINUTOS_BLOQUEO` | Duración del bloqueo | 15 minutos |

Si se cambia la contraseña de `sigsm_app` en el script 01, cambiarla también en `BD_CLAVE`.

## 4. Direcciones de acceso

| Página | URL |
|---|---|
| Portada pública (destino del QR) | http://localhost/sigsm/ |
| Documentos | http://localhost/sigsm/documentos.php |
| Encuestas | http://localhost/sigsm/encuestas.php |
| Código QR para imprimir | http://localhost/sigsm/qr.php |
| Ingreso de personal | http://localhost/sigsm/login.php |
| Panel | http://localhost/sigsm/admin/panel.php |

Para que los celulares de la red lean el QR, abrir `qr.php` usando la IP del servidor (por ejemplo `http://192.168.1.50/sigsm/qr.php`): el QR se genera con la dirección con la que se abrió la página.

## 5. Credenciales de prueba

| Rol | Cédula | Contraseña | Puede |
|---|---|---|---|
| Administrador | `41234567` | `Admin.2026` | Todo: documentos, encuestas, resultados, servicios, usuarios, auditoría |
| Usuario básico | `38765432` | `Usuario.2026` | Gestionar documentos y cambiar su contraseña |
| Usuario dado de baja | `3456789` | `Usuario.2026` | No puede ingresar (prueba de baja lógica) |

Los usuarios 3, 4 y 5 de los datos de prueba también son usuarios básicos con la contraseña `Usuario.2026`.

## 6. Estructura del proyecto

```
TechUy/
├── README.md
├── docs/
│   ├── documento_tecnico.docx   → documentación completa
│   └── capturas/                → pantallas, phpMyAdmin, DER y modelo relacional
└── sigsm/                       → aplicación (va dentro de htdocs)
    ├── index.php, documentos.php, encuestas.php, responder_encuesta.php,
    │   ver_documento.php, qr.php → portal público
    ├── login.php, logout.php     → sesión
    ├── admin/      → panel, documentos, usuarios, servicios, encuestas, resultados, auditoría
    ├── config/     → config.php y conexion.php
    ├── includes/   → sesión, CSRF, funciones, errores, validaciones, formularios
    ├── public/     → css, js, img
    ├── documentos/ → PDF (acceso directo bloqueado)
    ├── logs/       → errores.log (acceso directo bloqueado)
    ├── database/   → 01_estructura.sql, 02_datos_prueba.sql
    └── scripts/    → respaldo y restauración (Windows y Debian)
```

Cada página separa **procesamiento** (arriba, PHP) de **presentación** (abajo, HTML); la **conexión** está en `config/conexion.php`.

## 7. Seguridad implementada

- Contraseñas con `password_hash()` / `password_verify()`.
- Sesiones PHP con cookie `HttpOnly` + `SameSite=Strict`, `session_regenerate_id()` al ingresar, y comprobación en cada página de que el usuario siga activo.
- Control de permisos en PHP en cada página (`requerir_login()` / `requerir_admin()`), también al entrar por URL.
- Consultas preparadas PDO en todas las consultas con datos del usuario.
- Token CSRF en todos los formularios que modifican datos; bajas y cierre de sesión solo por POST.
- `htmlspecialchars()` (función `e()`) en todo lo que se muestra.
- PDF: se valida el tipo real (`finfo`) y el tamaño; nombre de archivo generado por el sistema.
- `/documentos`, `/config`, `/includes`, `/logs`, `/database` bloqueados con `.htaccess`; los PDF solo se abren por `ver_documento.php`, que verifica que el documento esté activo, publicado y con servicio activo.
- Límite de intentos de login, campo trampa + tiempo mínimo en la encuesta pública, token de un solo uso por envío.
- Errores registrados en `logs/errores.log`; al usuario solo se le muestra un mensaje genérico.
- El usuario de base `sigsm_app` **no tiene permiso DELETE**: la baja lógica está garantizada también por la base.

## 8. Copias de seguridad

- Windows: `scripts\respaldo.bat` → crea `respaldos\AAAAMMDD_HHMM\` con `base.sql` y `documentos.zip`. Restaurar con `scripts\restaurar.bat AAAAMMDD_HHMM`.
- Debian: `scripts/respaldo.sh` (programar con cron, conserva 30 días) y `scripts/restaurar.sh`.

## 9. Integrantes — TechUY

- Santiago Da Silva (Coordinador)
- Dario Imas (Sub-Coordinador)
- Mateo Zeballos
- Marcos Posada
