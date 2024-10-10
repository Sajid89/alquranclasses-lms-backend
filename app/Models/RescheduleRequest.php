<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class RescheduleRequest extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $fillable = ['student_id', 'teacher_id', 'weekly_class_id', 'reschedule_slot_id','old_class_time', 'Course','reschedule_date', 'status', 'updated_by','Requestable_type', 'Requestable_id'];


    public function Requestable()
    {
        return $this->morphTo();
    }

    /**
     * Get the user that owns the RescheduleRequest
     *
     * @return BelongsTo
     */
    public function Student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function Teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function Slot()
    {
        return $this->belongsTo(AvailabilitySlot::class, 'reschedule_slot_id');
    }

    public function WeeklyClass()
    {
        return $this->belongsTo(WeeklyClass::class, 'weekly_class_id');
    }
}
