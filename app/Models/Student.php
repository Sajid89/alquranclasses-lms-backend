<?php

namespace App\Models;

use App\Classes\Enums\StatusEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\AvailabilityTrait;

class Student extends Model
{
    use HasFactory;
    use SoftDeletes;
    use AvailabilityTrait;

    protected $fillable = ['name', 'age', 'gender', 'status','timezone', 'user_id'];
    //protected $with = ['course', 'user', 'teacher', 'routine_classes', 'subscription'];
    protected $appends = ['reg_no', 'first_name', 'user_type'];

    public function getFirstNameAttribute()
    {
        return @explode(' ', $this->name)[0];
    }

    public function getUserTypeAttribute()
    {
        return 'student';
    }

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getRegNoAttribute()
    {
        return 'ALQ-STD-' . $this->id;
    }

    public function availability()
    {
        return $this->morphOne(Availability::class, 'available');
    }

    public function shift() {
        return $this->belongsTo(Shift::class, 'shift_id');
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function courses()
    {
        return $this->hasMany(StudentCourse::class, 'student_id');
    }

    public function trialClass()
    {
        return $this->hasOne(TrialClass::class, 'student_id');
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class, 'student_id');
    }

    public function routineClasses()
    {
        return $this->hasMany(RoutineClass::class, 'student_id');
    }

    public function weeklyClasses()
    {
        return $this->hasMany(WeeklyClass::class, 'student_id');
    }

    public function usedCoupons() {
        return $this->belongsToMany(Coupon::class, 'coupon_user')->withTimestamps();
    }

    public function progressReports()
    {
        return $this->hasMany(ProgressReport::class, 'student_id');
    }

    public function studentCourses()
    {
        return $this->hasMany(StudentCourse::class, 'student_id');
    }

     /**
     * Get attendance for the User(teacher)
     * @return MorphMany
     */
    public function attendance()
    {
        return $this->morphMany(Attendance::class, 'person');
    }

    /**
     * Scope query to get student by ID
     * @param Builder $query
     * @param array $select
     * @param array $student_id
     * @return mixed
     */
    public function scopeById(Builder $query, $student_id, array $select = ['*'], array $relations = [])
    {
        return $query->select($select)->whereId($student_id)->with($relations);
    }

    public static function countStudents()
    {
        return self::count();
    }

    public function teacherChangeHistory()
    {
        return $this->hasOne(StudentChangeTeacherHistory::class, 'student_id');
    }
    
    public function scopeFailedPayment($query, $status, $relationship, $startDate, $endDate, array $select = ['*'])
    {
        return $query->select($select)->whereHas($relationship, function ($query) use ($status, $startDate, $endDate) {
            $query->where('payment_status', $status)->whereBetween('updated_at', [$startDate, $endDate]);
        });
    }

    // public function scopeCancelledSubscription($query, $status, $relationship, $startDate, $endDate, array $select = ['*'])
    // {
    //     return $query->select($select)->where('subscription_status',$status)
    //     ->with('approvedCancelSubscriptionHistory')->whereHas('approvedCancelSubscriptionHistory', function ($query)  use ($startDate, $endDate) {
    //         $query->whereBetween('updated_at', [$startDate, $endDate]);
    //     });
    // }



}
