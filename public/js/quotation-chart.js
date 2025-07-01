$(document).ready(function() {
    let currentItemIndex = null;
    let currentItemQuantity = 1;
    
    // Generar gráfico de comparación
    setTimeout(function() {
        createChart();
    }, 500);
    
    function createChart() {
        const ctx = document.getElementById('quotationChart');
        if (!ctx) {
            console.log('Canvas not found');
            return;
        }
        
        console.log('Creating chart...');
        
        // Datos de ejemplo
        const chartData = {
            labels: ['Proveedor ABC', 'Proveedor XYZ'],
            datasets: [{
                label: 'Monto Total',
                data: [150000, 135000],
                backgroundColor: [
                    'rgba(54, 162, 235, 0.8)',
                    'rgba(75, 192, 192, 0.8)'
                ],
                borderColor: [
                    'rgba(54, 162, 235, 1)',
                    'rgba(75, 192, 192, 1)'
                ],
                borderWidth: 1
            }]
        };
        
        new Chart(ctx, {
            type: 'bar',
            data: chartData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Total: $' + context.parsed.y.toLocaleString('es-CO', {
                                    minimumFractionDigits: 2
                                });
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '$' + value.toLocaleString('es-CO');
                            }
                        }
                    },
                    x: {
                        ticks: {
                            maxRotation: 45,
                            minRotation: 0
                        }
                    }
                }
            }
        });
        
        console.log('Chart created successfully');
    }
    
    // Abrir modal para seleccionar proveedor
    $(document).on('click', '.select-provider-btn, .change-selection-btn', function() {
        currentItemIndex = $(this).data('item-index');
        currentItemQuantity = $(this).data('item-quantity');
        
        $('#modal-item-description').text($(this).data('item-description'));
        $('#modal-item-quantity').text(currentItemQuantity);
        $('#modal-item-index').val(currentItemIndex);
        
        // Limpiar formulario
        $('.quotation-radio').prop('checked', false);
        $('.unit-price-input').val('');
        $('.total-display').text('$0.00');
        $('#justification').val('');
        
        $('#providerSelectionModal').modal('show');
    });
    
    // Calcular total cuando cambie el precio unitario
    $('.unit-price-input').on('input', function() {
        const quotationId = $(this).data('quotation-id');
        const unitPrice = parseFloat($(this).val()) || 0;
        const total = unitPrice * currentItemQuantity;
        
        $('.total-display[data-quotation-id="' + quotationId + '"]').text(
            '$' + total.toLocaleString('es-CO', {minimumFractionDigits: 2, maximumFractionDigits: 2})
        );
        
        // Seleccionar automáticamente la cotización cuando se ingrese un precio
        if (unitPrice > 0) {
            $('#quotation-' + quotationId).prop('checked', true);
        }
    });
    
    // Seleccionar cotización
    $('.quotation-radio').on('change', function() {
        const quotationId = $(this).val();
        const unitPriceInput = $('.unit-price-input[data-quotation-id="' + quotationId + '"]');
        
        if (unitPriceInput.val() === '') {
            unitPriceInput.focus();
        }
    });
    
    // Guardar selección
    $('#saveSelectionBtn').click(function() {
        const selectedQuotation = $('.quotation-radio:checked').val();
        const unitPrice = $('.unit-price-input[data-quotation-id="' + selectedQuotation + '"]').val();
        const justification = $('#justification').val();
        const purchaseRequestId = $('#modal-purchase-request-id').val();
        const itemIndex = $('#modal-item-index').val();
        
        if (!selectedQuotation) {
            alert('Por favor seleccione una cotización.');
            return;
        }
        
        if (!unitPrice || parseFloat(unitPrice) <= 0) {
            alert('Por favor ingrese un precio unitario válido.');
            return;
        }
        
        // Deshabilitar botón mientras se procesa
        $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i>Guardando...');
        
        // Llamada AJAX real
        $.ajax({
            url: '/quotation-selections/select-item',
            method: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                purchase_request_id: purchaseRequestId,
                item_index: itemIndex,
                quotation_id: selectedQuotation,
                unit_price: unitPrice,
                justification: justification
            },                success: function(response) {
                if (response.success) {
                    // Actualizar la fila en la tabla
                    updateItemRow(itemIndex, response.data);
                    
                    // Cerrar modal
                    $('#providerSelectionModal').modal('hide');
                    
                    // Mostrar mensaje de éxito
                    showAlert('success', response.message || 'Selección guardada correctamente');
                    
                    // Actualizar el total general
                    updateGrandTotal(response.grand_total);
                    
                    // Actualizar botones dinámicamente usando el nuevo manager
                    if (window.quotationButtonManager) {
                        window.quotationButtonManager.forceUpdate();
                    } else if (typeof window.updateSelectionButtons === 'function') {
                        window.updateSelectionButtons();
                    }
                } else {
                    showAlert('danger', response.message || 'Error al guardar la selección');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error AJAX:', xhr.responseText);
                showAlert('danger', 'Error de conexión. Por favor intente nuevamente.');
            },
            complete: function() {
                // Rehabilitar botón
                $('#saveSelectionBtn').prop('disabled', false).html('<i class="fas fa-save mr-2"></i>Guardar Selección');
            }
        });
    });
    
    // Eliminar selección
    $(document).on('click', '.remove-selection-btn', function() {
        const itemIndex = $(this).data('item-index');
        
        if (confirm('¿Está seguro de que desea eliminar esta selección?')) {
            // AJAX call para eliminar selección
            $.ajax({
                url: '/quotation-selections/remove',
                method: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    purchase_request_id: $('#modal-purchase-request-id').val() || $('input[name="purchase_request_id"]').val(),
                    item_index: itemIndex
                },
                success: function(response) {
                    if (response.success) {
                        removeItemSelection(itemIndex);
                        showAlert('success', response.message || 'Selección eliminada correctamente');
                        updateGrandTotal(response.grand_total);
                        
                        // Actualizar botones dinámicamente usando el nuevo manager
                        if (window.quotationButtonManager) {
                            window.quotationButtonManager.forceUpdate();
                        } else if (typeof window.updateSelectionButtons === 'function') {
                            window.updateSelectionButtons();
                        }
                    } else {
                        showAlert('danger', response.message || 'Error al eliminar la selección');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error AJAX:', xhr.responseText);
                    showAlert('danger', 'Error de conexión. Por favor intente nuevamente.');
                }
            });
        }
    });
    
    function removeItemSelection(itemIndex) {
        const row = $('#item-row-' + itemIndex);
        
        // Restaurar estado inicial
        row.find('td:nth-child(4)').html(
            '<div class="selection-area">' +
                '<button type="button" class="btn btn-primary btn-sm select-provider-btn" ' +
                        'data-item-index="' + itemIndex + '" ' +
                        'data-item-description="Item ' + (itemIndex + 1) + '" ' +
                        'data-item-quantity="1">' +
                    '<i class="fas fa-hand-pointer"></i> Seleccionar Proveedor' +
                '</button>' +
            '</div>'
        );
        
        row.find('td:nth-child(5)').html('<span class="text-muted">-</span>');
        row.find('td:nth-child(6)').html('<span class="text-muted">-</span>');
        row.find('td:nth-child(7)').html('');
    }
    
    function showAlert(type, message) {
        const alertHtml = 
            '<div class="alert alert-' + type + ' alert-dismissible fade show">' +
                '<button type="button" class="close" data-dismiss="alert">&times;</button>' +
                '<i class="fas fa-' + (type === 'success' ? 'check-circle' : 'exclamation-circle') + ' mr-2"></i>' + message +
            '</div>';
        
        $('.container-fluid').prepend(alertHtml);
        
        // Auto-hide after 5 seconds
        setTimeout(function() {
            $('.alert').fadeOut();
        }, 5000);
    }
    
    function updateItemRow(itemIndex, selectionData) {
        const row = $('#item-row-' + itemIndex);
        
        // Actualizar columna de proveedor seleccionado
        const providerHtml = 
            '<div class="selected-provider" id="selected-' + itemIndex + '">' +
                '<strong class="text-success">' + selectionData.provider_name + '</strong><br>' +
                '<small class="text-muted">' +
                    'Seleccionado por ' + selectionData.selected_by + '<br>' +  
                    selectionData.selected_at +
                '</small>' +
                (selectionData.justification ? 
                    '<br><small class="text-info"><i class="fas fa-comment"></i> ' + selectionData.justification + '</small>' : 
                    '') +
            '</div>';
        
        row.find('td:nth-child(4)').html(providerHtml);
        
        // Actualizar precio unitario
        row.find('#unit-price-' + itemIndex).html('$' + parseFloat(selectionData.unit_price).toLocaleString('es-CO', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }));
        
        // Actualizar total
        row.find('#total-price-' + itemIndex).html(
            '<strong class="text-success">$' + parseFloat(selectionData.total_price).toLocaleString('es-CO', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }) + '</strong>'
        );
        
        // Agregar botones de acción
        const actionsHtml = 
            '<button type="button" class="btn btn-warning btn-sm change-selection-btn" ' +
                    'data-item-index="' + itemIndex + '" ' +
                    'data-item-description="' + selectionData.item_description + '" ' +
                    'data-item-quantity="' + selectionData.quantity + '">' +
                '<i class="fas fa-edit"></i>' +
            '</button> ' +
            '<button type="button" class="btn btn-danger btn-sm remove-selection-btn" ' +
                    'data-item-index="' + itemIndex + '">' +
                '<i class="fas fa-times"></i>' +
            '</button>';
        
        row.find('td:nth-child(7)').html(actionsHtml);
    }
    
    function updateGrandTotal(grandTotal) {
        $('#grand-total').html('$' + parseFloat(grandTotal).toLocaleString('es-CO', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }));
    }
});
