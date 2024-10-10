<?php
namespace App\Jobs;

use Illuminate\Support\Facades\Mail;
use App\Mail\Customer\TrialClassCancelled as CusomerTrialClassCancelled;
use App\Mail\Teacher\TrialClassCancelled as TeacherTrialClassCancelled;
use App\Mail\Coordinator\TrialClassCancelled as CoordinatorTrialClassCancelled;
use App\Mail\SchedulingTeam\TrialClassCancelled as SchedulingTeamTrialClassCancelled;
use App\Models\TrialClass;

class MakeupRequestAcceptRejectEmails extends Job
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function handle()
    {
        $customer = $this->data['customer_name'];
        $customerEmail = $this->data['customer_email'];
        $student = $this->data['student_name'];
        $classDatetimeStdTz = $this->data['class_datetime_std_tz'];

        $teacher = $this->data['teacher_name'];
        $teacherEmail = $this->data['teacher_email'];
        $classDateTimeTchrTz = $this->data['class_datetime_tchr_tz'];

        $coordinator = $this->data['coordinator_name'];
        $coordinatorEmail = $this->data['coordinator_email'];
        $schedulingTeam = env('SCHEDULING_EMAIL');

        Mail::to($customerEmail)->send(new CusomerTrialClassCancelled($customer, $student, $classDatetimeStdTz));
        Mail::to($teacherEmail)->send(new TeacherTrialClassCancelled($teacher, $student, $classDateTimeTchrTz));
        Mail::to($coordinatorEmail)->send(new CoordinatorTrialClassCancelled($coordinator, $teacher, $classDateTimeTchrTz));
        Mail::to($schedulingTeam)->send(new SchedulingTeamTrialClassCancelled($student, $teacher, $classDatetimeStdTz));
    }
}