<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class PrevisitaConsolidado extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'previsita_consolidados';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'lugar',
        'fecha_visita',
        'vencimiento',
        'responsable',
        'aprobacion_sitio',
        'observaciones_recomendaciones',
        'novedades_visita_archivo',
        'user_id'
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'fecha_visita' => 'date',
            'vencimiento' => 'date',
            'aprobacion_sitio' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * Get the user that created this previsita consolidado.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the URL for the novedades visita archivo.
     *
     * @return string|null
     */
    public function getNovedadesVisitaArchivoUrlAttribute()
    {
        if ($this->novedades_visita_archivo) {
            return Storage::url($this->novedades_visita_archivo);
        }
        return null;
    }

    /**
     * Get the filename for the novedades visita archivo.
     *
     * @return string|null
     */
    public function getNovedadesVisitaArchivoNameAttribute()
    {
        if ($this->novedades_visita_archivo) {
            return basename($this->novedades_visita_archivo);
        }
        return null;
    }

    /**
     * Scope a query to only include records with approval.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeApproved($query)
    {
        return $query->where('aprobacion_sitio', true);
    }

    /**
     * Scope a query to only include records without approval.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeNotApproved($query)
    {
        return $query->where('aprobacion_sitio', false);
    }

    /**
     * Scope a query to filter by date range.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $startDate
     * @param  string  $endDate
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('fecha_visita', [$startDate, $endDate]);
    }
}