<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CancelSubscriptionHistory extends Model
{
    use HasFactory;
    protected $table = 'cancel_subscription_histroys';
    protected $fillable = [
        'user_id',
        'student_id',
        'student_name',
        'sub_id',
        'price',
        'reasons',
        'comments',
        'status',
        'created_at',
        'updated_at'
    ];
}