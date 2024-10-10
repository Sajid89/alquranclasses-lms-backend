<?php
namespace App\Jobs;

use Illuminate\Support\Facades\Mail;
use App\Mail\Customer\TrialClassCreated;
use App\Mail\Teacher\TrialClassCreated as TeacherTrialClassCreated;
use App\Mail\SchedulingTeam\TrialClassCreated as SchedulingTeamTrialClassCreated;

class SendTrialClassCreatedEmail extends Job
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function handle()
    {
        Mail::to($this->data['customer_email'])->send(new TrialClassCreated($this->data));
        Mail::to($this->data['teacher_email'])->send(new TeacherTrialClassCreated($this->data));
        Mail::to(env('SCHEDULING_EMAIL'))->send(new SchedulingTeamTrialClassCreated($this->data));
    }
}