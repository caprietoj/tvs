<?php

echo "=== CREANDO SCRIPTS DE VERIFICACIÓN PARA PRODUCCIÓN ===\n\n";

// Script 1: Verificar configuración de menú
$verifyMenuScript = '<?php
require_once __DIR__ . "/vendor/autoload.php";
$app = require_once __DIR__ . "/bootstrap/app.php";
$kernel = $app->make(Illuminate\\Contracts\\Console\\Kernel::class);
$kernel->bootstrap();

echo "=== VERIFICACIÓN DE MENÚ EN PRODUCCIÓN ===\n";
echo "Entorno: " . app()->environment() . "\n";
echo "Fecha: " . now()->format("Y-m-d H:i:s") . "\n\n";

$config = config("adminlte.menu");
if (!$config) {
    echo "❌ No se pudo cargar configuración de AdminLTE\n";
    exit(1);
}

echo "📋 Total elementos de menú: " . count($config) . "\n";

$found = false;
foreach ($config as $index => $item) {
    if (isset($item["submenu"])) {
        foreach ($item["submenu"] as $subIndex => $sub) {
            if (isset($sub["text"]) && stripos($sub["text"], "previsitas") !== false) {
                echo "✅ ENCONTRADO: {$sub["text"]}\n";
                echo "   - Posición: " . ($index + 1) . "." . ($subIndex + 1) . "\n";
                echo "   - URL: {$sub["url"]}\n";
                echo "   - Permiso: {$sub["can"]}\n";
                $found = true;
            }
        }
    }
    if (isset($item["text"]) && stripos($item["text"], "previsitas") !== false) {
        echo "✅ ENCONTRADO: {$item["text"]}\n";
        echo "   - Posición: " . ($index + 1) . "\n";
        echo "   - URL: " . ($item["url"] ?? "N/A") . "\n";
        echo "   - Permiso: " . ($item["can"] ?? "N/A") . "\n";
        $found = true;
    }
}

if (!$found) {
    echo "❌ NO ENCONTRADO EN CONFIGURACIÓN DE MENÚ\n";
    echo "🔍 Verificando archivo config/adminlte.php...\n";
    
    $configFile = config_path("adminlte.php");
    if (file_exists($configFile)) {
        $content = file_get_contents($configFile);
        if (strpos($content, "Consolidado Previsitas") !== false) {
            echo "⚠️ Texto encontrado en archivo pero no en configuración cargada\n";
            echo "🔧 PROBLEMA: Cache de configuración desactualizado\n";
        } else {
            echo "❌ Texto NO encontrado en archivo de configuración\n";
            echo "🔧 PROBLEMA: Configuración faltante en archivo\n";
        }
    } else {
        echo "❌ Archivo config/adminlte.php no existe\n";
    }
} else {
    echo "\n✅ Configuración de menú correcta\n";
}
?>';

// Script 2: Verificar usuario y permisos
$checkUserScript = '<?php
require_once __DIR__ . "/vendor/autoload.php";
$app = require_once __DIR__ . "/bootstrap/app.php";
$kernel = $app->make(Illuminate\\Contracts\\Console\\Kernel::class);
$kernel->bootstrap();

use App\\Models\\User;

echo "=== VERIFICACIÓN DE USUARIO EN PRODUCCIÓN ===\n";
echo "Entorno: " . app()->environment() . "\n";
echo "Fecha: " . now()->format("Y-m-d H:i:s") . "\n\n";

$email = "asistenteadministrativa@tvs.edu.co";
$user = User::where("email", $email)->first();

if (!$user) {
    echo "❌ Usuario no encontrado: $email\n";
    exit(1);
}

echo "✅ Usuario encontrado: {$user->name} (ID: {$user->id})\n";
echo "📧 Email: {$user->email}\n";
echo "🔐 Estado: " . ($user->email_verified_at ? "Verificado" : "No verificado") . "\n";
echo "📅 Creado: {$user->created_at}\n";
echo "📅 Actualizado: {$user->updated_at}\n\n";

// Verificar permisos
echo "=== PERMISOS ===\n";
$hasView = $user->hasPermissionTo("previsitas.view");
echo ($hasView ? "✅" : "❌") . " previsitas.view: " . ($hasView ? "TIENE" : "NO TIENE") . "\n";

$previsitasPermissions = $user->permissions->filter(function($perm) {
    return strpos($perm->name, "previsitas") !== false;
});

