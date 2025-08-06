<?php

namespace App\Http\Controllers;

use App\Models\PurchaseRequest;
use App\Models\Quotation;
use App\Models\RequestHistory;
use App\Models\User;
use App\Notifications\QuotationsUploaded;
use App\Notifications\QuotationsCompletedCompras;
use App\Notifications\PurchaseRequestStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class QuotationController extends Controller
{
    public function create(PurchaseRequest $purchaseRequest)
    {
        $this->authorize('addQuotation', $purchaseRequest);
        
        // Obtener la lista de proveedores para el selector
        $proveedores = \App\Models\Proveedor::orderBy('nombre')->get();
        
        return view('purchases.quotations.create', compact('purchaseRequest', 'proveedores'));
    }
    
    public function store(Request $request, PurchaseRequest $purchaseRequest)
    {
        $this->authorize('addQuotation', $purchaseRequest);
        
        // Log para depuración - Estado inicial
        \Log::info('Estado inicial de la solicitud antes de agregar cotización', [
            'request_number' => $purchaseRequest->request_number,
            'request_id' => $purchaseRequest->id,
            'status_before' => $purchaseRequest->status,
            'section_area' => $purchaseRequest->section_area
        ]);
        
        // Guardar el estado original para verificación posterior
        $originalStatus = $purchaseRequest->status;
        
        // Verificar que no haya más cotizaciones del límite configurado
        $requiredQuotations = $purchaseRequest->getRequiredQuotationsCount();
        if ($purchaseRequest->quotations()->count() >= $requiredQuotations) {
            return redirect()->back()->with('error', "Ya se han subido {$requiredQuotations} cotizaciones para esta solicitud.");
        }
        
        $validator = Validator::make($request->all(), [
            'provider_name' => 'required|string|max:255',
            'subtotal' => 'required|numeric|min:0',
            'total_amount' => 'required|numeric|min:0',
            'includes_iva' => 'nullable|boolean',
            'iva_amount' => 'nullable|numeric|min:0',
            'includes_iva_19' => 'nullable|boolean',
            'iva_19_amount' => 'nullable|numeric|min:0',
            'includes_iva_5' => 'nullable|boolean',
            'iva_5_amount' => 'nullable|numeric|min:0',
            'includes_ipoconsumo_8' => 'nullable|boolean',
            'ipoconsumo_8_amount' => 'nullable|numeric|min:0',
            'includes_ipoconsumo_4' => 'nullable|boolean',
            'ipoconsumo_4_amount' => 'nullable|numeric|min:0',
            'tax_application_mode' => 'nullable|string|in:global,per_item',
            'quotation_file' => 'required|file|mimes:pdf|max:5120',
            'item_prices' => 'nullable|array',
            'item_prices.*' => 'nullable|numeric|min:0',
            'item_totals' => 'nullable|array',
            'item_totals.*' => 'nullable|numeric|min:0',
            'item_iva_19' => 'nullable|array',
            'item_iva_5' => 'nullable|array',
            'item_ipoconsumo_8' => 'nullable|array',
            'item_ipoconsumo_4' => 'nullable|array',
            'general_service_price' => 'nullable|numeric|min:0',
            'additional_items' => 'nullable|array',
            'additional_items.*.description' => 'required_with:additional_items|string|max:255',
            'additional_items.*.quantity' => 'required_with:additional_items|numeric|min:0',
            'additional_items.*.unit' => 'nullable|string|max:50',
            'additional_items.*.price' => 'required_with:additional_items|numeric|min:0',
            'additional_items.*.includes_iva_19' => 'nullable|boolean',
            'additional_items.*.includes_iva_5' => 'nullable|boolean',
            'additional_items.*.includes_ipoconsumo_8' => 'nullable|boolean',
            'additional_items.*.includes_ipoconsumo_4' => 'nullable|boolean',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        // Procesar precios de items originales de la solicitud
        $originalItemPrices = [];
        $originalItemTotals = [];
        $originalItemTaxes = [];
        
        // Obtener los items de la solicitud para validación
        $requestItems = [];
        if ($purchaseRequest->type === 'purchase' && $purchaseRequest->purchase_items) {
            $requestItems = is_array($purchaseRequest->purchase_items) 
                ? $purchaseRequest->purchase_items 
                : json_decode($purchaseRequest->purchase_items, true);
        } elseif ($purchaseRequest->type === 'services' && $purchaseRequest->service_type === 'regular' && $purchaseRequest->service_items) {
            $requestItems = is_array($purchaseRequest->service_items) 
                ? $purchaseRequest->service_items 
                : json_decode($purchaseRequest->service_items, true);
        }
        
        // Procesar precios unitarios y totales de items originales
        if ($request->has('item_prices') && is_array($request->item_prices)) {
            foreach ($request->item_prices as $index => $price) {
                if (!empty($price) && is_numeric($price) && isset($requestItems[$index])) {
                    $quantity = $requestItems[$index]['quantity'] ?? 1;
                    $unitPrice = floatval($price);
                    $itemTotal = $quantity * $unitPrice;
                    
                    $originalItemPrices[$index] = $unitPrice;
                    $originalItemTotals[$index] = $itemTotal;
                }
            }
        }
        
        // Procesar servicios generales
        if ($request->has('general_service_price') && !empty($request->general_service_price)) {
            $generalPrice = floatval($request->general_service_price);
            $originalItemPrices['general'] = $generalPrice;
            $originalItemTotals['general'] = $generalPrice; // Para servicios generales, cantidad = 1
        }
        
        // Procesar impuestos por item
        $taxTypes = ['iva_19', 'iva_5', 'ipoconsumo_8', 'ipoconsumo_4'];
        foreach ($taxTypes as $taxType) {
            if ($request->has("item_{$taxType}") && is_array($request->{"item_{$taxType}"})) {
                foreach ($request->{"item_{$taxType}"} as $index => $value) {
                    if (!empty($value)) {
                        $originalItemTaxes[$index][$taxType] = true;
                    }
                }
            }
        }
        
        // Procesar items adicionales
        $additionalItems = [];
        $taxMode = $request->input('tax_application_mode', 'global');
        
        if ($request->has('additional_items') && is_array($request->additional_items)) {
            foreach ($request->additional_items as $key => $item) {
                if (!empty($item['description']) && !empty($item['quantity']) && isset($item['price'])) {
                    $quantity = floatval($item['quantity']);
                    $price = floatval($item['price']);
                    $total = $quantity * $price;
                    
                    $itemData = [
                        'description' => $item['description'],
                        'quantity' => $quantity,
                        'unit' => $item['unit'] ?? 'Unidad',
                        'price' => $price,
                        'total' => $total
                    ];
                    
                    // Si está en modo por item, agregar información de impuestos
                    if ($taxMode === 'per_item') {
                        $itemData['includes_iva_19'] = isset($item['includes_iva_19']) && $item['includes_iva_19'];
                        $itemData['includes_iva_5'] = isset($item['includes_iva_5']) && $item['includes_iva_5'];
                        $itemData['includes_ipoconsumo_8'] = isset($item['includes_ipoconsumo_8']) && $item['includes_ipoconsumo_8'];
                        $itemData['includes_ipoconsumo_4'] = isset($item['includes_ipoconsumo_4']) && $item['includes_ipoconsumo_4'];
                        
                        // Calcular impuestos para este item
                        $itemData['iva_19_amount'] = $itemData['includes_iva_19'] ? $total * 0.19 : 0;
                        $itemData['iva_5_amount'] = $itemData['includes_iva_5'] ? $total * 0.05 : 0;
                        $itemData['ipoconsumo_8_amount'] = $itemData['includes_ipoconsumo_8'] ? $total * 0.08 : 0;
                        $itemData['ipoconsumo_4_amount'] = $itemData['includes_ipoconsumo_4'] ? $total * 0.04 : 0;
                        $itemData['total_with_taxes'] = $total + $itemData['iva_19_amount'] + $itemData['iva_5_amount'] + 
                                                      $itemData['ipoconsumo_8_amount'] + $itemData['ipoconsumo_4_amount'];
                    }
                    
                    $additionalItems[] = $itemData;
                }
            }
        }
        
        // Validar cálculos (verificar que los totales sean consistentes)
        $subtotal = floatval($request->subtotal);
        $additionalItemsTotal = array_sum(array_column($additionalItems, 'total'));
        $originalItemsTotal = array_sum($originalItemTotals);
        $totalSubtotal = $subtotal + $additionalItemsTotal;
        
        // Verificar que el subtotal coincida con la suma de items originales (con tolerancia)
        if (!empty($originalItemTotals) && abs($subtotal - $originalItemsTotal) > 0.01) {
            \Log::warning('Discrepancia en subtotal de items originales', [
                'subtotal_declarado' => $subtotal,
                'suma_items_originales' => $originalItemsTotal,
                'diferencia' => abs($subtotal - $originalItemsTotal)
            ]);
        }
        
        // Calcular todos los impuestos según el modo
        $includesIva = $request->has('includes_iva');
        $includesIva19 = $request->has('includes_iva_19');
        $includesIva5 = $request->has('includes_iva_5');
        $includesIpoconsumo8 = $request->has('includes_ipoconsumo_8');
        $includesIpoconsumo4 = $request->has('includes_ipoconsumo_4');
        
        if ($taxMode === 'global') {
            // Modo global: aplicar impuestos a todo el subtotal
            $iva19Amount = $includesIva19 ? $totalSubtotal * 0.19 : 0;
            $iva5Amount = $includesIva5 ? $totalSubtotal * 0.05 : 0;
            $ipoconsumo8Amount = $includesIpoconsumo8 ? $totalSubtotal * 0.08 : 0;
            $ipoconsumo4Amount = $includesIpoconsumo4 ? $totalSubtotal * 0.04 : 0;
        } else {
            // Modo por item: sumar los impuestos calculados por item
            $iva19Amount = 0;
            $iva5Amount = 0;
            $ipoconsumo8Amount = 0;
            $ipoconsumo4Amount = 0;
            
            // Impuestos de items adicionales
            foreach ($additionalItems as $item) {
                $iva19Amount += $item['iva_19_amount'] ?? 0;
                $iva5Amount += $item['iva_5_amount'] ?? 0;
                $ipoconsumo8Amount += $item['ipoconsumo_8_amount'] ?? 0;
                $ipoconsumo4Amount += $item['ipoconsumo_4_amount'] ?? 0;
            }
            
            // Impuestos de items originales
            foreach ($originalItemTaxes as $index => $taxes) {
                $itemTotal = $originalItemTotals[$index] ?? 0;
                if (isset($taxes['iva_19']) && $taxes['iva_19']) {
                    $iva19Amount += $itemTotal * 0.19;
                }
                if (isset($taxes['iva_5']) && $taxes['iva_5']) {
                    $iva5Amount += $itemTotal * 0.05;
                }
                if (isset($taxes['ipoconsumo_8']) && $taxes['ipoconsumo_8']) {
                    $ipoconsumo8Amount += $itemTotal * 0.08;
                }
                if (isset($taxes['ipoconsumo_4']) && $taxes['ipoconsumo_4']) {
                    $ipoconsumo4Amount += $itemTotal * 0.04;
                }
            }
            
            // Marcar que hay impuestos si hay algún monto
            $includesIva19 = $iva19Amount > 0;
            $includesIva5 = $iva5Amount > 0;
            $includesIpoconsumo8 = $ipoconsumo8Amount > 0;
            $includesIpoconsumo4 = $ipoconsumo4Amount > 0;
        }
        
        // Para compatibilidad con el IVA original
        $expectedIvaAmount = $includesIva ? $totalSubtotal * 0.19 : $iva19Amount;
        
        $totalImpuestos = $iva19Amount + $iva5Amount + $ipoconsumo8Amount + $ipoconsumo4Amount;
        $expectedTotal = $totalSubtotal + $totalImpuestos;
        
        // Verificar que el total calculado coincida con el enviado (con tolerancia de 0.01)
        if (abs($expectedTotal - floatval($request->total_amount)) > 0.01) {
            return redirect()->back()
                ->with('error', 'Error en el cálculo de totales. Por favor, verifique los montos.')
                ->withInput();
        }
        
        try {
            // Usar una transacción para asegurar la integridad de los datos
            \DB::beginTransaction();
            
            $filePath = $request->file('quotation_file')->store('public/quotations');
            
            $quotation = Quotation::create([
                'purchase_request_id' => $purchaseRequest->id,
                'provider_name' => $request->provider_name,
                'file_path' => $filePath,
                'total_amount' => $request->total_amount,
                'subtotal' => $request->subtotal,
                'includes_iva' => $includesIva || $includesIva19,
                'iva_amount' => $expectedIvaAmount,
                'includes_iva_19' => $includesIva19,
                'iva_19_amount' => $iva19Amount,
                'includes_iva_5' => $includesIva5,
                'iva_5_amount' => $iva5Amount,
                'includes_ipoconsumo_8' => $includesIpoconsumo8,
                'ipoconsumo_8_amount' => $ipoconsumo8Amount,
                'includes_ipoconsumo_4' => $includesIpoconsumo4,
                'ipoconsumo_4_amount' => $ipoconsumo4Amount,
                'tax_application_mode' => $taxMode,
                'additional_items' => $additionalItems,
                'original_item_prices' => $originalItemPrices,
                'original_item_totals' => $originalItemTotals,
                'original_item_taxes' => $originalItemTaxes,
                'delivery_time' => $request->delivery_time,
                'payment_method' => $request->payment_method,
                'validity' => $request->validity,
                'warranty' => $request->warranty,
            ]);
            
            $quotationCount = $purchaseRequest->quotations()->count();
            
            // Actualizar estado de la solicitud si es la primera cotización
            if ($quotationCount === 1 || $originalStatus === 'pending' || $originalStatus === 'approved') {
                \Log::info('Actualizando estado a En Cotización', [
                    'request_id' => $purchaseRequest->id,
                    'original_status' => $originalStatus
                ]);
                
                // Asignar directamente el estado
                $purchaseRequest->status = 'En Cotización';
                $purchaseRequest->save();
                
                // Registrar el cambio en el historial
                RequestHistory::create([
                    'purchase_request_id' => $purchaseRequest->id,
                    'user_id' => Auth::id(),
                    'action' => 'Cambio de estado a: En Cotización',
                    'notes' => 'Cotización #' . $quotationCount . ' agregada'
                ]);
            }
            
            // Log cuando se completan las cotizaciones requeridas (sin envío automático)
            $requiredQuotations = $purchaseRequest->getRequiredQuotationsCount();
            if ($quotationCount >= $requiredQuotations) {
                \Log::info("Se completaron {$requiredQuotations} cotizaciones - esperando confirmación manual para envío", [
                    'purchase_request' => $purchaseRequest->request_number,
                    'quotation_count' => $quotationCount,
                    'required_quotations' => $requiredQuotations
                ]);
            }
            
            // Registrar en historial
            RequestHistory::create([
                'purchase_request_id' => $purchaseRequest->id,
                'user_id' => Auth::id(),
                'action' => 'Cotización agregada',
                'notes' => 'Proveedor: ' . $request->provider_name . ' - Monto: ' . $request->total_amount,
            ]);
            
            \DB::commit();
            
            // Verificar que el estado no haya cambiado a "rejected" por alguna razón
            if ($purchaseRequest->fresh()->status === 'rejected') {
                \Log::error('Estado cambiado inesperadamente a rejected tras agregar cotización', [
                    'request_id' => $purchaseRequest->id
                ]);
                
                // Forzar nuevamente el estado a "En Cotización"
                $purchaseRequest->status = 'En Cotización';
                $purchaseRequest->save();
                
                \Log::info('Estado corregido nuevamente a En Cotización', [
                    'request_id' => $purchaseRequest->id
                ]);
            }
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Error al adjuntar cotización: ' . $e->getMessage(), [
                'request_id' => $purchaseRequest->id,
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()->with('error', 'Error al adjuntar la cotización: ' . $e->getMessage());
        }
        
        // Log para depuración - Estado final
        \Log::info('Estado final de la solicitud después de agregar cotización', [
            'request_number' => $purchaseRequest->request_number,
            'request_id' => $purchaseRequest->id,
            'status_after' => $purchaseRequest->fresh()->status,
            'quotation_count' => $quotationCount
        ]);
        
        // Verificar si ahora tiene las cotizaciones requeridas y mostrar modal
        $requiredQuotations = $purchaseRequest->getRequiredQuotationsCount();
        if ($quotationCount >= $requiredQuotations && is_null($purchaseRequest->preapproval_sent_at)) {
            return redirect()->route('purchase-requests.show', $purchaseRequest->id)
                ->with('success', 'Cotización agregada exitosamente.')
                ->with('show_preapproval_modal', true)
                ->with('quotation_count', $quotationCount);
        }
        
        return redirect()->route('purchase-requests.show', $purchaseRequest->id)
            ->with('success', 'Cotización agregada exitosamente.');
    }
    
    public function destroy(Quotation $quotation)
    {
        $this->authorize('deleteQuotation', $quotation->purchaseRequest);
        
        $purchaseRequest = $quotation->purchaseRequest;
        
        // No permitir eliminar si ya hay una orden de compra
        if ($purchaseRequest->purchaseOrder) {
            return redirect()->back()->with('error', 'No se puede eliminar la cotización porque ya hay una orden de compra.');
        }
        
        // Borrar el archivo
        Storage::delete($quotation->file_path);
        
        // Registrar en historial
        RequestHistory::create([
            'purchase_request_id' => $purchaseRequest->id,
            'user_id' => Auth::id(),
            'action' => 'Cotización eliminada',
            'notes' => 'Proveedor: ' . $quotation->provider_name . ' - Monto: ' . $quotation->total_amount,
        ]);
        
        $quotation->delete();
        
        return redirect()->route('purchase-requests.show', $purchaseRequest->id)
            ->with('success', 'Cotización eliminada exitosamente.');
    }
    
    public function download(Quotation $quotation)
    {
        $this->authorize('viewQuotation', $quotation->purchaseRequest);
        
        return Storage::download($quotation->file_path, $quotation->provider_name . '.pdf');
    }
    
    public function select(Request $request, Quotation $quotation)
    {
        // Agregar log para diagnóstico
        \Log::info('QuotationController@select called', [
            'quotation_id' => $quotation->id,
            'request_method' => $request->method(),
            'request_has_observation' => $request->has('observation')
        ]);
        
        // Si es una solicitud GET, mostramos el formulario 
        if ($request->isMethod('get')) {
            $purchaseRequest = $quotation->purchaseRequest;
            return view('purchases.quotations.select', compact('quotation', 'purchaseRequest'));
        }
        
        $request->validate([
            'observation' => 'required|string',
        ]);
        
        $purchaseRequest = $quotation->purchaseRequest;
        
        // Verificar si la solicitud está en estado de cotización
        if ($purchaseRequest->status !== 'En Cotización') {
            return back()->with('error', 'Solo se pueden pre-aprobar cotizaciones en solicitudes que estén en estado "En Cotización".');
        }
        
        // Desmarcar cualquier otra cotización seleccionada
        $quotation->purchaseRequest->quotations()->update(['is_selected' => false]);
        
        // Marcar esta cotización como seleccionada
        $quotation->is_selected = true;
        $quotation->save();
        
        // Actualizar el estado de la solicitud
        $purchaseRequest->updateStatus('Pre-aprobada', auth()->id(), $request->observation);
        
        return redirect()->route('purchase-requests.show', $purchaseRequest->id)
            ->with('success', 'Cotización pre-aprobada exitosamente.');
    }

    /**
     * Mostrar el índice de cotizaciones para gestión general
     */
    public function index()
    {
        // Obtener solicitudes que requieren cotizaciones (compras y servicios regulares)
        $purchaseRequests = PurchaseRequest::where(function($query) {
                                // Solicitudes de compra
                                $query->where('type', 'purchase')
                                      // O servicios regulares (que requieren cotización)
                                      ->orWhere(function($q) {
                                          $q->where('type', 'services')
                                            ->where('service_type', 'regular');
                                      });
                            })
                            ->whereIn('status', ['pending', 'En Cotización'])
                            ->with('quotations', 'user')
                            ->latest()
                            ->paginate(10);
        
        return view('quotations.index', compact('purchaseRequests'));
    }

    /**
     * Preguntar al usuario si desea adjuntar otra cotización
     */
    public function askForMore(PurchaseRequest $purchaseRequest)
    {
        // Verificar que sea una solicitud que requiere cotizaciones
        if (!$purchaseRequest->requiresQuotations()) {
            return redirect()->back()->with('error', 'Esta solicitud no requiere cotizaciones.');
        }

        // Verificar que no tenga ya el límite máximo de cotizaciones configurado
        $requiredQuotations = $purchaseRequest->getRequiredQuotationsCount();
        if ($purchaseRequest->quotations()->count() >= $requiredQuotations) {
            return redirect()->back()->with('error', "Ya se han subido {$requiredQuotations} cotizaciones para esta solicitud.");
        }
        
        return view('quotations.ask_for_more', compact('purchaseRequest'));
    }
    
    /**
     * Procesar la respuesta del usuario sobre adjuntar otra cotización
     */
    public function processMoreQuotations(Request $request, PurchaseRequest $purchaseRequest)
    {
        $answer = $request->input('answer', 'no');
        
        if ($answer === 'yes') {
            // Redirigir a la página de crear nueva cotización
            return redirect()->route('quotations.create', $purchaseRequest);
        } else {
            // Si el usuario no desea adjuntar más cotizaciones y no hay suficientes,
            // enviar correo a la sección correspondiente
            $quotationCount = $purchaseRequest->quotations()->count();
            
            if ($quotationCount > 0 && $quotationCount < 3) {
                // Obtener correos de la sección correspondiente
                $sectionEmails = $this->getSectionEmails($purchaseRequest->section_area);
                
                if (!empty($sectionEmails)) {
                    // Enviar notificación de cotizaciones incompletas
                    $this->sendIncompleteQuotationsNotification($purchaseRequest, $sectionEmails);
                    
                    // Actualizar estado
                    $requiredQuotations = $purchaseRequest->getRequiredQuotationsCount();
                    $purchaseRequest->updateStatus('En Cotización', Auth::id(), "Cotizaciones incompletas enviadas para revisión ({$quotationCount} de {$requiredQuotations})");
                    
                    // Registrar en historial
                    RequestHistory::create([
                        'purchase_request_id' => $purchaseRequest->id,
                        'user_id' => Auth::id(),
                        'action' => 'Cotizaciones incompletas',
                        'notes' => "Se enviaron {$quotationCount} cotizaciones para revisión sin completar las {$requiredQuotations} requeridas.",
                    ]);
                }
            }
            
            return redirect()->route('purchase-requests.show', $purchaseRequest)
                ->with('success', 'Cotizaciones enviadas para revisión. No se agregarán más cotizaciones a esta solicitud.');
        }
    }

    /**
     * Mostrar los detalles de una cotización específica
     *
     * @param Quotation $quotation
     * @return \Illuminate\Http\Response
     */
    public function show(Quotation $quotation)
    {
        // Verificar permisos
        $this->authorize('view', $quotation);
        
        // Obtener la solicitud de compra asociada
        $purchaseRequest = $quotation->purchaseRequest;
        
        return view('purchases.quotations.show', compact('quotation', 'purchaseRequest'));
    }
    
    /**
     * Obtener los correos electrónicos de la sección especificada
     * 
     * @param string $section_area Nombre de la sección o área
     * @return array Lista de correos electrónicos asociados a la sección
     */
    private function getSectionEmails($section_area)
    {
        if (empty($section_area)) {
            \Log::warning('Sección/Área vacía en getSectionEmails');
            $configSource = \App\Services\DynamicSectionEmailsService::getCurrentConfigSource();
            return [config($configSource . '.default')];
        }
        
        \Log::info("Buscando email para la sección: '$section_area'");
        
        // Obtener todas las secciones configuradas usando configuración dinámica
        $configSource = \App\Services\DynamicSectionEmailsService::getCurrentConfigSource();
        $configuredSections = config($configSource . '.sections');
        
        // Verificar si la sección existe exactamente como está escrita
        if (isset($configuredSections[$section_area])) {
            $email = $configuredSections[$section_area];
            $emailLog = is_array($email) ? '[' . implode(', ', $email) . ']' : $email;
            \Log::info("✓ Email encontrado para la sección '$section_area': " . $emailLog);
            return is_array($email) ? $email : [$email];
        }
        
        // Si no se encuentra exactamente, buscar por coincidencia sin distinguir mayúsculas/minúsculas
        foreach ($configuredSections as $sectionName => $sectionEmail) {
            if (strcasecmp($sectionName, $section_area) === 0) {
                $emailLog = is_array($sectionEmail) ? '[' . implode(', ', $sectionEmail) . ']' : $sectionEmail;
                \Log::info("✓ Email encontrado para la sección '$section_area' (case insensitive): " . $emailLog);
                return is_array($sectionEmail) ? $sectionEmail : [$sectionEmail];
            }
        }
        
        // Último intento - buscar si la sección es parte del nombre configurado o viceversa
        foreach ($configuredSections as $sectionName => $sectionEmail) {
            if (stripos($sectionName, $section_area) !== false || stripos($section_area, $sectionName) !== false) {
                $emailLog = is_array($sectionEmail) ? '[' . implode(', ', $sectionEmail) . ']' : $sectionEmail;
                \Log::info("✓ Email encontrado por coincidencia parcial '$sectionName' para la sección '$section_area': " . $emailLog);
                return is_array($sectionEmail) ? $sectionEmail : [$sectionEmail];
            }
        }
        
        // Si aún no se encuentra, usar el email predeterminado
        $defaultEmail = config($configSource . '.default');
        \Log::warning("✗ No se encontró email configurado para la sección: '$section_area'. Usando predeterminado: $defaultEmail");
        
        return [$defaultEmail];
    }

    /**
     * Enviar notificación de cotizaciones incompletas
     */
    private function sendIncompleteQuotationsNotification($purchaseRequest, $emails)
    {
        // Creamos la notificación
        $notification = new \App\Notifications\IncompleteQuotations($purchaseRequest);
        
        // Añadimos correos adicionales que siempre deben ser notificados usando configuración dinámica
        $configSource = \App\Services\DynamicSectionEmailsService::getCurrentConfigSource();
        $additionalEmails = config($configSource . '.always_notify', []);
        
        // Combinamos los correos sin duplicados
        $allEmails = array_unique(array_merge($emails, $additionalEmails));
        
        \Log::info('Preparando envío de notificación de cotizaciones incompletas', [
            'purchase_request' => $purchaseRequest->request_number,
            'section_area' => $purchaseRequest->section_area,
            'emails' => $allEmails
        ]);
        
        // Enviamos por mail
        try {
            foreach ($allEmails as $email) {
                \Log::info('Enviando notificación de cotizaciones incompletas a: ' . $email);
                Notification::route('mail', $email)->notify($notification);
            }
            
            // Registrar en logs los correos notificados
            \Log::info('Notificación de cotizaciones incompletas enviada', [
                'purchase_request' => $purchaseRequest->request_number,
                'emails' => $allEmails
            ]);
        } catch (\Exception $e) {
            \Log::error('Error al enviar notificación de cotizaciones incompletas: ' . $e->getMessage(), [
                'purchase_request' => $purchaseRequest->request_number,
                'emails' => $allEmails,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Enviar email de pre-aprobación manualmente
     */
    public function sendPreApprovalEmail(PurchaseRequest $purchaseRequest)
    {
        try {
            // Verificar que hay cotizaciones
            if ($purchaseRequest->quotations()->count() == 0) {
                return redirect()->back()->with('error', 'No hay cotizaciones para enviar.');
            }

            // Verificar estado de la solicitud
            if ($purchaseRequest->status !== 'En Cotización') {
                return redirect()->back()->with('error', 'La solicitud debe estar en estado "En Cotización" para enviar emails de pre-aprobación.');
            }

            // Obtener emails de la sección correspondiente
            $sectionEmails = $this->getSectionEmails($purchaseRequest->section_area);
            
            // Obtener configuración dinámica
            $configSource = \App\Services\DynamicSectionEmailsService::getCurrentConfigSource();
            $comprasEmail = config($configSource . '.sections.Compras', config($configSource . '.default'));
            
            \Log::info('Enviando email de pre-aprobación manual con flujo diferenciado', [
                'purchase_request' => $purchaseRequest->request_number,
                'section_area' => $purchaseRequest->section_area,
                'section_emails' => $sectionEmails,
                'compras_email' => $comprasEmail,
                'quotations_count' => $purchaseRequest->quotations()->count()
            ]);
            
            // 1. ENVIAR NOTIFICACIÓN CON BOTÓN A DIRECTORES/COORDINADORES
            $notificationWithButton = new QuotationsUploaded($purchaseRequest->fresh());
            
            foreach ($sectionEmails as $email) {
                \Log::info('Enviando notificación CON BOTÓN (manual) a director/coordinador: ' . $email);
                Notification::route('mail', $email)
                    ->notify($notificationWithButton);
            }
            
            // 2. ENVIAR NOTIFICACIÓN INFORMATIVA A COMPRAS (SIN BOTÓN)
            \Log::info('Enviando notificación INFORMATIVA (manual) a compras: ' . $comprasEmail);
            $informativeNotification = new \App\Notifications\QuotationsCompletedCompras($purchaseRequest->fresh());
            Notification::route('mail', $comprasEmail)
                ->notify($informativeNotification);
            
            $allEmails = array_merge($sectionEmails, [$comprasEmail]);
            
            // Marcar que se envió para pre-aprobación y cambiar estado
            $purchaseRequest->update([
                'status' => 'En pre-aprobación',
                'preapproval_sent_at' => now(),
                'preapproval_sent_by' => Auth::id()
            ]);
            
            // Registrar en el historial
            RequestHistory::create([
                'purchase_request_id' => $purchaseRequest->id,
                'user_id' => Auth::id(),
                'action' => 'Email de pre-aprobación enviado - Estado cambiado a "En pre-aprobación"',
                'notes' => 'Emails enviados a: ' . implode(', ', $allEmails)
            ]);
            
            \Log::info('Email de pre-aprobación enviado exitosamente', [
                'purchase_request' => $purchaseRequest->request_number,
                'emails' => $allEmails
            ]);
            
            return redirect()->back()->with('success', 'Emails de pre-aprobación enviados exitosamente a: ' . implode(', ', $allEmails));
            
        } catch (\Exception $e) {
            \Log::error('Error al enviar email de pre-aprobación: ' . $e->getMessage(), [
                'purchase_request' => $purchaseRequest->request_number,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()->with('error', 'Error al enviar emails de pre-aprobación: ' . $e->getMessage());
        }
    }
    
    /**
     * Marcar una solicitud como completada y enviarla automáticamente a preaprobación
     */
    public function markCompleted(PurchaseRequest $purchaseRequest)
    {
        try {
            // Verificar autorización (usar la misma política que para agregar cotizaciones)
            $this->authorize('addQuotation', $purchaseRequest);

            // Verificar estado de la solicitud - Permitir tanto 'pending' como 'En Cotización'
            if (!in_array($purchaseRequest->status, ['pending', 'En Cotización'])) {
                return redirect()->back()->with('error', 'La solicitud debe estar en estado "pending" o "En Cotización" para marcarla como completada.');
            }

            // Log para auditoría
            \Log::info('Marcando solicitud como completada y enviando a preaprobación', [
                'purchase_request_id' => $purchaseRequest->id,
                'request_number' => $purchaseRequest->request_number,
                'user_id' => Auth::id(),
                'quotations_count' => $purchaseRequest->quotations()->count()
            ]);

            // Obtener emails de la sección correspondiente
            $sectionEmails = $this->getSectionEmails($purchaseRequest->section_area);
            
            // Obtener configuración dinámica
            $configSource = \App\Services\DynamicSectionEmailsService::getCurrentConfigSource();
            $comprasEmail = config($configSource . '.sections.Compras', config($configSource . '.default'));
            
            // 1. ENVIAR NOTIFICACIÓN CON BOTÓN A DIRECTORES/COORDINADORES
            $notificationWithButton = new QuotationsUploaded($purchaseRequest->fresh());
            
            foreach ($sectionEmails as $email) {
                \Log::info('Enviando notificación CON BOTÓN (hecho cumplido) a director/coordinador: ' . $email);
                Notification::route('mail', $email)
                    ->notify($notificationWithButton);
            }
            
            // 2. ENVIAR NOTIFICACIÓN INFORMATIVA A COMPRAS (SIN BOTÓN)
            \Log::info('Enviando notificación INFORMATIVA (hecho cumplido) a compras: ' . $comprasEmail);
            $informativeNotification = new \App\Notifications\QuotationsCompletedCompras($purchaseRequest->fresh());
            Notification::route('mail', $comprasEmail)
                ->notify($informativeNotification);
            
            $allEmails = array_merge($sectionEmails, [$comprasEmail]);
            
            // Marcar que se envió para pre-aprobación y cambiar estado
            $purchaseRequest->update([
                'status' => 'En pre-aprobación',
                'preapproval_sent_at' => now(),
                'preapproval_sent_by' => Auth::id()
            ]);
            
            // Registrar en el historial
            RequestHistory::create([
                'purchase_request_id' => $purchaseRequest->id,
                'user_id' => Auth::id(),
                'action' => 'Hecho Cumplido - Estado cambiado a "En pre-aprobación"',
                'notes' => 'Solicitud marcada como completada' . 
                          ($purchaseRequest->quotations()->count() > 0 ? 
                           '. Emails enviados a: ' . implode(', ', $allEmails) : 
                           ' sin cotizaciones. Emails enviados a: ' . implode(', ', $allEmails))
            ]);
            
            \Log::info('Solicitud marcada como completada y enviada a preaprobación exitosamente', [
                'purchase_request' => $purchaseRequest->request_number,
                'emails' => $allEmails,
                'had_quotations' => $purchaseRequest->quotations()->count() > 0
            ]);
            
            $successMessage = 'Solicitud marcada como completada exitosamente.';
            if (count($allEmails) > 0) {
                $successMessage .= ' Se han enviado las notificaciones de preaprobación a: ' . implode(', ', $allEmails);
            }
            
            return redirect()->route('quotations.index')->with('success', $successMessage);
            
        } catch (\Exception $e) {
            \Log::error('Error al marcar solicitud como completada: ' . $e->getMessage(), [
                'purchase_request' => $purchaseRequest->request_number,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()->with('error', 'Error al marcar la solicitud como completada: ' . $e->getMessage());
        }
    }
    
    /**
     * Anular una solicitud de compra por falta de descripción adecuada
     */
    public function cancelForDescription(Request $request, PurchaseRequest $purchaseRequest)
    {
        // Verificar autorización
        $this->authorize('addQuotation', $purchaseRequest);
        
        // Validar los datos del formulario
        $request->validate([
            'reason' => 'required|string|max:500|min:10',
        ], [
            'reason.required' => 'El motivo de anulación es obligatorio.',
            'reason.max' => 'El motivo no puede exceder 500 caracteres.',
            'reason.min' => 'El motivo debe tener al menos 10 caracteres.',
        ]);
        
        try {
            // Verificar que la solicitud esté en un estado que permita anulación
            if (!in_array($purchaseRequest->status, ['pending', 'in_process', 'waiting_quotations'])) {
                return redirect()->back()->with('error', 'Esta solicitud no puede ser anulada en su estado actual.');
            }
            
            // Log para auditoría
            \Log::info('Iniciando anulación por falta de descripción', [
                'purchase_request_id' => $purchaseRequest->id,
                'request_number' => $purchaseRequest->request_number,
                'current_status' => $purchaseRequest->status,
                'cancelled_by' => Auth::id(),
                'reason' => $request->reason
            ]);
            
            // Actualizar el estado y guardar el motivo de anulación
            $purchaseRequest->status = 'cancelled_for_description';
            $purchaseRequest->rejection_reason = $request->reason;
            $purchaseRequest->save();
            
            // Crear registro en el historial
            RequestHistory::create([
                'purchase_request_id' => $purchaseRequest->id,
                'user_id' => Auth::id(),
                'action' => 'Solicitud anulada por falta de descripción',
                'notes' => 'Motivo: ' . $request->reason
            ]);
            
            // Crear notificación personalizada para anulación por descripción
            $notificationData = [
                'subject' => 'Solicitud Anulada - Se Requiere Más Información',
                'greeting' => '¡Hola ' . $purchaseRequest->requester . '!',
                'line1' => 'Su solicitud de compra ha sido **anulada** porque necesita información adicional en la descripción.',
                'line2' => '**Motivo específico:** ' . $request->reason,
                'line3' => '**¿Qué debe hacer ahora?**',
                'line4' => '• Revise el motivo indicado arriba',
                'line5' => '• Proporcione una descripción más detallada y específica',
                'line6' => '• Cree una nueva solicitud con la información completa',
                'action_text' => 'Crear Nueva Solicitud',
                'action_url' => route('purchase-requests.create'),
                'closing' => 'Esto nos ayuda a obtener mejores cotizaciones para su solicitud.',
                'salutation' => 'Equipo de Compras - The Victoria School'
            ];
            
            // Enviar notificación al usuario
            if ($purchaseRequest->user) {
                $purchaseRequest->user->notify(new class($purchaseRequest, $notificationData) extends \Illuminate\Notifications\Notification {
                    use \Illuminate\Bus\Queueable;
                    
                    protected $purchaseRequest;
                    protected $data;
                    
                    public function __construct($purchaseRequest, $data)
                    {
                        $this->purchaseRequest = $purchaseRequest;
                        $this->data = $data;
                    }
                    
                    public function via($notifiable)
                    {
                        return ['mail', 'database'];
                    }
                    
                    public function toMail($notifiable)
                    {
                        return (new \Illuminate\Notifications\Messages\MailMessage)
                            ->subject($this->data['subject'])
                            ->greeting($this->data['greeting'])
                            ->line($this->data['line1'])
                            ->line($this->data['line2'])
                            ->line('')
                            ->line($this->data['line3'])
                            ->line($this->data['line4'])
                            ->line($this->data['line5'])
                            ->line($this->data['line6'])
                            ->line('')
                            ->action($this->data['action_text'], $this->data['action_url'])
                            ->line($this->data['closing'])
                            ->salutation($this->data['salutation']);
                    }
                    
                    public function toDatabase($notifiable)
                    {
                        return [
                            'id' => $this->purchaseRequest->id,
                            'title' => $this->purchaseRequest->title,
                            'message' => 'Solicitud anulada - Se requiere más información en la descripción',
                            'url' => route('purchase-requests.show', $this->purchaseRequest->id)
                        ];
                    }
                });
            }
            
            \Log::info('Solicitud anulada por falta de descripción exitosamente', [
                'purchase_request_id' => $purchaseRequest->id,
                'request_number' => $purchaseRequest->request_number,
                'cancelled_by' => Auth::id()
            ]);
            
            return redirect()->route('quotations.index')
                ->with('success', 'La solicitud ha sido anulada exitosamente. Se ha notificado al usuario que debe proporcionar una descripción más detallada.');
                
        } catch (\Exception $e) {
            \Log::error('Error al anular solicitud por falta de descripción: ' . $e->getMessage(), [
                'purchase_request_id' => $purchaseRequest->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()->with('error', 'Error al anular la solicitud: ' . $e->getMessage());
        }
    }
}
