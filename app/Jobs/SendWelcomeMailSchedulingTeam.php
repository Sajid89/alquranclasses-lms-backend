<?php
namespace App\Jobs;

use App\Mail\SchedulingTeam\Welcome;
use Illuminate\Support\Facades\Mail;

class SendWelcomeMailSchedulingTeam extends Job
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function handle()
    {
        Mail::to(env('SCHEDULING_EMAIL'))->send(new Welcome($this->data));
    }
}