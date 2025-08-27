<?php

echo "=== SOLUCIÓN PARA PROBLEMA DE MENÚ EN PRODUCCIÓN ===\n\n";

echo "📋 DIAGNÓSTICO COMPLETADO:\n";
echo "✅ Usuario tiene todos los permisos necesarios\n";
echo "✅ Configuración de menú está correcta\n";
echo "✅ Rutas funcionan correctamente\n";
echo "✅ Middleware configurado correctamente\n\n";

echo "🚨 PROBLEMA: El menú no aparece SOLO en producción\n\n";

echo "=== POSIBLES CAUSAS EN PRODUCCIÓN ===\n";
echo "1. 🗂️ Archivo config/adminlte.php diferente entre local y producción\n";
echo "2. 🔄 Cache de configuración no actualizado correctamente\n";
echo "3. 🔐 Permisos de archivos incorrectos en servidor\n";
echo "4. 🌐 Variables de entorno diferentes\n";
echo "5. 📦 Versión de código desactualizada en producción\n\n";

echo "=== COMANDOS PARA EJECUTAR EN PRODUCCIÓN ===\n\n";

echo "🔍 PASO 1: VERIFICAR ARCHIVO DE CONFIGURACIÓN\n";
echo "cd /ruta/del/proyecto/en/produccion\n";
echo "grep -n 'Consolidado Previsitas' config/adminlte.php\n";
echo "# Debe mostrar la línea con la configuración del menú\n\n";

echo "🔍 PASO 2: VERIFICAR PERMISOS DE ARCHIVOS\n";
echo "ls -la config/adminlte.php\n";
echo "# Verificar que el archivo sea legible\n\n";

echo "🔄 PASO 3: LIMPIAR TODOS LOS CACHES (ORDEN ESPECÍFICO)\n";
echo "php artisan down\n";
echo "php artisan config:clear\n";
echo "php artisan cache:clear\n";
echo "php artisan view:clear\n";
echo "php artisan route:clear\n";
echo "php artisan permission:cache-reset\n";
echo "composer dump-autoload\n";
echo "php artisan config:cache\n";
echo "php artisan route:cache\n";
echo "php artisan up\n\n";

echo "🔍 PASO 4: VERIFICAR VARIABLES DE ENTORNO\n";
echo "cat .env | grep APP_ENV\n";
echo "cat .env | grep APP_DEBUG\n";
echo "cat .env | grep CACHE_DRIVER\n";
echo "# Verificar que las variables sean correctas para producción\n\n";

echo "🔍 PASO 5: CREAR SCRIPT DE VERIFICACIÓN EN PRODUCCIÓN\n";
echo "# Crear archivo: verify_menu_production.php\n";
echo "<?php\n";
echo "require_once __DIR__ . '/vendor/autoload.php';\n";
echo "\$app = require_once __DIR__ . '/bootstrap/app.php';\n";
echo "\$kernel = \$app->make(Illuminate\\Contracts\\Console\\Kernel::class);\n";
echo "\$kernel->bootstrap();\n";
echo "\$config = config('adminlte.menu');\n";
echo "\$found = false;\n";
echo "foreach (\$config as \$item) {\n";
echo "    if (isset(\$item['submenu'])) {\n";
echo "        foreach (\$item['submenu'] as \$sub) {\n";
echo "            if (isset(\$sub['text']) && stripos(\$sub['text'], 'previsitas') !== false) {\n";
echo "                echo 'ENCONTRADO: ' . \$sub['text'] . \"\\n\";\n";
echo "                \$found = true;\n";
echo "            }\n";
echo "        }\n";
echo "    }\n";
echo "}\n";
echo "if (!\$found) echo 'NO ENCONTRADO EN CONFIGURACIÓN';\n";
echo "?>\n\n";

echo "🔍 PASO 6: EJECUTAR VERIFICACIÓN\n";
echo "php verify_menu_production.php\n";
echo "# Debe mostrar: ENCONTRADO: Consolidado Previsitas\n\n";

echo "🔍 PASO 7: VERIFICAR CÓDIGO ACTUALIZADO\n";
echo "git status\n";
echo "git log -1 --oneline\n";
echo "# Verificar que el código esté actualizado\n\n";

echo "🔍 PASO 8: VERIFICAR PERMISOS DEL USUARIO EN PRODUCCIÓN\n";
echo "# Crear archivo: check_user_production.php\n";
echo "<?php\n";
echo "require_once __DIR__ . '/vendor/autoload.php';\n";
echo "\$app = require_once __DIR__ . '/bootstrap/app.php';\n";
echo "\$kernel = \$app->make(Illuminate\\Contracts\\Console\\Kernel::class);\n";
echo "\$kernel->bootstrap();\n";
echo "use App\\Models\\User;\n";
echo "\$user = User::where('email', 'asistenteadministrativa@tvs.edu.co')->first();\n";
echo "if (\$user) {\n";
echo "    echo 'Usuario: ' . \$user->name . \"\\n\";\n";
echo "    echo 'Permiso previsitas.view: ' . (\$user->hasPermissionTo('previsitas.view') ? 'SÍ' : 'NO') . \"\\n\";\n";
echo "} else {\n";
echo "    echo 'Usuario no encontrado';\n";
echo "}\n";
echo "?>\n\n";

echo "php check_user_production.php\n";
echo "# Debe mostrar que el usuario tiene el permiso\n\n";

echo "=== SOLUCIÓN ALTERNATIVA TEMPORAL ===\n";
echo "Si el problema persiste, agregar directamente en la vista:\n";
echo "Archivo: resources/views/layouts/app.blade.php o similar\n";
echo "Agregar enlace manual:\n";
echo "<a href=\"/previsitas\" class=\"nav-link\">\n";
echo "    <i class=\"fas fa-list\"></i>\n";
echo "    <p>Consolidado Previsitas</p>\n";
echo "</a>\n\n";

echo "=== VERIFICACIÓN FINAL ===\n";
echo "1. Solicitar al usuario que:\n";
echo "   - Cierre completamente el navegador\n";
echo "   - Borre cache del navegador (Ctrl+Shift+Del)\n";
echo "   - Abra nueva ventana de incógnito\n";
echo "   - Inicie sesión nuevamente\n\n";

echo "2. Si aún no aparece, verificar en:\n";
echo "   - Herramientas de desarrollador (F12)\n";
echo "   - Consola de JavaScript (errores)\n";
echo "   - Red (Network) para ver si hay errores de carga\n\n";

echo "3. URL directa de acceso:\n";
echo "   https://[dominio-produccion]/previsitas\n\n";

echo "=== CONTACTO TÉCNICO ===\n";
echo "Si el problema persiste después de estos pasos:\n";
echo "1. Enviar captura de pantalla del menú actual\n";
echo "2. Enviar resultado de los comandos de verificación\n";
echo "3. Verificar logs del servidor: tail -f storage/logs/laravel.log\n\n";

echo "¡Guía de solución completa!\n";