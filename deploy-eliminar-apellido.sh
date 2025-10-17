#!/bin/bash
# Script para aplicar cambios de eliminación de apellido en PRODUCCIÓN
# Ejecutar este script en el servidor de producción

echo "🚀 Iniciando actualización en PRODUCCIÓN..."
echo ""

# Colores para mensajes
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# 1. Hacer backup de la base de datos
echo -e "${YELLOW}📦 Paso 1: Creando backup de la base de datos...${NC}"
FECHA=$(date +%Y%m%d_%H%M%S)
php artisan db:backup --filename="backup_antes_eliminar_apellido_${FECHA}.sql" 2>/dev/null || echo "Backup manual recomendado"
echo ""

# 2. Verificar estado actual
echo -e "${YELLOW}🔍 Paso 2: Verificando estructura actual...${NC}"
php artisan tinker --execute="echo Schema::hasColumn('personas', 'apellido') ? 'La columna apellido EXISTE' : 'La columna apellido NO EXISTE'; exit();"
echo ""

# 3. Descargar últimos cambios
echo -e "${YELLOW}📥 Paso 3: Descargando últimos cambios del repositorio...${NC}"
git pull origin main
if [ $? -ne 0 ]; then
    echo -e "${RED}❌ Error al hacer git pull${NC}"
    exit 1
fi
echo ""

# 4. Instalar/actualizar dependencias si es necesario
echo -e "${YELLOW}📚 Paso 4: Verificando dependencias de Composer...${NC}"
composer install --no-dev --optimize-autoloader
echo ""

# 5. Poner aplicación en modo mantenimiento
echo -e "${YELLOW}🛠️  Paso 5: Poniendo aplicación en modo mantenimiento...${NC}"
php artisan down --message="Actualización en progreso" --retry=60
echo ""

# 6. Ejecutar migraciones
echo -e "${YELLOW}🗄️  Paso 6: Ejecutando migraciones...${NC}"
php artisan migrate --force
if [ $? -ne 0 ]; then
    echo -e "${RED}❌ Error en las migraciones${NC}"
    php artisan up
    exit 1
fi
echo ""

# 7. Limpiar cachés
echo -e "${YELLOW}🧹 Paso 7: Limpiando cachés...${NC}"
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
echo ""

# 8. Optimizar aplicación
echo -e "${YELLOW}⚡ Paso 8: Optimizando aplicación...${NC}"
php artisan config:cache
php artisan route:cache
php artisan view:cache
echo ""

# 9. Verificar que la migración se aplicó correctamente
echo -e "${YELLOW}✅ Paso 9: Verificando resultado...${NC}"
php artisan tinker --execute="echo Schema::hasColumn('personas', 'apellido') ? 'ADVERTENCIA: La columna apellido todavía existe' : 'OK: La columna apellido fue eliminada correctamente'; exit();"
echo ""

# 10. Verificar que no hay errores
echo -e "${YELLOW}🔍 Paso 10: Verificando integridad...${NC}"
php artisan tinker --execute="echo 'Total de personas: ' . App\\Models\\Persona::count(); exit();"
echo ""

# 11. Sacar aplicación del modo mantenimiento
echo -e "${YELLOW}✅ Paso 11: Activando aplicación...${NC}"
php artisan up
echo ""

echo -e "${GREEN}✅ ¡Actualización completada exitosamente!${NC}"
echo ""
echo "Próximos pasos:"
echo "1. Probar la importación de personas en: https://intranet.tvs.edu.co/porteria/personas/import"
echo "2. Verificar que los formularios funcionen correctamente"
echo "3. Ejecutar: php artisan personas:corregir-datos (si hay registros antiguos)"
echo ""
