<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    protected $fillable = ['name', 'description'];

    public function requests()
    {
        return $this->hasMany(DocumentRequest::class);
    }
}