echo "📋 Permisos de previsitas del usuario:\n";
foreach ($previsitasPermissions as $perm) {
    echo "   - {$perm->name}\n";
}

// Verificar roles
echo "\n=== ROLES ===\n";
$roles = $user->roles;
echo "👤 Roles del usuario:\n";
foreach ($roles as $role) {
    echo "   - {$role->name}\n";
    
    $rolePrevisitasPerms = $role->permissions->filter(function($perm) {
        return strpos($perm->name, "previsitas") !== false;
    });
    
    if ($rolePrevisitasPerms->count() > 0) {
        echo "     Permisos de previsitas en este rol:\n";
        foreach ($rolePrevisitasPerms as $perm) {
            echo "       - {$perm->name}\n";
        }
    }
}

echo "\n" . ($hasView ? "✅ USUARIO TIENE ACCESO CORRECTO" : "❌ USUARIO SIN ACCESO") . "\n";
?>';

// Script 3: Verificar diferencias de entorno
$checkEnvironmentScript = '<?php
require_once __DIR__ . "/vendor/autoload.php";
$app = require_once __DIR__ . "/bootstrap/app.php";
$kernel = $app->make(Illuminate\\Contracts\\Console\\Kernel::class);
$kernel->bootstrap();

echo "=== VERIFICACIÓN DE ENTORNO ===\n";
echo "🌍 APP_ENV: " . env("APP_ENV", "no definido") . "\n";
echo "🔧 APP_DEBUG: " . (env("APP_DEBUG", false) ? "true" : "false") . "\n";
echo "💾 CACHE_DRIVER: " . env("CACHE_DRIVER", "no definido") . "\n";
echo "📊 DB_CONNECTION: " . env("DB_CONNECTION", "no definido") . "\n";
echo "🔐 SESSION_DRIVER: " . env("SESSION_DRIVER", "no definido") . "\n\n";

echo "=== VERIFICACIÓN DE ARCHIVOS ===\n";
$files = [
    "config/adminlte.php" => config_path("adminlte.php"),
    "config/app.php" => config_path("app.php"),
    "config/permission.php" => config_path("permission.php")
];

foreach ($files as $name => $path) {
    if (file_exists($path)) {
        $size = filesize($path);
        $modified = date("Y-m-d H:i:s", filemtime($path));
        echo "✅ $name: EXISTE ($size bytes, modificado: $modified)\n";
    } else {
        echo "❌ $name: NO EXISTE\n";
    }
}

echo "\n=== VERIFICACIÓN DE CACHE ===\n";
try {
    $cacheWorks = cache()->put("test_key", "test_value", 60);
    $cacheValue = cache()->get("test_key");
    echo ($cacheValue === "test_value" ? "✅" : "❌") . " Cache funcionando: " . ($cacheValue === "test_value" ? "SÍ" : "NO") . "\n";
    cache()->forget("test_key");
} catch (Exception $e) {
    echo "❌ Error en cache: {$e->getMessage()}\n";
}

echo "\n=== COMANDOS RECOMENDADOS ===\n";
echo "Si hay problemas, ejecutar en orden:\n";
echo "1. php artisan config:clear\n";
echo "2. php artisan cache:clear\n";
echo "3. php artisan permission:cache-reset\n";
echo "4. php artisan view:clear\n";
echo "5. php artisan config:cache\n";
?>';

// Crear los archivos
file_put_contents('verify_menu_production.php', $verifyMenuScript);
file_put_contents('check_user_production.php', $checkUserScript);
file_put_contents('check_environment_production.php', $checkEnvironmentScript);

echo "✅ Scripts creados exitosamente:\n";
echo "   - verify_menu_production.php\n";
echo "   - check_user_production.php\n";
echo "   - check_environment_production.php\n\n";

echo "📋 INSTRUCCIONES PARA PRODUCCIÓN:\n";
echo "1. Subir estos 3 archivos al servidor de producción\n";
echo "2. Ejecutar en orden:\n";
echo "   php verify_menu_production.php\n";
echo "   php check_user_production.php\n";
echo "   php check_environment_production.php\n\n";

echo "3. Enviar los resultados para análisis\n\n";

echo "🔧 Si los scripts muestran que todo está correcto:\n";
echo "   - El problema puede ser específico del navegador del usuario\n";
echo "   - Solicitar al usuario que use modo incógnito\n";
echo "   - Verificar si otros usuarios tienen el mismo problema\n\n";

echo "¡Scripts de diagnóstico listos para producción!\n";