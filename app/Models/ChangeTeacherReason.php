<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChangeTeacherReason extends Model
{
    use HasFactory;
    protected $table = "change_teacher_reasons";
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'reason'
    ];

    public function studentChangeTeacherHistories()
    {
        return $this->hasMany(StudentChangeTeacherHistory::class, 'change_teacher_reason_id');
    }
}
