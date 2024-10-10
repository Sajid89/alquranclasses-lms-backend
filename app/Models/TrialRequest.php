<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrialRequest extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'student_id', 'request_date', 'status', 'label', 'reason', 'created_at', 'teacher_preference', 'availability_slot_id',
    ];


}
