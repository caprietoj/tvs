<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SpaceReservationItem extends Model
{
    /**
     * Los atributos que son asignables masivamente.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'space_reservation_id',
        'space_item_id',
        'quantity',
        'status',
        'notes',
    ];

    /**
     * Obtiene la reserva a la que pertenece este ítem.
     */
    public function reservation(): BelongsTo
    {
        return $this->belongsTo(SpaceReservation::class, 'space_reservation_id');
    }

    /**
     * Obtiene el implemento asociado a este ítem de reserva.
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(SpaceItem::class, 'space_item_id');
    }

    /**
     * Verifica si hay suficiente stock del implemento para esta reserva.
     *
     * @return bool
     */
    public function hasAvailableStock(): bool
    {
        // Obtener el implemento asociado
        $spaceItem = $this->item;
        
        // Si no hay implemento o no está disponible
        if (!$spaceItem || !$spaceItem->available) {
            return false;
        }
        
        // Verificar que la cantidad solicitada no exceda el stock disponible
        return $this->quantity <= $spaceItem->quantity;
    }
}
