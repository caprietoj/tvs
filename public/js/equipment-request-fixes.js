/**
 * Correcciones para el módulo de solicitud de equipos
 * Este archivo corrige problemas con las funciones de disponibilidad
 */

document.addEventListener('DOMContentLoaded', function() {
    console.log('Aplicando correcciones para solicitud de equipos');
    
    // Asegurarse de que la función checkAvailability está disponible globalmente
    if (typeof window.checkAvailability !== 'function') {
        window.checkAvailability = function() {
            console.log('Verificando disponibilidad de equipos');
            
            const elements = {
                equipmentSelect: document.getElementById('equipment-select'),
                loanDateInput: document.getElementById('loan-date-input'),
                sectionSelect: document.getElementById('section-select'),
                startTimeInput: document.getElementById('start-time-input'),
                endTimeInput: document.getElementById('end-time-input'),
                availabilityInfo: document.getElementById('availability-info'),
                totalUnitsValue: document.getElementById('total-units-value'),
                occupiedUnitsValue: document.getElementById('occupied-units-value'),
                availableUnitsValue: document.getElementById('available-units-value'),
                unitsAvailableText: document.getElementById('units-available-text'),
                unitsSummary: document.getElementById('units-summary'),
                unitsInput: document.getElementById('units-input'),
                unitsHelpText: document.getElementById('units-help-text'),
                timelineContainer: document.getElementById('timeline-container'),
                timelineSlots: document.getElementById('timeline-slots'),
                timelineSelection: document.getElementById('timeline-selection'),
                timeSlotWarning: document.getElementById('time-slot-warning')
            };
            
            const equipmentId = elements.equipmentSelect.value;
            const loanDate = elements.loanDateInput.value;
            const section = elements.sectionSelect.value;
            const startTime = elements.startTimeInput.value;
            const endTime = elements.endTimeInput.value;
            
            if (!equipmentId || !loanDate || !section) {
                return;
            }
            
            // Mostrar spinner mientras carga
            elements.availabilityInfo.classList.remove('d-none');
            elements.availabilityInfo.innerHTML = `
                <div class="d-flex justify-content-center p-3">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Verificando disponibilidad...</span>
                    </div>
                </div>
            `;
            
            const formData = new FormData();
            formData.append('equipment_id', equipmentId);
            formData.append('loan_date', loanDate);
            formData.append('section', section);
            formData.append('_token', document.querySelector('input[name="_token"]').value);
            
            // Si hay horarios seleccionados, incluirlos en la solicitud
            if (startTime && endTime) {
                formData.append('start_time', startTime);
                formData.append('end_time', endTime);
            }
            
            fetch('/equipment/check-availability', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) throw new Error('Error al verificar disponibilidad');
                return response.json();
            })
            .then(data => {
                if (typeof window.updateAvailabilityInfo === 'function') {
                    window.updateAvailabilityInfo(data);
                } else {
                    console.error('La función updateAvailabilityInfo no está disponible');
                }
            })
            .catch(error => {
                elements.availabilityInfo.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i> Error: ${error.message}
                    </div>
                `;
                console.error('Error al verificar disponibilidad:', error);
            });
        };
    }
    
    // Asegurarse de que la función updateAvailabilityInfo está disponible globalmente
    if (typeof window.updateAvailabilityInfo !== 'function') {
        window.updateAvailabilityInfo = function(data) {
            console.log('Actualizando información de disponibilidad');
            
            const elements = {
                equipmentSelect: document.getElementById('equipment-select'),
                loanDateInput: document.getElementById('loan-date-input'),
                sectionSelect: document.getElementById('section-select'),
                startTimeInput: document.getElementById('start-time-input'),
                endTimeInput: document.getElementById('end-time-input'),
                availabilityInfo: document.getElementById('availability-info'),
                totalUnitsValue: document.getElementById('total-units-value'),
                occupiedUnitsValue: document.getElementById('occupied-units-value'),
                availableUnitsValue: document.getElementById('available-units-value'),
                unitsAvailableText: document.getElementById('units-available-text'),
                unitsSummary: document.getElementById('units-summary'),
                unitsInput: document.getElementById('units-input'),
                unitsHelpText: document.getElementById('units-help-text'),
                timelineContainer: document.getElementById('timeline-container'),
                timelineSlots: document.getElementById('timeline-slots'),
                timelineSelection: document.getElementById('timeline-selection'),
                timeSlotWarning: document.getElementById('time-slot-warning')
            };
            
            // Actualizar contadores
            if (elements.totalUnitsValue) elements.totalUnitsValue.textContent = data.total_units;
            if (elements.occupiedUnitsValue) elements.occupiedUnitsValue.textContent = data.occupied_units;
            if (elements.availableUnitsValue) elements.availableUnitsValue.textContent = data.available_units;
            if (elements.unitsAvailableText) elements.unitsAvailableText.textContent = data.available_units;
            
            // Actualizar el atributo max del campo de unidades
            if (elements.unitsInput) {
                elements.unitsInput.max = data.available_units;
                if (elements.unitsSummary) elements.unitsSummary.classList.remove('d-none');
            }
            
            // Actualizar texto de ayuda para el campo de unidades
            if (elements.unitsHelpText) {
                if (data.available_units > 0) {
                    elements.unitsHelpText.textContent = `Puede solicitar hasta ${data.available_units} unidades`;
                    elements.unitsHelpText.classList.remove('text-danger', 'text-info');
                    elements.unitsHelpText.classList.add('text-muted');
                } else {
                    elements.unitsHelpText.textContent = "Busque otro horario para encontrar disponibilidad";
                    elements.unitsHelpText.classList.remove('text-muted', 'text-danger');
                    elements.unitsHelpText.classList.add('text-info');
                }
            }
            
            // Actualizar la información de disponibilidad
            if (elements.availabilityInfo) {
                elements.availabilityInfo.innerHTML = '';
                
                // Verificar si hay horarios seleccionados
                const startTime = elements.startTimeInput ? elements.startTimeInput.value : '';
                const endTime = elements.endTimeInput ? elements.endTimeInput.value : '';
                const hasSelectedTime = startTime && endTime;
                
                if (data.available_units > 0) {
                    if (hasSelectedTime) {
                        elements.availabilityInfo.innerHTML = `
                            <div class="alert alert-success">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-check-circle fa-lg mr-3"></i>
                                    <div>
                                        <span class="font-weight-bold">¡Disponible!</span> 
                                        <span>Hay ${data.available_units} unidades disponibles para el horario ${startTime} - ${endTime}.</span>
                                    </div>
                                </div>
                            </div>
                        `;
                    } else {
                        elements.availabilityInfo.innerHTML = `
                            <div class="alert alert-info">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-info-circle fa-lg mr-3"></i>
                                    <div>
                                        <span class="font-weight-bold">Seleccione un horario.</span> 
                                        <span>Consulte la línea de tiempo para ver la disponibilidad por horas. Actualmente hay ${data.available_units} unidades disponibles.</span>
                                    </div>
                                </div>
                            </div>
                        `;
                    }
                } else {
                    elements.availabilityInfo.innerHTML = `
                        <div class="alert alert-warning">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-exclamation-circle fa-lg mr-3"></i>
                                <div>
                                    <span class="font-weight-bold">Disponibilidad limitada.</span> 
                                    <span>Revise la línea de tiempo para encontrar horarios disponibles o seleccione otro horario.</span>
                                </div>
                            </div>
                        </div>
                    `;
                }
            }
            
            // Actualizar el timeline si es necesario
            if (data.occupied_slots && elements.timelineSlots) {
                updateTimelineSlots(data.occupied_slots);
            }
            
            function updateTimelineSlots(occupiedSlots) {
                if (!occupiedSlots || occupiedSlots.length === 0 || !elements.timelineContainer || !elements.timelineSlots) {
                    if (elements.timelineContainer) elements.timelineContainer.classList.add('d-none');
                    return;
                }
                
                elements.timelineContainer.classList.remove('d-none');
                elements.timelineSlots.innerHTML = '';
                
                // Convertir las horas a minutos desde 7:00 AM
                function timeToMinutes(timeString) {
                    const [hours, minutes] = timeString.split(':').map(Number);
                    return (hours - 7) * 60 + minutes;
                }
                
                // Convertir minutos a porcentaje de ancho (de 7:00 a 17:00 = 10 horas = 600 minutos)
                function minutesToPercent(minutes) {
                    return (minutes / 600) * 100;
                }
                
                // Crear slots en el timeline
                occupiedSlots.forEach((slot, index) => {
                    const startMinutes = timeToMinutes(slot.start);
                    const endMinutes = timeToMinutes(slot.end);
                    const leftPos = minutesToPercent(startMinutes);
                    const width = minutesToPercent(endMinutes - startMinutes);
                    
                    const slotElement = document.createElement('div');
                    slotElement.className = 'timeline-slot';
                    slotElement.style.left = `${leftPos}%`;
                    slotElement.style.width = `${width}%`;
                    slotElement.dataset.start = slot.start;
                    slotElement.dataset.end = slot.end;
                    slotElement.dataset.units = slot.units_taken;
                    slotElement.innerHTML = `${slot.units_taken} u.`;
                    
                    // Tooltip con información
                    slotElement.title = `${slot.start} - ${slot.end} (${slot.units_taken} unidades)`;
                    
                    elements.timelineSlots.appendChild(slotElement);
                });
                
                // Actualizar la selección si es necesario
                if (elements.timelineSelection) {
                    updateTimelineSelection();
                }
            }
            
            // Función para actualizar la selección en el timeline
            function updateTimelineSelection() {
                const startTime = elements.startTimeInput ? elements.startTimeInput.value : '';
                const endTime = elements.endTimeInput ? elements.endTimeInput.value : '';
                
                if (!startTime || !endTime || !elements.timelineSelection) {
                    if (elements.timelineSelection) elements.timelineSelection.classList.add('d-none');
                    return;
                }
                
                // Convertir las horas a minutos desde 7:00 AM
                function timeToMinutes(timeString) {
                    const [hours, minutes] = timeString.split(':').map(Number);
                    return (hours - 7) * 60 + minutes;
                }
                
                // Convertir minutos a porcentaje de ancho (de 7:00 a 17:00 = 10 horas = 600 minutos)
                function minutesToPercent(minutes) {
                    return (minutes / 600) * 100;
                }
                
                const startMinutes = timeToMinutes(startTime);
                const endMinutes = timeToMinutes(endTime);
                
                // Verificar si hay conflictos con otros slots
                let hasConflict = false;
                let maxOccupiedUnits = 0;
                
                const slots = elements.timelineSlots ? elements.timelineSlots.querySelectorAll('.timeline-slot') : [];
                
                // Primero, resetear todos los estilos
                slots.forEach(slot => {
                    slot.style.backgroundColor = 'rgba(220, 53, 69, 0.7)';
                });
                
                // Luego verificar conflictos y calcular unidades ocupadas
                slots.forEach(slot => {
                    const slotStart = timeToMinutes(slot.dataset.start);
                    const slotEnd = timeToMinutes(slot.dataset.end);
                    const slotUnits = parseInt(slot.dataset.units);
                    
                    // Verificar si hay solapamiento
                    if ((startMinutes >= slotStart && startMinutes < slotEnd) || 
                        (endMinutes > slotStart && endMinutes <= slotEnd) ||
                        (startMinutes <= slotStart && endMinutes >= slotEnd)) {
                        hasConflict = true;
                        slot.style.backgroundColor = 'rgba(220, 53, 69, 0.9)';
                        slot.classList.add('conflict');
                        
                        // Calcular las unidades ocupadas en este conflicto
                        maxOccupiedUnits = Math.max(maxOccupiedUnits, slotUnits);
                    } else {
                        slot.style.backgroundColor = 'rgba(220, 53, 69, 0.7)';
                        slot.classList.remove('conflict');
                    }
                });
                
                // Mostrar advertencia si hay conflicto
                if (hasConflict && elements.timeSlotWarning) {
                    elements.timeSlotWarning.classList.remove('d-none');
                    elements.timeSlotWarning.innerHTML = `
                        <i class="fas fa-exclamation-triangle"></i> Hay ${maxOccupiedUnits} unidades ocupadas en el horario seleccionado. Verifique la disponibilidad antes de solicitar.
                    `;
                } else if (elements.timeSlotWarning) {
                    elements.timeSlotWarning.classList.add('d-none');
                }
                
                // Actualizar la posición y tamaño de la selección
                const leftPos = minutesToPercent(startMinutes);
                const width = minutesToPercent(endMinutes - startMinutes);
                
                elements.timelineSelection.style.left = `${leftPos}%`;
                elements.timelineSelection.style.width = `${width}%`;
                elements.timelineSelection.classList.remove('d-none');
                elements.timelineSelection.innerHTML = `<span class="font-weight-bold">${startTime} - ${endTime}</span>`;
                elements.timelineSelection.style.background = hasConflict ? 
                    'rgba(255, 193, 7, 0.5)' : 'rgba(25, 135, 84, 0.5)';
                elements.timelineSelection.style.borderColor = hasConflict ? 
                    '#ffc107' : '#198754';
            }
        };
    }
});
