# 🏫 Sistema de Gestión TVS (Teresiano Vida y Santidad)

[![Laravel](https://img.shields.io/badge/Laravel-10.x-FF2D20?style=flat&logo=laravel)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.1+-777BB4?style=flat&logo=php)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-8.0+-4479A1?style=flat&logo=mysql)](https://www.mysql.com)
[![License](https://img.shields.io/badge/License-Proprietary-red.svg)](LICENSE)

Sistema integral de gestión escolar para el colegio Teresiano Vida y Santidad, desarrollado con Laravel 10, que incluye módulos de préstamos de equipos, enfermería, compras, servicios generales y más.

---

## 📋 Tabla de Contenidos

- [Características Principales](#-características-principales)
- [Tecnologías Utilizadas](#-tecnologías-utilizadas)
- [Requisitos del Sistema](#-requisitos-del-sistema)
- [Instalación](#-instalación)
- [Configuración](#-configuración)
- [Módulos del Sistema](#-módulos-del-sistema)
- [Características Destacadas](#-características-destacadas)
- [Estructura del Proyecto](#-estructura-del-proyecto)
- [Documentación Adicional](#-documentación-adicional)
- [Mantenimiento](#-mantenimiento)
- [Soporte](#-soporte)

---

## ✨ Características Principales

### 🎯 Gestión de Préstamos de Equipos
- Solicitud de préstamos con restricciones de fecha inteligentes
- Validación en 3 capas (Frontend, JavaScript, Backend)
- Restricción de préstamos para el mismo día (mínimo 1 día de anticipación)
- Sistema de notificaciones por correo electrónico
- Dashboard de seguimiento de equipos

### 🏥 Módulo de Enfermería
- **Formularios Inteligentes con Reconocimiento de Voz**
  - Web Speech API integrada
  - Transcripción en tiempo real (español Colombia - es-CO)
  - Disponible en formularios de estudiantes y colaboradores
- Gestión de ingresos de estudiantes y colaboradores
- **Reportes Avanzados con Filtros Dinámicos**
  - Exportación a Excel profesional con SheetJS
  - Filtros por fecha, sección/tipo, cantidad mínima
  - Filtros rápidos (Hoy, Esta Semana, Este Mes)
  - Recalculación dinámica de totales
- Seguimiento de incidencias médicas
- Sistema de encuestas y observaciones

### 📧 Sistema de Notificaciones
- Notificaciones por email con configuración dinámica
- Clasificación automática por sección y monto
- Modo de prueba para desarrollo local
- Interceptación de emails en ambiente de testing
- Enrutamiento inteligente a directores correspondientes

### 🛒 Gestión de Compras y Servicios
- Solicitudes de compra
- Cotizaciones
- Control de presupuestos
- Importación masiva de datos
- Formularios externos personalizables

### 👥 Gestión de Colaboradores
- Importación de datos desde Excel
- Perfiles de empleados
- Roles y permisos con Spatie
- Historial de movimientos

### 🚪 Control de Portería
- Registro de ingresos y salidas
- Gestión de visitantes
- Importación de plantillas
- Reportes de acceso

---

## 🛠 Tecnologías Utilizadas

### Backend
- **Laravel 10.x** - Framework PHP principal
- **PHP 8.1+** - Lenguaje de programación
- **MySQL 8.0+** - Base de datos relacional
- **Spatie Laravel Permission** - Gestión de roles y permisos
- **Laravel Excel (Maatwebsite)** - Importación/exportación Excel
- **Carbon** - Manipulación de fechas

### Frontend
- **AdminLTE 3** - Panel de administración
- **Bootstrap 4** - Framework CSS
- **jQuery** - Librería JavaScript
- **DataTables** - Tablas interactivas
- **SheetJS (xlsx)** - Generación de Excel profesional
- **Web Speech API** - Reconocimiento de voz
- **Toastr** - Notificaciones visuales
- **Select2** - Selectores avanzados
- **Font Awesome** - Iconografía

### Herramientas de Desarrollo
- **Composer** - Gestor de dependencias PHP
- **NPM** - Gestor de paquetes JavaScript
- **Vite** - Build tool para assets
- **Git** - Control de versiones

---

## 📦 Requisitos del Sistema

### Servidor
- PHP >= 8.1
- Composer >= 2.0
- Node.js >= 16.x
- NPM >= 8.x

### Base de Datos
- MySQL >= 8.0 o MariaDB >= 10.3

### Extensiones PHP Requeridas
```
php-xml
php-mbstring
php-curl
php-zip
php-gd
php-mysql
php-pdo
php-bcmath
php-intl
php-fileinfo
```

### Navegadores Soportados
- Chrome/Edge (recomendado para reconocimiento de voz)
- Firefox
- Safari
- Opera

---

## 🚀 Instalación

### 1. Clonar el Repositorio
```bash
git clone https://github.com/caprietoj/tvs.git
cd tvs
```

### 2. Instalar Dependencias PHP
```bash
composer install
```

### 3. Instalar Dependencias JavaScript
```bash
npm install
```

### 4. Configurar Variables de Entorno
```bash
cp .env.example .env
php artisan key:generate
```

### 5. Configurar Base de Datos
Edita el archivo `.env` con tus credenciales:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=tvs
DB_USERNAME=tu_usuario
DB_PASSWORD=tu_contraseña
```

### 6. Ejecutar Migraciones
```bash
php artisan migrate
```

### 7. Ejecutar Seeders (Opcional)
```bash
php artisan db:seed
```

### 8. Compilar Assets
```bash
npm run build
# O para desarrollo:
npm run dev
```

### 9. Crear Link de Storage
```bash
php artisan storage:link
```

### 10. Iniciar Servidor de Desarrollo
```bash
php artisan serve
```

El sistema estará disponible en `http://127.0.0.1:8000`

---

## ⚙️ Configuración

### Configuración de Correo Electrónico
Edita `.env` con tus credenciales SMTP:
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=tu_email@gmail.com
MAIL_PASSWORD=tu_app_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@tvs.edu.co
MAIL_FROM_NAME="Sistema TVS"
```

### Configuración de Google OAuth (Opcional)
```env
GOOGLE_CLIENT_ID=tu_client_id
GOOGLE_CLIENT_SECRET=tu_client_secret
GOOGLE_REDIRECT_URI=http://127.0.0.1:8000/auth/google/callback
```

### Permisos de Archivos
```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

---

## 📚 Módulos del Sistema

### 1. **Préstamos de Equipos** (`/equipment`)
- Solicitud de préstamos
- Restricciones por día de la semana
- Historial de préstamos
- Notificaciones automáticas

### 2. **Enfermería** (`/enfermeria`)
- **Ingreso Estudiantes** (`/ingreso-estudiantes`)
  - Formulario con reconocimiento de voz
  - Búsqueda inteligente de estudiantes
  - Registro de síntomas y acciones
- **Ingreso Colaboradores** (`/ingreso-colaboradores`)
  - Formulario con reconocimiento de voz
  - Búsqueda de empleados
  - Gestión de incidencias
- **Reportes** (`/reporte-estudiantes`, `/reporte-colaboradores`)
  - Filtros dinámicos avanzados
  - Exportación Excel profesional
  - Totales dinámicos

### 3. **Compras y Servicios** (`/purchase-requests`, `/service-requests`)
- Solicitudes de compra
- Cotizaciones
- Control presupuestal
- Aprobaciones multinivel

### 4. **Gestión de Colaboradores** (`/colaboradores`)
- Perfiles de empleados
- Importación masiva
- Roles y permisos
- Historial laboral

### 5. **Control de Portería** (`/porteria`)
- Registro de accesos
- Gestión de visitantes
- Importación de datos
- Reportes de seguridad

### 6. **Administración** (`/admin`)
- Gestión de usuarios
- Configuración del sistema
- Roles y permisos
- Logs de actividad

---

## 🌟 Características Destacadas

### 🎤 Reconocimiento de Voz
Sistema avanzado de transcripción de voz a texto:
- **Idioma**: Español de Colombia (es-CO)
- **Campos soportados**: Descripción del evento, Acción de enfermería
- **Características**:
  - Transcripción en tiempo real
  - Reconocimiento continuo
  - Indicadores visuales (animaciones de pulso)
  - Manejo de errores robusto
  - Compatibilidad con Chrome, Edge, Safari

### 📊 Exportación Excel Profesional
Generación de archivos Excel con SheetJS:
- Formato profesional con colores institucionales
- Encabezados azules con texto blanco
- Columnas con anchos optimizados
- Totales con formato especial
- **Integración de filtros**: Exporta solo datos filtrados
- Información de filtros aplicados en el archivo
- Nombres de archivo dinámicos con fecha y filtros

### 🔍 Sistema de Filtros Dinámicos
Filtros avanzados en reportes:
- Rango de fechas (desde/hasta)
- Filtro por sección/tipo
- Cantidad mínima
- Filtros rápidos: Hoy, Esta Semana, Este Mes
- Recalculación automática de totales
- Badge visual de filtros activos
- Integración completa con exportación Excel

### 📧 Sistema de Notificaciones Inteligente
- Enrutamiento automático según sección y monto
- Modo de prueba para desarrollo local
- Interceptación de emails en testing
- Configuración dinámica por archivo
- Plantillas personalizables

### 🔒 Seguridad
- Autenticación con Laravel Sanctum
- Roles y permisos con Spatie
- Protección CSRF
- Validación en múltiples capas
- Logs de auditoría

---

## 📂 Estructura del Proyecto

```
tvs/
├── app/
│   ├── Console/          # Comandos Artisan
│   ├── Exports/          # Clases de exportación Excel
│   ├── Helpers/          # Funciones auxiliares
│   ├── Http/
│   │   ├── Controllers/  # Controladores
│   │   ├── Middleware/   # Middleware personalizado
│   │   └── Requests/     # Form Requests
│   ├── Imports/          # Clases de importación Excel
│   ├── Mail/             # Clases de email
│   ├── Models/           # Modelos Eloquent
│   ├── Notifications/    # Notificaciones
│   ├── Observers/        # Observers de modelos
│   ├── Policies/         # Políticas de autorización
│   ├── Providers/        # Service Providers
│   ├── Services/         # Lógica de negocio
│   ├── Traits/           # Traits reutilizables
│   └── Utils/            # Utilidades
├── bootstrap/            # Arranque de Laravel
├── config/               # Archivos de configuración
├── database/
│   ├── factories/        # Factories para testing
│   ├── migrations/       # Migraciones
│   └── seeders/          # Seeders
├── public/
│   ├── css/              # Estilos compilados
│   ├── js/               # JavaScript compilado
│   └── images/           # Imágenes públicas
├── resources/
│   ├── css/              # Archivos CSS fuente
│   ├── js/               # Archivos JS fuente
│   └── views/            # Plantillas Blade
│       ├── auth/         # Vistas de autenticación
│       ├── enfermeria/   # Módulo de enfermería
│       ├── equipment/    # Módulo de equipos
│       ├── porteria/     # Módulo de portería
│       └── layouts/      # Layouts principales
├── routes/
│   ├── web.php           # Rutas web
│   ├── api.php           # Rutas API
│   └── console.php       # Comandos de consola
├── storage/
│   ├── app/              # Archivos de aplicación
│   ├── logs/             # Logs del sistema
│   └── framework/        # Archivos de framework
├── tests/                # Tests automatizados
├── vendor/               # Dependencias PHP
├── .env.example          # Ejemplo de variables de entorno
├── composer.json         # Dependencias PHP
├── package.json          # Dependencias JavaScript
├── phpunit.xml           # Configuración PHPUnit
├── vite.config.js        # Configuración Vite
└── README.md             # Este archivo
```

---

## 📖 Documentación Adicional

### Documentos Técnicos
- [Configuración de Google OAuth](CONFIGURACION_GOOGLE_OAUTH.md)
- [Configuración de Presupuestos](CONFIGURACION_PRESUPUESTO.md)
- [Módulo de Colaboradores](MODULO_COLABORADORES_README.md)
- [Contexto Módulo de Compras](CONTEXTO_MODULO_COMPRAS.md)
- [Implementación Formulario Externo](IMPLEMENTACION_FORMULARIO_EXTERNO.md)

### Guías de Migración
- [Checklist de Migración](CHECKLIST_MIGRACION.md)
- [Migración a Producción](MIGRACION_PRODUCCION.md)
- [Resumen de Migración](RESUMEN_MIGRACION.md)
- [Despliegue Producción Final](DESPLIEGUE_PRODUCCION_FINAL.md)

### Solución de Problemas
- [FAQ Migraciones](FAQ_MIGRACIONES.md)
- [Solución Error Migración Producción](SOLUCION_ERROR_MIGRACION_PRODUCCION.md)
- [Solución Acceso No Autorizado](SOLUCION_ACCESO_NO_AUTORIZADO.md)
- [Solución Importación Personas](SOLUCION_IMPORTACION_PERSONAS.md)
- [Solución Límite Gmail](SOLUCION_LIMITE_GMAIL.md)

### Documentación de Portería
- [Sistema Completo Portería](Contextos/porteria-sistema-completo.md)
- [Gestión de Personas](Contextos/porteria-gestion-personas.md)
- [Modal de Visitantes](Contextos/porteria-modal-visitantes.md)
- [Plantilla de Importación](Contextos/porteria-plantilla-importacion.md)

---

## 🔧 Mantenimiento

### Comandos Útiles

#### Limpiar Cachés
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

#### Optimizar para Producción
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
composer install --optimize-autoloader --no-dev
```

#### Ejecutar Cola de Trabajos
```bash
php artisan queue:work
```

#### Mantenimiento del Sistema
```bash
# Poner en modo mantenimiento
php artisan down --message="Actualizando sistema" --retry=60

# Salir de modo mantenimiento
php artisan up
```

### Backups
Se recomienda realizar backups diarios de:
- Base de datos
- Archivos de storage
- Archivo .env

```bash
# Backup manual de base de datos
mysqldump -u usuario -p nombre_db > backup_$(date +%Y%m%d).sql
```

---

## 🐛 Testing

### Ejecutar Tests
```bash
# Todos los tests
php artisan test

# Tests específicos
php artisan test --filter=NombreTest

# Con cobertura
php artisan test --coverage
```

---

## 🤝 Contribución

Este es un proyecto privado del Colegio Teresiano Vida y Santidad. Para contribuir:

1. Crea una rama con el formato: `feature/nombre-funcionalidad`
2. Realiza tus cambios
3. Ejecuta los tests
4. Crea un Pull Request con descripción detallada

---

## 📝 Changelog

### Versión 2.0.0 (Noviembre 2025)
- ✅ **Reconocimiento de voz** en formularios de enfermería
- ✅ **Exportación Excel profesional** con SheetJS
- ✅ **Sistema de filtros dinámicos** en reportes
- ✅ **Restricción de préstamos** para el mismo día
- ✅ **Redirección de notificaciones** a administrativedirector
- ✅ Mejoras en UI/UX con animaciones
- ✅ Integración completa de filtros con Excel
- ✅ Recalculación dinámica de totales

### Versión 1.5.0 (Octubre 2025)
- ✅ Restricciones de fecha por día de la semana
- ✅ Sistema de notificaciones mejorado
- ✅ Módulo de portería completo

---

## 📄 Licencia

Este software es propiedad del **Colegio Teresiano Vida y Santidad**. Todos los derechos reservados.

---

## 👥 Equipo de Desarrollo

**Desarrollador Principal**: Carlos Prieto  
**Institución**: Teresiano Vida y Santidad  
**GitHub**: [@caprietoj](https://github.com/caprietoj)

---

## 📞 Soporte

Para soporte técnico o consultas:
- **Email**: soporte@tvs.edu.co
- **Repositorio**: https://github.com/caprietoj/tvs

---

## 🙏 Agradecimientos

- Equipo administrativo del Colegio Teresiano Vida y Santidad
- Comunidad Laravel
- Contribuidores de librerías de código abierto

---

<p align="center">
  <strong>Desarrollado con ❤️ para el Colegio Teresiano Vida y Santidad</strong>
</p>

<p align="center">
  <img src="https://img.shields.io/badge/Made%20with-Laravel-FF2D20?style=for-the-badge&logo=laravel" alt="Made with Laravel">
  <img src="https://img.shields.io/badge/Status-Active-success?style=for-the-badge" alt="Status Active">
</p>
