<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventNovelty extends Model
{
    use HasFactory;

    /**
     * Los atributos que son asignables en masa.
     *
     * @var array
     */
    protected $fillable = [
        'event_id',
        'user_id',
        'observation',
    ];

    /**
     * Obtiene el evento al que pertenece la novedad.
     */
    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Obtiene el usuario que creó la novedad.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}