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
        
        // Verificar que no haya más de 3 cotizaciones
        if ($purchaseRequest->quotations()->count() >= 3) {
            return redirect()->back()->with('error', 'Ya se han subido 3 cotizaciones para esta solicitud.');
        }
        
        $validator = Validator::make($request->all(), [
            'provider_name' => 'required|string|max:255',
            'subtotal' => 'required|numeric|min:0',
            'total_amount' => 'required|numeric|min:0',
            'includes_iva' => 'nullable|boolean',
            'iva_amount' => 'nullable|numeric|min:0',
            'quotation_file' => 'required|file|mimes:pdf|max:5120',
            'additional_items' => 'nullable|array',
            'additional_items.*.description' => 'required_with:additional_items|string|max:255',
            'additional_items.*.quantity' => 'required_with:additional_items|numeric|min:0',
            'additional_items.*.unit' => 'nullable|string|max:50',
            'additional_items.*.price' => 'required_with:additional_items|numeric|min:0',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        // Procesar items adicionales
        $additionalItems = [];
        if ($request->has('additional_items') && is_array($request->additional_items)) {
            foreach ($request->additional_items as $key => $item) {
                if (!empty($item['description']) && !empty($item['quantity']) && isset($item['price'])) {
                    $quantity = floatval($item['quantity']);
                    $price = floatval($item['price']);
                    $total = $quantity * $price;
                    
                    $additionalItems[] = [
                        'description' => $item['description'],
                        'quantity' => $quantity,
                        'unit' => $item['unit'] ?? 'Unidad',
                        'price' => $price,
                        'total' => $total
                    ];
                }
            }
        }
        
        // Validar cálculos (verificar que los totales sean consistentes)
        $subtotal = floatval($request->subtotal);
        $additionalItemsTotal = array_sum(array_column($additionalItems, 'total'));
        $totalSubtotal = $subtotal + $additionalItemsTotal;
        $includesIva = $request->has('includes_iva');
        $expectedIvaAmount = $includesIva ? $totalSubtotal * 0.19 : 0;
        $expectedTotal = $totalSubtotal + $expectedIvaAmount;
        
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
                'includes_iva' => $includesIva,
                'iva_amount' => $expectedIvaAmount,
                'additional_items' => $additionalItems,
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
            
            // Si ya hay 3 cotizaciones, notificar según el flujo corregido
            if ($quotationCount >= 3) {
                // Obtener correos de la sección correspondiente usando configuración dinámica
                $sectionEmails = $this->getSectionEmails($purchaseRequest->section_area);
                
                // Obtener configuración dinámica
                $configSource = \App\Services\DynamicSectionEmailsService::getCurrentConfigSource();
                $comprasEmail = config($configSource . '.sections.Compras', config($configSource . '.default'));
                
                \Log::info('Preparando envío de notificaciones diferenciadas', [
                    'purchase_request' => $purchaseRequest->request_number,
                    'section_area' => $purchaseRequest->section_area,
                    'section_emails' => $sectionEmails,
                    'compras_email' => $comprasEmail
                ]);
                
                // 1. ENVIAR NOTIFICACIÓN CON BOTÓN A DIRECTORES/COORDINADORES
                $notificationWithButton = new QuotationsUploaded($purchaseRequest->fresh());
                
                try {
                    foreach ($sectionEmails as $email) {
                        \Log::info('Enviando notificación CON BOTÓN (director/coordinador) a: ' . $email);
                        Notification::route('mail', $email)
                            ->notify($notificationWithButton);
                    }
                } catch (\Exception $e) {
                    \Log::error('Error al enviar notificación con botón: ' . $e->getMessage(), [
                        'purchase_request' => $purchaseRequest->request_number,
                        'section_emails' => $sectionEmails,
                        'error' => $e->getMessage()
                    ]);
                }
                
                // 2. ENVIAR NOTIFICACIÓN INFORMATIVA A COMPRAS (SIN BOTÓN)
                try {
                    \Log::info('Enviando notificación INFORMATIVA (sin botón) a compras: ' . $comprasEmail);
                    $informativeNotification = new \App\Notifications\QuotationsCompletedCompras($purchaseRequest->fresh());
                    Notification::route('mail', $comprasEmail)
                        ->notify($informativeNotification);
                } catch (\Exception $e) {
                    \Log::error('Error al enviar notificación informativa a compras: ' . $e->getMessage(), [
                        'purchase_request' => $purchaseRequest->request_number,
                        'compras_email' => $comprasEmail,
                        'error' => $e->getMessage()
                    ]);
                }
                
                // Registrar resumen final
                \Log::info('Notificaciones diferenciadas enviadas exitosamente', [
                    'purchase_request' => $purchaseRequest->request_number,
                    'directores_con_boton' => $sectionEmails,
                    'compras_informativo' => $comprasEmail
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
            'status_after' => $purchaseRequest->fresh()->status
        ]);
        
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
        // Obtener solicitudes de tipo compra (purchase) que estén pendientes o en cotización
        $purchaseRequests = PurchaseRequest::where('type', 'purchase')
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
        // Verificar que sea una solicitud de tipo compra
        if (!$purchaseRequest->isPurchaseRequest()) {
            return redirect()->back()->with('error', 'Solo se pueden adjuntar cotizaciones a solicitudes de compra.');
        }

        // Verificar que no tenga ya 3 cotizaciones
        if ($purchaseRequest->quotations()->count() >= 3) {
            return redirect()->back()->with('error', 'Ya se han subido 3 cotizaciones para esta solicitud.');
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
                    $purchaseRequest->updateStatus('En Cotización', Auth::id(), 'Cotizaciones incompletas enviadas para revisión (' . $quotationCount . ' de 3)');
                    
                    // Registrar en historial
                    RequestHistory::create([
                        'purchase_request_id' => $purchaseRequest->id,
                        'user_id' => Auth::id(),
                        'action' => 'Cotizaciones incompletas',
                        'notes' => 'Se enviaron ' . $quotationCount . ' cotizaciones para revisión sin completar las 3 requeridas.',
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
            
            // Registrar en el historial
            RequestHistory::create([
                'purchase_request_id' => $purchaseRequest->id,
                'user_id' => Auth::id(),
                'action' => 'Email de pre-aprobación enviado',
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
