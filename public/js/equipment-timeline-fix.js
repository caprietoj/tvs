/**
 * CORRECCIÓN PARA LÍNEA DE TIEMPO EN PRÉSTAMOS DE EQUIPOS
 * 
 * Este script arregla problemas con la visualización de la línea de tiempo
 * en la página de gestión de préstamos de equipos.
 */

document.addEventListener('DOMContentLoaded', function() {
    // Esperar a que la página esté completamente cargada
    setTimeout(function() {
        console.log('Iniciando corrección de línea de tiempo...');
        fixTimelineIssues();
    }, 1000);

    function fixTimelineIssues() {
        // Verificar si estamos en la página correcta
        if (!window.location.pathname.includes('/equipment/loans')) {
            return;
        }

        // Verificar si la variable allLoans existe
        if (typeof allLoans === 'undefined' || !Array.isArray(allLoans)) {
            console.error('Variable allLoans no encontrada o no es un array');
            window.allLoans = [];
        }

        // Verificar si hay datos en allLoans
        if (allLoans.length === 0) {
            console.log('No hay datos en allLoans, recargando desde la tabla...');
            reloadLoansFromTable();
        }

        // Asegurarse de que la función updateTimeline existe
        if (typeof updateTimeline !== 'function') {
            console.error('Función updateTimeline no encontrada, creando versión alternativa...');
            window.updateTimeline = createUpdateTimelineFunction();
        }

        // Forzar actualización de la línea de tiempo
        const today = new Date().toISOString().split('T')[0];
        if ($('#timelineDate').val()) {
            updateTimeline($('#timelineDate').val());
        } else {
            $('#timelineDate').val(today);
            updateTimeline(today);
        }

        // Agregar listener para cuando se cambia a la pestaña de línea de tiempo
        $('#timeline-tab').on('shown.bs.tab', function(e) {
            console.log('Pestaña de línea de tiempo activada, actualizando...');
            if ($('#timelineDate').val()) {
                updateTimeline($('#timelineDate').val());
            } else {
                const today = new Date().toISOString().split('T')[0];
                $('#timelineDate').val(today);
                updateTimeline(today);
            }
        });
    }

    function reloadLoansFromTable() {
        // Recargar datos de préstamos desde la tabla
        $('.loan-row').each(function() {
            const row = $(this);
            const id = row.data('loan-id') || row.find('td:first').text().trim();
            const section = row.data('section') || '';
            const equipmentType = row.data('equipment-type') || '';
            const status = row.data('status') || '';
            const date = row.data('loan-date') || '';
            const autoReturn = row.data('auto-return') === '1';

            // Extraer más datos
            const user = row.find('td:eq(1)').text().trim();
            const sectionName = row.find('td:eq(2)').text().trim();
            const grade = row.find('td:eq(3)').text().trim();
            
            // Obtener nombre del equipo y unidades
            const equipmentName = row.find('td:eq(4) .badge').text().trim();
            const units = row.find('td:eq(5) .badge').text().trim();
            
            // Obtener fecha formateada
            const dateFormatted = row.find('td:eq(6)').text().trim();
            
            // Extraer tiempos
            const timeCell = row.find('td:eq(7)');
            const timeRange = timeCell.find('.time-badge').text().trim();
            let startTime = '';
            let endTime = '';
            
            // Extraer tiempos con regex
            const timeRegex = /(\d{1,2}:\d{2})\s*-\s*(\d{1,2}:\d{2})/;
            const timeMatch = timeRange.match(timeRegex);
            
            if (timeMatch && timeMatch.length >= 3) {
                startTime = timeMatch[1];
                endTime = timeMatch[2];
            }
            
            // Verificar período
            let periodId = null;
            let isPeriodBlock = false;
            
            if (timeCell.find('.badge:contains("Bloque")').length > 0) {
                periodId = '1:2';
                isPeriodBlock = true;
            } else if (timeCell.find('.badge:contains("Período")').length > 0) {
                periodId = '1';
                isPeriodBlock = false;
            }
            
            // Obtener texto de estado
            const statusText = row.find('td:eq(8) .badge').text().trim();
            
            // Crear objeto de préstamo
            const loan = {
                id,
                user,
                section,
                sectionName,
                grade,
                equipmentType,
                equipmentName,
                units,
                date,
                dateFormatted,
                startTime,
                endTime,
                status,
                statusText,
                autoReturn,
                periodId,
                isPeriodBlock
            };
            
            // Añadir a allLoans solo si tiene los datos necesarios
            if (id && date && startTime && endTime) {
                window.allLoans.push(loan);
                console.log(`Préstamo recargado: ID ${id}, ${date} ${startTime}-${endTime}`);
            }
        });
        
        console.log(`Total de préstamos recargados: ${window.allLoans.length}`);
    }

    function createUpdateTimelineFunction() {
        return function(date) {
            console.log(`Actualizando línea de tiempo para fecha: ${date}`);
            
            const container = $('#timeline-equipment-container');
            container.empty();
            
            // Obtener préstamos para la fecha seleccionada
            const selectedSection = $('#sectionFilter').val();
            const selectedEquipmentType = $('#equipmentTypeFilter').val();
            
            // Obtener estados seleccionados
            const selectedStatuses = [];
            $('.status-checkbox:checked').each(function() {
                selectedStatuses.push($(this).val());
            });
            
            // Filtrar préstamos para la fecha seleccionada
            const dayLoans = window.allLoans.filter(loan => {
                if (loan.date !== date) return false;
                if (selectedSection && loan.section !== selectedSection) return false;
                if (selectedEquipmentType && loan.equipmentType !== selectedEquipmentType) return false;
                if (selectedStatuses.length > 0 && !selectedStatuses.includes(loan.status)) return false;
                return true;
            });
            
            if (dayLoans.length === 0) {
                $('.timeline-info').removeClass('d-none')
                    .text('No hay préstamos para la fecha seleccionada con los filtros aplicados.');
                return;
            }
            
            $('.timeline-info').addClass('d-none');
            
            // Agrupar préstamos por tipo de equipo
            const groupedByType = {};
            dayLoans.forEach(loan => {
                if (!groupedByType[loan.equipmentType]) {
                    groupedByType[loan.equipmentType] = [];
                }
                groupedByType[loan.equipmentType].push(loan);
            });
            
            // Crear la línea de tiempo para cada tipo de equipo
            Object.keys(groupedByType).forEach(type => {
                const typeName = type === 'laptop' ? 'Portátil' : 'iPad';
                const typeIcon = type === 'laptop' ? 'fas fa-laptop' : 'fas fa-tablet-alt';
                
                // Crear contenedor por tipo
                const typeContainer = $(`<div class="timeline-equipment-type">
                    <h6 class="mt-3"><i class="${typeIcon}"></i> ${typeName}s</h6>
                </div>`);
                
                // Añadir cada préstamo
                groupedByType[type].forEach((loan, index) => {
                    const equipmentRow = $(`<div class="timeline-equipment">
                        <div class="timeline-equipment-name">
                            <span class="badge badge-${type === 'laptop' ? 'info' : 'warning'} mr-2">${loan.units}</span> ${loan.grade}
                        </div>
                        <div class="timeline-slots" id="timeline-${loan.id}"></div>
                    </div>`);
                    
                    typeContainer.append(equipmentRow);
                    
                    // Una vez añadido al DOM, calcular las posiciones
                    setTimeout(() => {
                        const slotsContainer = $(`#timeline-${loan.id}`);
                        if (slotsContainer.length === 0) {
                            console.error(`Contenedor timeline-${loan.id} no encontrado`);
                            return;
                        }
                        
                        const containerWidth = slotsContainer.width();
                        
                        // Verificar que tenemos startTime y endTime válidos
                        if (!loan.startTime || !loan.endTime) {
                            console.error(`Tiempos inválidos para préstamo ${loan.id}`);
                            return;
                        }
                        
                        // Convertir horas a porcentaje del ancho
                        const startParts = loan.startTime.split(':');
                        const endParts = loan.endTime.split(':');
                        
                        if (startParts.length !== 2 || endParts.length !== 2) {
                            console.error(`Formato de tiempo inválido para préstamo ${loan.id}`);
                            return;
                        }
                        
                        const startHour = parseInt(startParts[0]) + parseInt(startParts[1]) / 60;
                        const endHour = parseInt(endParts[0]) + parseInt(endParts[1]) / 60;
                        
                        // Calcular posición relativa (de 7 a 18 horas -> 11 horas totales)
                        const left = ((startHour - 7) / 11) * 100;
                        const width = ((endHour - startHour) / 11) * 100;
                        
                        const statusClass = loan.status === 'pending' ? 'warning' : 
                                            loan.status === 'delivered' ? 'info' : 
                                            loan.status === 'returned' ? 'success' : 'secondary';
                        
                        const slot = $(`<div class="timeline-slot timeline-slot-${type} badge-${statusClass}" 
                                        style="left: ${left}%; width: ${width}%; background-color: var(--${statusClass});" 
                                        data-loan-id="${loan.id}"
                                        title="${loan.startTime} - ${loan.endTime}">
                            ${loan.startTime}-${loan.endTime}
                        </div>`);
                        
                        slotsContainer.append(slot);
                        
                        // Añadir evento click
                        slot.on('click', function() {
                            if (typeof showLoanDetails === 'function') {
                                showLoanDetails(loan.id);
                            } else {
                                console.log(`Detalles del préstamo ${loan.id}: ${loan.dateFormatted}, ${loan.startTime}-${loan.endTime}, ${loan.statusText}`);
                            }
                        });
                        
                    }, 100);
                });
                
                container.append(typeContainer);
            });
        };
    }
});
