<?php
namespace App\Jobs;

use App\Mail\Teacher\TeacherMakeupClassMail;
use Illuminate\Support\Facades\Mail;

class SendMailToTeacherOnMakeupRequest extends Job
{
    protected $details;

    public function __construct($details)
    {
        $this->details = $details;
    }

    public function handle()
    {
        Mail::to($this->details['teacherEmail'])->send(new TeacherMakeupClassMail($this->details));
    }
}