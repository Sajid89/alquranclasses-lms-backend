<?php

namespace App\Repository;

use App\Models\RegularClassRate;
use App\Models\TeacherPaymentMethod;
use App\Models\TeacherPayroll;
use App\Models\TrialClassRate;
use Illuminate\Support\Facades\DB;

class TeacherPayrollRepository
{
    private $model;

    public function __construct(TeacherPayroll $teacherPayroll)
    {
        $this->model = $teacherPayroll;
    }

    public function create($data)
    {
        return $this->model->create($data);
    }

    public function findByUserId($teacherId, $limit, $offset)
    {
        return $this->model->where('teacher_id', $teacherId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->offset($offset)
            ->get();
    }

    public function update($id, $data)
    {
        return $this->model->where('id', $id)->update($data);
    }

    public function getPayrollStats($teacherId)
    {
        $payrollData = DB::table('teacher_payrolls')
            ->selectRaw('
                sum(trial_classes_count + regular_classes_count) as total_class_count, 
                sum(net_to_pay) as total_earning, 
                sum(total_trial_duration + total_regular_duration) as total_hours')
            ->where('teacher_id', $teacherId)
            ->where('salary_status', '<>', 'pending')
            ->get();

        $totalClassCount = $payrollData[0]->total_class_count;
        $totalEarning = $payrollData[0]->total_earning;
        $totalHours = $payrollData[0]->total_hours;

        $trialRate = TrialClassRate::first()->rate;
        $regularRate = RegularClassRate::where('teacher_id', $teacherId)->first()->rate;

        return [
            'total_class_count' => $totalClassCount,
            'total_earning' => $totalEarning,
            'total_hours' => $totalHours,
            'trial_rate' => $trialRate,
            'regular_rate' => $regularRate
        ];
    }

    public function findById($id)
    {
        return $this->model->find($id);
    }

    /**
     * total count of coordinated teachers payroll records
     */
    public function getTotalPayrolls($teacherIds)
    {
        return $this->model->whereIn('teacher_id', $teacherIds)->count();
    }

    /**
     * get all teachers payrolls
     * coordinated teachers payrolls
     * with pagination
     */
    public function getAllTeachersPayrolls($teacherIds, $limit, $offset) {
        return $this->model->orderBy('created_at', 'desc')
            ->whereIn('teacher_id', $teacherIds)
            ->limit($limit)
            ->offset($offset)
            ->get();
    }
}