/**
 * Gestión dinámica de botones para la selección mixta de proveedores
 * Este archivo maneja la visibilidad de los botones "Guardar y Enviar" y "Finalizar Selección"
 * basándose en el estado actual de las selecciones
 */

class QuotationButtonManager {
    constructor() {
        this.totalItems = 0;
        this.selectedCount = 0;
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
        console.log('Configurando QuotationButtonManager');
        
        // Obtener el total de items de múltiples fuentes
        this.totalItems = this.getTotalItemsCount();
        
        // Contar selecciones actuales desde el DOM
        this.selectedCount = this.getCurrentSelections();
        
        console.log('QuotationButtonManager inicializado. Total items:', this.totalItems);
        console.log('Selecciones existentes al inicializar:', this.selectedCount);

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
        
        // Forzar una segunda actualización después de un pequeño delay para asegurar que todo esté renderizado
        setTimeout(() => {
            console.log('Forzando segunda actualización después del setup');
            this.forceUpdate();
        }, 250);
    }

    getTotalItemsCount() {
        // Intentar obtener desde diferentes fuentes
        
        // 1. Desde input hidden
        const hiddenInputValue = $('#total-items-count').val();
        if (hiddenInputValue) {
            return parseInt(hiddenInputValue);
        }
        
        // 2. Desde span dinámico
        const spanValue = $('#dynamic-total-count').text();
        if (spanValue && spanValue !== '0') {
            return parseInt(spanValue);
        }
        
        // 3. Desde variable JavaScript global (si existe)
        if (window.totalItemsCount !== undefined) {
            return parseInt(window.totalItemsCount);
        }
        
        // 4. Contar filas de la tabla (excluyendo header y footer)
        const tableRows = $('table tbody tr[id^="item-row-"]');
        if (tableRows.length > 0) {
            return tableRows.length;
        }
        
        // 5. Contar desde cualquier elemento con data-item-index
        const itemElements = $('[data-item-index]');
        if (itemElements.length > 0) {
            // Obtener el índice más alto + 1
            let maxIndex = -1;
            itemElements.each(function() {
                const index = parseInt($(this).attr('data-item-index'));
                if (index > maxIndex) {
                    maxIndex = index;
                }
            });
            return maxIndex + 1;
        }
        
        console.warn('No se pudo determinar el total de items');
        return 0;
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
        // Contar elementos con la clase .selected-provider que sean visibles
        const selectedProviders = $('.selected-provider');
        let visibleCount = 0;
        
        selectedProviders.each(function() {
            // Verificar que el elemento sea visible y no esté oculto
            if ($(this).is(':visible') && $(this).css('display') !== 'none') {
                visibleCount++;
            }
        });
        
        return visibleCount;
    }

    updateButtons() {
        if (!this.initialized) {
            console.log('ButtonManager aún no inicializado, saltando actualización');
            return;
        }

        // Actualizar conteo actual
        this.selectedCount = this.getCurrentSelections();
        
        console.log(`Actualizando botones: ${this.selectedCount}/${this.totalItems} selecciones`);
        console.log('Elementos .selected-provider encontrados:', $('.selected-provider').length);

        // Ocultar todos los elementos primero
        this.hideAllElements();

        // Actualizar contadores dinámicos
        $('#dynamic-selected-count').text(this.selectedCount);
        $('#dynamic-total-count').text(this.totalItems);
        $('#dynamic-progress-count').text(this.selectedCount);
        $('#dynamic-progress-total').text(this.totalItems);

        // Mostrar elementos apropiados según el estado
        if (this.selectedCount === this.totalItems && this.selectedCount > 0) {
            // Selección completa
            console.log('Mostrando elementos de selección completa');
            $('#dynamic-complete-alert').show().fadeIn(300);
            $('#dynamic-complete-buttons').show().fadeIn(300);
        } else if (this.selectedCount > 0) {
            // Selección parcial
            console.log('Mostrando elementos de selección parcial');
            $('#dynamic-partial-alert').show().fadeIn(300);
            $('#dynamic-partial-buttons').show().fadeIn(300);
        } else {
            // Sin selecciones
            console.log('Mostrando alerta sin selecciones');
            $('#dynamic-no-selection-alert').show().fadeIn(300);
        }
        
        // Debug: Log del estado final
        console.log('Estado final de elementos:');
        console.log('- Complete alert visible:', $('#dynamic-complete-alert').is(':visible'));
        console.log('- Complete buttons visible:', $('#dynamic-complete-buttons').is(':visible'));
        console.log('- Partial alert visible:', $('#dynamic-partial-alert').is(':visible'));
        console.log('- Partial buttons visible:', $('#dynamic-partial-buttons').is(':visible'));
        console.log('- No selection alert visible:', $('#dynamic-no-selection-alert').is(':visible'));
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

    // Método público para forzar una actualización con valores específicos
    forceUpdate(selectedCount = null, totalItems = null) {
        console.log('Actualización forzada de botones');
        
        if (selectedCount !== null) {
            this.selectedCount = selectedCount;
        } else {
            this.selectedCount = this.getCurrentSelections();
        }
        
        if (totalItems !== null) {
            this.totalItems = totalItems;
        } else {
            this.totalItems = this.getTotalItemsCount();
        }
        
        console.log('Valores actualizados - Selecciones:', this.selectedCount, 'Total:', this.totalItems);
        this.updateButtons();
    }

    // Métodos llamados cuando hay cambios en las selecciones
    onSelectionAdded() {
        console.log('Selección agregada');
        setTimeout(() => this.updateButtons(), 100);
    }

    onSelectionRemoved() {
        console.log('Selección eliminada');
        setTimeout(() => this.updateButtons(), 100);
    }
}

// Inicializar el manager cuando el script se carga
const buttonManager = new QuotationButtonManager();

// Exportar para uso global si es necesario
window.QuotationButtonManager = QuotationButtonManager;
window.quotationButtonManager = buttonManager;
