<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;

class InventoryItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'producto',
        'cantidad_sugerida',
        'stock',
        'alerta_enviada',
        'ultima_alerta',
        'user_id'
    ];

    protected $appends = ['sobre_stock', 'cantidad_comprar'];

    // Relación con el usuario que creó/modificó el registro
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relación con los movimientos de inventario
    public function movements()
    {
        return $this->hasMany(InventoryMovement::class);
    }

    // Método para obtener la cantidad en sobre stock
    public function getSobreStockAttribute()
    {
        return $this->stock - $this->cantidad_sugerida;
    }

    // Método para obtener la cantidad a comprar
    public function getCantidadComprarAttribute()
    {
        return $this->stock < $this->cantidad_sugerida ? $this->cantidad_sugerida - $this->stock : 0;
    }

    // Método para verificar si se requiere enviar alerta
    public function requiereAlerta()
    {
        return $this->stock < $this->cantidad_sugerida && 
               (!$this->alerta_enviada || 
                ($this->ultima_alerta && now()->diffInDays($this->ultima_alerta) > 3));
    }

    // Método para enviar alerta por email
    public function enviarAlertaStock()
    {
        if (!$this->requiereAlerta()) {
            return false;
        }

        try {
            Mail::raw(
                "El producto '{$this->producto}' está por debajo del nivel sugerido.\n" .
                "Stock actual: {$this->stock}\n" .
                "Cantidad sugerida: {$this->cantidad_sugerida}\n" .
                "Cantidad a comprar: {$this->cantidad_comprar}\n\n" .
                "Este correo es generado automáticamente por el sistema de Inventario.",
                function ($message) {
                    $message->to('compras@tvs.edu.co')
                        ->subject('Alerta de Stock: ' . $this->producto);
                }
            );

            $this->update([
                'alerta_enviada' => true,
                'ultima_alerta' => now()
            ]);

            return true;
        } catch (\Exception $e) {
            \Log::error('Error al enviar alerta de inventario: ' . $e->getMessage());
            return false;
        }
    }
}
