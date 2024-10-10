<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shareable extends Model
{
    use HasFactory;
    
    protected $table = 'shareables';

    protected $fillable = [
        'shared_library_id',
        'shareable_id',
        'shareable_type',
    ];

    public function user() {
        return $this->belongsTo(User::class, 'created_by');
    }

    // public function shareable() {
    //     return $this->morphTo();
    // }

    public function sharedLibrary() {
        return $this->belongsTo(SharedLibrary::class, 'shared_library_id');
    }
}
