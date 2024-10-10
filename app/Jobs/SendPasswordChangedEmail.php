<?php

namespace App\Jobs;

use App\Mail\PasswordResetMail;
use App\Traits\Dispatchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class SendPasswordChangedEmail implements ShouldQueue
{
    use Dispatchable, Queueable;

    private $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function handle()
    {
        Mail::to($this->data['customer_email'])->send(new PasswordResetMail($this->data));
    }
}
