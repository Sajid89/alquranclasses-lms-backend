<?php

namespace App\Mail\Teacher;

use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OnTrialSuccessfull extends Mailable
{
    use SerializesModels;

    public $classData;

    public function __construct($classData)
    {
        $this->classData = $classData;
    }

    public function build()
    {
        $sub_heading = $this->classData['teacher_name'];
        $top_paragraphs = [
            'Your journey towards mastery is just beginning, and we are excited to be a part of it.',
            "Please ensure you’ve completed any follow-up actions, such as providing feedback or notes from the session, in our system. Your insights are valuable for both the student's learning journey and our course improvement."
        ];

        $bottom_paragraphs = [
            'Thank you for your dedication to providing exceptional education.'
        ];

        $contact_email = env('SCHEDULING_EMAIL');

        return $this->subject('Success! Your Recent Trial Class with Student '.$this->classData['student_name'])
            ->view('emails.email_template', compact('sub_heading', 'top_paragraphs', 'bottom_paragraphs', 'contact_email'));
    }
}
