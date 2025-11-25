# 📦 Sistema TVS - Archivos de Documentación

Este directorio contiene la documentación del Sistema de Gestión TVS.

## 📂 Estructura

```
docs/
├── index.md                    # Página principal de documentación
├── voice-recognition.md        # Guía de reconocimiento de voz
├── reports-filtering.md        # Sistema de filtros y Excel
├── notifications.md            # Sistema de notificaciones
├── equipment-restrictions.md   # Restricciones de equipos
├── email-config.md            # Configuración de emails
├── permissions.md             # Roles y permisos
├── troubleshooting.md         # Solución de problemas
└── tutorials/                 # Tutoriales paso a paso
    ├── installation.md
    ├── first-login.md
    └── ...
```

## 🚀 Ver la Documentación

### Localmente con Jekyll

```bash
# Instalar dependencias
bundle install

# Iniciar servidor Jekyll
bundle exec jekyll serve

# Abrir en navegador
# http://localhost:4000/tvs
```

### En GitHub Pages

La documentación se despliega automáticamente en:
https://caprietoj.github.io/tvs

## ✏️ Editar Documentación

### Formato

Todos los archivos de documentación usan Markdown con front matter:

```markdown
---
layout: page
title: Título de la Página
description: Descripción breve
---

# Contenido

Tu contenido aquí...
```

### Agregar Nueva Página

1. Crea un archivo `.md` en `docs/`
2. Agrega front matter al inicio
3. Escribe contenido en Markdown
4. Actualiza el índice si es necesario

### Sintaxis de Código

Usa bloques de código con resaltado:

\```php
<?php
// Tu código PHP aquí
\```

\```javascript
// Tu código JavaScript aquí
\```

### Imágenes

Agrega imágenes en `docs/images/`:

```markdown
![Descripción](images/screenshot.png)
```

### Enlaces

Enlaces relativos:
```markdown
[Ver API](API.md)
[Guía de Contribución](CONTRIBUTING.md)
```

Enlaces externos:
```markdown
[Laravel](https://laravel.com)
```

## 🔧 Configuración de Jekyll

La configuración está en `_config.yml` en la raíz del proyecto.

### Variables disponibles

```yaml
site.title          # Título del sitio
site.description    # Descripción
site.url            # URL base
site.baseurl        # URL base del proyecto
```

### Usar en templates

```liquid
{{ site.title }}
{{ page.title }}
{{ content }}
```

## 📝 Guías de Estilo

### Títulos

```markdown
# Título Principal (H1)
## Sección (H2)
### Subsección (H3)
#### Detalle (H4)
```

### Énfasis

```markdown
**Negrita**
*Cursiva*
`Código inline`
~~Tachado~~
```

### Listas

```markdown
- Item 1
- Item 2
  - Sub-item 2.1
  - Sub-item 2.2

1. Primer paso
2. Segundo paso
3. Tercer paso
```

### Tablas

```markdown
| Columna 1 | Columna 2 | Columna 3 |
|-----------|-----------|-----------|
| Dato 1    | Dato 2    | Dato 3    |
```

### Alertas

```markdown
> **Nota:** Información importante

> **Advertencia:** Ten cuidado con esto

> **Tip:** Consejo útil
```

### Bloques de código

\```bash
# Comandos de terminal
php artisan migrate
\```

\```php
// Código PHP
$variable = "valor";
\```

## 🤝 Contribuir

Para contribuir a la documentación:

1. Crea una rama: `docs/nombre-seccion`
2. Realiza tus cambios
3. Verifica localmente con Jekyll
4. Crea un Pull Request

## 📚 Recursos

- [Markdown Guide](https://www.markdownguide.org/)
- [Jekyll Documentation](https://jekyllrb.com/docs/)
- [GitHub Pages](https://pages.github.com/)
- [Liquid Templates](https://shopify.github.io/liquid/)

## 🐛 Reportar Problemas

Si encuentras errores en la documentación:
1. [Crea un issue](https://github.com/caprietoj/tvs/issues/new)
2. Usa la etiqueta `documentation`
3. Describe el problema claramente

---

Última actualización: 4 de noviembre de 2025
