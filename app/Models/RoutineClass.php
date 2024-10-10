<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @method whereIn(string $string, $slotIDS)
 * @method where(string $string, $teacher_id)
 * @method static insert(array $classes)
 */
class RoutineClass extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'student_id',
        'teacher_id',
        'slot_id',
        'student_course_id',
        'status',
    ];

    public function availabilitySlot()
    {
        return $this->belongsTo(AvailabilitySlot::class, 'slot_id');
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }
    
    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function WeeklyClass()
    {
        return $this->hasMany(WeeklyClass::class, 'routine_class_id');
    }

    public function studentCourse()
    {
        return $this->belongsTo(StudentCourse::class, 'student_course_id');
    }

    /**
     * Scope query to get student routine classes
     * @param Builder $query
     * @param array $select
     * @param array $student_id
     * @return mixed
     */
    public function scopeByStudentId(Builder $query, $student_id, array $select = ['*'])
    {
        return $query->select($select)->where('student_id', $student_id);
    }
}
