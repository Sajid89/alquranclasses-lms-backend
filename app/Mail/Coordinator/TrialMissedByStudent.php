<?php

namespace App\Mail\Coordinator;

use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TrialMissedByStudent extends Mailable
{
    use SerializesModels;

    private $coordinator;
    private $teacher;
    private $student;
    private $classDateTimeTchrTz;

    public function __construct($coordinator, $teacher, $student, $classDateTimeTchrTz)
    {
        $this->coordinator = $coordinator;
        $this->teacher = $teacher;
        $this->student = $student;
        $this->classDateTimeTchrTz = $classDateTimeTchrTz;
    }

    public function build()
    {
        $sub_heading = $this->coordinator;
        $top_paragraphs = [
            'Please be advised that the '.$this->student.' did not attend the trial class assigned to '.$this->teacher.' on '.$this->classDateTimeTchrTz.'.',
            "We are in the process of contacting the student to understand the reasons behind their absence and to reschedule the class. We will keep you updated on any developments."
        ];

        $bottom_paragraphs = [
            'Thank you for your support and coordination.'
        ];

        $contact_email = env('SCHEDULING_EMAIL');

        return $this->subject('Missed Trial Class Alert for '.$this->teacher)
            ->view('emails.email_template', compact('sub_heading', 'top_paragraphs', 'bottom_paragraphs', 'contact_email'));
    }
}
