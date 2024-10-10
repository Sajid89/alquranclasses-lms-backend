<?php

namespace App\Mail\SchedulingTeam;

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
            'Please be advised that '.$this->data['student_name'].' with course '.$this->data['course'].' has activated their subscription to '.$this->data['stripe_plan'],
        ];

        $bottom_paragraphs = [
            'Thank you for your diligence and cooperation in maintaining our scheduling integrity.'
        ];

        $contact_email = env('SCHEDULING_EMAIL');

        return $this->subject('New Subscription Activation: '.$this->data['student_name'].' with '.$this->data['teacher_name'])
            ->view('emails.email_template', compact('sub_heading', 'top_paragraphs', 'bottom_paragraphs', 'contact_email'));
    }
}
