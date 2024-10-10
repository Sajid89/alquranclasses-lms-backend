<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ParentalPinEmail extends Mailable
{
    use SerializesModels;

    private $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function build()
    {
        $sub_heading = $this->data['customer_name'];
        $top_paragraphs = [
            'We received a request to reset the parental pin for your account. To verify your email address and reset the parental pin, please click the button below.',
            'Please click on the link given below to reset parental pin:'
        ];

        $button = [
            'text' => 'Reset Parental Pin',
            'url' => $this->data['reset_link'],
        ];

        $bottom_paragraphs = [
            'This link will expire in 24 hours. ',
            'If you have any questions or need assistance, please don’t hesitate to <a href="mailto:support@alquranclasses.com" style="color: #01563F; text-decoration: none; font-weight: 600;">contact us</a>.'
        ];
        
        $contact_email = env('SCHEDULING_EMAIL');

        return $this->subject('Reset parental pin for AlQuranClasses')
            ->view('emails.email_template', compact('sub_heading', 'top_paragraphs', 'button', 'bottom_paragraphs', 'contact_email'));
    }
}
