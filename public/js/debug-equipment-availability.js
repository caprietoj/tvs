/**
 * Script de depuración para verificar la disponibilidad de equipos
 */
document.addEventListener('DOMContentLoaded', function() {
    // Verificar si estamos en la página de solicitud de equipos
    if (window.location.pathname.includes('/equipment/request')) {
        console.log('Debug: Script de verificación de disponibilidad activo');
        
        // Elementos a monitorizar
        const elementsToWatch = [
            document.getElementById('equipment-select'),
            document.getElementById('units-available-text'),
            document.getElementById('units-input'),
            document.getElementById('available-units-value'),
            document.getElementById('total-units-value')
        ];
        
        // Para cada elemento, verificar si existe
        elementsToWatch.forEach(element => {
            if (element) {
                // Agregar observador para cambios
                const observer = new MutationObserver(function(mutations) {
                    console.log('Cambio detectado en:', element.id, {
                        value: element.value || element.textContent,
                        attribute: element.getAttribute('max'),
                        time: new Date().toLocaleTimeString()
                    });
                });
                
                // Opciones de observación
                const config = {
                    attributes: true,
                    childList: true,
                    characterData: true,
                    subtree: true
                };
                
                // Iniciar observación
                observer.observe(element, config);
                
                // También registrar el valor inicial
                console.log('Valor inicial de', element.id, {
                    value: element.value || element.textContent,
                    attribute: element.getAttribute('max'),
                    time: new Date().toLocaleTimeString()
                });
            } else {
                console.warn('Elemento no encontrado:', element);
            }
        });
        
        // Verificar también cada vez que se envíe una solicitud
        const form = document.getElementById('loanRequestForm');
        if (form) {
            form.addEventListener('submit', function(event) {
                // No prevenir el envío, solo registrar datos
                console.group('Datos al enviar el formulario');
                console.log('Sección:', document.getElementById('section-select').value);
                console.log('Equipo:', document.getElementById('equipment-select').value);
                console.log('Unidades solicitadas:', document.getElementById('units-input').value);
                console.log('Unidades disponibles:', document.getElementById('units-available-text').textContent);
                console.log('Fecha:', document.getElementById('loan-date-input').value);
                console.log('Hora inicio:', document.getElementById('start-time-input').value);
                console.log('Hora fin:', document.getElementById('end-time-input').value);
                console.groupEnd();
            });
        }
        
        // Capturar todas las peticiones AJAX
        const originalFetch = window.fetch;
        window.fetch = function() {
            const url = arguments[0];
            
            // Solo monitorear las peticiones relevantes para disponibilidad
            if (url && typeof url === 'string' && 
                (url.includes('check-availability') || url.includes('equipment/types'))) {
                console.group('Petición AJAX:', url);
                console.log('Argumentos:', arguments);
                
                return originalFetch.apply(this, arguments)
                    .then(response => {
                        // Clonar la respuesta para poder leerla múltiples veces
                        const responseClone = response.clone();
                        responseClone.json().then(data => {
                            console.log('Respuesta recibida:', data);
                            console.groupEnd();
                        });
                        return response;
                    });
            }
            
            return originalFetch.apply(this, arguments);
        };
    }
});
