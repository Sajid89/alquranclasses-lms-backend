<?php

namespace App\Http\Controllers;

use App\Services\NotToCancelSubscriptionService;
use App\Http\Requests\NotToCancelSubscriptionRequest;
use Illuminate\Http\Request;

class NotToCancelReasonsController extends Controller
{
    private $notToCancelSubscriptionService;
    private $notToCancelRequest;
    public function __construct(
        NotToCancelSubscriptionService $notToCancelSubscriptionService,
        NotToCancelSubscriptionRequest $notToCancelRequest
    )
    {
       $this->notToCancelSubscriptionService = $notToCancelSubscriptionService;
       $this->notToCancelRequest = $notToCancelRequest;
    }

    /**
     * to get all not to cancel reasons
     */
    public function getAllReasons()
    {
        $data = $this->notToCancelSubscriptionService->getAllReasons();
        return $this->success($data, 'Not to cancel reasons fetched successfully', 200);
    }

    public function updateNotToCancelReason(Request $request){
        $this->notToCancelRequest->validateNotToCancelReasonUpdate($request);
        $data = $this->notToCancelSubscriptionService->updateNotToCancelReason($request);
        return $this->success($data, 'Not to cancel reason updated successfully', 200);
    }


    public function deleteNotToCancelReason(Request $request)
    {
        $this->notToCancelRequest->validateCancelationReasonDelete($request);
        $data = $this->notToCancelSubscriptionService->deleteNotToCancelReason($request);
        return $this->success($data, 'Not to cancel reason deleted successfully', 200);
    }
}
