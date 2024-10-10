<?php

namespace App\Http\Controllers;

use App\Http\Requests\TeacherRequest;
use App\Repository\UserRepository;
use App\Services\TeacherService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class TeacherController extends Controller
{
    protected $teacherRequest;
    protected $teacherService;
    protected $userRepository;

    public function __construct(
        TeacherRequest $teacherRequest,
        TeacherService $teacherService,
        UserRepository $userRepository
    )
    {
        $this->teacherRequest = $teacherRequest;
        $this->teacherService = $teacherService;
        $this->userRepository = $userRepository;
    }
    
    /**
     * Get teacher's enrolled students
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function getActiveStudents(Request $request)
    {
        $user = Auth::user();
        if($user) {
            $this->teacherRequest->validateActiveStudentsRequest($request);
            $page = $request->page;
            $limit = $request->limit;
            $teacherId = $user->id;
            $offset = ($page - 1) * $limit;
            $data = $this->teacherService->getActiveStudents($teacherId, $offset, $limit);
            
            return $this->success($data, 'active students fetched successfully', 200);
        } else {
            return $this->error('Unauthenticated', 404);
        }

    }

    /**
     * Get student's activities for a teacher
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function getStudentActivities(Request $request) {
        $user = Auth::user();
        if($user) {
            $this->teacherRequest->validateStudentActivities($request);
            $page = $request->page;
            $limit = $request->limit;
            $studentId = $request->student_id;
            $offset = ($page - 1) * $limit;
            $data = $this->teacherService->getStudentActivities($studentId, $offset, $limit);
            
            return $this->success($data, 'Student activities fetched successfully', 200);
        } else {
            return $this->error('Unauthenticated', 404);
        }  
    }

    /**
     * Get user's with unread messages count
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function getUsersWithUnreadMessagesCount()
    {
        $teacherId = Auth::id();
        $students  = $this->teacherService->getUsersWithUnreadMessagesCount($teacherId);

        return $this->success($students, 'Students with unread messages count retrieved successfully');
    }

    public function updateProfile(Request $request) {
        $user = Auth::user();
        if($user) {
            $teacher = $this->userRepository->updateUser($request->all());
            return $this->success($teacher, 'Teacher profile updated successfully', 200);
        } else {
            return $this->error('Unauthenticated', 404);
        }
    }

    public function getProfile() {
        $user = Auth::user();
        if($user) {
            $teacherProfile = $this->teacherService->getProfile($user->id);
            return $this->success($teacherProfile, 'Teacher profile fetched successfully', 200);
        } else {
            return $this->error('Unauthenticated', 404);
        }
    }

    public function updatePassword(Request $request) {
        $user = Auth::user();
        if($user) {
            $this->teacherRequest->validatePasswordUpdateRequest($request);
            $newPassword = Hash::make($request->password);

            $teacher = $this->teacherService->updatePassword($user->id, $newPassword);
            
            return $this->success($teacher, 'Teacher password updated successfully', 200);
        } else {
            return $this->error('Unauthenticated', 404);
        }
    }
}