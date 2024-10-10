<?php
namespace App\Jobs;

use App\Mail\Customer\TeacherChangedToCustomer;
use App\Mail\Support\TeacherChangedToSupport;
use App\Mail\Teacher\TeacherChangedToTeacher;
use Illuminate\Support\Facades\Mail;

class SendEmailOnTeacherChanged extends Job
{
    private $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * email will be sent to following in case of change teacher
     * 1. Email to customer
     * 2. Email previous teacher
     * 3. Email to customer support
    */
    public function handle()
    {
        Mail::to($this->data['customer_email'])->send(new TeacherChangedToCustomer($this->data));
        Mail::to($this->data['teacher_email'])->send(new TeacherChangedToTeacher($this->data));
        Mail::to(env('SUPPORT_EMAIL'))->send(new TeacherChangedToSupport($this->data));
    }
}