<?php

namespace App\Http\Controllers;

use App\Models\PurchaseRequest;
use App\Models\Quotation;
use App\Models\RequestHistory;
use App\Models\User;
use App\Notifications\QuotationsUploaded;
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
            'total_amount' => 'required|numeric|min:0',
            'quotation_file' => 'required|file|mimes:pdf|max:5120',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
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
            
            // Si ya hay 3 cotizaciones, notificar a la sección correspondiente
            if ($quotationCount >= 3) {
                // Obtener correos de la sección correspondiente
                $sectionEmails = $this->getSectionEmails($purchaseRequest->section_area);
                
                // Añadir correos adicionales que siempre deben ser notificados
                $additionalEmails = config('section_emails.always_notify', []);
                $allEmails = array_unique(array_merge($sectionEmails, $additionalEmails));
                
                \Log::info('Preparando envío de notificación de cotizaciones completas', [
                    'purchase_request' => $purchaseRequest->request_number,
                    'section_area' => $purchaseRequest->section_area,
                    'section_emails' => $sectionEmails,
                    'additional_emails' => $additionalEmails,
                    'all_emails' => $allEmails
                ]);
                
                // Crear y enviar la notificación
                $notification = new QuotationsUploaded($purchaseRequest->fresh());
                
                try {
                    foreach ($allEmails as $email) {
                        // Usar un pequeño retraso para evitar problemas de throttling
                        \Log::info('Enviando notificación a: ' . $email);
                        Notification::route('mail', $email)
                            ->notify($notification);
                    }
                    
                    // Registrar en logs
                    \Log::info('Notificación de cotizaciones completas enviada', [
                        'purchase_request' => $purchaseRequest->request_number,
                        'emails' => $allEmails
                    ]);
                } catch (\Exception $e) {
                    \Log::error('Error al enviar notificación de cotizaciones: ' . $e->getMessage(), [
                        'purchase_request' => $purchaseRequest->request_number,
                        'emails' => $allEmails,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }
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
     * Obtener los correos electrónicos de la sección especificada
     * 
     * @param string $section_area Nombre de la sección o área
     * @return array Lista de correos electrónicos asociados a la sección
     */
    private function getSectionEmails($section_area)
    {
        if (empty($section_area)) {
            \Log::warning('Sección/Área vacía en getSectionEmails');
            return [config('section_emails.default')];
        }
        
        \Log::info("Buscando email para la sección: '$section_area'");
        
        // Obtener todas las secciones configuradas
        $configuredSections = config('section_emails.sections');
        
        // Verificar si la sección existe exactamente como está escrita
        if (isset($configuredSections[$section_area])) {
            $email = $configuredSections[$section_area];
            \Log::info("✓ Email encontrado para la sección '$section_area': " . $email);
            return is_array($email) ? $email : [$email];
        }
        
        // Si no se encuentra exactamente, buscar por coincidencia sin distinguir mayúsculas/minúsculas
        foreach ($configuredSections as $sectionName => $sectionEmail) {
            if (strcasecmp($sectionName, $section_area) === 0) {
                \Log::info("✓ Email encontrado para la sección '$section_area' (case insensitive): " . $sectionEmail);
                return is_array($sectionEmail) ? $sectionEmail : [$sectionEmail];
            }
        }
        
        // Último intento - buscar si la sección es parte del nombre configurado o viceversa
        foreach ($configuredSections as $sectionName => $sectionEmail) {
            if (stripos($sectionName, $section_area) !== false || stripos($section_area, $sectionName) !== false) {
                \Log::info("✓ Email encontrado por coincidencia parcial '$sectionName' para la sección '$section_area': " . $sectionEmail);
                return is_array($sectionEmail) ? $sectionEmail : [$sectionEmail];
            }
        }
        
        // Si aún no se encuentra, usar el email predeterminado
        $defaultEmail = config('section_emails.default');
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
        
        // Añadimos correos adicionales que siempre deben ser notificados
        $additionalEmails = config('section_emails.always_notify', []);
        
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
            
            // Añadir correos adicionales que siempre deben ser notificados
            $additionalEmails = config('section_emails.always_notify', []);
            $allEmails = array_unique(array_merge($sectionEmails, $additionalEmails));
            
            \Log::info('Enviando email de pre-aprobación manual', [
                'purchase_request' => $purchaseRequest->request_number,
                'section_area' => $purchaseRequest->section_area,
                'emails' => $allEmails,
                'quotations_count' => $purchaseRequest->quotations()->count()
            ]);
            
            // Crear y enviar la notificación QuotationsUploaded
            $notification = new QuotationsUploaded($purchaseRequest->fresh());
            
            foreach ($allEmails as $email) {
                \Log::info('Enviando notificación de pre-aprobación a: ' . $email);
                Notification::route('mail', $email)
                    ->notify($notification);
            }
            
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
}
