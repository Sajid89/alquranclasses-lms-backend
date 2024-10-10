<?php

namespace App\Mail\Teacher;

use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PaymentMethodSentForReview extends Mailable
{
    use SerializesModels;

    public $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function build()
    {
        $sub_heading = $this->data['name'];
        $top_paragraphs = [
            'Your bank account information has been successfully submitted for review. 
            We will notify you once it has been approved. 
            In the meantime, you can continue to teach your classes as scheduled.'
        ];

        $bottom_paragraphs = [
            'If you have any questions or need assistance, please don’t hesitate to <a href="mailto:support@alquranclasses.com" style="color: #01563F; text-decoration: none; font-weight: 600;">contact us</a>.',
        ];

        $contact_email = env('SALES_EMAIL');

        return $this->subject('Bank Account information has been submitted - '.$this->data['name'])
            ->view('emails.email_template', compact('sub_heading', 'top_paragraphs', 'bottom_paragraphs', 'contact_email'));
    }
}
