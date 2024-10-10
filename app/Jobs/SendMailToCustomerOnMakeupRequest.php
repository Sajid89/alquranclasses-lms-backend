<?php
namespace App\Jobs;

use App\Mail\Customer\MakeupClassCreatedByTeacher;
use Illuminate\Support\Facades\Mail;

class SendMailToCustomerOnMakeupRequest extends Job
{
    protected $details;

    public function __construct($details)
    {
        $this->details = $details;
    }

    public function handle()
    {
        Mail::to($this->details['customerEmail'])->send(new MakeupClassCreatedByTeacher($this->details));
    }
}