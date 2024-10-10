<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentCourse extends Model
{
    use SoftDeletes;
    use HasFactory;
    protected $table = 'student_courses';

    protected $fillable = [
        'student_id',
        'course_id',
        'course_level',
        'teacher_id',
        'teacher_preference',
        'shift_id',
        'subscription_id',
    ];

    
    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class, 'shift_id');
    }

    public function subscription() {
        return $this->belongsTo(Subscription::class, 'subscription_id');
    }

    public function routineClasses()
    {
        return $this->hasMany(RoutineClass::class, 'student_course_id');
    }

    public function trialClass()
    {
        return $this->hasMany(TrialClass::class, 'student_course_id');
    }

    public function studentChangeTeacherHistory()
    {
        return $this->hasMany(StudentChangeTeacherHistory::class, 'student_course_id');
    }

    public function studentCourseActivities()
    {
        return $this->hasMany(StudentCourseActivity::class, 'student_course_id');
    }

    public function creditHistory()
    {
        return $this->hasMany(CreditHistory::class, 'student_course_id');
    }

    public function makeupRequests()
    {
        return $this->hasMany(MakeupRequest::class, 'student_course_id');
    }
}
