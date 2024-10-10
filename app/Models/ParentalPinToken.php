<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

class ParentalPinToken extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id', 
        'token',
        'created_at',
        'expired_at'
    ];
    
    
    public function user(): Relation
    {
        return $this->belongsTo(User::class);
    }

}
