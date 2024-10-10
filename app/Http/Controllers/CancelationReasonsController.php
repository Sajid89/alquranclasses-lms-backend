<?php

namespace App\Http\Controllers;

use App\Http\Requests\CancelationRequest;
use App\Services\CancelationReasonsService;
use Illuminate\Http\Request;

class CancelationReasonsController extends Controller
{
    private $cancelationReasonsService;
    private $cancelationRequest;
    public function __construct(
        CancelationReasonsService $cancelationReasonsService,
        CancelationRequest $cancelationRequest
    )
    {
       $this->cancelationReasonsService = $cancelationReasonsService;
       $this->cancelationRequest = $cancelationRequest;
    }

    /**
     * to get all cancelation reasons
     */
    public function getAllReasons()
    {
        $data = $this->cancelationReasonsService->getAllReasons();
        return $this->success($data, 'Cancelation reasons fetched successfully', 200);
    }

    public function updateCancelationReason(Request $request)
    {
        
        $this->cancelationRequest->validateCancelationReasonUpdate($request);
        $data = $this->cancelationReasonsService->updateCancelationReason($request);

        return $this->success($data, 'Cancelation reasons updated successfully', 200);
    }

    public function deleteCancelationReason(Request $request)
    {
        $this->cancelationRequest->validateCancelationReasonDelete($request);
        $data = $this->cancelationReasonsService->deleteCancelationReason($request);
        return $this->success($data, 'Cancelation reason deleted successfully', 200);
    }

    public function getAllChangeTeacherReasons(Request $request)
    {
        $data = $this->cancelationReasonsService->getAllChangeTeacherReasons();
        return $this->success($data, 'Change teacher reasons fetched successfully', 200);
    }
}
