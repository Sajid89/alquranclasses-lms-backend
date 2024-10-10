<?php
namespace App\Jobs;

use App\Mail\Teacher\PaymentMethodSentForReview;
use Illuminate\Support\Facades\Mail;

class SendEmailOnPaymentMethodSubmit extends Job
{
    private $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * email will be sent to teacher
    */
    public function handle()
    {
        Mail::to($this->data['email'])->send(new PaymentMethodSentForReview($this->data));
    }
}