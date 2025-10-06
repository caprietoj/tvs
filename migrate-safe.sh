#!/bin/bash

###############################################################################
# Script de Migración Segura para Producción
# Uso: ./migrate-safe.sh [--dry-run] [--force] [--no-backup]
###############################################################################

set -e  # Salir si hay error

# Colores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuración
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="backups"
DB_NAME="${DB_DATABASE:-tvs}"
DRY_RUN=false
FORCE=false
NO_BACKUP=false

# Funciones de utilidad
print_header() {
    echo -e "\n${BLUE}========================================${NC}"
    echo -e "${BLUE}$1${NC}"
    echo -e "${BLUE}========================================${NC}\n"
}

print_success() {
    echo -e "${GREEN}✅ $1${NC}"
}

print_error() {
    echo -e "${RED}❌ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

print_info() {
    echo -e "${BLUE}ℹ️  $1${NC}"
}

# Procesar argumentos
while [[ $# -gt 0 ]]; do
    case $1 in
        --dry-run)
            DRY_RUN=true
            shift
            ;;
        --force)
            FORCE=true
            shift
            ;;
        --no-backup)
            NO_BACKUP=true
            shift
            ;;
        --help)
            echo "Uso: $0 [opciones]"
            echo ""
            echo "Opciones:"
            echo "  --dry-run     Mostrar qué se haría sin hacer cambios"
            echo "  --force       No pedir confirmación"
            echo "  --no-backup   No hacer backup de la base de datos"
            echo "  --help        Mostrar esta ayuda"
            exit 0
            ;;
        *)
            print_error "Opción desconocida: $1"
            echo "Usa --help para ver las opciones disponibles"
            exit 1
            ;;
    esac
done

# Banner
print_header "🚀 Script de Migración Segura para Producción"

# Verificar que estamos en el directorio correcto
if [ ! -f "artisan" ]; then
    print_error "No se encontró el archivo 'artisan'. ¿Estás en el directorio raíz de Laravel?"
    exit 1
fi

print_success "Directorio correcto detectado"

# Verificar conexión a la base de datos
print_info "Verificando conexión a la base de datos..."
if ! php artisan tinker --execute="DB::connection()->getPdo(); echo 'OK';" > /dev/null 2>&1; then
    print_error "No se pudo conectar a la base de datos"
    exit 1
fi
print_success "Conexión a la base de datos OK"

# Backup de la base de datos (si no se especificó --no-backup)
if [ "$NO_BACKUP" = false ] && [ "$DRY_RUN" = false ]; then
    print_header "📦 Creando backup de la base de datos"
    
    mkdir -p "$BACKUP_DIR"
    BACKUP_FILE="${BACKUP_DIR}/backup_${DB_NAME}_${TIMESTAMP}.sql"
    
    print_info "Creando backup en: $BACKUP_FILE"
    
    if mysqldump -u root "$DB_NAME" > "$BACKUP_FILE" 2>/dev/null; then
        print_success "Backup creado exitosamente: $BACKUP_FILE"
        
        # Comprimir backup
        if command -v gzip &> /dev/null; then
            gzip "$BACKUP_FILE"
            print_success "Backup comprimido: ${BACKUP_FILE}.gz"
        fi
    else
        print_warning "No se pudo crear el backup automático"
        if [ "$FORCE" = false ]; then
            read -p "¿Deseas continuar sin backup? (y/N): " -n 1 -r
            echo
            if [[ ! $REPLY =~ ^[Yy]$ ]]; then
                print_error "Operación cancelada"
                exit 1
            fi
        fi
    fi
fi

# Verificar estado de migraciones
print_header "🔍 Verificando estado de migraciones"

php artisan migrate:status

# Ejecutar comando de registro
print_header "📝 Registrando migraciones existentes"

if [ "$DRY_RUN" = true ]; then
    print_warning "MODO DRY-RUN: Solo se mostrarán los cambios sin aplicarlos"
    php artisan migrate:register-existing --dry-run
    exit 0
fi

# Ejecutar el registro
if [ "$FORCE" = true ]; then
    php artisan migrate:register-existing --force
else
    php artisan migrate:register-existing
fi

# Preguntar si continuar con migrate
echo ""
if [ "$FORCE" = false ]; then
    read -p "¿Deseas ejecutar 'php artisan migrate' ahora? (Y/n): " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Nn]$ ]]; then
        print_warning "Migraciones registradas pero no ejecutadas"
        print_info "Ejecuta 'php artisan migrate' cuando estés listo"
        exit 0
    fi
fi

# Ejecutar migraciones
print_header "⚙️  Ejecutando migraciones pendientes"

if php artisan migrate --force; then
    print_success "Migraciones ejecutadas exitosamente"
else
    print_error "Error al ejecutar migraciones"
    print_warning "Revisa los logs en storage/logs/laravel.log"
    exit 1
fi

# Verificar estado final
print_header "✅ Verificando estado final"

php artisan migrate:status | tail -20

# Resumen
print_header "🎉 Proceso completado"

if [ "$NO_BACKUP" = false ]; then
    print_info "Backup guardado en: $BACKUP_DIR/"
fi

print_success "Todas las migraciones han sido ejecutadas correctamente"
print_info "Revisa el estado arriba para confirmar"

# Recomendaciones finales
echo ""
print_warning "Recomendaciones post-migración:"
echo "  1. Probar funcionalidad de los módulos afectados"
echo "  2. Revisar logs: tail -f storage/logs/laravel.log"
echo "  3. Verificar que no haya errores en la aplicación"
echo "  4. Monitorear el rendimiento"

exit 0
