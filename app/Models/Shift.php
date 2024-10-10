<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'from',
        'to'
    ];

    public function student()
    {
        return $this->hasOne(Student::class);
    }

    public function shiftSlots()
    {
        return $this->hasMany(ShiftSlot::class);
    }

    public function scopeGetStudentShiftById($query, $shiftId) {
        return $query->whereId($shiftId);
    }

    /**
     * Get all the course for a shift
     * 
     * @return HasMany
     */
    public function studentCourses()
    {
        return $this->hasMany(StudentCourse::class, 'shift_id');
    }

    /**
     * accessor to get shift start time and end time combined in AM PM format
     * @return string
     */
    public function getTimeRangeAttribute()
    {
        $start = Carbon::createFromFormat('H:i:s', $this->starts_at);
        $end = Carbon::createFromFormat('H:i:s', $this->ends_at);
        $formattedStart = $start->format('g:i A');
        $formattedEnd = $end->format('g:i A');
        return "{$formattedStart} - {$formattedEnd}";
    }

}
