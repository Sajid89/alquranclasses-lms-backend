<?php

namespace App\Mail\Customer;

use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TrialClassCancelled extends Mailable
{
    use SerializesModels;

    private $customer;
    private $student;
    private $classDatetimeStdTz;

    public function __construct($customer, $student, $classDatetimeStdTz)
    {
        $this->customer = $customer;
        $this->student = $student;
        $this->classDatetimeStdTz = $classDatetimeStdTz;
    }

    public function build()
    {
        $sub_heading = $this->customer;
        $top_paragraphs = [
            'We have received your request to cancel the upcoming trial class scheduled for '.$this->classDatetimeStdTz.'. Your trial has been successfully cancelled.',
            'We are sorry to see you go and would love to understand if there was a reason for the cancellation. Your feedback is valuable and helps us improve our services.'
        ];

        $bottom_paragraphs = [
            'If you decide to reschedule or if there is anything we can assist you with, please feel free to contact us at <a href="mailto:product.scheduling@alquranclasses.com" style="color: #01563F; text-decoration: none; font-weight: 600;">product.scheduling@alquranclasses.com</a>.',
            'Thank you for considering our services. We hope to have the opportunity to assist you in the future.'
        ];

        $contact_email = env('SCHEDULING_EMAIL');

        return $this->subject('Confirmation of Your Trial Cancellation for '.$this->student)
            ->view('emails.email_template', compact('sub_heading', 'top_paragraphs', 'bottom_paragraphs', 'contact_email'));
    }
}
