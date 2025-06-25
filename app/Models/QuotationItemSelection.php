<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuotationItemSelection extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_request_id',
        'quotation_id',
        'item_index',
        'item_description',
        'quantity',
        'unit_price',
        'total_price',
        'justification',
        'selected_by',
        'selected_at'
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'selected_at' => 'datetime'
    ];

    public function purchaseRequest()
    {
        return $this->belongsTo(PurchaseRequest::class);
    }

    public function quotation()
    {
        return $this->belongsTo(Quotation::class);
    }

    public function selectedBy()
    {
        return $this->belongsTo(User::class, 'selected_by');
    }

    /**
     * Obtener el total seleccionado para una solicitud de compra
     */
    public static function getTotalForRequest($purchaseRequestId)
    {
        return self::where('purchase_request_id', $purchaseRequestId)
                   ->sum('total_price');
    }

    /**
     * Verificar si todos los items de una solicitud tienen selección
     */
    public static function isCompleteSelection($purchaseRequestId)
    {
        $purchaseRequest = PurchaseRequest::find($purchaseRequestId);
        if (!$purchaseRequest) return false;

        $purchaseItems = is_array($purchaseRequest->purchase_items) 
            ? $purchaseRequest->purchase_items 
            : json_decode($purchaseRequest->purchase_items, true);
            
        $totalItems = count($purchaseItems ?? []);
        $selectedItems = self::where('purchase_request_id', $purchaseRequestId)->count();

        return $totalItems === $selectedItems;
    }
}
