<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EquipmentBlockOverride extends Model
{
    protected $table = 'equipment_block_overrides';

    protected $fillable = [
        'equipment_block_id',
        'override_date',
        'new_reason',
        'assigned_user_id',
        'equipment_loan_id',
        'created_by',
        'notes',
    ];

    protected $casts = [
        'override_date' => 'date',
    ];

    public function block(): BelongsTo
    {
        return $this->belongsTo(EquipmentBlock::class, 'equipment_block_id');
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
