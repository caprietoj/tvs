<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class LoanRequest extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'amount',
        'installments',
        'installment_type',
        'installment_value',
        'deduction_start_date',
        'full_name',
        'document_number',
        'position',
        'signature',
        'status',
        'rejection_reason',
        'current_salary',
        'has_active_loans',
        'current_loan_balance',
        'has_advances',
        'advances_amount',
        'hr_signature',
        'admin_signature',
        'review_date',
        'decision_date',
        // Nuevos campos
        'department',
        'phone',
        'email',
        'employment_years',
        'contract_type',
        'purpose',
        'bank_name',
        'account_type',
        'account_number'
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'installment_value' => 'decimal:2',
        'deduction_start_date' => 'date',
        'current_salary' => 'decimal:2',
        'has_active_loans' => 'boolean',
        'current_loan_balance' => 'decimal:2',
        'has_advances' => 'boolean',
        'advances_amount' => 'decimal:2',
        'review_date' => 'datetime',
        'decision_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * The model's default values for attributes.
     */
    protected $attributes = [
        'purpose' => 'Gastos personales',
        'status' => 'pending'
    ];

    /**
     * Get the user that owns the loan request.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the formatted creation date.
     */
    public function getFormattedCreationDateAttribute()
    {
        // Use Carbon to format date as "Bogotá, DD de Month de YYYY"
        $months = [
            1 => 'enero', 2 => 'febrero', 3 => 'marzo', 4 => 'abril',
            5 => 'mayo', 6 => 'junio', 7 => 'julio', 8 => 'agosto',
            9 => 'septiembre', 10 => 'octubre', 11 => 'noviembre', 12 => 'diciembre'
        ];
        
        $date = $this->created_at;
        return "Bogotá, " . $date->day . " de " . $months[$date->month] . " de " . $date->year;
    }

    /**
     * Get the status label attribute.
     */
    public function getStatusLabelAttribute()
    {
        $labels = [
            'pending' => 'Pendiente',
            'reviewed' => 'Revisado por Recursos Humanos',
            'approved' => 'Aprobado',
            'rejected' => 'Rechazado'
        ];

        return $labels[$this->status] ?? 'Desconocido';
    }

    /**
     * Get the installment type label attribute.
     */
    public function getInstallmentTypeLabelAttribute()
    {
        $labels = [
            'monthly' => 'Mensuales',
            'biweekly' => 'Quincenales'
        ];

        return $labels[$this->installment_type] ?? 'Desconocido';
    }
}
