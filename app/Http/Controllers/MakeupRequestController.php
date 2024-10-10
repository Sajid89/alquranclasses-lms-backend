<?php

namespace App\Http\Controllers;

use App\Http\Requests\MakeupRequest;
use App\Repository\UserRepository;
use App\Services\MakeupRequestService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MakeupRequestController extends Controller
{
    protected $makeupRequest;
    protected $makeupRequestService;

    private $userRepository;

    public function __construct(
        MakeupRequest $makeupRequest,
        MakeupRequestService $makeupRequestService,
        UserRepository $userRepository
    )
    {
        $this->makeupRequest = $makeupRequest;
        $this->makeupRequestService = $makeupRequestService;
        $this->userRepository = $userRepository;
    }

    /**
     * Create makeup request
     * 
     */
    public function createMakeupRequestForTeacher(Request $request)
    {
        $user = Auth::user();
        if($user) {
            $this->makeupRequest->validateCreateMakeupRequest($request);
            $teacherId = $user->id;
            $classId = $request->class_id;
            $availabilitySlotId = $request->availability_slot_id;
            $makeupDateTime = $request->makeup_date_time;
            $classType = $request->class_type;

            $data = $this->makeupRequestService->createMakeupRequest($teacherId, $classId, $availabilitySlotId, $makeupDateTime, $classType);
            
            return $this->success($data, 'Makeup request has been created', 200);
        } else {
            return $this->error('Unauthenticated', 404);
        }
    }

    /**
     * Get makeup requests for a teacher
     * 
     * @return JsonResponse
     */
    public function teacherMakeupRequests(Request $request) 
    {
        $user = Auth::user();
        if($user) {
            $this->makeupRequest->validateTeacherMakeupRequests($request);
            
            $teacherId = $user->id;
            $teacherTimezone = $user->timezone;

            $page = $request->query('page');
            $limit = $request->query('limit');
            $offset = ($page - 1) * $limit;

            $makeupRequests = $this->makeupRequestService->teacherMakeupRequests($teacherId, $teacherTimezone, $offset, $limit);
            
            return $this->success($makeupRequests, 'Makeup requests fetched successfully', 200);
        } else {
            return $this->error('Unauthenticated', 404);
        }
    }

    /**
     * Withdraw makeup request
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function withdrawMakeupRequest(Request $request) 
    {
        $user = Auth::user();

        if($user) {
            $this->makeupRequest->validateWithdrawRequest($request);
            $teacherId = $user->id;
            $classId = $request->class_id;
            $message = $this->makeupRequestService->withdrawMakeupRequest($teacherId, $classId);
            
            return $this->success([], $message, 200);
        } else {
            return $this->error('Unauthenticated', 404);
        }
    }

    /**
     * Get all teachers makeup requests
     * 
     */
    public function getAllTeachersMakeupRequests(Request $request) 
    {
        $user = Auth::user();
        if($user) {
            $this->makeupRequest->validateTeacherMakeupRequests($request);
            
            $page = $request->page;
            $limit = $request->limit;
            $offset = ($page - 1) * $limit;

            $teacherIds = $this->userRepository->getCoordinatedTeachers($user->id);

            $makeupRequests = $this->makeupRequestService->getAllTeachersMakeupRequests($teacherIds, $offset, $limit);
            
            return $this->success($makeupRequests, 'Makeup requests fetched successfully', 200);
        } else {
            return $this->error('Unauthenticated', 404);
        }
    }
}