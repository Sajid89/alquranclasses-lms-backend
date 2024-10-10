<?php
namespace App\Services;

use App\Classes\Enums\CommonEnum;
use App\Repository\TeacherPayrollRepository;
use App\Traits\DecryptionTrait;
use Carbon\Carbon;

class PayrollService
{
    use DecryptionTrait;
    private $payrollRepository;
    
    public function __construct(
        TeacherPayrollRepository $payrollRepository
    )
    {
        $this->payrollRepository = $payrollRepository;
    }

    public function getAllTeachersPayrolls($teacherIds, $limit, $offset) {
        $count = $this->payrollRepository->getTotalPayrolls($teacherIds);
        $payrolls = $this->payrollRepository->getAllTeachersPayrolls($teacherIds, $limit, $offset);
        
        $data = array();
        foreach ($payrolls as $payroll) {
            $data[] = [
                'teacher_id' => $payroll->teacher_id,
                'teacher_name' => $this->decryptValue($payroll->teacher->name),
                'created_at' =>  Carbon::createFromFormat('Y-m-d H:i:s', $payroll->created_at)->format('F j, Y'),
                'total_hours' => $payroll->total_hours,
                'total_trial_duration' => $payroll->total_trial_duration,
                'loan_deduction' => ($payroll->loan_deduction + $payroll->late_joining_deduction),
                'team_bonus' => $payroll->team_bonus,
                'allowance' => $payroll->allowance,
                'salary_status' => $payroll->salary_status,
                'net_to_pay' => $payroll->net_to_pay
            ];
        }

        return [
            'count' => $count,
            'data' => $data
        ];
    }
}