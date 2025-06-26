<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;
use App\Mail\DocumentRequestCreated;
use App\Models\Configuration;

class DocumentRequest extends Model
{
    protected $fillable = ['user_id', 'document_id', 'description', 'status', 'certificate'];

    protected static function booted()
    {
        static::created(function($documentRequest) {
            // Obtener correos configurados
            $config = Configuration::where('key', 'rrhh_requests_emails')->first();
            $notificationEmails = $config ? explode(',', $config->value) : [];
            
            $emailTestService = new \App\Services\EmailTestModeService();
            
            // Enviar a todos los correos configurados
            foreach ($notificationEmails as $email) {
                $interceptedEmail = $emailTestService->interceptEmail(trim($email), 'RRHH');
                Mail::to($interceptedEmail)->send(new DocumentRequestCreated($documentRequest));
            }

            // Enviar al usuario que realizó la solicitud
            if ($documentRequest->user && $documentRequest->user->email) {
                $interceptedUserEmail = $emailTestService->interceptEmail($documentRequest->user->email, 'General');
                Mail::to($interceptedUserEmail)->send(new DocumentRequestCreated($documentRequest));
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function document()
    {
        return $this->belongsTo(Document::class);
    }
}