<?php

namespace App\Models;

use App\Classes\AlQuranConfig;
use App\Classes\Enums\StatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Course extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $fillable = ['title', 'description', 'is_custom', 'status', 'created_by', 'trial_request_id', 'is_locked'];

    public function creator(){
        return $this->belongsTo(User::class, 'created_by');
    }

    public function teachers(){
        return $this->morphedByMany(User::class, 'courseable');
    }

    public function students() {
        return $this->hasMany(Student::class);
    }

    public function studentCourses() {
        return $this->hasMany(StudentCourse::class, 'course_id');
    }

    public function teacherChangeHistory()
    {
        return $this->hasOne(StudentChangeTeacherHistory::class, 'course_id');
    }

    public function progressReports()
    {
        return $this->hasMany(ProgressReport::class, 'course_id');
    }

    // public function users()
    // {
    //     return $this->morphedByMany(Video::class, 'taggable');
    // }

    /**
     * Get general courses & those are related to current customer
     * @param $requestID
     * @return mixed
     */
    public static function currentRequestCourses($creatorID, $requestID) {

        $getGeneralCourses = Course::where(['status' => StatusEnum::Active, 'is_custom' => 0])->get();
        $getCustomCourses = Course::where(['is_custom' => 1, 'created_by' => $creatorID, 'trial_request_id' => $requestID])->get();
        return $getGeneralCourses->merge($getCustomCourses);
    }

    public function sharedLibraries() {
        return $this->hasMany(SharedLibrary::class, 'course_id');
    }

}
