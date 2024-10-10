<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentChangeTeacherHistory extends Model
{
    use HasFactory;
    protected $table = "student_change_teacher_history";
    protected $fillable = ['student_course_id', 'change_teacher_reason_id'];

    public function studentCourse()
    {
        return $this->belongsTo(StudentCourse::class, 'student_course_id');
    }

    public function changeTeacherReason()
    {
        return $this->belongsTo(ChangeTeacherReason::class, 'change_teacher_reason_id');
    }
}