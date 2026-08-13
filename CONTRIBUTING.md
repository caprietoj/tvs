# 🤝 Guía de Contribución

¡Gracias por tu interés en contribuir al Sistema TVS! Esta guía te ayudará a entender cómo puedes colaborar efectivamente.

## 📋 Tabla de Contenidos
- [Código de Conducta](#código-de-conducta)
- [Cómo Puedo Contribuir](#cómo-puedo-contribuir)
- [Proceso de Desarrollo](#proceso-de-desarrollo)
- [Estándares de Código](#estándares-de-código)
- [Commits y Mensajes](#commits-y-mensajes)
- [Pull Requests](#pull-requests)
- [Testing](#testing)
- [Documentación](#documentación)

## 📜 Código de Conducta

Este proyecto se adhiere a un código de conducta profesional. Al participar, se espera que mantengas este código:

- Usa lenguaje acogedor e inclusivo
- Respeta diferentes puntos de vista y experiencias
- Acepta críticas constructivas con gracia
- Enfócate en lo que es mejor para la comunidad educativa
- Muestra empatía hacia otros miembros

## 🚀 Cómo Puedo Contribuir

### Reportar Bugs

Si encuentras un bug, por favor crea un issue con:

1. **Título descriptivo**
2. **Descripción detallada** del problema
3. **Pasos para reproducir**
4. **Comportamiento esperado vs actual**
5. **Screenshots** (si aplica)
6. **Información del entorno**:
   - Versión de PHP
   - Versión de Laravel
   - Navegador y versión
   - Sistema operativo

#### Ejemplo de Reporte de Bug

```markdown
## 🐛 Bug: Error al exportar reporte de enfermería

### Descripción
Al intentar exportar el reporte de estudiantes con filtros aplicados, se genera un error 500.

### Pasos para reproducir
1. Ir a `/enfermeria/reporte-estudiantes`
2. Aplicar filtro de fecha: 01/11/2025 - 04/11/2025
3. Aplicar filtro de sección: Preescolar
4. Hacer clic en "Exportar a Excel"

### Comportamiento esperado
Descarga archivo Excel con datos filtrados

### Comportamiento actual
Error 500 en consola, no se descarga nada

### Entorno
- PHP: 8.1.10
- Laravel: 10.x
- Navegador: Chrome 119
- SO: Windows 11
```

### Sugerir Mejoras

Para sugerir nuevas características:

1. **Verifica** que no exista una sugerencia similar
2. **Describe** claramente la funcionalidad deseada
3. **Explica** por qué sería útil
4. **Proporciona** ejemplos de uso

### Contribuir con Código

1. **Fork** el repositorio
2. **Crea** una rama para tu feature
3. **Desarrolla** tu característica
4. **Prueba** exhaustivamente
5. **Documenta** tus cambios
6. **Envía** un Pull Request

## 💻 Proceso de Desarrollo

### 1. Configurar el Entorno

```bash
# Clonar el repositorio
git clone https://github.com/caprietoj/tvs.git
cd tvs

# Instalar dependencias
composer install
npm install

# Configurar entorno
cp .env.example .env
php artisan key:generate

# Migrar base de datos
php artisan migrate
php artisan db:seed
```

### 2. Crear una Rama

```bash
# Nomenclatura de ramas
git checkout -b tipo/nombre-descriptivo

# Tipos permitidos:
# - feature/  (nueva funcionalidad)
# - fix/      (corrección de bug)
# - docs/     (documentación)
# - style/    (formato, sin cambios de lógica)
# - refactor/ (refactorización)
# - test/     (agregar tests)
# - chore/    (mantenimiento)

# Ejemplos:
git checkout -b feature/reconocimiento-voz-colaboradores
git checkout -b fix/error-exportacion-excel
git checkout -b docs/actualizar-readme
```

### 3. Desarrollar

```bash
# Trabaja en tu rama
# Haz commits frecuentes
# Mantén los commits atómicos (un propósito por commit)

# Sincroniza con main regularmente
git fetch origin
git rebase origin/main
```

## 📝 Estándares de Código

### PHP (Laravel)

#### PSR-12
Seguimos el estándar PSR-12:

```php
<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $students = Student::with('section')
            ->orderBy('name')
            ->paginate(20);
            
        return view('students.index', compact('students'));
    }
}
```

#### Convenciones

- **Clases**: PascalCase (`StudentController`)
- **Métodos**: camelCase (`getStudentData`)
- **Variables**: snake_case (`$user_name`) o camelCase (`$userName`)
- **Constantes**: UPPER_SNAKE_CASE (`MAX_UPLOAD_SIZE`)
- **Rutas**: kebab-case (`/ingreso-estudiantes`)

#### Documentación

```php
/**
 * Procesa el reconocimiento de voz y guarda la transcripción.
 *
 * @param  Request  $request
 * @param  string  $fieldId
 * @return JsonResponse
 * 
 * @throws \Exception Si el campo no existe
 */
public function processVoiceRecognition(Request $request, string $fieldId): JsonResponse
{
    // Implementación
}
```

### JavaScript

#### ES6+
Usamos sintaxis moderna:

```javascript
// ✅ Correcto
const recognition = new SpeechRecognition();
const students = await fetchStudents();
const filtered = data.filter(item => item.active);

// ❌ Incorrecto
var recognition = new SpeechRecognition();
var students = fetchStudents();
var filtered = data.filter(function(item) { return item.active; });
```

#### jQuery
Para código jQuery:

```javascript
// ✅ Correcto
$(document).ready(function() {
    const $table = $('#studentTable');
    
    $table.DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json'
        }
    });
});

// ❌ Incorrecto
$(document).ready(function() {
    $('#studentTable').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
        }
    });
});
```

### Blade

```blade
{{-- ✅ Correcto --}}
@extends('layouts.app')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">{{ $title }}</h3>
        </div>
        <div class="card-body">
            @foreach($items as $item)
                <p>{{ $item->name }}</p>
            @endforeach
        </div>
    </div>
@endsection

{{-- ❌ Incorrecto --}}
@extends('layouts.app')
@section('content')
<div class="card"><div class="card-header"><h3>{{ $title }}</h3></div>
<div class="card-body">
@foreach($items as $item)<p>{{ $item->name }}</p>@endforeach
</div></div>
@endsection
```

### CSS

```css
/* ✅ Correcto */
.voice-btn {
    border: 2px solid var(--tvs-accent);
    color: var(--tvs-accent);
    background-color: var(--tvs-white);
    transition: all 0.3s ease;
}

.voice-btn:hover {
    background-color: var(--tvs-accent);
    color: var(--tvs-white);
}

/* ❌ Incorrecto */
.voice-btn{border:2px solid #3498db;color:#3498db;background-color:#fff}
.voice-btn:hover{background-color:#3498db;color:#fff}
```

## 📤 Commits y Mensajes

### Formato de Commits

Usamos [Conventional Commits](https://www.conventionalcommits.org/):

```
tipo(alcance): descripción breve

Descripción detallada (opcional)

BREAKING CHANGE: descripción del cambio (opcional)
```

### Tipos de Commits

- `feat`: Nueva funcionalidad
- `fix`: Corrección de bug
- `docs`: Cambios en documentación
- `style`: Formato (sin cambios de lógica)
- `refactor`: Refactorización
- `test`: Agregar o modificar tests
- `chore`: Mantenimiento

### Ejemplos

```bash
# Feature
git commit -m "feat(enfermeria): agregar reconocimiento de voz en formulario colaboradores"

# Fix
git commit -m "fix(excel): corregir caracteres HTML en exportación"

# Docs
git commit -m "docs(readme): actualizar sección de instalación"

# Refactor
git commit -m "refactor(services): extraer lógica de notificaciones a servicio"

# Breaking Change
git commit -m "feat(equipment): cambiar validación de fecha a after:today

BREAKING CHANGE: Los préstamos ahora requieren mínimo 1 día de anticipación"
```

## 🔀 Pull Requests

### Antes de Crear un PR

1. ✅ Todos los tests pasan
2. ✅ Código formateado correctamente
3. ✅ Sin conflictos con `main`
4. ✅ Documentación actualizada
5. ✅ Changelog actualizado (si aplica)

### Plantilla de PR

```markdown
## 🎯 Tipo de Cambio
- [ ] Nueva funcionalidad
- [ ] Corrección de bug
- [ ] Documentación
- [ ] Refactorización
- [ ] Otro (especificar)

## 📝 Descripción
Descripción clara y concisa de los cambios realizados.

## 🔗 Issue Relacionado
Closes #123

## 🧪 ¿Cómo se probó?
Describe las pruebas que realizaste:
- [ ] Test unitarios
- [ ] Test de integración
- [ ] Prueba manual en navegador

## 📸 Screenshots (si aplica)
Agregar capturas de pantalla que demuestren los cambios.

## ✅ Checklist
- [ ] Mi código sigue los estándares del proyecto
- [ ] He revisado mi propio código
- [ ] He comentado código complejo
- [ ] He actualizado la documentación
- [ ] Mis cambios no generan nuevas advertencias
- [ ] He agregado tests que prueban mi corrección/funcionalidad
- [ ] Todos los tests nuevos y existentes pasan
- [ ] He actualizado el CHANGELOG.md
```

### Proceso de Revisión

1. **Crear PR** con descripción detallada
2. **Esperar revisión** del equipo
3. **Atender comentarios** y hacer ajustes
4. **Aprobar tests** automáticos
5. **Merge** después de aprobación

## 🧪 Testing

### Ejecutar Tests

```bash
# Todos los tests
php artisan test

# Tests específicos
php artisan test --filter=VoiceRecognitionTest

# Con cobertura
php artisan test --coverage-html coverage/
```

### Escribir Tests

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class EnfermeriaTest extends TestCase
{
    use RefreshDatabase;
    
    /** @test */
    public function puede_crear_ingreso_estudiante_con_reconocimiento_voz(): void
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)
            ->post('/enfermeria/ingreso-estudiantes', [
                'descripcion_evento' => 'Transcripción de voz de prueba',
                'accion_enfermeria' => 'Acción realizada'
            ]);
            
        $response->assertStatus(201);
        $this->assertDatabaseHas('ingreso_estudiantes', [
            'descripcion_evento' => 'Transcripción de voz de prueba'
        ]);
    }
}
```

## 📚 Documentación

### Documentar Código

```php
/**
 * Exporta el reporte de estudiantes a Excel con filtros aplicados.
 * 
 * Este método genera un archivo Excel profesional usando SheetJS,
 * aplicando los filtros activos y agregando información de filtros
 * al archivo exportado.
 *
 * @param  Request  $request Contiene los filtros aplicados
 * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
 * 
 * @throws \Exception Si hay error al generar el archivo
 * 
 * @example
 * // Exportar con filtros de fecha
 * exportarReporte(['fecha_desde' => '2025-11-01', 'fecha_hasta' => '2025-11-04'])
 */
public function exportarReporte(Request $request)
{
    // Implementación
}
```

### Actualizar README

Cuando agregues funcionalidades importantes, actualiza:
- README.md principal
- Documentación específica del módulo
- CHANGELOG.md

## 🎓 Recursos Adicionales

- [Documentación Laravel](https://laravel.com/docs)
- [PSR-12 Coding Standard](https://www.php-fig.org/psr/psr-12/)
- [Conventional Commits](https://www.conventionalcommits.org/)
- [JavaScript Style Guide](https://github.com/airbnb/javascript)

## ❓ ¿Necesitas Ayuda?

Si tienes dudas:
1. Revisa la documentación existente
2. Busca en los issues cerrados
3. Pregunta en el canal de desarrollo
4. Contacta al equipo técnico

---

<p align="center">
  <strong>¡Gracias por contribuir al Sistema TVS! 🎉</strong>
</p>
