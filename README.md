# S.I.G.S.M. — Sistema de Gestión de Servicios Médicos

Módulo de gestión documental y encuestas de satisfacción desarrollado para
el Hospital de Clínicas, como Proyecto de Egreso (ISBO 2026) de UTU.

Permite al personal de enfermería y administración cargar material
informativo por especialidad (con acceso vía código QR desde el celular
del paciente) y gestionar encuestas de satisfacción anónimas.

## Integrantes — TechUy

- Santiago da Silva — Coordinador
- Darío Imas — Sub-coordinador
- Marcos Posada
- Mateo Zeballos

Contacto: tech.uy.samm@gmail.com

## Instalación

Ver la guía paso a paso en [`docs/manual_instalacion.md`](docs/manual_instalacion.md).

## Uso del prototipo estático

El prototipo no funcional (HTML/CSS/Bootstrap, sin lógica de backend) se
encuentra en la carpeta [`/prototipo`](prototipo). Incluye:

- `index.html` — login del personal.
- `panel.html` — panel de administración de documentos.
- `documento.html` — vista del documento para el paciente (mobile-first,
  acceso vía QR).
- `encuesta.html` — formulario de encuesta de satisfacción.

Para visualizarlo, abrir la carpeta en VS Code y ejecutar `index.html` con
la extensión Live Server.

## Tecnologías utilizadas

- PHP 8.x (PDO, sentencias preparadas)
- MySQL/MariaDB
- Bootstrap 5.3
- GitHub (Conventional Commits)
- Visual Studio Code

## Estructura del repositorio

```
sigsm-full-stack/
├── prototipo/        # HTML/CSS/Bootstrap - sin lógica
├── src/               # Código fuente PHP a desarrollar
│   ├── public/
│   ├── app/
│   └── config/
├── database/
│   ├── ddl_3fn.sql
│   └── datos_ejemplo.sql
├── docs/
│   ├── documento_tecnico.docx
│   ├── der.png
│   └── manual_instalacion.md
├── .env.example
├── .gitignore
└── README.md
```

## Convenciones de commits

Se adopta el estándar [Conventional Commits](https://www.conventionalcommits.org/):

| Prefijo    | Uso                                                        |
|------------|-------------------------------------------------------------|
| `feat:`    | Nueva funcionalidad (ej: `feat: alta de documento por especialidad`) |
| `fix:`     | Corrección de errores                                       |
| `docs:`    | Cambios en documentación                                     |
| `style:`   | Cambios de formato/estilo sin afectar lógica                 |
| `refactor:`| Reestructuración de código sin cambiar comportamiento        |
| `test:`    | Incorporación o ajuste de pruebas                            |

## Licencia y estado del proyecto

Proyecto académico (UTU, Proyecto de Egreso ISBO 2026). En desarrollo.
