<?php
namespace App\Jobs;

use App\Mail\Customer\OnTrialSuccessfull;
use App\Mail\Teacher\OnTrialSuccessfull as TeacherOnTrialSuccessfull;
use App\Mail\Coordinator\OnTrialSuccessfull as CoordinatorOnTrialSuccessfull;
use App\Mail\SchedulingTeam\OnTrialSuccessfull as SchedulingTeamOnTrialSuccessfull;
use App\Mail\Customer\StudentAdded;
use Illuminate\Support\Facades\Mail;

class SendTrailSuccessMailToCustomer extends Job
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function handle()
    {
        Mail::to($this->data['customer_email'])->send(new OnTrialSuccessfull($this->data));
        Mail::to($this->data['teacher_email'])->send(new TeacherOnTrialSuccessfull($this->data));
        Mail::to($this->data['coordinator_email'])->send(new CoordinatorOnTrialSuccessfull($this->data));
        Mail::to(env('SCHEDULING_EMAIL'))->send(new SchedulingTeamOnTrialSuccessfull($this->data));
    }
}