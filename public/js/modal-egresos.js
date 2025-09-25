/**
 * Modal de Egresos - Funcionalidad JavaScript pura (sin jQuery/Bootstrap)
 * Maneja la visualización de detalles de egresos en un modal
 */

class EgresosModal {
    constructor() {
        this.modal = document.getElementById('modal-egresos');
        this.overlay = this.modal?.querySelector('.modal-overlay');
        this.container = this.modal?.querySelector('.modal-container');
        this.title = document.getElementById('modal-title');
        this.subtitle = document.getElementById('modal-subtitle');
        this.loading = document.getElementById('modal-loading');
        this.content = document.getElementById('modal-content');
        this.error = document.getElementById('modal-error');
        this.tableBody = document.getElementById('modal-table-body');
        this.closeBtn = this.modal?.querySelector('.modal-close');

        this.init();
    }

    init() {
        if (!this.modal) {
            console.error('Modal de egresos no encontrado en el DOM');
            return;
        }

        // Event listeners
        this.overlay?.addEventListener('click', () => this.close());
        this.closeBtn?.addEventListener('click', () => this.close());
        
        // Escape key para cerrar
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.modal.classList.contains('show')) {
                this.close();
            }
        });

        // Click en elementos clickeables
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('egreso-clickable')) {
                const mes = e.target.getAttribute('data-mes');
                const concepto = e.target.getAttribute('data-concepto');
                if (mes && concepto) {
                    this.show(mes, concepto);
                }
            } else if (e.target.classList.contains('clickable-ingresos')) {
                const mes = e.target.getAttribute('data-mes');
                const concepto = e.target.getAttribute('data-concepto');
                if (mes && concepto) {
                    this.showIngresos(mes, concepto);
                }
            } else if (e.target.classList.contains('resumen-clickable')) {
                const mes = e.target.getAttribute('data-mes');
                const concepto = e.target.getAttribute('data-concepto');
                if (mes && concepto) {
                    this.showResumen(mes, concepto);
                }
            }
        });

        console.log('Modal de egresos inicializado');
    }

    show(mes, concepto) {
        if (!this.modal) return;

        // Mostrar modal con loading
        this.modal.style.display = 'flex';
        setTimeout(() => this.modal.classList.add('show'), 10);
        
        // Actualizar título
        if (this.title) {
            this.title.textContent = `Detalle de Egresos - ${concepto}`;
        }
        
        // Actualizar subtítulo con información básica
        if (this.subtitle) {
            this.subtitle.textContent = `${this.capitalizeFirst(mes)} 2024-2025 • Cargando información...`;
        }

        // Mostrar loading y ocultar contenido
        this.showLoading();

        // Hacer petición AJAX
        this.fetchDetails(mes, concepto);
    }

    close() {
        if (!this.modal) return;

        // Remover la clase show para iniciar la animación de cierre
        this.modal.classList.remove('show');
        
        // Esperar a que termine la animación antes de ocultar completamente
        setTimeout(() => {
            this.modal.style.display = 'none';
        }, 250); // Tiempo sincronizado con la transición CSS
    }

    showLoading() {
        if (this.loading) this.loading.style.display = 'flex';
        if (this.content) this.content.style.display = 'none';
        if (this.error) this.error.style.display = 'none';
    }

    showContent() {
        if (this.loading) this.loading.style.display = 'none';
        if (this.content) this.content.style.display = 'block';
        if (this.error) this.error.style.display = 'none';
    }

    showError() {
        if (this.loading) this.loading.style.display = 'none';
        if (this.content) this.content.style.display = 'none';
        if (this.error) this.error.style.display = 'block';
    }

    async fetchDetails(mes, concepto) {
        try {
            const url = new URL('/contabilidad/presupuesto/egresos-detalle', window.location.origin);
            url.searchParams.set('mes', mes);
            url.searchParams.set('concepto', concepto);

            const response = await fetch(url.toString(), {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();

            if (data.success && data.data && Array.isArray(data.data)) {
                this.renderDetails(data.data, data.meta || {});
                this.showContent();
            } else {
                throw new Error(data.message || 'Datos inválidos recibidos');
            }

        } catch (error) {
            console.error('Error al obtener detalles de egresos:', error);
            this.showError();
        }
    }

    renderDetails(details, meta = {}) {
        if (!this.tableBody) return;

        // Calcular totales
        const totalRegistros = details.length;
        const totalMonto = details.reduce((sum, item) => sum + (parseFloat(item.valor) || 0), 0);

        // Actualizar subtítulo con información descriptiva
        if (this.subtitle) {
            this.subtitle.textContent = `${totalRegistros} transacciones • Total: $${this.formatNumber(totalMonto)}`;
        }

        this.tableBody.innerHTML = '';

        if (details.length === 0) {
            // También actualizar el subtítulo para caso vacío
            if (this.subtitle) {
                this.subtitle.textContent = 'No se encontraron transacciones para este período';
            }
            this.tableBody.innerHTML = `
                <tr>
                    <td colspan="7" style="text-align: center; color: #6c757d; font-style: italic; padding: 20px;">
                        No se encontraron detalles para este período
                    </td>
                </tr>
            `;
            return;
        }

        details.forEach(detail => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${this.formatDate(detail.fecha)}</td>
                <td><strong>${this.escapeHtml(detail.documento || 'N/A')}</strong></td>
                <td class="text-left">${this.escapeHtml(detail.descripcion || 'Sin descripción')}</td>
                <td class="number-cell">$${this.formatNumber(detail.valor || 0)}</td>
                <td><span class="badge badge-${detail.valor > 0 ? 'success' : 'primary'}">${detail.valor > 0 ? 'INGRESO' : 'EGRESO'}</span></td>
                <td class="text-center">${detail.centro_costo || '-'}</td>
                <td class="text-center">${detail.cuenta || '-'}</td>
            `;
            this.tableBody.appendChild(row);
        });
    }

    showIngresos(mes, concepto) {
        if (!this.modal) return;

        // Mostrar modal con loading
        this.modal.style.display = 'flex';
        setTimeout(() => this.modal.classList.add('show'), 10);
        
        // Actualizar título
        if (this.title) {
            this.title.textContent = `Detalle de Ingresos Escolares - ${concepto}`;
        }
        
        // Actualizar subtítulo con información básica
        if (this.subtitle) {
            this.subtitle.textContent = `${this.capitalizeFirst(mes)} 2024-2025 • Cargando información...`;
        }

        // Mostrar loading y ocultar contenido
        this.showLoading();

        // Hacer petición AJAX
        this.fetchIngresosDetails(mes, concepto);
    }

    async fetchIngresosDetails(mes, concepto) {
        try {
            const url = new URL('/contabilidad/presupuesto/ingresos-escolares-detalle', window.location.origin);
            url.searchParams.set('mes', mes);
            url.searchParams.set('concepto', concepto);

            const response = await fetch(url.toString(), {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();

            if (data.success) {
                this.populateIngresosTable(data.data || []);
                this.showContent();
            } else {
                throw new Error(data.message || 'Error al obtener los datos');
            }

        } catch (error) {
            console.error('Error fetching ingresos details:', error);
            this.showError();
        }
    }

    populateIngresosTable(details) {
        // Calcular totales
        const totalRegistros = details.length;
        const totalMonto = details.reduce((sum, item) => sum + (parseFloat(item.valor) || 0), 0);

        // Actualizar subtítulo con información descriptiva
        if (this.subtitle) {
            this.subtitle.textContent = `${totalRegistros} transacciones • Total: $${this.formatNumber(totalMonto)}`;
        }

        this.tableBody.innerHTML = '';

        if (details.length === 0) {
            // También actualizar el subtítulo para caso vacío
            if (this.subtitle) {
                this.subtitle.textContent = 'No se encontraron transacciones para este período';
            }
            this.tableBody.innerHTML = `
                <tr>
                    <td colspan="7" style="text-align: center; color: #6c757d; font-style: italic; padding: 20px;">
                        No se encontraron detalles para este período
                    </td>
                </tr>
            `;
            return;
        }

        details.forEach(detail => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${this.formatDate(detail.fecha)}</td>
                <td><strong>${this.escapeHtml(detail.documento || 'N/A')}</strong></td>
                <td class="text-left">${this.escapeHtml(detail.descripcion || 'Sin descripción')}</td>
                <td class="number-cell">$${this.formatNumber(detail.valor || 0)}</td>
                <td><span class="badge badge-success">INGRESO</span></td>
                <td class="text-center">${detail.centro_costo || '-'}</td>
                <td class="text-center">${detail.cuenta || '-'}</td>
            `;
            this.tableBody.appendChild(row);
        });
    }

    formatNumber(number) {
        return Math.abs(Number(number) || 0).toLocaleString('es-CO', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        });
    }

    formatDate(dateString) {
        if (!dateString) return 'N/A';
        
        try {
            const date = new Date(dateString);
            return date.toLocaleDateString('es-CO', {
                year: 'numeric',
                month: '2-digit',
                day: '2-digit'
            });
        } catch (error) {
            return dateString;
        }
    }

    escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return String(text).replace(/[&<>"']/g, (m) => map[m]);
    }

    capitalizeFirst(str) {
        return str.charAt(0).toUpperCase() + str.slice(1);
    }

    showResumen(mes, concepto) {
        if (!this.modal) return;

        // Mostrar modal con loading
        this.modal.style.display = 'flex';
        setTimeout(() => this.modal.classList.add('show'), 10);
        
        // Actualizar título
        if (this.title) {
            this.title.textContent = `Detalle de Resumen - ${concepto}`;
        }
        
        // Actualizar subtítulo con información básica
        if (this.subtitle) {
            this.subtitle.textContent = `${this.capitalizeFirst(mes)} 2024-2025 • Cargando información...`;
        }

        // Mostrar loading y ocultar contenido
        this.showLoading();

        // Hacer petición AJAX
        this.fetchResumenDetails(mes, concepto);
    }

    async fetchResumenDetails(mes, concepto) {
        try {
            const url = new URL('/contabilidad/presupuesto/resumen-detalle', window.location.origin);
            url.searchParams.set('mes', mes);
            url.searchParams.set('concepto', concepto);

            const response = await fetch(url.toString(), {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const data = await response.json();
            
            if (data.success && data.data) {
                this.displayResumenDetails(data.data, concepto, mes);
            } else {
                this.showError(data.message || 'No se encontraron detalles para este concepto y mes');
            }
        } catch (error) {
            console.error('Error al cargar detalles del resumen:', error);
            this.showError(`Error al cargar datos: ${error.message}`);
        }
    }

    displayResumenDetails(data, concepto, mes) {
        if (!this.tableBody) return;

        // Limpiar contenido anterior
        this.tableBody.innerHTML = '';

        // Mostrar contenido y ocultar loading
        if (this.content) this.content.style.display = 'block';
        if (this.loading) this.loading.style.display = 'none';
        if (this.error) this.error.style.display = 'none';

        // Actualizar subtítulo con información del total
        if (this.subtitle) {
            const total = data.reduce((sum, item) => sum + parseFloat(item.valor || 0), 0);
            this.subtitle.innerHTML = `${this.capitalizeFirst(mes)} 2024-2025 • Total: <strong>$${total.toLocaleString('es-CO')}</strong> • ${data.length} registros`;
        }

        // Crear filas de la tabla
        data.forEach(item => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${item.fecha || '-'}</td>
                <td><strong>${item.documento || '-'}</strong></td>
                <td class="text-left">${item.detalle || item.descripcion || '-'}</td>
                <td class="number-cell">$${parseFloat(item.valor || 0).toLocaleString('es-CO')}</td>
                <td><span class="badge badge-${item.tipo === 'ingreso' ? 'success' : 'primary'}">${item.tipo || 'N/A'}</span></td>
                <td class="text-center">${item.centro_costo || '-'}</td>
                <td class="text-center">${item.cuenta || '-'}</td>
            `;
            this.tableBody.appendChild(row);
        });
    }
}

// Hacer disponible globalmente
window.EgresosModal = EgresosModal;
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        if (!window.egresosModal) {
            window.egresosModal = new EgresosModal();
        }
    });
} else {
    // DOM ya está listo
    if (!window.egresosModal) {
        window.egresosModal = new EgresosModal();
    }
}
