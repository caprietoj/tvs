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
        
        // Permitir que las solicitudes sin cotizaciones continúen al flujo de preaprobación
        // ya que pueden haber pasado por el proceso de "Hecho Cumplido" sin requerir cotizaciones
        
        return view('quotation-approvals.show', compact('request'));
    }

    /**
     * Pre-aprobar una solicitud seleccionando una cotización.
     */
    public function preApprove(Request $request, $id)
    {
        // Validar la entrada
        $validated = $request->validate([
            'quotation_id' => 'required|exists:quotations,id',
            'comments' => 'nullable|string',
            'budget' => 'required|string|max:255',
        ]);

        // Obtener la solicitud y la cotización
        $purchaseRequest = PurchaseRequest::findOrFail($id);
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
}
