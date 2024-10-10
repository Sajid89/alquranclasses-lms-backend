<?php

namespace App\Http\Controllers;

use App\Events\TrialClassCreated;
use App\Helpers\GeneralHelper;
use App\Http\Requests\TrialRequest;
use App\Http\Resources\TrialClassResource;
use Illuminate\Http\Request;
use App\Jobs\SendTrialClassCreatedEmail;
use App\Models\Notification;
use App\Services\TrialService;
use Illuminate\Support\Facades\DB;

class TrialController extends Controller
{
    protected $trialRequest;
    protected $trialService;

    public function __construct(
        TrialRequest $trialRequest,
        TrialService $trialService
    )
    {
        $this->trialRequest = $trialRequest;
        $this->trialService = $trialService;
    }

    
    public function createTrialClass(Request $request)
    {
        $this->trialRequest->validateCreateTrial($request);

        $trialClass = $this->trialService->createTrialClass($request);
        $classTimeStdTz = GeneralHelper::convertTimeToUserTimezone($trialClass->class_time, $trialClass->student->timezone);
        $classTimeTchrTz = GeneralHelper::convertTimeToUserTimezone($trialClass->class_time, $trialClass->teacher->timezone);

        $emailData = [
            'customer_name'    => $trialClass->student->user->name,
            'customer_email'   => $trialClass->student->user->email,
            'student_name'     => $trialClass->student->name,
            'teacher_name'     => $trialClass->teacher->name,
            'teacher_email'    => $trialClass->teacher->email,
            'classTimeStdTz'   => $classTimeStdTz,
            'classTimeTchrTz'  => $classTimeTchrTz,
            'student_timezone' => $trialClass->student->timezone,
        ];

        // Dispatch the job to send the emails
        dispatch(new SendTrialClassCreatedEmail($emailData));

        // convert the class time to the student's timezone
        $trialClass->class_time = GeneralHelper::convertTimeToUserTimezone($trialClass->class_time, $trialClass->student->timezone);

        // Create a new notification
        Notification::create([
            'user_id' => $trialClass->student->user->id,
            'student_id' => $trialClass->student->id,
            'type' => 'trial_class',
            'read' => false,
            'message' => "A trial class has been scheduled for {$trialClass->student->name} on {$trialClass->class_time} with {$trialClass->teacher->name}."
        ]);

        return $this->success(new TrialClassResource($trialClass), 'Trial class created successfully.', 201);
    }
}
