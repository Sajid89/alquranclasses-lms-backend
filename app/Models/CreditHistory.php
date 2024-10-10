<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CreditHistory extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'credit_history';
    public $timestamps = false;
    protected $fillable = [
        'student_course_id',
        'class_type',
        'class_id',
        'created_at',
        'expired_at'
    ];

    public function studentCourse()
    {
        return $this->belongsTo(StudentCourse::class, 'id');
    }

    public function class()
    {
        return $this->morphTo();
    }
}
