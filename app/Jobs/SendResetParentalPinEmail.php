<?php

namespace App\Jobs;

use App\Traits\Dispatchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;
use App\Mail\ParentalPinEmail;
use Illuminate\Support\Facades\Mail;

class SendResetParentalPinEmail implements ShouldQueue
{
    use Dispatchable, Queueable, SerializesModels;

    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function handle()
    {
        Mail::to($this->data['customer_email'])->send(new ParentalPinEmail($this->data));
    }
}
