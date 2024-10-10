<?php

namespace App\Mail\Coordinator;

use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewSubscription extends Mailable
{
    use SerializesModels;

    public $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function build()
    {
        $sub_heading = $this->data['coordinator_name'];
        $top_paragraphs = [
            'This email is to notify you that '.$this->data['student_name'].' with course '.$this->data['course'].' has activated their subscription with Teacher '.$this->data['teacher_name'].'. This may influence the class schedules and assignments for our teaching staff.',
            'Please coordinate with the teachers to ensure they are prepared to accommodate and support the new subscriber effectively.'
        ];

        $bottom_paragraphs = [
            'Thank you for your leadership and support in managing our educational offerings.'
        ];

        $contact_email = env('SALES_EMAIL');

        return $this->subject('Subscription Activation Notice : '.$this->data['student_name'])
            ->view('emails.email_template', compact('sub_heading', 'top_paragraphs', 'bottom_paragraphs', 'contact_email'));
    }
}
