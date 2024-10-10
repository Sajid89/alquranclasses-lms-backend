<?php

namespace App\Mail\Teacher;

use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TrialMissedByTeacher extends Mailable
{
    use SerializesModels;

    private $teacher;
    private $student;
    private $classDateTimeTchrTz;

    public function __construct($teacher, $student, $classDateTimeTchrTz)
    {
        $this->teacher = $teacher;
        $this->student = $student;
        $this->classDateTimeTchrTz = $classDateTimeTchrTz;
    }

    public function build()
    {
        $sub_heading = $this->teacher;
        $top_paragraphs = [
            'We have noticed that the trial class scheduled with '.$this->student.' on '.$this->classDateTimeTchrTz.' was missed. We understand that unforeseen circumstances can occur and are concerned about your absence.',
            'Please inform us about what happened and ensure that such instances are communicated in advance so we can make the necessary arrangements.'
        ];

        $bottom_paragraphs = [
            "It's important to maintain a reliable and professional standard for our students and to uphold the quality of our teaching service. Let's discuss how we can avoid future occurrences and ensure a positive experience for our students."
        ];

        $contact_email = env('SCHEDULING_EMAIL');

        return $this->subject('Missed Trial Class Notification for '.$this->student)
            ->view('emails.email_template', compact('sub_heading', 'top_paragraphs', 'bottom_paragraphs', 'contact_email'));
    }
}
