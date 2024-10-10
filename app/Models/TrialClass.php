<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;
use Webpatser\Uuid\Uuid;

class TrialClass extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'customer_id', 'student_id', 'class_time', 'status', 'student_status', 'teacher_status',
        'class_duration','teacher_presence','student_presence','teacher_id',
        'availability_slot_id', 'student_course_id', 'email_sent_on_teacher_student_join'
    ];

    /**
     * Get the teacher's availability
     *
     * @return string
     */
    public function availability()
    {
        return $this->morphOne(Availability::class, 'available');
    }

    public function trialReview()
    {
        return $this->hasOne(TrialReview::class, 'trial_class_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function attendance()
    {
        return $this->morphMany(Attendance::class, 'class')->whereNotNull('left_at');
    }

    public function availabilitySlot() 
    {
        return $this->belongsTo(AvailabilitySlot::class, 'availability_slot_id');
    }

    public function studentCourse()
    {
        return $this->belongsTo(StudentCourse::class, 'student_course_id');
    }

    public function creditHistory()
    {
        return $this->morphOne(CreditHistory::class, 'class');
    }

    public function makeupRequest()
    {
        return $this->morphOne(MakeupRequest::class, 'class');
    }
}
