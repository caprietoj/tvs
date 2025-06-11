<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseOrder extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Los atributos que son asignables en masa.
     *
     * @var array
     */
    protected $fillable = [
        'purchase_request_id',
        'provider_id',
        'order_number',
        'total_amount',
        'subtotal',
        'includes_iva',
        'iva_amount',
        'payment_terms',
        'delivery_date',
        'file_path',
        'observations',
        'additional_items',
        'created_by',
        'status',
        'sent_to_accounting_at',
        'sent_by',
        'payment_date',
        'payment_reference',
        'cancelled_at',
        'cancelled_by',
        'cancellation_reason',
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     *
     * @var array
     */
    protected $casts = [
        'delivery_date' => 'date',
        'payment_date' => 'date',
        'sent_to_accounting_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'includes_iva' => 'boolean',
        'additional_items' => 'array',
    ];

    /**
     * Obtener la solicitud de compra relacionada.
     */
    public function purchaseRequest()
    {
        return $this->belongsTo(PurchaseRequest::class);
    }

    /**
     * Obtener el proveedor relacionado.
     */
    public function provider()
    {
        return $this->belongsTo(Proveedor::class, 'provider_id');
    }

    /**
     * Obtener el usuario que creó la orden.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Obtener el usuario que envió la orden a contabilidad.
     */
    public function sender()
    {
        return $this->belongsTo(User::class, 'sent_by');
    }

    /**
     * Obtener el usuario que canceló la orden.
     */
    public function canceller()
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    /**
     * Comprobar si la orden está pendiente.
     */
    public function isPending()
    {
        return $this->status === 'pending';
    }

    /**
     * Comprobar si la orden ha sido enviada a contabilidad.
     */
    public function isSentToAccounting()
    {
        return $this->status === 'sent_to_accounting';
    }

    /**
     * Comprobar si la orden está pagada.
     */
    public function isPaid()
    {
        return $this->status === 'paid';
    }

    /**
     * Comprobar si la orden está cancelada.
     */
    public function isCancelled()
    {
        return $this->status === 'cancelled';
    }

    /**
     * Generar automáticamente un número de orden único al crear.
     */
    protected static function booted()
    {
        static::creating(function ($order) {
            if (!$order->order_number) {
                $prefix = 'OC-';
                $lastOrder = self::withTrashed()->latest('id')->first();
                $nextId = $lastOrder ? intval(substr($lastOrder->order_number, 3)) + 1 : 1;
                $order->order_number = $prefix . str_pad($nextId, 4, '0', STR_PAD_LEFT);
            }
        });
    }
}
