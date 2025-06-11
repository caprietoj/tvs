<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;
use App\Mail\TicketCreated;
use App\Models\Configuration;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'titulo', 
        'descripcion', 
        'estado', 
        'prioridad',
        'tipo_requerimiento', 
        'user_id',
        'tecnico_id'
    ];

    public const PRIORIDAD_ALTA = 'Alta';
    public const PRIORIDAD_MEDIA = 'Media';
    public const PRIORIDAD_BAJA = 'Baja';

    protected static function booted()
    {
        static::created(function($ticket) {
            // Enviar siempre a sistemas@tvs.edu.co
            Mail::to('sistemas@tvs.edu.co')->send(new TicketCreated($ticket));

            // Obtener correos adicionales configurados (por si hay más destinatarios)
            $config = Configuration::where('key', 'helpdesk_emails')->first();
            $supportEmails = $config ? explode(',', $config->value) : [];
            
            // Filtrar sistemas@tvs.edu.co para evitar duplicados
            $supportEmails = array_filter($supportEmails, function($email) {
                return trim($email) !== 'sistemas@tvs.edu.co';
            });

            // Enviar a otros correos configurados si existen
            foreach ($supportEmails as $email) {
                Mail::to(trim($email))->send(new TicketCreated($ticket));
            }

            // Enviar al usuario que creó el ticket
            if ($ticket->user && $ticket->user->email) {
                Mail::to($ticket->user->email)->send(new TicketCreated($ticket));
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tecnico()
    {
        return $this->belongsTo(User::class, 'tecnico_id');
    }

    public static function countByEstado($estado)
    {
        return self::where('estado', $estado)->count();
    }

    public static function countByPrioridad($prioridad)
    {
        return self::where('prioridad', $prioridad)->count();
    }

    public function getStatusColor()
    {
        return match($this->estado) {
            'Abierto' => 'warning',
            'En Proceso' => 'info',
            'Cerrado' => 'success',
            default => 'secondary'
        };
    }

    public function getPriorityColor()
    {
        return match($this->prioridad) {
            'Alta' => 'danger',
            'Media' => 'warning',
            'Baja' => 'success',
            default => 'secondary'
        };
    }
}