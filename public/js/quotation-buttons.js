/**
 * Gestión dinámica de botones para la selección mixta de proveedores
 * Este archivo maneja la visibilidad de los botones "Guardar y Enviar" y "Finalizar Selección"
 * basándose en el estado actual de las selecciones
 */

class QuotationButtonManager {
    constructor() {
        this.totalItems = 0;
        this.initialized = false;
        this.init();
    }

    init() {
        // Esperar a que el DOM esté listo
        $(document).ready(() => {
            this.setup();
        });
    }

    setup() {
        // Obtener el total de items desde el atributo data o contando las filas
        this.totalItems = parseInt($('#total-items-count').val()) || 
                         parseInt($('#dynamic-total-count').text()) || 
                         $('table tbody tr').length;

        console.log('QuotationButtonManager inicializado. Total items:', this.totalItems);

        // Realizar la primera actualización
        this.updateButtons();

        // Configurar observadores
        this.setupObservers();

        // Registrar la función global
        window.updateSelectionButtons = () => {
            console.log('updateSelectionButtons llamada externamente');
            this.updateButtons();
        };

        this.initialized = true;
    }

    setupObservers() {
        // Observer para cambios en el DOM
        const observer = new MutationObserver((mutations) => {
            let shouldUpdate = false;

            mutations.forEach((mutation) => {
                if (mutation.type === 'childList') {
                    // Verificar si se añadieron o eliminaron elementos relevantes
                    const addedNodes = Array.from(mutation.addedNodes);
                    const removedNodes = Array.from(mutation.removedNodes);

                    const hasRelevantChanges = [...addedNodes, ...removedNodes].some(node => {
                        if (node.nodeType === Node.ELEMENT_NODE) {
                            return node.classList.contains('selected-provider') ||
                                   node.querySelector && node.querySelector('.selected-provider') ||
                                   node.classList.contains('selection-area') ||
                                   node.querySelector && node.querySelector('.selection-area');
                        }
                        return false;
                    });

                    if (hasRelevantChanges) {
                        shouldUpdate = true;
                    }
                }
            });

            if (shouldUpdate) {
                console.log('Cambios relevantes detectados por MutationObserver');
                setTimeout(() => this.updateButtons(), 50);
            }
        });

        // Observar la tabla principal
        const table = document.querySelector('table tbody');
        if (table) {
            observer.observe(table, {
                childList: true,
                subtree: true
            });
        }

        // Escuchar eventos AJAX como respaldo
        $(document).on('ajaxSuccess', (event, xhr, settings) => {
            if (settings.url && (
                settings.url.includes('/quotation-selections/select-item') ||
                settings.url.includes('/quotation-selections/remove')
            )) {
                console.log('AJAX success detectado para selección:', settings.url);
                setTimeout(() => this.updateButtons(), 100);
            }
        });

        // Escuchar clics en botones como respaldo adicional
        $(document).on('click', '.remove-selection-btn', () => {
            setTimeout(() => this.updateButtons(), 300);
        });
    }

    getCurrentSelections() {
        return $('.selected-provider').length;
    }

    updateButtons() {
        if (!this.initialized) {
            console.log('ButtonManager aún no inicializado, saltando actualización');
            return;
        }

        const currentSelections = this.getCurrentSelections();
        
        console.log(`Actualizando botones: ${currentSelections}/${this.totalItems} selecciones`);

        // Ocultar todos los elementos primero
        this.hideAllElements();

        // Actualizar contadores dinámicos
        $('#dynamic-selected-count').text(currentSelections);
        $('#dynamic-total-count').text(this.totalItems);
        $('#dynamic-progress-count').text(currentSelections);
        $('#dynamic-progress-total').text(this.totalItems);

        // Mostrar elementos apropiados según el estado
        if (currentSelections === this.totalItems && currentSelections > 0) {
            // Selección completa
            console.log('Mostrando elementos de selección completa');
            $('#dynamic-complete-alert').fadeIn(300);
            $('#dynamic-complete-buttons').fadeIn(300);
        } else if (currentSelections > 0) {
            // Selección parcial
            console.log('Mostrando elementos de selección parcial');
            $('#dynamic-partial-alert').fadeIn(300);
            $('#dynamic-partial-buttons').fadeIn(300);
        } else {
            // Sin selecciones
            console.log('Mostrando alerta sin selecciones');
            $('#dynamic-no-selection-alert').fadeIn(300);
        }
    }

    hideAllElements() {
        // Ocultar elementos dinámicos
        $('#dynamic-complete-alert, #dynamic-complete-buttons').hide();
        $('#dynamic-partial-alert, #dynamic-partial-buttons').hide();
        $('#dynamic-no-selection-alert').hide();

        // También ocultar elementos estáticos para evitar duplicados
        $('#complete-selection-alert, #complete-buttons').hide();
        $('#partial-selection-alert, #partial-buttons').hide();
        $('#no-selection-alert').hide();
    }

    // Método público para forzar una actualización
    forceUpdate() {
        console.log('Actualización forzada de botones');
        this.updateButtons();
    }
}

// Inicializar el manager cuando el script se carga
const buttonManager = new QuotationButtonManager();

// Exportar para uso global si es necesario
window.QuotationButtonManager = QuotationButtonManager;
window.quotationButtonManager = buttonManager;
