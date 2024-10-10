<?php

namespace App\Mail\Teacher;

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
        $sub_heading = $this->data['teacher_name'];
        $top_paragraphs = [
            'We are pleased to inform you that '.$this->data['student_name'].' with course '.$this->data['course'].' has successfully activated their subscription with your schedule. 
            They may soon be joining your classes as they embark on their learning journey.',
        ];

        $bottom_paragraphs = [
            'The new classes are added on your schedule and you can access them from your account.',
            'Please prepare to welcome them warmly and facilitate their integration into our educational community.',
            'Thank you for your continued dedication and effort in delivering exceptional educational experiences.'
        ];

        $contact_email = env('SALES_EMAIL');

        return $this->subject('New Student Added: '.$this->data['student_name'])
            ->view('emails.email_template', compact('sub_heading', 'top_paragraphs', 'bottom_paragraphs', 'contact_email'));
    }
}
