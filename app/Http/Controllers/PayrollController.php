<?php
namespace App\Http\Controllers;

use App\Http\Requests\PayrollRequest;
use App\Models\TeacherPayroll;
use App\Repository\TeacherPayrollRepository;
use App\Repository\UserRepository;
use App\Services\PayrollService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PayrollController extends Controller
{
    private $teacherPayrollRepository;
    private $payrollRequest;
    private $userRepository;
    private $payrollService;
    
    public function __construct(
        TeacherPayrollRepository $teacherPayrollRepository,
        PayrollRequest $payrollRequest,
        UserRepository $userRepository,
        PayrollService $payrollService
    )
    {
        $this->teacherPayrollRepository = $teacherPayrollRepository;
        $this->payrollRequest = $payrollRequest;
        $this->userRepository = $userRepository;
        $this->payrollService = $payrollService;
    }

    /**
     * Get teacher payrolls
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function teacherPayrolls()
    {
        $user = Auth::user();
        if($user && $user->user_type === 'teacher') 
        {
            $this->payrollRequest->validateTeacherPayrolls(request());

            $userId = $user->id;
            $page = request('page');
            $limit = request('limit');
            $offset = ($page - 1) * $limit;

            $data = $this->teacherPayrollRepository->findByUserId($userId, $limit, $offset);

            return $this->success($data, 'Teacher payroll fetched successfully', 200);
        } else {
            return $this->error('Unauthorized', 401);
        }
    }   

    /**
     * Get teacher payroll stats
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function teacherPayrollStats()
    {
        $user = Auth::user();
        if($user && $user->user_type === 'teacher') {
            $userId = $user->id;
            $data = $this->teacherPayrollRepository->getPayrollStats($userId);
            return $this->success($data, 'Teacher payroll stats fetched successfully', 200);
        } else {
            return $this->error('Unauthorized', 401);
        }
    }

    /**
     * Get single payroll
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSinglePayroll(Request $request)
    {
        $user = Auth::user();
        if($user && $user->user_type === 'teacher') {
            $this->payrollRequest->validateGetSinglePayroll($request);

            $id = $request->payroll_id;
            $data = $this->teacherPayrollRepository->findById($id);
            return $this->success($data, 'Teacher single payroll fetched successfully', 200);
        } else {
            return $this->error('Unauthorized', 401);
        }
    }

    public function getAllTeachersPayrolls(Request $request)
    {
        $user = Auth::user();
        if($user) {
            $this->payrollRequest->validateGetAllTeachersPayrolls($request);

            $page = $request->page;
            $limit = $request->limit;
            $offset = ($page - 1) * $limit;
            $teacherIds = $this->userRepository->getCoordinatedTeachers($user->id);

            $data = $this->payrollService->getAllTeachersPayrolls($teacherIds, $limit, $offset);
            return $this->success($data, 'All teachers payrolls fetched successfully', 200);
        } else {
            return $this->error('Unauthorized', 401);
        }

    }
}