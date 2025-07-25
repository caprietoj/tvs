<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PurchaseRequest;
use App\Models\Quotation;
use App\Models\RequestHistory;
use App\Notifications\QuotationSelected;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;

class QuotationApprovalController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Mostrar la lista de solicitudes pendientes de pre-aprobación.
     */
    public function index()
    {
        // Obtener todas las solicitudes de compra que estén en estado "En Cotización", "En pre-aprobación" o "Pre-aprobada"
        // Excluir servicios sin cotización
        $requests = PurchaseRequest::whereIn('status', ['En Cotización', 'En pre-aprobación', 'Pre-aprobada'])
            ->where(function($query) {
                $query->where('type', '!=', 'services')
                      ->orWhere(function($subQuery) {
                          $subQuery->where('type', 'services')
                                   ->where(function($serviceQuery) {
                                       $serviceQuery->whereNull('service_type')
                                                   ->orWhere('service_type', 'regular');
                                   });
                      });
            })
            ->with(['quotations'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('quotation-approvals.index', compact('requests'));
    }

    /**
     * Mostrar los detalles de una solicitud específica.
     */
    public function show($id)
    {
        // Obtener la solicitud específica con sus cotizaciones
        $request = PurchaseRequest::with(['quotations'])->findOrFail($id);
        
        // Para servicios sin cotización, redirigir al flujo de aprobación directa
        if ($request->type === 'services' && $request->isNoQuotationService()) {
            return redirect()->route('approvals.show', $id)
                ->with('info', 'Este servicio no requiere cotización. Redirigiendo al flujo de aprobación directa.');
        }
        
        // Verificar si hay selecciones mixtas
        $mixedSelections = \App\Models\QuotationItemSelection::where('purchase_request_id', $id)
            ->with(['quotation', 'selectedBy'])
            ->get();
            
        $hasMixedSelections = $mixedSelections->count() > 0;
        
        // Obtener items de la solicitud para mostrar comparación con selecciones
        $purchaseItems = [];
        $selectedQuotations = collect();
        
        if ($hasMixedSelections) {
            $purchaseItems = is_array($request->purchase_items) 
                ? $request->purchase_items 
                : json_decode($request->purchase_items, true);
                
            // Obtener las cotizaciones que fueron seleccionadas en la selección mixta
            $selectedQuotationIds = $mixedSelections->pluck('quotation_id')->unique();
            $selectedQuotations = $request->quotations()->whereIn('id', $selectedQuotationIds)->get();
            
            // Agrupar selecciones por cotización para mostrar el resumen
            $selectionsByQuotation = $mixedSelections->groupBy('quotation_id');
            $selectedQuotations = $selectedQuotations->map(function($quotation) use ($selectionsByQuotation) {
                $quotation->selectedItems = $selectionsByQuotation->get($quotation->id, collect());
                $quotation->selectedItemsTotal = $quotation->selectedItems->sum('total_price');
                return $quotation;
            });
        }
        
        // Permitir que las solicitudes sin cotizaciones continúen al flujo de preaprobación
        // ya que pueden haber pasado por el proceso de "Hecho Cumplido" sin requerir cotizaciones
        
        // Verificar el estado de aprobación antes de mostrar la vista
        $approvalInfo = $this->checkApprovalStatus($request);
        
        return view('quotation-approvals.show', compact('request', 'mixedSelections', 'hasMixedSelections', 'purchaseItems', 'selectedQuotations', 'approvalInfo'));
    }

    /**
     * Verificar el estado de aprobación de una solicitud antes de permitir acciones
     */
    private function checkApprovalStatus(PurchaseRequest $request)
    {
        // Verificar si ya está aprobada
        if ($request->status === 'approved') {
            $approver = \App\Models\User::find($request->approved_by);
            return [
                'type' => 'approved',
                'title' => 'Solicitud ya Aprobada',
                'message' => 'Esta solicitud ya ha sido aprobada definitivamente.',
                'approver' => $approver ? $approver->name : 'Usuario no encontrado',
                'date' => $request->approval_date ? $request->approval_date->format('d/m/Y H:i:s') : 'Fecha no disponible'
            ];
        }
        
        // Verificar si ya está pre-aprobada
        if (in_array($request->status, ['pre-approved', 'Pre-aprobada'])) {
            $preApprover = \App\Models\User::find($request->pre_approved_by);
            return [
                'type' => 'pre-approved',
                'title' => 'Solicitud ya Pre-aprobada',
                'message' => 'Esta solicitud ya ha sido pre-aprobada y está esperando aprobación final.',
                'approver' => $preApprover ? $preApprover->name : 'Usuario no encontrado',
                'date' => $request->pre_approved_at ? $request->pre_approved_at->format('d/m/Y H:i:s') : 'Fecha no disponible'
            ];
        }
        
        return null;
    }

    /**
     * Pre-aprobar una solicitud seleccionando una cotización.
     */
    public function preApprove(Request $request, $id)
    {
        \Log::info('Iniciando pre-aprobación normal', [
            'purchase_request_id' => $id,
            'request_data' => $request->all(),
            'user_id' => auth()->id()
        ]);
        
        // Validar la entrada
        $validated = $request->validate([
            'quotation_id' => 'required|exists:quotations,id',
            'comments' => 'nullable|string',
            'budget' => 'required|string|max:255',
        ]);

        \Log::info('Validación exitosa para pre-aprobación', [
            'validated_data' => $validated
        ]);

        // Obtener la solicitud y la cotización
        $purchaseRequest = PurchaseRequest::findOrFail($id);
        
        // Verificar el estado de aprobación antes de proceder
        $approvalInfo = $this->checkApprovalStatus($purchaseRequest);
        if ($approvalInfo) {
            return redirect()->back()->with('approvalInfo', $approvalInfo);
        }
        
        $quotation = Quotation::findOrFail($validated['quotation_id']);

        // Desmarcar cualquier otra cotización que pudiera estar seleccionada
        Quotation::where('purchase_request_id', $purchaseRequest->id)
            ->update(['is_selected' => false]);
            
        // Actualizar el estado de la cotización a pre-aprobada y marcarla como seleccionada
        $quotation->update([
            'status' => 'pre-approved',
            'is_selected' => true,
            'pre_approval_date' => now(),
            'pre_approval_comments' => $validated['comments'] ?? null,
            'pre_approved_by' => auth()->id(),
        ]);

        // Actualizar el estado de la solicitud a pre-aprobada
        $purchaseRequest->update([
            'status' => 'Pre-aprobada',
            'pre_approved_quotation_id' => $quotation->id,
            'pre_approved_by' => auth()->id(),
            'pre_approved_at' => now(),
            'budget' => $validated['budget']
        ]);

        \Log::info('Solicitud de compra actualizada exitosamente', [
            'purchase_request_id' => $id,
            'budget_saved' => $validated['budget'],
            'status' => 'Pre-aprobada'
        ]);

        // Registrar en el historial
        RequestHistory::create([
            'purchase_request_id' => $purchaseRequest->id,
            'user_id' => Auth::id(),
            'action' => 'Pre-aprobación de cotización',
            'notes' => 'Cotización de ' . $quotation->provider_name . ' pre-aprobada' . 
                      ($validated['comments'] ? '. Comentarios: ' . $validated['comments'] : ''),
        ]);

        // Cargar la solicitud con las relaciones necesarias para la notificación
        $purchaseRequest = $purchaseRequest->fresh(['user']);

        try {
            // Determinar el tipo de sección y enviar correo al director correspondiente
            $sectionClassifier = new \App\Services\SectionClassifierService();
            $directorEmail = $sectionClassifier->getDirectorEmail($purchaseRequest->section_area);
            
            // Obtener emails específicos de la sección
            $sectionEmails = $sectionClassifier->getSectionEmails($purchaseRequest->section_area);
            
            // Crear lista de todos los emails que deben ser notificados
            $allEmails = [];
            
            // Agregar director
            if ($directorEmail) {
                $allEmails[] = $directorEmail;
            }
            
            // Agregar emails específicos de la sección usando configuración dinámica
            if (!empty($sectionEmails)) {
                $allEmails = array_merge($allEmails, $sectionEmails);
            }
            
            // Agregar correos que SIEMPRE deben ser notificados (incluye compras)
            $configSource = \App\Services\DynamicSectionEmailsService::getCurrentConfigSource();
            $alwaysNotifyEmails = config($configSource . '.always_notify', []);
            foreach ($alwaysNotifyEmails as $email) {
                if (!in_array($email, $allEmails)) {
                    $allEmails[] = $email;
                }
            }
            
            // Doble verificación: agregar compras específicamente si no está incluido
            $comprasEmail = config($configSource . '.sections.Compras', config($configSource . '.default'));
            if (!in_array($comprasEmail, $allEmails)) {
                $allEmails[] = $comprasEmail;
            }
            
            // Eliminar duplicados
            $allEmails = array_unique($allEmails);
            
            // Registrar en log a quién se está enviando la notificación
            \Log::info('Enviando notificación de pre-aprobación', [
                'purchase_request' => $purchaseRequest->request_number,
                'section' => $purchaseRequest->section_area,
                'classification' => $sectionClassifier->classifySection($purchaseRequest->section_area),
                'director_email' => $directorEmail,
                'section_emails' => $sectionEmails,
                'all_emails' => $allEmails,
                'quotation_id' => $quotation->id,
                'provider' => $quotation->provider_name
            ]);
            
            // CORREGIDO: Enviar notificación SOLO al director de sección (no a coordinadores ni usuario)
            // Solo obtener el email del director específico para la sección
            $directorEmail = $sectionClassifier->getDirectorEmail($purchaseRequest->section_area);
            
            if ($directorEmail) {
                Notification::route('mail', $directorEmail)
                    ->notify(new \App\Notifications\QuotationPreApproved($purchaseRequest, $directorEmail, $quotation));
                \Log::info("Notificación de pre-aprobación enviada SOLO al director: $directorEmail");
            }
            
            // NUEVO: Enviar notificación informativa a compras (sin botón de aprobar)
            $configSource = \App\Services\DynamicSectionEmailsService::getCurrentConfigSource();
            $comprasEmail = config($configSource . '.sections.Compras', config($configSource . '.default'));
            
            if ($comprasEmail) {
                Notification::route('mail', $comprasEmail)
                    ->notify(new \App\Notifications\QuotationPreApprovedCompras($purchaseRequest, $quotation));
                \Log::info("Notificación informativa de pre-aprobación enviada a compras: $comprasEmail");
            }
            
            \Log::info('Notificaciones de pre-aprobación enviadas correctamente', [
                'director_email' => $directorEmail,
                'compras_email' => $comprasEmail,
                'purchase_request' => $purchaseRequest->request_number,
                'quotation_id' => $quotation->id
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error al enviar notificación de pre-aprobación: ' . $e->getMessage(), [
                'purchase_request' => $purchaseRequest->request_number,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }

        return redirect()->route('quotation-approvals.show', $id)
            ->with('success', 'La cotización ha sido pre-aprobada correctamente y el estado de la solicitud ha sido actualizado.');
    }

    /**
     * Mostrar la comparación entre cotizaciones.
     */
    public function compareQuotations($id)
    {
        // Obtener la solicitud con sus cotizaciones
        $request = PurchaseRequest::with(['quotations'])->findOrFail($id);
        
        // Si no hay cotizaciones, o hay menos de 2, redirigir con un mensaje
        if ($request->quotations->count() < 2) {
            return redirect()->route('quotation-approvals.index')
                ->with('error', 'No hay suficientes cotizaciones disponibles para comparar. Se requieren al menos 2 cotizaciones.');
        }

        return view('quotation-approvals.comparison', compact('request'));
    }
    
    /**
     * Pre-aprobar una solicitud sin cotizaciones
     */
    public function preApproveWithoutQuotation(Request $request, $id)
    {
        $purchaseRequest = PurchaseRequest::findOrFail($id);
        
        // Verificar el estado de aprobación antes de proceder
        $approvalInfo = $this->checkApprovalStatus($purchaseRequest);
        if ($approvalInfo) {
            return redirect()->back()->with('approvalInfo', $approvalInfo);
        }
        
        // Validar los datos del formulario
        $request->validate([
            'comments' => 'required|string|max:500|min:10',
            'budget_line' => 'required|string'
        ], [
            'comments.required' => 'Los comentarios son obligatorios.',
            'comments.max' => 'Los comentarios no pueden exceder 500 caracteres.',
            'comments.min' => 'Los comentarios deben tener al menos 10 caracteres.',
            'budget_line.required' => 'Debe seleccionar un rubro presupuestal.'
        ]);
        
        try {
            // Log para auditoría
            \Log::info('Iniciando pre-aprobación sin cotización', [
                'purchase_request_id' => $purchaseRequest->id,
                'request_number' => $purchaseRequest->request_number,
                'user_id' => Auth::id(),
                'comments' => $request->comments,
                'budget_line' => $request->budget_line
            ]);
            
            // Actualizar el estado de la solicitud
            $purchaseRequest->update([
                'status' => 'Pre-aprobada',
                'budget_line' => $request->budget_line,
                'pre_approval_date' => now(),
                'pre_approval_comments' => $request->comments,
                'pre_approved_by' => Auth::id()
            ]);
            
            // Registrar en el historial
            RequestHistory::create([
                'purchase_request_id' => $purchaseRequest->id,
                'user_id' => Auth::id(),
                'action' => 'Pre-aprobación sin cotización',
                'notes' => 'Solicitud pre-aprobada sin cotizaciones. Rubro: ' . $request->budget_line . '. Comentarios: ' . $request->comments
            ]);
            
            \Log::info('Pre-aprobación sin cotización completada exitosamente', [
                'purchase_request_id' => $purchaseRequest->id,
                'request_number' => $purchaseRequest->request_number,
                'new_status' => $purchaseRequest->status
            ]);
            
            return redirect()->route('quotation-approvals.index')
                ->with('success', 'La solicitud ha sido pre-aprobada exitosamente sin cotizaciones. Rubro asignado: ' . $request->budget_line);
                
        } catch (\Exception $e) {
            \Log::error('Error al pre-aprobar solicitud sin cotización: ' . $e->getMessage(), [
                'purchase_request_id' => $purchaseRequest->id,
                'request_number' => $purchaseRequest->request_number,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->with('error', 'Error al pre-aprobar la solicitud: ' . $e->getMessage());
        }
    }
    
    /**
     * Pre-aprobar una solicitud con selección mixta de proveedores.
     */
    public function preApproveMixedSelection(Request $request, $id)
    {
        \Log::info('Iniciando pre-aprobación de selección mixta', [
            'purchase_request_id' => $id,
            'request_data' => $request->all(),
            'user_id' => auth()->id()
        ]);
        
        // Validar la entrada
        $validated = $request->validate([
            'comments' => 'nullable|string|max:500',
            'budget_line' => 'required|string|max:255',
        ]);

        // Obtener la solicitud
        $purchaseRequest = PurchaseRequest::findOrFail($id);
        
        \Log::info('Solicitud encontrada para pre-aprobación mixta', [
            'purchase_request_id' => $id,
            'status' => $purchaseRequest->status,
            'request_number' => $purchaseRequest->request_number
        ]);
        
        // Obtener la solicitud
        $purchaseRequest = PurchaseRequest::findOrFail($id);
        
        // Verificar el estado de aprobación antes de proceder
        $approvalInfo = $this->checkApprovalStatus($purchaseRequest);
        if ($approvalInfo) {
            return redirect()->back()->with('approvalInfo', $approvalInfo);
        }

        // Verificar que la solicitud esté en estado correcto
        if (!in_array($purchaseRequest->status, ['En pre-aprobación', 'En Cotización'])) {
            \Log::warning('Estado de solicitud no válido para pre-aprobación mixta', [
                'purchase_request_id' => $id,
                'current_status' => $purchaseRequest->status
            ]);
            return redirect()->back()->with('error', 'Esta solicitud no está en un estado válido para pre-aprobación. Estado actual: ' . $purchaseRequest->status);
        }        // Verificar que tenga selecciones mixtas
        $mixedSelections = \App\Models\QuotationItemSelection::where('purchase_request_id', $id)->get();
        if ($mixedSelections->count() === 0) {
            return redirect()->back()->with('error', 'No se encontraron selecciones mixtas para esta solicitud.');
        }

        try {
            \DB::beginTransaction();

            \Log::info('Iniciando transacción para pre-aprobación de selección mixta', [
                'purchase_request_id' => $id,
                'user_id' => auth()->id(),
                'validated_data' => $validated
            ]);

            // Actualizar el estado de la solicitud a pre-aprobada
            $purchaseRequest->update([
                'status' => 'Pre-aprobada',
                'pre_approved_by' => auth()->id(),
                'pre_approved_at' => now(),
                'pre_approval_comments' => $validated['comments'] ?? 'Selección mixta pre-aprobada',
                'budget' => $validated['budget_line']
            ]);

            \Log::info('Solicitud actualizada exitosamente', [
                'purchase_request_id' => $id,
                'new_status' => 'Pre-aprobada',
                'budget' => $validated['budget_line']
            ]);

            // Registrar en el historial
            \App\Models\RequestHistory::create([
                'purchase_request_id' => $purchaseRequest->id,
                'user_id' => auth()->id(),
                'action' => 'Pre-aprobación de selección mixta',
                'notes' => 'Selección mixta pre-aprobada. Total de selecciones: ' . $mixedSelections->count() . '. Comentarios: ' . ($validated['comments'] ?? 'Sin comentarios')
            ]);

            // Enviar notificaciones
            $this->sendMixedSelectionPreApprovalNotifications($purchaseRequest, $mixedSelections);

            \DB::commit();

            \Log::info('Pre-aprobación de selección mixta completada exitosamente', [
                'purchase_request_id' => $id,
                'request_number' => $purchaseRequest->request_number,
                'mixed_selections_count' => $mixedSelections->count()
            ]);

            return redirect()->route('quotation-approvals.index')
                ->with('success', 'La selección mixta ha sido pre-aprobada correctamente. La solicitud está lista para aprobación final.');

        } catch (\Exception $e) {
            \DB::rollback();
            \Log::error('Error al pre-aprobar selección mixta: ' . $e->getMessage(), [
                'purchase_request_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()->with('error', 'Error al pre-aprobar la selección mixta: ' . $e->getMessage());
        }
    }
    
    /**
     * Enviar notificaciones para pre-aprobación de selección mixta
     */
    private function sendMixedSelectionPreApprovalNotifications($purchaseRequest, $mixedSelections)
    {
        try {
            \Log::info('Iniciando envío de notificaciones de pre-aprobación de selección mixta', [
                'purchase_request' => $purchaseRequest->request_number,
                'purchase_request_id' => $purchaseRequest->id,
                'mixed_selections_count' => $mixedSelections->count()
            ]);
            
            // Obtener configuración dinámica
            $configSource = \App\Services\DynamicSectionEmailsService::getCurrentConfigSource();
            
            // Obtener emails de la sección para notificación de aprobación final
            $sectionEmails = $this->getSectionEmails($purchaseRequest->section_area);
            $comprasEmail = config($configSource . '.sections.Compras', config($configSource . '.default'));
            
            // Calcular total de la selección mixta
            $totalAmount = $mixedSelections->sum('total_price');
            
            \Log::info('Configuración de emails para notificaciones', [
                'section_area' => $purchaseRequest->section_area,
                'section_emails' => $sectionEmails,
                'compras_email' => $comprasEmail,
                'total_amount' => $totalAmount
            ]);
            
            // 1. ENVIAR NOTIFICACIÓN A DIRECTORES/COORDINADORES PARA APROBACIÓN FINAL
            if (!empty($sectionEmails)) {
                // Usar la nueva notificación específica para aprobación final de selección mixta
                $approvalNotification = new \App\Notifications\MixedSelectionFinalApproval(
                    $purchaseRequest,
                    $mixedSelections,
                    $totalAmount
                );
                
                foreach ($sectionEmails as $email) {
                    \Log::info('Enviando notificación de pre-aprobación para aprobación final a: ' . $email);
                    \Notification::route('mail', $email)->notify($approvalNotification);
                }
            }
            
            // 2. ENVIAR NOTIFICACIÓN INFORMATIVA A COMPRAS
            if ($comprasEmail) {
                $comprasNotification = new \App\Notifications\MixedSelectionCompras(
                    $purchaseRequest, 
                    true, // Es completa porque ya fue pre-aprobada
                    $mixedSelections->count(), 
                    $mixedSelections->count() // Total count es igual al selected count ya que está completa
                );
                
                \Log::info('Enviando notificación informativa a compras: ' . $comprasEmail);
                \Notification::route('mail', $comprasEmail)->notify($comprasNotification);
            }
            
            \Log::info('Notificaciones de pre-aprobación de selección mixta enviadas correctamente', [
                'section_emails' => $sectionEmails,
                'compras_email' => $comprasEmail,
                'purchase_request' => $purchaseRequest->request_number,
                'selections_count' => $mixedSelections->count(),
                'total_amount' => $totalAmount
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error al enviar notificación de pre-aprobación de selección mixta: ' . $e->getMessage(), [
                'purchase_request' => $purchaseRequest->request_number,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
    
    /**
     * Obtener los correos electrónicos de la sección especificada
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
}
