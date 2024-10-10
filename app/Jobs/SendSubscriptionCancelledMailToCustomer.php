<?php

namespace App\Jobs;

use App\Mail\Customer\SubscriptionCancelled;
use App\Traits\Dispatchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;
use App\Mail\VerificationEmail;
use Illuminate\Support\Facades\Mail;

class SendSubscriptionCancelledMailToCustomer implements ShouldQueue
{
    use Dispatchable, Queueable, SerializesModels;

    protected $subscription;

    public function __construct($subscription)
    {
        $this->subscription = $subscription;
    }

    public function handle()
    {
        $details = [
            'student'  => $this->subscription->student,
            'course'   => $this->subscription->course->course->title,
            'customer' => $this->subscription->user,
        ];

        Mail::to($details['customer']->email)->send(new SubscriptionCancelled($details));
    }
}
