/**
 * Equipment Request Module
 * Handles equipment request form functionality
 */

window.initializeEquipmentRequest = function(elements) {
    // Elementos adicionales para los horarios de clase
    const classPeriods = {
        container: document.getElementById('class-periods-container'),
        list: document.getElementById('periods-list'),
        noPeriodsMessage: document.getElementById('no-periods-message'),
        manualHoursCheckbox: document.getElementById('manual-hours-checkbox'),
        periodIdInput: document.getElementById('period-id-input'),
        // Nuevos elementos para el diseño mejorado
        typeSingle: document.getElementById('period-type-single'),
        typeBlock: document.getElementById('period-type-block'),
        singleSelector: document.getElementById('single-period-selector'),
        blockSelector: document.getElementById('block-period-selector'),
        startSelect: document.getElementById('period-start-select'),
        endSelect: document.getElementById('period-end-select'),
        blockSummary: document.getElementById('block-period-summary'),
        blockPeriodText: document.getElementById('block-period-text'),
        selectedPeriodBadge: document.getElementById('selected-period-badge'),
        manualHoursContainer: document.getElementById('manual-hours-container'),
        manualStartTime: document.getElementById('manual-start-time'),
        manualEndTime: document.getElementById('manual-end-time'),
        applyManualHoursBtn: document.getElementById('apply-manual-hours-btn')
    };

    // Variable para almacenar todos los períodos cargados
    let allPeriods = [];

    // Validar que todos los elementos existen
    if (!elements || !classPeriods.container || !classPeriods.list || !classPeriods.noPeriodsMessage) {
        console.error('Missing required elements for class periods');
        return;
    }

    // Event listeners
    elements.sectionSelect.addEventListener('change', handleSectionChange);
    elements.loanDateInput.addEventListener('change', handleDateChange);
    elements.equipmentSelect.addEventListener('change', handleEquipmentChange);
    
    // Configurar el rango de fechas permitido al cargar la página
    function setupDateConstraints() {
        const today = new Date();
        today.setHours(0, 0, 0, 0); // Normalizar a inicio del día
        
        // Mañana como fecha mínima
        const tomorrow = new Date(today);
        tomorrow.setDate(today.getDate() + 1);
        
        // Calcular el fin de semana y activar la semana siguiente los viernes
        const endOfWeek = new Date(today);
        const dayOfWeek = today.getDay(); // 0 = domingo, 1 = lunes, ..., 6 = sábado
        
        if (dayOfWeek === 5) { // Si es viernes, permitir reservar para toda la próxima semana
            endOfWeek.setDate(today.getDate() + 9); // Hoy (viernes) + 9 días = domingo de la próxima semana
        } else if (dayOfWeek === 0) { // Si es domingo
            endOfWeek.setDate(today.getDate() + 7); // Próximo domingo
        } else {
            endOfWeek.setDate(today.getDate() + (7 - dayOfWeek)); // Domingo de esta semana
        }
        
        // Formatear fechas para el input date
        const tomorrowFormatted = tomorrow.toISOString().split('T')[0];
        const endOfWeekFormatted = endOfWeek.toISOString().split('T')[0];
        
        // Mostrar información de depuración en consola
        console.log("Configurando fechas:");
        console.log("Fecha mínima (mañana):", tomorrowFormatted);
        console.log("Fecha máxima (fin de semana):", endOfWeekFormatted);
        
        // Establecer valores min, max y default
        elements.loanDateInput.min = tomorrowFormatted;
        elements.loanDateInput.max = endOfWeekFormatted;
        
        // Si el valor actual está fuera de rango, establecerlo al valor predeterminado (mañana)
        const currentValue = elements.loanDateInput.value ? new Date(elements.loanDateInput.value + "T00:00:00") : null;
        if (!currentValue || isNaN(currentValue.getTime()) || currentValue < tomorrow || currentValue > endOfWeek) {
            elements.loanDateInput.value = tomorrowFormatted;
        }
        
        // Ya no mostraremos un mensaje adicional sobre las fechas límite
        
        // Limpiar cualquier mensaje previo si existe
        const dateInputContainer = elements.loanDateInput.parentElement;
        const existingInfo = dateInputContainer.querySelector('.alert-info');
        if (existingInfo) {
            dateInputContainer.removeChild(existingInfo);
        }
    }
    
    // Función auxiliar para formatear fecha como DD/MM/YYYY
    function formatFecha(fechaISO) {
        // Crear fecha de manera segura para evitar problemas de zona horaria
        if (typeof fechaISO === 'string' && fechaISO.includes('-')) {
            // Formato YYYY-MM-DD
            const [year, month, day] = fechaISO.split('-');
            const fecha = new Date(parseInt(year), parseInt(month) - 1, parseInt(day));
            return fecha.toLocaleDateString('es-ES', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric'
            });
        } else {
            // Caso de fallback
            const fecha = new Date(fechaISO);
            return fecha.toLocaleDateString('es-ES', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric'
            });
        }
    }
    
    // Configurar fechas al cargar la página
    setupDateConstraints();
    
    if (classPeriods.manualHoursCheckbox) {
        classPeriods.manualHoursCheckbox.addEventListener('change', handleManualHoursToggle);
    }

    // Nuevos event listeners para UI mejorada
    document.getElementById('period-type-single-label').addEventListener('click', function() {
        handlePeriodTypeChange('single');
    });
    
    document.getElementById('period-type-block-label').addEventListener('click', function() {
        handlePeriodTypeChange('block');
    });

    // Listeners para los selectores de bloque
    if (classPeriods.startSelect && classPeriods.endSelect) {
        classPeriods.startSelect.addEventListener('change', handleStartPeriodChange);
        classPeriods.endSelect.addEventListener('change', handleEndPeriodChange);
    }

    // Listener para el botón de aplicar horario manual
    if (classPeriods.applyManualHoursBtn) {
        classPeriods.applyManualHoursBtn.addEventListener('click', applyManualHours);
    }
    
    // Inicialización de los estilos de los botones de tipo de período
    document.getElementById('period-type-single-label').classList.add('active');
    document.getElementById('period-type-block-label').classList.remove('active');

    // Asegurar que los campos de horario estén habilitados al enviar el formulario
    if (elements.form) {
        elements.form.addEventListener('submit', function(event) {
            // Habilitar los campos de hora antes de enviar el formulario
            elements.startTimeInput.disabled = false;
            elements.endTimeInput.disabled = false;
            
            // Verificar si los campos están vacíos
            if (!elements.startTimeInput.value || !elements.endTimeInput.value) {
                event.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Campos requeridos',
                    text: 'Por favor, indique la hora de inicio y hora de finalización del préstamo'
                });
                return false;
            }
        });
    }

    // Función para formatear horas al formato válido HH:mm
    function formatTimeString(timeStr) {
        if (!timeStr) return '';
        
        // Si el formato ya es correcto, devolverlo tal cual
        if (/^([0-1][0-9]|2[0-3]):[0-5][0-9]$/.test(timeStr)) {
            return timeStr;
        }
        
        // Formato esperado: "7:30", "8:20", etc.
        const parts = timeStr.split(':');
        if (parts.length !== 2) return timeStr; // Si no tiene el formato esperado, devolverlo sin cambios
        
        // Añadir ceros a la izquierda si es necesario
        const hours = parts[0].padStart(2, '0');
        const minutes = parts[1].padStart(2, '0');
        
        return `${hours}:${minutes}`;
    }

    // Función para manejar el cambio de tipo de período (simple o bloque)
    function handlePeriodTypeChange(type) {
        const isBlockMode = type === 'block';
        
        if (isBlockMode) {
            // Modo bloque
            classPeriods.singleSelector.style.display = 'none';
            classPeriods.blockSelector.style.display = 'block';
            
            // Llenar el selector de período inicial si hay períodos disponibles
            if (allPeriods.length > 0) {
                populateStartPeriodSelect();
            }
            
            // Asegurar que los estilos de texto se apliquen correctamente
            document.getElementById('period-type-single-label').classList.remove('active');
            document.getElementById('period-type-block-label').classList.add('active');
        } else {
            // Modo simple (un solo período)
            classPeriods.singleSelector.style.display = 'block';
            classPeriods.blockSelector.style.display = 'none';
            
            // Resetear los selectores
            classPeriods.startSelect.value = '';
            classPeriods.endSelect.value = '';
            classPeriods.endSelect.disabled = true;
            classPeriods.blockSummary.style.display = 'none';
            
            // Asegurar que los estilos de texto se apliquen correctamente
            document.getElementById('period-type-single-label').classList.add('active');
            document.getElementById('period-type-block-label').classList.remove('active');
        }
        
        // Resetear el valor del período seleccionado
        classPeriods.periodIdInput.value = '';
        classPeriods.selectedPeriodBadge.style.display = 'none';
    }

    // Función para aplicar horarios manuales
    function applyManualHours() {
        const startTime = classPeriods.manualStartTime.value;
        const endTime = classPeriods.manualEndTime.value;
        
        if (!startTime || !endTime) {
            Swal.fire({
                icon: 'warning',
                title: 'Campos requeridos',
                text: 'Por favor, indique la hora de inicio y hora de finalización'
            });
            return;
        }
        
        // Validar que la hora de fin sea después de la hora de inicio
        if (startTime >= endTime) {
            Swal.fire({
                icon: 'error',
                title: 'Error de horario',
                text: 'La hora de finalización debe ser posterior a la hora de inicio'
            });
            return;
        }
        
        // Aplicar los horarios a los campos del formulario
        elements.startTimeInput.value = startTime;
        elements.endTimeInput.value = endTime;
        
        // Reiniciar el ID del período ya que es un horario manual
        classPeriods.periodIdInput.value = '';
        
        // Verificar disponibilidad con el nuevo horario
        checkAvailability();
        
        // Mostrar notificación de éxito
        Swal.fire({
            icon: 'success',
            title: 'Horario aplicado',
            text: `Se ha establecido el horario de ${startTime} a ${endTime}`,
            timer: 1500,
            showConfirmButton: false
        });
        
        // Mostrar notificación
        Swal.fire({
            icon: 'success',
            title: 'Horario aplicado',
            text: `Horario establecido: ${startTime} a ${endTime}`,
            timer: 1500,
            showConfirmButton: false
        });
        
        // Actualizar la disponibilidad
        checkAvailability();
    }

    // Función para manejar el cambio de período inicial
    function handleStartPeriodChange() {
        const startPeriodId = classPeriods.startSelect.value;
        
        if (!startPeriodId) {
            classPeriods.endSelect.disabled = true;
            classPeriods.endSelect.innerHTML = '<option value="">Seleccione período final</option>';
            classPeriods.blockSummary.style.display = 'none';
            return;
        }
        
        // Obtener el índice del período seleccionado
        const selectedIndex = allPeriods.findIndex(p => p.id === startPeriodId);
        if (selectedIndex === -1) return;
        
        // Habilitar el selector de período final
        classPeriods.endSelect.disabled = false;
        
        // Solo mostrar períodos posteriores al seleccionado como inicial
        const validEndPeriods = allPeriods.slice(selectedIndex + 1);
        
        // Llenar el selector de período final
        populateEndPeriodSelect(validEndPeriods);
    }

    // Función para manejar el cambio de período final
    function handleEndPeriodChange() {
        const startPeriodId = classPeriods.startSelect.value;
        const endPeriodId = classPeriods.endSelect.value;
        
        if (!startPeriodId || !endPeriodId) {
            classPeriods.blockSummary.style.display = 'none';
            return;
        }
        
        // Encontrar los períodos seleccionados
        const startPeriod = allPeriods.find(p => p.id === startPeriodId);
        const endPeriod = allPeriods.find(p => p.id === endPeriodId);
        
        if (!startPeriod || !endPeriod) return;
        
        // Actualizar los campos de horario con formato correcto
        elements.startTimeInput.value = formatTimeString(startPeriod.start);
        elements.endTimeInput.value = formatTimeString(endPeriod.end);
        
        // Almacenar los IDs de los períodos (formato: start_period_id:end_period_id)
        classPeriods.periodIdInput.value = `${startPeriodId}:${endPeriodId}`;
        
        // Mostrar resumen del bloque seleccionado
        classPeriods.blockSummary.style.display = 'block';
        classPeriods.blockPeriodText.textContent = `Bloque seleccionado: ${startPeriod.label} a ${endPeriod.label} (${startPeriod.start} - ${endPeriod.end})`;
        
        // Mostrar el badge de horario seleccionado
        classPeriods.selectedPeriodBadge.style.display = 'inline-block';
        
        // Actualizar la disponibilidad
        checkAvailability();
    }

    // Función para llenar el selector de período inicial
    function populateStartPeriodSelect() {
        // Filtrar solo los períodos de clase (no descansos)
        const classPeriodsList = allPeriods.filter(period => period.type === 'class');
        
        // Resetear el selector
        classPeriods.startSelect.innerHTML = '<option value="">Seleccione período inicial</option>';
        
        // Añadir opciones
        classPeriodsList.forEach(period => {
            const option = document.createElement('option');
            option.value = period.id;
            option.textContent = `${period.label} (${period.start} - ${period.end})`;
            classPeriods.startSelect.appendChild(option);
        });
    }

    // Función para llenar el selector de período final
    function populateEndPeriodSelect(validEndPeriods) {
        // Resetear el selector
        classPeriods.endSelect.innerHTML = '<option value="">Seleccione período final</option>';
        
        // Añadir opciones
        validEndPeriods.forEach(period => {
            // Solo añadir períodos de clase (no descansos)
            if (period.type === 'class') {
                const option = document.createElement('option');
                option.value = period.id;
                option.textContent = `${period.label} (${period.start} - ${period.end})`;
                classPeriods.endSelect.appendChild(option);
            }
        });
    }

    // Función para manejar el cambio de sección
    function handleSectionChange() {
        // Reiniciar el selector de períodos
        resetClassPeriods();
        
        // Si se ha seleccionado una sección, mostrar selector de períodos 
        // Ahora también incluimos la sección administrativa
        const selectedSection = elements.sectionSelect.value;
        if (selectedSection) {
            loadClassPeriods();
        }
    }

    // Función para manejar el cambio de fecha
    function handleDateChange() {
        // Si hay una sección seleccionada, recargar los períodos
        // También se incluye la sección administrativa
        const selectedSection = elements.sectionSelect.value;
        if (selectedSection) {
            loadClassPeriods();
        }

        // Verificar disponibilidad si también hay un equipo seleccionado
        if (elements.equipmentSelect.value) {
            checkAvailability();
        }
        
        // Verificar que la fecha seleccionada esté dentro del rango permitido
        const selectedDate = new Date(elements.loanDateInput.value + "T00:00:00");
        const today = new Date();
        today.setHours(0, 0, 0, 0); // Normalizar a inicio del día
        
        const tomorrow = new Date(today);
        tomorrow.setDate(today.getDate() + 1);
        tomorrow.setHours(0, 0, 0, 0); // Normalizar a inicio del día
        
        // Calcular el fin de semana y activar la semana siguiente los viernes
        const endOfWeek = new Date(today);
        const dayOfWeek = today.getDay(); // 0 = domingo, 1 = lunes, ..., 6 = sábado
        
        if (dayOfWeek === 5) { // Si es viernes, permitir reservar para toda la próxima semana
            endOfWeek.setDate(today.getDate() + 9); // Hoy (viernes) + 9 días = domingo de la próxima semana
        } else if (dayOfWeek === 0) { // Si es domingo
            endOfWeek.setDate(today.getDate() + 7); // Próximo domingo
        } else {
            endOfWeek.setDate(today.getDate() + (7 - dayOfWeek)); // Domingo de esta semana
        }
        endOfWeek.setHours(23, 59, 59, 999); // Establecer al final del día
        
        // Si la fecha seleccionada está fuera de rango, mostrar una alerta
        if (selectedDate < tomorrow || selectedDate > endOfWeek) {
            Swal.fire({
                title: 'Fecha no disponible',
                html: `
                    <p>Solo puede reservar equipos para días de la semana actual (excepto hoy).</p>
                    <p>Rango permitido: desde <strong>${formatFecha(tomorrow.toISOString().split('T')[0])}</strong> 
                    hasta <strong>${formatFecha(endOfWeek.toISOString().split('T')[0])}</strong></p>
                `,
                icon: 'warning'
            }).then(() => {
                // Establecer la fecha a mañana si está fuera de rango
                elements.loanDateInput.value = tomorrow.toISOString().split('T')[0];
                handleDateChange(); // Llamar recursivamente para actualizar todo
            });
        }
    }

    // Función para manejar el cambio de equipo
    function handleEquipmentChange() {
        if (elements.equipmentSelect.value && elements.loanDateInput.value) {
            checkAvailability();
            
            // Mostrar mensaje informativo sobre la disponibilidad por horas
            Swal.fire({
                title: 'Disponibilidad por horarios',
                icon: 'info',
                html: `
                    <div class="text-left">
                        <p>La disponibilidad de equipos varía según el horario del día.</p>
                        <p>Para encontrar equipos disponibles:</p>
                        <ul>
                            <li>Seleccione un horario específico en el formulario</li>
                            <li>Verifique la línea de tiempo para ver los horarios ya reservados</li>
                            <li>Busque espacios libres donde pueda realizar su reserva</li>
                        </ul>
                    </div>
                `,
                confirmButtonText: 'Entendido'
            });
        }
    }

    // Función para cargar los períodos de clase
    function loadClassPeriods() {
        const selectedSection = elements.sectionSelect.value;
        const selectedDate = elements.loanDateInput.value;
        
        if (!selectedSection || !selectedDate) {
            return;
        }
        
        // Verificar que la fecha esté dentro del rango permitido (semana actual, excluyendo hoy)
        const selectedDateObj = new Date(selectedDate + "T00:00:00"); // Añadir hora para normalizar
        const today = new Date();
        today.setHours(0, 0, 0, 0); // Normalizar a inicio del día
        
        const tomorrow = new Date(today);
        tomorrow.setDate(today.getDate() + 1);
        tomorrow.setHours(0, 0, 0, 0); // Normalizar a inicio del día
        
        // Calcular el fin de semana y activar la semana siguiente los viernes
        const endOfWeek = new Date(today);
        const dayOfWeek = today.getDay();
        
        if (dayOfWeek === 5) { // Si es viernes, permitir reservar para toda la próxima semana
            endOfWeek.setDate(today.getDate() + 9); // Hoy (viernes) + 9 días = domingo de la próxima semana
        } else if (dayOfWeek === 0) { // Si es domingo
            endOfWeek.setDate(today.getDate() + 7); // Próximo domingo
        } else {
            endOfWeek.setDate(today.getDate() + (7 - dayOfWeek)); // Domingo de esta semana
        }
        endOfWeek.setHours(23, 59, 59, 999); // Establecer al final del día
        
        // Para depuración en consola
        console.log("Fecha seleccionada:", selectedDateObj.toISOString().split('T')[0]);
        console.log("Fecha mínima (mañana):", tomorrow.toISOString().split('T')[0]);
        console.log("Fecha máxima (fin de semana):", endOfWeek.toISOString().split('T')[0]);
        
        // Si está fuera de rango, mostrar mensaje y no continuar con la carga
        if (selectedDateObj < tomorrow || selectedDateObj > endOfWeek) {
            Swal.fire({
                title: 'Fecha no disponible',
                html: `
                    <p>Solo puede reservar equipos para días dentro de la semana actual (excepto hoy).</p>
                    <p>Rango permitido: desde <strong>${formatFecha(tomorrow.toISOString().split('T')[0])}</strong> 
                    hasta <strong>${formatFecha(endOfWeek.toISOString().split('T')[0])}</strong></p>
                `,
                icon: 'warning'
            }).then(() => {
                // Establecer a mañana si está fuera de rango
                elements.loanDateInput.value = tomorrow.toISOString().split('T')[0];
                setupDateConstraints(); // Actualizar restricciones
            });
            return;
        }

        // Mostrar el contenedor de períodos para cualquier sección, incluida administrativo
        classPeriods.container.style.display = 'block';

        // Para la sección administrativa, usamos horarios predefinidos en lugar de cargarlos desde el servidor
        if (selectedSection === 'administrativo') {
            // Crear horarios administrativos estándar
            const today = new Date(selectedDate + "T00:00:00"); // Añadir hora para evitar problemas de zona horaria
            const dayOfWeek = today.toLocaleDateString('es-ES', { weekday: 'long' });
            const capitalizedDayOfWeek = dayOfWeek.charAt(0).toUpperCase() + dayOfWeek.slice(1);
            
            const adminPeriods = {
                periods: [
                    { id: 'admin_0', type: 'class', start: '8:00', end: '9:00', label: 'Hora 1' },
                    { id: 'admin_1', type: 'class', start: '9:00', end: '10:00', label: 'Hora 2' },
                    { id: 'admin_2', type: 'class', start: '10:00', end: '11:00', label: 'Hora 3' },
                    { id: 'admin_3', type: 'class', start: '11:00', end: '12:00', label: 'Hora 4' },
                    { id: 'break_0', type: 'break', start: '12:00', end: '13:00', label: 'ALMUERZO' },
                    { id: 'admin_4', type: 'class', start: '13:00', end: '14:00', label: 'Hora 5' },
                    { id: 'admin_5', type: 'class', start: '14:00', end: '15:00', label: 'Hora 6' },
                    { id: 'admin_6', type: 'class', start: '15:00', end: '16:00', label: 'Hora 7' },
                    { id: 'admin_7', type: 'class', start: '16:00', end: '17:00', label: 'Hora 8' }
                ],
                is_friday: today.getDay() === 5,
                day_of_week: capitalizedDayOfWeek
            };
            
            displayClassPeriods(adminPeriods);
            return;
        }

        // Para otras secciones, cargamos los períodos como antes
        // Determinar si es preescolar o primaria
        let subSection = null;
        if (selectedSection === 'preescolar_primaria') {
            // Por defecto asumimos primaria, pero podríamos agregar un selector
            subSection = 'primaria';
        }

        classPeriods.list.innerHTML = `
            <div class="d-flex justify-content-center p-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="sr-only">Cargando...</span>
                </div>
            </div>
        `;
        
        // Consultar API para obtener los períodos
        const params = new URLSearchParams({
            section: selectedSection,
            loan_date: selectedDate,
            sub_section: subSection
        });
        
        fetch(`/equipment/class-schedule?${params}`)
            .then(response => {
                if (!response.ok) throw new Error('Error al cargar los horarios de clase');
                return response.json();
            })
            .then(data => {
                displayClassPeriods(data);
            })
            .catch(error => {
                console.error('Error:', error);
                classPeriods.list.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i> Error al cargar los horarios de clase. ${error.message}
                    </div>
                `;
            });
    }

    // Función para mostrar los períodos de clase
    function displayClassPeriods(data) {
        const { periods, is_friday, day_of_week } = data;
        
        // Si no hay períodos, mostrar mensaje
        if (!periods || periods.length === 0) {
            classPeriods.list.style.display = 'none';
            classPeriods.noPeriodsMessage.style.display = 'block';
            return;
        }
        
        // Mostrar los períodos
        classPeriods.list.style.display = 'block';
        classPeriods.noPeriodsMessage.style.display = 'none';
        
        // Ordenar los períodos por hora de inicio
        const sortedPeriods = [...periods].sort((a, b) => {
            // Convertir hora en formato "HH:MM" a minutos para poder comparar
            const timeToMinutes = (timeStr) => {
                const [hours, minutes] = timeStr.split(':').map(Number);
                return hours * 60 + minutes;
            };
            
            return timeToMinutes(a.start) - timeToMinutes(b.start);
        });
        
        // Guardar los períodos ordenados para uso posterior
        allPeriods = sortedPeriods;

        // Agrupar períodos por tipo (clases y descansos)
        const classPeriodsList = sortedPeriods.filter(period => period.type === 'class');
        const breakPeriods = sortedPeriods.filter(period => period.type === 'break');
        
        // Crear contenido HTML para los períodos - NUEVO FORMATO VISUAL
        let html = `
            <div class="alert alert-info mb-3">
                <i class="fas fa-calendar-day"></i> <strong>${day_of_week}</strong> ${is_friday ? '(viernes)' : ''}
            </div>
        `;
        
        // Crear la cuadrícula de períodos de clase
        classPeriodsList.forEach(period => {
            html += `
                <div class="period-item" 
                    data-period-id="${period.id}" 
                    data-start="${period.start}" 
                    data-end="${period.end}">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="period-time">
                            <i class="fas fa-clock mr-1"></i> ${period.start} - ${period.end}
                        </div>
                        <div>
                            <span class="period-label">${period.label}</span>
                        </div>
                    </div>
                    <div class="period-status"></div>
                </div>
            `;
        });
        
        // Añadir períodos de descanso
        breakPeriods.forEach(period => {
            html += `
                <div class="period-item break" 
                    data-period-id="${period.id}" 
                    data-start="${period.start}" 
                    data-end="${period.end}">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="period-time">
                            <i class="fas fa-coffee mr-1"></i> ${period.start} - ${period.end}
                        </div>
                        <div>
                            <span class="period-label">${period.label}</span>
                        </div>
                    </div>
                </div>
            `;
        });
        
        classPeriods.list.innerHTML = html;
        
        // Agregar event listeners a los períodos
        const periodItems = classPeriods.list.querySelectorAll('.period-item:not(.break)');
        periodItems.forEach(item => {
            item.addEventListener('click', function() {
                const periodId = this.dataset.periodId;
                const startTime = formatTimeString(this.dataset.start);
                const endTime = formatTimeString(this.dataset.end);
                const periodInfo = allPeriods.find(p => p.id === periodId);
                
                // Actualizar campos
                elements.startTimeInput.value = startTime;
                elements.endTimeInput.value = endTime;
                classPeriods.periodIdInput.value = periodId;
                
                // Actualizar estado visual
                periodItems.forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');
                
                // Mostrar el badge de período seleccionado
                classPeriods.selectedPeriodBadge.style.display = 'inline-block';
                
                // Mostrar notificación con animación suave
                const toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 1500,
                    timerProgressBar: true
                });
                
                toast.fire({
                    icon: 'success',
                    title: `Período seleccionado: ${periodInfo ? periodInfo.label : ''}`
                });
                
                // Actualizar la disponibilidad
                checkAvailability();
            });
        });

        // Si está en modo bloque, actualizar los selectores
        if (classPeriods.typeBlock.checked) {
            populateStartPeriodSelect();
        }
    }
    
    // Función para reiniciar el selector de períodos
    function resetClassPeriods() {
        classPeriods.container.style.display = 'none';
        classPeriods.list.innerHTML = '';
        classPeriods.periodIdInput.value = '';
        classPeriods.selectedPeriodBadge.style.display = 'none';
        classPeriods.blockSummary.style.display = 'none';
        
        // Resetear los selectores de bloque
        classPeriods.startSelect.innerHTML = '<option value="">Seleccione período inicial</option>';
        classPeriods.endSelect.innerHTML = '<option value="">Seleccione período final</option>';
        classPeriods.endSelect.disabled = true;
        
        // Resetear la lista de períodos
        allPeriods = [];
    }
    
    // Función para manejar el toggle entre horario manual o por períodos
    function handleManualHoursToggle() {
        const isManual = classPeriods.manualHoursCheckbox.checked;
        
        // Mostrar u ocultar el contenedor de horario manual
        classPeriods.manualHoursContainer.style.display = isManual ? 'block' : 'none';
        
        // Si es manual, habilitar campos de horario y deshabilitar selección de períodos
        if (isManual) {
            elements.startTimeInput.disabled = false;
            elements.endTimeInput.disabled = false;
            
            // Inicializar los valores de los campos manuales con los valores actuales
            if (elements.startTimeInput.value) {
                classPeriods.manualStartTime.value = elements.startTimeInput.value;
            }
            if (elements.endTimeInput.value) {
                classPeriods.manualEndTime.value = elements.endTimeInput.value;
            }
            
            // Deshabilitar todos los controles de selección de períodos
            classPeriods.list.querySelectorAll('.period-item').forEach(btn => {
                btn.classList.remove('active');
                btn.classList.add('disabled');
                btn.style.opacity = '0.5';
                btn.style.pointerEvents = 'none';
            });
            
            document.getElementById('period-type-single-label').classList.add('disabled');
            document.getElementById('period-type-block-label').classList.add('disabled');
            classPeriods.startSelect.disabled = true;
            classPeriods.endSelect.disabled = true;
            
            classPeriods.periodIdInput.value = '';
        } else {
            // Si no es manual, deshabilitar campos de horario y habilitar selección de períodos
            elements.startTimeInput.disabled = true;
            elements.endTimeInput.disabled = true;
            
            // Habilitar controles de selección de períodos
            classPeriods.list.querySelectorAll('.period-item').forEach(btn => {
                if (!btn.classList.contains('break')) {
                    btn.classList.remove('disabled');
                    btn.style.opacity = '1';
                    btn.style.pointerEvents = 'auto';
                }
            });
            
            document.getElementById('period-type-single-label').classList.remove('disabled');
            document.getElementById('period-type-block-label').classList.remove('disabled');
            
            // Actualizar según el tipo de selección actual
            if (document.getElementById('period-type-block').checked) {
                classPeriods.startSelect.disabled = false;
                if (classPeriods.startSelect.value) {
                    classPeriods.endSelect.disabled = false;
                }
            }
        }
    }
    
    // Función para verificar la disponibilidad de equipos
    function checkAvailability() {
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
            updateAvailabilityInfo(data);
        })
        .catch(error => {
            elements.availabilityInfo.innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> Error: ${error.message}
                </div>
            `;
        });
    }
    
    // Función para actualizar la información de disponibilidad
    function updateAvailabilityInfo(data) {
        console.log('Datos de disponibilidad recibidos:', data);
        
        // Actualizar contadores
        elements.totalUnitsValue.textContent = data.total_units;
        elements.occupiedUnitsValue.textContent = data.occupied_units;
        elements.availableUnitsValue.textContent = data.available_units;
        elements.unitsAvailableText.textContent = data.available_units;
        
        // Actualizar el atributo max del campo de unidades
        elements.unitsInput.max = data.available_units;
        
        elements.unitsSummary.classList.remove('d-none');
        
        // Actualizar texto de ayuda para el campo de unidades
        if (data.available_units > 0) {
            elements.unitsHelpText.textContent = `Puede solicitar hasta ${data.available_units} unidades`;
            elements.unitsHelpText.classList.remove('text-danger', 'text-info');
            elements.unitsHelpText.classList.add('text-muted');
        } else {
            elements.unitsHelpText.textContent = "Busque otro horario para encontrar disponibilidad";
            elements.unitsHelpText.classList.remove('text-muted', 'text-danger');
            elements.unitsHelpText.classList.add('text-info');
        }
        
        // Actualizar la información de disponibilidad
        elements.availabilityInfo.innerHTML = '';
        
        // Verificar si hay horarios seleccionados
        const startTime = elements.startTimeInput.value;
        const endTime = elements.endTimeInput.value;
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
            // Mostrar mensaje unificado y clarificado para cuando no hay disponibilidad
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
        
        // Actualizar el timeline
        updateTimelineSlots(data.occupied_slots);
    }
    
    // Función para actualizar el timeline con los slots ocupados
    function updateTimelineSlots(occupiedSlots) {
        if (!occupiedSlots || occupiedSlots.length === 0) {
            elements.timelineContainer.classList.add('d-none');
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
        
        // Mostrar la selección actual en el timeline
        updateTimelineSelection();
    }
    
    // Función para actualizar la selección en el timeline
    function updateTimelineSelection() {
        const startTime = elements.startTimeInput.value;
        const endTime = elements.endTimeInput.value;
        
        if (!startTime || !endTime) {
            elements.timelineSelection.classList.add('d-none');
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
        let totalOccupiedUnits = 0;
        let maxOccupiedUnits = 0;
        const slots = elements.timelineSlots.querySelectorAll('.timeline-slot');
        
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
                totalOccupiedUnits += slotUnits;
                maxOccupiedUnits = Math.max(maxOccupiedUnits, slotUnits);
            } else {
                slot.style.backgroundColor = 'rgba(220, 53, 69, 0.7)';
                slot.classList.remove('conflict');
            }
        });
        
        // Mostrar advertencia si hay conflicto
        if (hasConflict) {
            elements.timeSlotWarning.classList.remove('d-none');
            elements.timeSlotWarning.innerHTML = `
                <i class="fas fa-exclamation-triangle"></i> Hay ${maxOccupiedUnits} unidades ocupadas en el horario seleccionado. Verifique la disponibilidad antes de solicitar.
            `;
            
            // Volver a verificar la disponibilidad específicamente para este horario
            const equipmentId = elements.equipmentSelect.value;
            const loanDate = elements.loanDateInput.value;
            const section = elements.sectionSelect.value;
            
            if (equipmentId && loanDate && section && startTime && endTime) {
                const formData = new FormData();
                formData.append('equipment_id', equipmentId);
                formData.append('loan_date', loanDate);
                formData.append('section', section);
                formData.append('start_time', startTime);
                formData.append('end_time', endTime);
                formData.append('_token', document.querySelector('input[name="_token"]').value);
                
                fetch('/equipment/check-availability', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    // Actualizar información específica para este horario
                    elements.unitsAvailableText.textContent = data.available_units;
                    elements.unitsInput.max = data.available_units;
                    console.log('Actualizando disponibilidad específica para horario:', {
                        startTime: startTime,
                        endTime: endTime,
                        availableUnits: data.available_units,
                        totalUnits: data.total_units
                    });
                    updateAvailabilityInfo(data);
                });
            }
        } else {
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
    }        // Escuchar cambios en las horas para actualizar la selección
    elements.startTimeInput.addEventListener('change', function() {
        updateTimelineSelection();
        if (elements.endTimeInput.value) {
            checkAvailability();
        }
    });
    
    elements.endTimeInput.addEventListener('change', function() {
        updateTimelineSelection();
        if (elements.startTimeInput.value) {
            checkAvailability();
        }
    });
    
    // Añadir listener para ocultar/mostrar el consejo de disponibilidad
    if (document.getElementById('time-availability-tip')) {
        elements.loanDateInput.addEventListener('change', function() {
            // Mostrar el consejo después de seleccionar una fecha
            document.getElementById('time-availability-tip').style.display = 'block';
        });
    }
    
    // Inicializar - Por defecto, deshabilitar los campos de horario y esperar la selección de un período
    elements.startTimeInput.disabled = true;
    elements.endTimeInput.disabled = true;
    
    // Añadir listener para depuración de fecha (activación con Ctrl+Alt+D)
    document.addEventListener('keydown', function(e) {
        if (e.ctrlKey && e.altKey && e.key === 'd') {
            debugDates();
            alert('Información de depuración de fechas mostrada en la consola');
        }
    });

    // Función de depuración para verificar las fechas
    function debugDates() {
        const selectedDate = elements.loanDateInput.value;
        const selectedDateObj = new Date(selectedDate + "T00:00:00");
        
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        
        const tomorrow = new Date(today);
        tomorrow.setDate(today.getDate() + 1);
        
        const endOfWeek = new Date(today);
        const dayOfWeek = today.getDay();
        
        if (dayOfWeek === 5) { // Si es viernes, permitir reservar para toda la próxima semana
            endOfWeek.setDate(today.getDate() + 9); // Hoy (viernes) + 9 días = domingo de la próxima semana
        } else if (dayOfWeek === 0) { // Si es domingo
            endOfWeek.setDate(today.getDate() + 7); // Próximo domingo
        } else {
            endOfWeek.setDate(today.getDate() + (7 - dayOfWeek)); // Domingo de esta semana
        }
        
        // Comprobar las fechas para la vista previa utilizando diferentes métodos
        const [year, month, day] = selectedDate.split('-');
        
        // Diferentes formas de crear el objeto Date para comprobar
        const previewSelectedDateSimple = new Date(selectedDate);
        const previewSelectedDateWithT = new Date(selectedDate + "T00:00:00");
        const previewSelectedDateComponents = new Date(parseInt(year), parseInt(month) - 1, parseInt(day));
        
        console.log("======= DEPURACIÓN DE FECHAS =======");
        console.log("Fecha seleccionada (valor bruto):", selectedDate);
        console.log("Componentes de fecha - Año:", year, "Mes:", month, "Día:", day);
        console.log("Fecha (objeto Date simple):", previewSelectedDateSimple.toISOString(), "- Día local:", previewSelectedDateSimple.getDate());
        console.log("Fecha (objeto Date con T00:00:00):", previewSelectedDateWithT.toISOString(), "- Día local:", previewSelectedDateWithT.getDate());
        console.log("Fecha (objeto Date por componentes):", previewSelectedDateComponents.toISOString(), "- Día local:", previewSelectedDateComponents.getDate());
        console.log("Fecha formateada para vista previa (componentes):", previewSelectedDateComponents.toLocaleDateString('es-ES', { 
            weekday: 'long', 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric' 
        }));
        console.log("====================================");
        endOfWeek.setDate(today.getDate() + daysUntilEndOfWeek);
        
        console.group("Depuración de fechas");
        console.log("Hoy:", today.toISOString());
        console.log("Mañana:", tomorrow.toISOString());
        console.log("Fin de semana:", endOfWeek.toISOString());
        console.log("Fecha seleccionada:", selectedDateObj.toISOString());
        console.log("¿Fecha seleccionada < mañana?", selectedDateObj < tomorrow);
        console.log("¿Fecha seleccionada > fin de semana?", selectedDateObj > endOfWeek);
        console.groupEnd();
        
        return {
            today: today.toISOString().split('T')[0],
            tomorrow: tomorrow.toISOString().split('T')[0],
            endOfWeek: endOfWeek.toISOString().split('T')[0],
            selected: selectedDateObj.toISOString().split('T')[0],
            isBeforeTomorrow: selectedDateObj < tomorrow,
            isAfterEndOfWeek: selectedDateObj > endOfWeek
        };
    }
    
    // Añadir un evento oculto para fácil depuración
    document.addEventListener('keydown', function(e) {
        // Ctrl+Alt+D para depurar fechas
        if (e.ctrlKey && e.altKey && e.key === 'd') {
            const results = debugDates();
            Swal.fire({
                title: 'Depuración de fechas',
                html: `
                    <div style="text-align: left; margin-top: 20px;">
                        <p><strong>Hoy:</strong> ${formatFecha(results.today)}</p>
                        <p><strong>Mañana:</strong> ${formatFecha(results.tomorrow)}</p>
                        <p><strong>Fin de semana:</strong> ${formatFecha(results.endOfWeek)}</p>
                        <p><strong>Fecha seleccionada:</strong> ${formatFecha(results.selected)}</p>
                        <hr>
                        <p><strong>¿Fecha seleccionada < mañana?</strong> ${results.isBeforeTomorrow ? 'Sí' : 'No'}</p>
                        <p><strong>¿Fecha seleccionada > fin de semana?</strong> ${results.isAfterEndOfWeek ? 'Sí' : 'No'}</p>
                    </div>
                `,
                icon: 'info'
            });
        }
    });
};
