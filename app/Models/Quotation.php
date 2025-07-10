<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quotation extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_request_id',
        'provider_name',
        'total_amount',
        'subtotal',
        'includes_iva',
        'iva_amount',
        'includes_iva_19',
        'iva_19_amount',
        'includes_iva_5',
        'iva_5_amount',
        'includes_ipoconsumo_8',
        'ipoconsumo_8_amount',
        'includes_ipoconsumo_4',
        'ipoconsumo_4_amount',
        'tax_application_mode',
        'delivery_time',
        'payment_method',
        'validity',
        'warranty',
        'file_path',
        'status',
        'pre_approval_date',
        'pre_approval_comments',
        'pre_approved_by',
        'additional_items',
        // otros campos necesarios
    ];

    protected $casts = [
        'additional_items' => 'array',
        'includes_iva' => 'boolean',
        'includes_iva_19' => 'boolean',
        'includes_iva_5' => 'boolean',
        'includes_ipoconsumo_8' => 'boolean',
        'includes_ipoconsumo_4' => 'boolean',
        'total_amount' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'iva_amount' => 'decimal:2',
        'iva_19_amount' => 'decimal:2',
        'iva_5_amount' => 'decimal:2',
        'ipoconsumo_8_amount' => 'decimal:2',
        'ipoconsumo_4_amount' => 'decimal:2',
    ];

    public function purchaseRequest()
    {
        return $this->belongsTo(PurchaseRequest::class);
    }

    public function preApprovedBy()
    {
        return $this->belongsTo(User::class, 'pre_approved_by');
    }
    
    /**
     * Obtener los datos del proveedor basándose en el nombre
     */
    public function getProviderData()
    {
        $provider = \App\Models\Proveedor::where('nombre', $this->provider_name)->first();
        
        if ($provider) {
            return [
                'nit' => $provider->nit,
                'email' => $provider->email,
                'telefono' => $provider->telefono,
                'contacto' => $provider->persona_contacto,
                'direccion' => $provider->direccion,
                'ciudad' => $provider->ciudad,
                'servicio_producto' => $provider->servicio_producto,
            ];
        }
        
        return [
            'nit' => 'N/A',
            'email' => 'N/A',
            'telefono' => 'N/A',
            'contacto' => 'N/A',
            'direccion' => 'N/A',
            'ciudad' => 'N/A',
            'servicio_producto' => 'N/A',
        ];
    }
    
    /**
     * Atributo para acceder directamente a los datos del proveedor
     */
    public function getProviderDataAttribute()
    {
        return $this->getProviderData();
    }
}
