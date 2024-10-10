<?php
namespace App\Jobs;

use App\Mail\Teacher\StudentJoinclass;
use Illuminate\Support\Facades\Mail;

class SendMailToTeacherWhenStudentJoinTrialClass extends Job
{
    private $classData;

    public function __construct($classData)
    {
        $this->classData = $classData;
    }

    public function handle()
    {
        Mail::to($this->classData['customer_email'])
            ->send(new StudentJoinclass($this->classData));
    }
}