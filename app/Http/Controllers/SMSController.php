<?php

namespace App\Http\Controllers;

use App\Services\TwilioService;
use Illuminate\Http\Request;
use App\Http\Requests\CustomerRequest;
use App\Models\User;
use Carbon\Carbon;

class SMSController extends Controller
{
    protected $twilioService;

    /**
     * SMSController constructor.
     * @param TwilioService $twilioService
     */
    public function __construct(TwilioService $twilioService)
    {
        $this->twilioService = $twilioService;
    }

    /**
     * Send SMS
     *
     * @param Request $request
     * @param CustomerRequest $customerRequest
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendSMS(Request $request, CustomerRequest $customerRequest)
    {
        $validatedRequest = $customerRequest->validateSMS($request);

        try {
            $response = $this->twilioService->sendSMS($validatedRequest->to);
            return $this->success($response, 'Verification code sent successfully.', 201);
        } catch (\Exception $e) {
            return $this->error('Failed to send SMS. '. $e->getMessage(), 500);
        }
    }

    /**
     * Check verification
     *
     * @param Request $request
     * @param CustomerRequest $customerRequest
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkVerification(Request $request, CustomerRequest $customerRequest)
    {
        $validatedRequest = $customerRequest->validateVerification($request);

        try {
            $user = User::where('phone', $request->to)->first();

            if (!$user) {
                return $this->error('User not found.', 404);
            }

            $response = $this->twilioService->checkVerification($validatedRequest->to, $validatedRequest->code);

            if ($response !== 'approved') {
                return $this->error('Invalid verification code.', 400);
            }

            $user->phone_number_verified_at = Carbon::now();
            $user->save();

            return $this->success($response, 'Verification successful.', 200);
        } catch (\Exception $e) {
            return $this->error('Failed to verify. '. $e->getMessage(), 500);
        }
    }
}
