<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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
        'drive_link',
        'user_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'fecha_visita' => 'date',
        'vencimiento' => 'date',
        'aprobacion_sitio' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Relación con el usuario que creó la previsita
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación con los archivos adjuntos
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function archivos()
    {
        return $this->hasMany(PrevisitaArchivo::class);
    }

    /**
     * Scope para previsitas aprobadas
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAprobadas($query)
    {
        return $query->where('aprobacion_sitio', true);
    }

    /**
     * Scope para previsitas pendientes
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePendientes($query)
    {
        return $query->where('aprobacion_sitio', false);
    }

    /**
     * Scope para previsitas vencidas
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeVencidas($query)
    {
        return $query->where('vencimiento', '<', now());
    }

    /**
     * Scope para previsitas próximas a vencer
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $dias
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeProximasVencer($query, $dias = 7)
    {
        return $query->where('vencimiento', '>=', now())
                    ->where('vencimiento', '<=', now()->addDays($dias));
    }

    /**
     * Accessor para obtener el estado de la previsita
     *
     * @return string
     */
    public function getEstadoAttribute()
    {
        if ($this->vencimiento && $this->vencimiento < now()) {
            return 'vencida';
        }
        
        if ($this->aprobacion_sitio) {
            return 'aprobada';
        }
        
        return 'pendiente';
    }

    /**
     * Accessor para obtener el estado con etiqueta HTML
     *
     * @return string
     */
    public function getEstadoBadgeAttribute()
    {
        $estado = $this->estado;
        
        switch ($estado) {
            case 'aprobada':
                return '<span class="badge badge-success">Aprobada</span>';
            case 'vencida':
                return '<span class="badge badge-danger">Vencida</span>';
            case 'pendiente':
            default:
                return '<span class="badge badge-warning">Pendiente</span>';
        }
    }

    /**
     * Accessor para verificar si tiene archivo adjunto
     *
     * @return bool
     */
    public function getTieneArchivoAttribute()
    {
        return !empty($this->novedades_visita_archivo);
    }

    /**
     * Accessor para obtener el nombre del archivo
     *
     * @return string|null
     */
    public function getNombreArchivoAttribute()
    {
        if (!$this->novedades_visita_archivo) {
            return null;
        }
        
        return basename($this->novedades_visita_archivo);
    }

    /**
     * Accessor para verificar si la previsita está próxima a vencer
     *
     * @return bool
     */
    public function getProximaVencerAttribute()
    {
        if (!$this->vencimiento) {
            return false;
        }
        
        return $this->vencimiento >= now() && $this->vencimiento <= now()->addDays(7);
    }
}
