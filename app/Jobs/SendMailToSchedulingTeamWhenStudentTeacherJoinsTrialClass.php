<?php
namespace App\Jobs;

use App\Mail\SchedulingTeam\StudentTeacherJoinClass;
use Illuminate\Support\Facades\Mail;

class SendMailToSchedulingTeamWhenStudentTeacherJoinsTrialClass extends Job
{
    private $classData;

    public function __construct($classData)
    {
        $this->classData = $classData;
    }

    public function handle()
    {
        Mail::to(env('SCHEDULING_EMAIL'))
            ->send(new StudentTeacherJoinClass($this->classData));
    }
}