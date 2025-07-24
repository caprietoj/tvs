<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class FeedbackSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'performance_evaluation_id',
        'supervisor_id', 
        'employee_id',
        'title',
        'description',
        'scheduled_datetime',
        'location',
        'status',
        'google_event_id',
        'meeting_notes',
        'completed_at'
    ];

    protected $casts = [
        'scheduled_datetime' => 'datetime',
        'completed_at' => 'datetime'
    ];

    /**
     * Relación con la evaluación de desempeño
     */
    public function performanceEvaluation()
    {
        return $this->belongsTo(PerformanceEvaluation::class);
    }

    /**
     * Relación con el supervisor
     */
    public function supervisor()
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    /**
     * Relación con el empleado
     */
    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    /**
     * Scopes
     */
    public function scopeProgramadas($query)
    {
        return $query->where('status', 'programada');
    }

    public function scopeRealizadas($query)
    {
        return $query->where('status', 'realizada');
    }

    public function scopeProximas($query)
    {
        return $query->where('status', 'programada')
                    ->where('scheduled_datetime', '>=', now());
    }

    /**
     * Marcar sesión como realizada
     */
    public function markAsCompleted($notes = null)
    {
        $this->update([
            'status' => 'realizada',
            'completed_at' => now(),
            'meeting_notes' => $notes
        ]);
    }

    /**
     * Cancelar sesión
     */
    public function cancel()
    {
        $this->update(['status' => 'cancelada']);
    }

    /**
     * Verificar si la sesión ya pasó
     */
    public function isPast()
    {
        return $this->scheduled_datetime->isPast();
    }

    /**
     * Verificar si la sesión es próxima (dentro de las próximas 2 horas)
     */
    public function isUpcoming()
    {
        return $this->scheduled_datetime->isFuture() && 
               $this->scheduled_datetime->diffInHours(now()) <= 2;
    }

    /**
     * Obtener formato de fecha y hora para mostrar
     */
    public function getFormattedDatetimeAttribute()
    {
        return $this->scheduled_datetime->format('d/m/Y \a \l\a\s H:i');
    }

    /**
     * Obtener el período de evaluación
     */
    public function getEvaluationPeriodAttribute()
    {
        $evaluation = $this->performanceEvaluation;
        return $evaluation->period_start->format('d/m/Y') . ' - ' . $evaluation->period_end->format('d/m/Y');
    }
}

class FeedbackSession extends Model
{
    //
}
