<?php
namespace App\Jobs;

use App\Mail\Customer\Welcome;
use Illuminate\Support\Facades\Mail;

class SendWelcomeMailCustomer extends Job
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function handle()
    {
        Mail::to($this->data['customer_email'])->send(new Welcome($this->data));
    }
}