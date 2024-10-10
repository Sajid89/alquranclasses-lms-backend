<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\DB;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = ['person_id', 'person_type', 'class_id', 'class_type', 'session_id', 'joined_at', 'left_at'];
    protected $casts = [
        'joined_at' => 'datetime',
        'left_at'   => 'datetime',
    ];

    public function person()
    {
        return $this->morphTo();
    }

    public function class()
    {
        return $this->morphTo();
    }

    /**
     * Get latest attendance by weekly class id
     * @param $query
     * @param $user_type
     * @param $user_id
     * @param $classId
     * @param $classType
     * @param $sessionId
     * @return mixed
     */
    public function scopeLatestClassAttendance($query, $user_type, $user_id, $classId, $classType, $sessionId)
    {
        return $query->where([
            'person_id' => $user_id,
            'person_type' => $user_type,
            'class_id' => $classId,
            'class_type' => $classType,
            'session_id' => $sessionId
        ])->latest()->first();
    }
}
