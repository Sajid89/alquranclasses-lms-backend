<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MakeupRequest extends Model
{
    use SoftDeletes;
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'student_course_id',
        'class_type',
        'class_id',
        'availability_slot_id',
        'makeup_date_time',
        'class_old_date_time',
        'status',
        'created_at',
        'updated_at',
        'is_teacher',
    ];

    public function studentCourse()
    {
        return $this->belongsTo(StudentCourse::class, 'student_course_id');
    }

    public function Slot()
    {
        return $this->belongsTo(AvailabilitySlot::class, 'reschedule_slot_id');
    }

    public function class()
    {
        return $this->morphTo();
    }
}
