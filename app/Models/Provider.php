<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;

class Provider extends Model
{
    use Notifiable;

    protected $table = 'proveedors';

    protected $fillable = [
        'nombre', 
        'nit', 
        'contacto',
        'email',
        'telefono',
        'direccion',
        'ciudad',
        'persona_contacto',
        'servicio_producto',
        'market_segment',
        'proveedor_critico',
        'alto_riesgo',
        'forma_pago',
        'descuento',
        'cobertura',
        'referencias_comerciales',
        'nivel_precios',
        'valores_agregados',
        'estado'
    ];

    // This accessor helps maintain compatibility with the purchase orders system
    public function getNameAttribute()
    {
        return $this->nombre;
    }

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    /**
     * Route notifications for the mail channel.
     */
    public function routeNotificationForMail()
    {
        return $this->email;
    }
}
