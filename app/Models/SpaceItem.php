<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SpaceItem extends Model
{
    protected $fillable = [
        'space_id',
        'name',
        'description',
        'quantity',
        'available',
    ];

    /**
     * Obtiene el espacio al que pertenece este ítem
     */
    public function space(): BelongsTo
    {
        return $this->belongsTo(Space::class);
    }

    /**
     * Obtiene las reservas que utilizan este ítem
     */
    public function reservations(): BelongsToMany
    {
        return $this->belongsToMany(SpaceReservation::class, 'space_reservation_items')
                    ->withPivot('quantity_requested')
                    ->withTimestamps();
    }
}
