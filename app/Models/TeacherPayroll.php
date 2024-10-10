<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeacherPayroll extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'teacher_payrolls';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'teacher_id',
        'total_trial_duration',
        'total_regular_duration',
        'from_date',
        'to_date',
        'trial_classes_count',
        'regular_classes_count',
        'team_bonus',
        'customer_bonus',
        'allowance',
        'late_joining_deduction',
        'loan_deduction',
        'salary_status',
        'total_regular_amount',
        'total_trial_amount',
        'net_to_pay',
        'updated_by',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'from_date' => 'date',
        'to_date' => 'date',
        // 'created_at' is automatically cast to a Carbon instance by Laravel
    ];

 //create relationship with users table
    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }   
}