<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CancelSubscriptionRequest extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'student_id',
        'reasons',
        'comments',
        'status',
        'created_at',
        'updated_at'
    ];
}