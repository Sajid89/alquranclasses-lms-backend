<?php
namespace App\Jobs;

use Illuminate\Support\Facades\Mail;
use App\Mail\Customer\NewSubscription;
use App\Mail\Teacher\NewSubscription as TeacherNewSubscription;
use App\Mail\Coordinator\NewSubscription as CoordinatorNewSubscription;
use App\Mail\SchedulingTeam\NewSubscription as SchedulingTeamNewSubscription;
use App\Mail\Support\NewSubscription as SupportNewSubscription;

class SendNewSubscriptionEmails extends Job
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function handle()
    {
        Mail::to($this->data['customer_email'])->send(new NewSubscription($this->data));
        Mail::to($this->data['teacher_email'])->send(new TeacherNewSubscription($this->data));
        Mail::to($this->data['coordinator_email'])->send(new CoordinatorNewSubscription($this->data));
        Mail::to(env('SCHEDULING_EMAIL'))->send(new SchedulingTeamNewSubscription($this->data));
        Mail::to(env('SALES_EMAIL'))->send(new SupportNewSubscription($this->data));
    }
}