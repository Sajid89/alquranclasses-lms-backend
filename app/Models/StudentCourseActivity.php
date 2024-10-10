<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentCourseActivity extends Model
{
    use HasFactory;

    protected $table = 'student_course_activity';
    protected $fillable = ['student_course_id', 'activity_type', 'description', 'file_size', 'file_name'];

    public function studentCourse()
    {
        return $this->belongsTo(StudentCourse::class, 'student_course_id');
    }
    
}