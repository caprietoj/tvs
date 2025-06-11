<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseRequest extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Los atributos que son asignables en masa.
     *
     * @var array
     */
    protected $fillable = [
        'request_number',
        'user_id',
        'type',
        'request_date',
        'requester',
        'section_area',
        'purchase_items',
        'purchase_justification',
        'service_items',
        'service_budget',
        'service_budget_text',
        'code',
        'grade',
        'section',
        'delivery_date',
        'copy_items',
        'material_items',
        'status',
        'approved_by',
        'approval_date',
        'selected_quotation_id',
        'pre_approval_comments',
        'pre_approved_by',
        'pre_approved_at',
        'pre_approved_quotation_id',
        'budget',
        'delivery_status',
        'delivery_marked_at',
        'delivery_marked_by',
        'delivery_notes',
        'rejection_reason',
        'required_quotations',
        'can_proceed_early',
        'original_file',
        'attached_files',
        // Campos de especificaciones para fotocopias
        'paper_size',
        'paper_type',
        'paper_color',
        'requires_binding',
        'requires_lamination',
        'requires_cutting',
        'special_details',
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     *
     * @var array
     */
    protected $casts = [
        'request_date' => 'date',
        'delivery_date' => 'date',
        'approval_date' => 'datetime',
        'pre_approved_at' => 'datetime',
        'delivery_marked_at' => 'datetime',
        'purchase_items' => 'array',
        'service_items' => 'array',
        'copy_items' => 'array',
        'material_items' => 'array',
        'attached_files' => 'array',
        'requires_binding' => 'boolean',
        'requires_lamination' => 'boolean',
        'requires_cutting' => 'boolean',
    ];

    /**
     * Obtener el usuario que creó esta solicitud.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Obtener el usuario que aprobó esta solicitud.
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Obtener el usuario que pre-aprobó esta solicitud.
     */
    public function preApprover()
    {
        return $this->belongsTo(User::class, 'pre_approved_by');
    }

    /**
     * Obtener el usuario que marcó el estado de entrega.
     */
    public function deliveryMarker()
    {
        return $this->belongsTo(User::class, 'delivery_marked_by');
    }

    /**
     * Obtener las cotizaciones relacionadas con esta solicitud.
     */
    public function quotations()
    {
        return $this->hasMany(Quotation::class, 'purchase_request_id');
    }

    /**
     * Obtener la cotización seleccionada para esta solicitud.
     */
    public function selectedQuotation()
    {
        return $this->belongsTo(Quotation::class, 'selected_quotation_id');
    }

    /**
     * Obtener la cotización pre-aprobada para esta solicitud.
     */
    public function preApprovedQuotation()
    {
        return $this->belongsTo(Quotation::class, 'pre_approved_quotation_id');
    }

    /**
     * Verificar si es una solicitud de compra.
     */
    public function isPurchaseRequest()
    {
        return $this->type === 'purchase';
    }

    /**
     * Verificar si es una solicitud de materiales.
     */
    public function isMaterialsRequest()
    {
        return $this->type === 'materials' && $this->material_items !== null;
    }
    
    /**
     * Verificar si es una solicitud de fotocopias.
     */
    public function isCopiesRequest()
    {
        return $this->type === 'materials' && $this->copy_items !== null && $this->material_items === null;
    }

    /**
     * Marcar el estado de entrega de fotocopias.
     */
    public function markDeliveryStatus($status, $userId, $notes = null)
    {
        if (!$this->isCopiesRequest()) {
            return false;
        }

        if (!in_array($status, ['delivered', 'not_delivered'])) {
            return false;
        }

        $this->delivery_status = $status;
        $this->delivery_marked_at = now();
        $this->delivery_marked_by = $userId;
        $this->delivery_notes = $notes;
        
        return $this->save();
    }

    /**
     * Verificar si la fotocopia ha sido entregada.
     */
    public function isDelivered()
    {
        // Verificar si la columna delivery_status existe
        if (\Schema::hasColumn('purchase_requests', 'delivery_status')) {
            return $this->delivery_status === 'delivered';
        }
        
        // Si no existe la columna, asumir que no está entregada
        return false;
    }

    /**
     * Verificar si la fotocopia no ha sido entregada.
     */
    public function isNotDelivered()
    {
        // Verificar si la columna delivery_status existe
        if (\Schema::hasColumn('purchase_requests', 'delivery_status')) {
            return $this->delivery_status === 'not_delivered';
        }
        
        // Si no existe la columna, asumir que no está entregada
        return true;
    }

    /**
     * Verificar si la entrega está pendiente.
     */
    public function isDeliveryPending()
    {
        return $this->delivery_status === 'pending' || $this->delivery_status === null;
    }

    /**
     * Generar automáticamente un número de solicitud único al crear.
     */
    protected static function booted()
    {
        static::creating(function ($request) {
            if (!$request->request_number) {
                $prefix = $request->type === 'purchase' ? 'SC-' : 'SM-';
                
                // Obtener el número más alto existente que siga el patrón correcto
                $maxNumber = self::withTrashed()
                    ->where('type', $request->type)
                    ->where('request_number', 'LIKE', $prefix . '%')
                    ->where('request_number', 'REGEXP', '^' . $prefix . '[0-9]+$')
                    ->get()
                    ->map(function($item) use ($prefix) {
                        $numberPart = substr($item->request_number, strlen($prefix));
                        return is_numeric($numberPart) ? intval($numberPart) : 0;
                    })
                    ->max();
                
                $nextId = $maxNumber ? $maxNumber + 1 : 1;
                $request->request_number = $prefix . str_pad($nextId, 4, '0', STR_PAD_LEFT);
            }
            
            if (!$request->request_date) {
                $request->request_date = now()->format('Y-m-d');
            }
        });
    }
    
    /**
     * Actualizar el estado de la solicitud y registrar el cambio en el historial
     * 
     * @param string $status Nuevo estado de la solicitud
     * @param int $userId ID del usuario que realiza el cambio
     * @param string|null $notes Notas adicionales sobre el cambio
     * @return bool
     */
    public function updateStatus($status, $userId, $notes = null)
    {
        $this->status = $status;
        $this->save();
        
        // Registrar en historial si existe la tabla y la clase
        if (class_exists('App\\Models\\RequestHistory')) {
            \App\Models\RequestHistory::create([
                'purchase_request_id' => $this->id,
                'user_id' => $userId,
                'action' => 'Cambio de estado a: ' . $status,
                'notes' => $notes
            ]);
        }
        
        return true;
    }

    /**
     * Obtener los items de la solicitud según su tipo.
     * Este método se utiliza para unificar el acceso a los items.
     */
    public function items()
    {
        if ($this->isPurchaseRequest()) {
            return $this->purchase_items ?? [];
        } else {
            return array_merge(
                $this->copy_items ?? [], 
                $this->material_items ?? []
            );
        }
    }

    /**
     * Obtener el historial de cambios de esta solicitud.
     */
    public function history()
    {
        return $this->hasMany(RequestHistory::class, 'purchase_request_id');
    }

    /**
     * Obtener el número de cotizaciones requeridas para esta solicitud.
     */
    public function getRequiredQuotationsCount()
    {
        return $this->required_quotations ?? 3; // Por defecto 3 si no está definido
    }

    /**
     * Verificar si se han alcanzado las cotizaciones requeridas.
     */
    public function hasRequiredQuotations()
    {
        return $this->quotations()->count() >= $this->getRequiredQuotationsCount();
    }

    /**
     * Verificar si se puede proceder con el flujo de aprobación antes de completar todas las cotizaciones.
     */
    public function canProceedEarly()
    {
        return $this->can_proceed_early || false;
    }

    /**
     * Verificar si se debe enviar notificación de cotizaciones completas.
     */
    public function shouldSendQuotationsNotification()
    {
        $quotationCount = $this->quotations()->count();
        $requiredCount = $this->getRequiredQuotationsCount();
        
        // Enviar si se han alcanzado las cotizaciones requeridas
        if ($quotationCount >= $requiredCount) {
            return true;
        }
        
        // O si se puede proceder antes y se ha marcado como tal
        if ($this->canProceedEarly()) {
            return true;
        }
        
        return false;
    }

    /**
     * Obtener el progreso de cotizaciones como string para mostrar en la UI.
     */
    public function getQuotationProgress()
    {
        $current = $this->quotations()->count();
        $required = $this->getRequiredQuotationsCount();
        return "{$current} de {$required}";
    }

    /**
     * Establecer el número de cotizaciones requeridas para esta solicitud.
     */
    public function setRequiredQuotations($count)
    {
        $this->update(['required_quotations' => max(1, intval($count))]);
    }

    /**
     * Permitir proceder con el flujo antes de completar todas las cotizaciones.
     */
    public function allowEarlyProceed($allow = true)
    {
        $this->update(['can_proceed_early' => $allow]);
    }
    
    /**
     * Configurar el número de cotizaciones requeridas para esta solicitud
     */
    public function configureRequiredQuotations($count, $canProceedEarly = false)
    {
        $this->update([
            'required_quotations' => $count,
            'can_proceed_early' => $canProceedEarly
        ]);
    }
    
    /**
     * Resetear a la configuración por defecto (3 cotizaciones)
     */
    public function resetToDefaultQuotations()
    {
        $this->update([
            'required_quotations' => 3,
            'can_proceed_early' => false
        ]);
    }

    /**
     * Calcular la cantidad total de materiales solicitados
     */
    public function getTotalMaterialsQuantity()
    {
        $total = 0;
        
        if ($this->material_items && is_array($this->material_items)) {
            foreach ($this->material_items as $item) {
                $quantity = isset($item['quantity']) ? intval($item['quantity']) : 0;
                $total += $quantity;
            }
        }
        
        return $total;
    }

    /**
     * Calcular la cantidad total de fotocopias solicitadas
     */
    public function getTotalCopiesQuantity()
    {
        $total = 0;
        
        if ($this->copy_items && is_array($this->copy_items)) {
            foreach ($this->copy_items as $item) {
                $copiesRequired = isset($item['copies_required']) ? intval($item['copies_required']) : 0;
                $doubleLetterColor = isset($item['double_letter_color']) ? intval($item['double_letter_color']) : 0;
                $blackWhite = isset($item['black_white']) ? intval($item['black_white']) : 0;
                $color = isset($item['color']) ? intval($item['color']) : 0;
                
                // Sumar todas las copias solicitadas para este item
                $itemTotal = $copiesRequired + $doubleLetterColor + $blackWhite + $color;
                $total += $itemTotal;
            }
        }
        
        return $total;
    }

    /**
     * Verificar si la solicitud requiere aprobación manual basado en las cantidades
     */
    public function requiresManualApproval()
    {
        // Para solicitudes de fotocopias
        if ($this->isCopiesRequest()) {
            return $this->getTotalCopiesQuantity() > 15;
        }
        
        // Para solicitudes de materiales
        if ($this->isMaterialsRequest()) {
            return $this->getTotalMaterialsQuantity() > 15;
        }
        
        // Las solicitudes de compra siempre requieren aprobación manual
        return true;
    }

    /**
     * Aprobar automáticamente la solicitud si cumple los criterios
     */
    public function autoApproveIfEligible()
    {
        // Solo auto-aprobar solicitudes de fotocopias y materiales con cantidades <= 15
        if (!$this->requiresManualApproval() && $this->status === 'pending') {
            $this->update([
                'status' => 'approved',
                'approved_by' => 1, // Sistema automático (ID 1 o crear un usuario sistema)
                'approval_date' => now(),
            ]);
            
            // Registrar en el historial que fue una aprobación automática
            if (class_exists('App\\Models\\RequestHistory')) {
                \App\Models\RequestHistory::create([
                    'purchase_request_id' => $this->id,
                    'user_id' => 1, // Usuario sistema
                    'action' => 'Aprobación automática',
                    'notes' => 'Solicitud aprobada automáticamente - cantidad total: ' . 
                              ($this->isCopiesRequest() ? $this->getTotalCopiesQuantity() . ' copias' : $this->getTotalMaterialsQuantity() . ' materiales')
                ]);
            }
            
            // Enviar notificación de aprobación automática al usuario
            try {
                $this->user->notify(new \App\Notifications\PurchaseRequestAutoApproved($this));
                \Log::info("Notificación de auto-aprobación enviada para solicitud #{$this->id}");
            } catch (\Exception $e) {
                \Log::error("Error al enviar notificación de auto-aprobación para solicitud #{$this->id}: " . $e->getMessage());
            }
            
            return true;
        }
        
        return false;
    }
}
