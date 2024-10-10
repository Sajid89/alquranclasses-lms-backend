<?php
namespace App\Services;

use App\Models\NotToCancelSubscriptionReason;
use App\Repository\NotToCancelReasonsRepository;
use Illuminate\Http\Request;

class NotToCancelSubscriptionService
{
     private $notToCancelReasonsRepository;

    public function __construct(NotToCancelReasonsRepository $notToCancelReasonsRepository)
    {
        $this->notToCancelReasonsRepository = $notToCancelReasonsRepository;
    }

    public function getAllReasons()
    {
        $reasons = $this->notToCancelReasonsRepository->all();
        $data = [];
        foreach ($reasons as $reason) {
            $data[] = [
                'id' => $reason->id,
                'reason' => $reason->reason,
            ];
        }
        return $data;
    }

    public function updateNotToCancelReason(Request $request)
    {
        $cdt = date('Y-m-d H:i:s');
        $this->notToCancelReasonsRepository->update(['id' => $request->id], ['reason' => $request->reason, 'updated_at' => $cdt]);
        $reason = array('id' => $request->id, 'reason' => $request->reason, 'updated_at' => $cdt);
        return $reason;
    }

    public function deleteNotToCancelReason(Request $request)
    {
        $status = NotToCancelSubscriptionReason::find($request->id)->delete();
        return $status;
    }
}