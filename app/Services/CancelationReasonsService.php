<?php
namespace App\Services;

use App\Models\ChangeTeacherReason;
use App\Models\SubscriptionCancelationReason;
use App\Repository\CancelationReasonsRepository;
use Illuminate\Http\Request;

class CancelationReasonsService
{
    private $cancelationReasonsRepository;

    public function __construct(CancelationReasonsRepository $cancelationReasonsRepository)
    {
        $this->cancelationReasonsRepository = $cancelationReasonsRepository;
    }

    public function getAllReasons()
    {
        $reasons = $this->cancelationReasonsRepository->all();
        $data = [];
        foreach ($reasons as $reason) {
            $data[] = [
                'id' => $reason->id,
                'reason' => $reason->reason,
            ];
        }
        return $data;
    }

    public function updateCancelationReason(Request $request)
    {
        $cdt = date('Y-m-d H:i:s');
        $this->cancelationReasonsRepository->update(['id' => $request->id], ['reason' => $request->reason, 'updated_at' => $cdt]);
        $reason = array('id' => $request->id, 'reason' => $request->reason, 'updated_at' => $cdt);
        return $reason;
    }

    public function deleteCancelationReason(Request $request)
    {
        $status = SubscriptionCancelationReason::find($request->id)->delete();
        return $status;
    }

    public function getAllChangeTeacherReasons()
    {
        return $this->cancelationReasonsRepository->getAllChangeTeacherReasons();
    }
}