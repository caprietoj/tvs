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
        // Obtener todas las solicitudes de compra que estén en estado "En Cotización" o "Pre-aprobada"
        $requests = PurchaseRequest::whereIn('status', ['En Cotización', 'Pre-aprobada'])
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
        
        // Si no hay cotizaciones, redirigir con un mensaje
        if ($request->quotations->isEmpty()) {
            return redirect()->route('quotation-approvals.index')
                ->with('error', 'No hay cotizaciones disponibles para esta solicitud.');
        }

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
            
            // Agregar emails específicos de la sección
            if (!empty($sectionEmails)) {
                $allEmails = array_merge($allEmails, $sectionEmails);
            }
            
            // Agregar compras@tvs.edu.co siempre
            $allEmails[] = 'compras@tvs.edu.co';
            
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
            
            // Enviar notificaciones a todos los emails relevantes
            foreach ($allEmails as $email) {
                Notification::route('mail', $email)
                    ->notify(new \App\Notifications\QuotationPreApproved($purchaseRequest, $email, $quotation));
                \Log::info("Notificación de pre-aprobación enviada a: $email");
            }
            
            // Notificar al solicitante por separado
            if ($purchaseRequest->user) {
                $purchaseRequest->user->notify(new \App\Notifications\QuotationPreApproved($purchaseRequest, $purchaseRequest->user->email, $quotation));
                \Log::info('Notificación de pre-aprobación enviada al solicitante: ' . $purchaseRequest->user->email);
            }
            
            \Log::info('Notificaciones de pre-aprobación enviadas correctamente', [
                'emails_sent' => count($allEmails) + ($purchaseRequest->user ? 1 : 0),
                'recipients' => array_merge($allEmails, $purchaseRequest->user ? [$purchaseRequest->user->email] : [])
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
}
