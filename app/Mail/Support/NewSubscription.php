<?php

namespace App\Mail\Support;

use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewSubscription extends Mailable
{
    use SerializesModels;

    private $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function build()
    {
        $sub_heading = 'Team';
        $top_paragraphs = [
            'We have a new subscriber, '.$this->data['student_name'].' with course '.$this->data['course'].', who has just activated their '.$this->data['stripe_plan'].' with Teacher '.$this->data['teacher_name'].'.',
            'As they begin to explore and utilize our services, they may reach out with questions or require assistance.'
        ];

        $bottom_paragraphs = [
            'Please be ready to offer support and ensure their experience is smooth and fulfilling. Familiarize yourselves with their subscription details and be proactive in providing the necessary guidance.',
            'Thank you for your commitment to delivering outstanding customer service.'
        ];

        $contact_email = env('SALES_EMAIL');

        return $this->subject('Alert: New Subscription Activation for '.$this->data['student_name'].' with '.$this->data['teacher_name'])
            ->view('emails.email_template', compact('sub_heading', 'top_paragraphs', 'bottom_paragraphs', 'contact_email'));
    }
}
