/**
 * Correcciones para el issue de check-availability
 * 
 * Este script corrige problemas con las llamadas a la ruta incorrecta
 * de check-availability en la página de préstamos de equipos.
 */

document.addEventListener('DOMContentLoaded', function() {
    console.log('Iniciando corrección para check-availability...');
    
    // Interceptar todas las llamadas fetch para corregir URLs incorrectas
    const originalFetch = window.fetch;
    
    window.fetch = function(url, options) {
        if (typeof url === 'string' && url.includes('/equipment/loans/check-availability')) {
            console.log('Interceptando llamada a ruta incorrecta:', url);
            
            // Reemplazar con la ruta correcta
            const correctedUrl = url.replace('/equipment/loans/check-availability', '/equipment/check-availability');
            console.log('Redirigiendo a:', correctedUrl);
            
            return originalFetch(correctedUrl, options)
                .then(response => {
                    if (!response.ok) {
                        console.error('Error en la respuesta después de la corrección:', response.status);
                    }
                    return response;
                })
                .catch(error => {
                    console.error('Error al hacer fetch con URL corregida:', error);
                    throw error;
                });
        }
        
        // Para cualquier otra URL, usar fetch normal
        return originalFetch(url, options);
    };
    
    // Corregir la función updateTimeline si existe
    if (typeof window.updateTimeline !== 'function') {
        console.log('Creando versión corregida de updateTimeline...');
        
        window.updateTimeline = function(date) {
            console.log('updateTimeline llamado con fecha:', date);
            
            const container = document.getElementById('timeline-equipment-container');
            if (!container) {
                console.error('Contenedor timeline-equipment-container no encontrado');
                return;
            }
            
            // Limpiar contenedor
            container.innerHTML = '';
            
            // Obtener todos los préstamos para esta fecha
            const dayLoans = allLoans && Array.isArray(allLoans) 
                ? allLoans.filter(loan => loan.date === date) 
                : [];
            
            if (dayLoans.length === 0) {
                const infoElement = document.querySelector('.timeline-info');
                if (infoElement) {
                    infoElement.classList.remove('d-none');
                    infoElement.textContent = 'No hay préstamos para la fecha seleccionada.';
                }
                return;
            }
            
            const infoElement = document.querySelector('.timeline-info');
            if (infoElement) {
                infoElement.classList.add('d-none');
            }
            
            // Agrupar préstamos por tipo de equipo
            const groupedByType = {};
            dayLoans.forEach(loan => {
                if (!groupedByType[loan.equipment_type]) {
                    groupedByType[loan.equipment_type] = [];
                }
                groupedByType[loan.equipment_type].push(loan);
            });
            
            // Crear la línea de tiempo para cada tipo
            Object.keys(groupedByType).forEach(type => {
                const typeName = type === 'laptop' ? 'Portátil' : 'iPad';
                const typeIcon = type === 'laptop' ? 'fas fa-laptop' : 'fas fa-tablet-alt';
                
                // Crear encabezado para este tipo
                const typeHeader = document.createElement('h6');
                typeHeader.classList.add('mt-3');
                typeHeader.innerHTML = `<i class="${typeIcon}"></i> ${typeName}s`;
                
                // Crear contenedor para este tipo
                const typeContainer = document.createElement('div');
                typeContainer.classList.add('timeline-equipment-type');
                typeContainer.appendChild(typeHeader);
                
                // Añadir cada préstamo
                groupedByType[type].forEach(loan => {
                    // Mostrar solo los que tienen horarios definidos
                    if (!loan.start_time || !loan.end_time) return;
                    
                    const equipmentRow = document.createElement('div');
                    equipmentRow.classList.add('timeline-equipment');
                    
                    const nameDiv = document.createElement('div');
                    nameDiv.classList.add('timeline-equipment-name');
                    nameDiv.innerHTML = `<span class="badge badge-${type === 'laptop' ? 'info' : 'warning'} mr-2">${loan.units_requested}</span> ${loan.grade || 'N/A'}`;
                    
                    const slotsDiv = document.createElement('div');
                    slotsDiv.classList.add('timeline-slots');
                    slotsDiv.id = `timeline-${loan.id}`;
                    
                    equipmentRow.appendChild(nameDiv);
                    equipmentRow.appendChild(slotsDiv);
                    typeContainer.appendChild(equipmentRow);
                    
                    // Esperar a que esté en el DOM
                    setTimeout(() => {
                        addLoanSlot(loan, type);
                    }, 100);
                });
                
                container.appendChild(typeContainer);
            });
        };
        
        function addLoanSlot(loan, type) {
            const slotContainer = document.getElementById(`timeline-${loan.id}`);
            if (!slotContainer) return;
            
            // Convertir horas a porcentaje
            const startParts = loan.start_time.split(':');
            const endParts = loan.end_time.split(':');
            
            if (startParts.length !== 2 || endParts.length !== 2) return;
            
            const startHour = parseInt(startParts[0]) + parseInt(startParts[1]) / 60;
            const endHour = parseInt(endParts[0]) + parseInt(endParts[1]) / 60;
            
            // Calcular posición (de 7 a 18 horas)
            const left = ((startHour - 7) / 11) * 100;
            const width = ((endHour - startHour) / 11) * 100;
            
            // Determinar color según estado
            const statusClass = loan.status === 'pending' ? 'warning' :
                              loan.status === 'delivered' ? 'info' :
                              loan.status === 'returned' ? 'success' : 'secondary';
            
            const slot = document.createElement('div');
            slot.classList.add('timeline-slot', `timeline-slot-${type}`, `badge-${statusClass}`);
            slot.style.left = `${left}%`;
            slot.style.width = `${width}%`;
            slot.style.backgroundColor = `var(--${statusClass})`;
            slot.dataset.loanId = loan.id;
            slot.title = `${loan.start_time} - ${loan.end_time}`;
            slot.textContent = `${loan.start_time}-${loan.end_time}`;
            
            slot.addEventListener('click', function() {
                if (typeof showLoanDetails === 'function') {
                    showLoanDetails(loan.id);
                }
            });
            
            slotContainer.appendChild(slot);
        }
    }
});
