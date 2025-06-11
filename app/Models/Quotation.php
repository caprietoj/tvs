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
        'delivery_time',
        'payment_method',
        'validity',
        'warranty',
        'file_path',
        'status',
        'pre_approval_date',
        'pre_approval_comments',
        'pre_approved_by',
        // otros campos necesarios
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
