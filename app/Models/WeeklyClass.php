<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use Illuminate\Database\Eloquent\SoftDeletes;
use Webpatser\Uuid\Uuid;

class WeeklyClass extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['customer_id', 'student_id', 'teacher_id', 'routine_class_id', 'status', 'student_status', 'teacher_status', 'class_time', 'session_key', 'class_link', 'RoomId', 'class_duration','teacher_presence','student_presence'];

    private static function GetUniqueSessionKey()
    {
        // $code = '';
        // do {
        //     $code = (string) Uuid::generate();
        //     $code[0]="W";
        //     $code[1]="K";
        //     $user_code = WeeklyClass::where('session_key', $code)->exists();
        // } while ($user_code);
        // return $code;
    }

    public static function boot()
    {

        parent::boot();

        /**
         * Write code on Method
         *
         * @return response()
         */
        // static::creating(function ($item) {

        //     $item->session_key = self::GetUniqueSessionKey();
        // });

    }
    /**
     * Get the user that owns the WeeklyClass
     *
     * @return BelongsTo
     */
    public function Student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function Teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function Course()
    {
        return $this->Student->Course->title;
    }

    public function attendance()
    {
        return $this->morphMany(Attendance::class, 'class')->whereNotNull('left_at');
    }

    public function RescheduleRequest()
    {
        return $this->hasOne(RescheduleRequest::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function routineClass()
    {
        return $this->belongsTo(RoutineClass::class);
    }

    public function makeupRequest()
    {
        return $this->morphOne(MakeupRequest::class, 'class');
    }

    public function creditHistory()
    {
        return $this->morphOne(CreditHistory::class, 'class');
    }

    /**
     * Get the student's status.
     *
     * @param  string  $value
     * @return string
     */
    public function getStudentStatusAttribute($value)
    {
        //if ($value == 'scheduled') {
            //return 'absent';
        //}

        return $value;
    }

    /**
     * Get the student's status.
     *
     * @param  string  $value
     * @return string
     */
    public function getTeacherStatusAttribute($value)
    {
        //if ($value == 'scheduled') {
            //return 'absent';
        //}

        return $value;
    }

    /** Get weekly class by ID
     * @param $query
     * @param $ID
     * @param array $columns
     * @return mixed
     */
    public function scopeGetById($query,  $ID, array $columns=['*'])
    {
        return $query->select($columns)->whereId($ID);
    }

    /**
     * get class if its time is passed
     * @param $query
     * @param $ID
     * @param array|string[] $columns
     * @return mixed
     */
    public function scopePastClass($query, $ID, array $columns=['*'])
    {
        return $query->select($columns)->whereId($ID)
            ->where('class_time','<=', Carbon::now()->subMinutes(30));
    }
}
