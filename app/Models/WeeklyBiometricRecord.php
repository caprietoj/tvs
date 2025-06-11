<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WeeklyBiometricRecord extends Model
{
    protected $fillable = [
        'employee_id',
        'full_name',
        'record_date',
        'entry_time',
        'exit_time',
        'department',
        'raw_marks'
    ];

    protected $casts = [
        'record_date' => 'date',
        'entry_time' => 'datetime',
        'exit_time' => 'datetime'
    ];
}
