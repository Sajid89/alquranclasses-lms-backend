<?php

namespace App\Mail\Teacher;

use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TrialClassCancelled extends Mailable
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
            'Please be informed that the customer has cancelled your scheduled trial class with '.$this->student.' for '.$this->classDateTimeTchrTz.'. As a result, your time slot is now free.',
            'We appreciate your readiness to conduct the class and apologise for any inconvenience this may cause.'
        ];

        $bottom_paragraphs = [
            'If there are any changes or new assignments, we will notify you promptly.',
            'Thank you for your understanding.'
        ];

        $contact_email = env('SCHEDULING_EMAIL');

        return $this->subject('Cancellation of Scheduled Trial Class '.$this->student)
            ->view('emails.email_template', compact('sub_heading', 'top_paragraphs', 'bottom_paragraphs', 'contact_email'));
    }
}
