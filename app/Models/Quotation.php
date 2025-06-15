<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quotation extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_request_id',
        'provider_name',
        'total_amount',
        'subtotal',
        'includes_iva',
        'iva_amount',
        'delivery_time',
        'payment_method',
        'validity',
        'warranty',
        'file_path',
        'status',
        'pre_approval_date',
        'pre_approval_comments',
        'pre_approved_by',
        'additional_items',
        // otros campos necesarios
    ];

    protected $casts = [
        'additional_items' => 'array',
        'includes_iva' => 'boolean',
        'total_amount' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'iva_amount' => 'decimal:2',
    ];

    public function purchaseRequest()
    {
        return $this->belongsTo(PurchaseRequest::class);
    }

    public function preApprovedBy()
    {
        return $this->belongsTo(User::class, 'pre_approved_by');
    }
}
