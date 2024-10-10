<?php
namespace App\Jobs;

use Illuminate\Support\Facades\Mail;
use App\Mail\Customer\PaymentFailed;

class SendPaymentFailedEmail extends Job
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function handle()
    {
        Mail::to($this->data['customer_email'])->send(new PaymentFailed($this->data));
    }
}