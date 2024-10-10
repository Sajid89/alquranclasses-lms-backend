<?php
namespace App\Jobs;

use App\Mail\Customer\SubscriptionUpdated;
use App\Mail\Customer\Welcome;
use Illuminate\Support\Facades\Mail;

class SendMailToCustomerOnsubscriptionUpdate extends Job
{
    protected $details;

    public function __construct($details)
    {
        $this->details = $details;
    }

    public function handle()
    {
        Mail::to($this->details['customerEmail'])->send(new SubscriptionUpdated($this->details));
    }
}