<?php
namespace App\Jobs;

use App\Mail\Customer\StudentAdded;
use Illuminate\Support\Facades\Mail;

class SendCustomerMailOnNewStudentAdded extends Job
{
    protected $user;
    protected $studentName;

    public function __construct($user, $studentName)
    {
        $this->user = $user;
        $this->studentName = $studentName;
    }

    public function handle()
    {
        Mail::to($this->user->email)->send(new StudentAdded($this->user, $this->studentName));
    }
}