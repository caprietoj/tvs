/**
 * Event Monitor - Script para monitorear problemas en el registro de eventos
 * Este script registra en consola cualquier problema durante el envío del formulario de eventos
 */
document.addEventListener('DOMContentLoaded', function() {
    // Obtener referencia al formulario de eventos
    const eventForm = document.getElementById('eventForm');
    
    if (!eventForm) {
        console.error('[Monitor de Eventos] No se encontró el formulario de eventos en la página');
        return;
    }

    console.info('[Monitor de Eventos] Inicializado correctamente');

    // Registrar datos originales antes de enviar el formulario
    const logFormData = () => {
        const formData = new FormData(eventForm);
        console.group('[Monitor de Eventos] Datos del formulario a enviar:');
        for (const [key, value] of formData.entries()) {
            // Detectar posibles problemas en los valores
            if (typeof value === 'string') {
                // Verificar si hay caracteres problemáticos
                if (value.includes('"') || value.includes("'") || value.includes('\\')) {
                    console.warn(`[ADVERTENCIA] El campo "${key}" contiene caracteres que podrían causar problemas: ${value}`);
                }
                // Verificar campos vacíos que deberían tener valor
                if (value.trim() === '' && eventForm.elements[key].required) {
                    console.error(`[ERROR] El campo "${key}" es requerido pero está vacío`);
                }
                // Validar formato de fecha
                if ((key.includes('date') || key.includes('_date')) && !/^\d{4}-\d{2}-\d{2}$/.test(value) && value.trim() !== '') {
                    console.warn(`[ADVERTENCIA] El campo "${key}" podría tener un formato de fecha incorrecto: ${value}`);
                }
            }
            console.log(`${key}: ${value}`);
        }
        console.groupEnd();
    };

    // Verificar campos requeridos
    const checkRequiredFields = () => {
        const requiredFields = eventForm.querySelectorAll('[required]');
        let allValid = true;
        
        requiredFields.forEach(field => {
            if (field.value.trim() === '') {
                console.error(`[ERROR] Campo requerido "${field.name}" está vacío`);
                allValid = false;
            }
        });
        
        return allValid;
    };

    // Detectar problemas en servicios seleccionados
    const checkServiceRequirements = () => {
        const services = [
            'metro_junior_required',
            'general_services_required',
            'maintenance_required',
            'systems_required',
            'aldimark_required',
            'purchases_required',
            'communications_required'
        ];
        
        services.forEach(serviceId => {
            const checkbox = document.getElementById(serviceId);
            if (checkbox && checkbox.checked) {
                // Verificar si se completaron los campos asociados a este servicio
                const serviceFields = document.getElementById(serviceId.replace('_required', '_fields'));
                if (serviceFields) {
                    const requiredInputs = serviceFields.querySelectorAll('input[required]');
                    requiredInputs.forEach(input => {
                        if (input.value.trim() === '') {
                            console.warn(`[ADVERTENCIA] Servicio "${serviceId}" seleccionado pero el campo "${input.name}" está vacío`);
                        }
                    });
                }
            }
        });
    };

    // Interceptar envío de formulario
    eventForm.addEventListener('submit', function(e) {
        console.group('[Monitor de Eventos] Enviando formulario...');
        console.time('Tiempo de procesamiento');
        
        // Registrar metadatos del formulario
        console.info(`URL: ${this.action}`);
        console.info(`Método: ${this.method.toUpperCase()}`);
        console.info(`Usuario: ${document.querySelector('meta[name="user-id"]')?.content || 'No identificado'}`);
        
        // Verificar campos requeridos
        const fieldsValid = checkRequiredFields();
        if (!fieldsValid) {
            console.warn('[ADVERTENCIA] Hay campos requeridos vacíos');
        }
        
        // Verificar servicios seleccionados
        checkServiceRequirements();
        
        // Registrar datos del formulario
        logFormData();
        
        console.groupEnd();
        
        // No bloqueamos el envío, solo registramos los problemas
    });

    // Capturar errores de respuesta
    const originalFetch = window.fetch;
    window.fetch = function(...args) {
        return originalFetch.apply(this, args).then(response => {
            // Capturar solo respuestas relacionadas con eventos
            if (args[0].includes('/events') || (args[1]?.body instanceof FormData && args[1].body.entries().next().value)) {
                if (!response.ok) {
                    console.error(`[ERROR] La respuesta del servidor no fue exitosa: ${response.status} ${response.statusText}`);
                    response.clone().json().catch(() => response.clone().text()).then(data => {
                        console.error('[ERROR] Detalles del error:', data);
                    }).catch(err => {
                        console.error('[ERROR] No se pudieron obtener detalles del error:', err);
                    });
                } else {
                    console.info('[Monitor de Eventos] Respuesta del servidor exitosa');
                    console.timeEnd('Tiempo de procesamiento');
                }
            }
            return response;
        }).catch(error => {
            console.error('[ERROR] Fallo en la petición:', error);
            throw error;
        });
    };
});
