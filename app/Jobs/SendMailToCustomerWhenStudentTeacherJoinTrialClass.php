<?php
namespace App\Jobs;

use App\Mail\Customer\StudentTeacherJoinClass;
use Illuminate\Support\Facades\Mail;

class SendMailToCustomerWhenStudentTeacherJoinTrialClass extends Job
{
    private $classData;

    public function __construct($classData)
    {
        $this->classData = $classData;
    }

    public function handle()
    {
        Mail::to($this->classData['customer_email'])
            ->send(new StudentTeacherJoinClass($this->classData));
    }
}