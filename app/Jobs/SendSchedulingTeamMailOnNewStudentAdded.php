<?php
namespace App\Jobs;

use App\Mail\SchedulingTeam\StudentAdded;
use Illuminate\Support\Facades\Mail;

class SendSchedulingTeamMailOnNewStudentAdded extends Job
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
        Mail::to(env('SCHEDULING_EMAIL'))->send(new StudentAdded($this->user, $this->studentName));
    }
}