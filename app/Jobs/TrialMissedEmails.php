<?php
namespace App\Jobs;

use Illuminate\Support\Facades\Mail;
use App\Mail\Customer\TrialMissedByTeacher as CusomerTrialMissedByTeacher;
use App\Mail\Teacher\TrialMissedByTeacher as TeacherTrialMissedByTeacher;
use App\Mail\Coordinator\TrialMissedByTeacher as CoordinatorTrialMissedByTeacher;
use App\Mail\SchedulingTeam\TrialClassCancelled as SchedulingTrialClassCancelled;
use App\Mail\Customer\TrialMissedByStudent as CusomerTrialMissedByStudent;
use App\Mail\Teacher\TrialMissedByTeacher as TeacherTrialMissedByStudent;
use App\Mail\Coordinator\TrialMissedByTeacher as CoordinatorTrialMissedByStudent;
use App\Mail\SchedulingTeam\TrialMissedByStudent as SchedulingTrialMissedByStudent;

class TrialMissedEmails extends Job
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

        if ($this->data['by_whom'] === 'teacher')
        {
            Mail::to($customerEmail)->send(new CusomerTrialMissedByTeacher($customer, $student, $teacher, $classDatetimeStdTz));
            Mail::to($teacherEmail)->send(new TeacherTrialMissedByTeacher($teacher, $student, $classDateTimeTchrTz));
            Mail::to($coordinatorEmail)->send(new CoordinatorTrialMissedByTeacher($coordinator, $teacher, $student, $classDateTimeTchrTz));
            Mail::to($schedulingTeam)->send(new SchedulingTrialClassCancelled($student, $teacher, $classDatetimeStdTz));
        }
        else
        {
            Mail::to($customerEmail)->send(new CusomerTrialMissedByStudent($customer, $student, $teacher, $classDatetimeStdTz));
            Mail::to($teacherEmail)->send(new TeacherTrialMissedByStudent($teacher, $student, $classDateTimeTchrTz));
            Mail::to($coordinatorEmail)->send(new CoordinatorTrialMissedByStudent($coordinator, $teacher, $student, $classDateTimeTchrTz));
            Mail::to($schedulingTeam)->send(new SchedulingTrialMissedByStudent($student, $teacher, $classDatetimeStdTz));
        }
    }
}