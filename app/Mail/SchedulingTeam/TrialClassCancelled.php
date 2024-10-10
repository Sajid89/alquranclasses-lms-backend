<?php

namespace App\Mail\SchedulingTeam;

use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TrialClassCancelled extends Mailable
{
    use SerializesModels;

    private $student;
    private $teacher;
    private $classDatetimeStdTz;

    public function __construct($student, $teacher, $classDatetimeStdTz)
    {
        $this->student = $student;
        $this->teacher = $teacher;
        $this->classDatetimeStdTz = $classDatetimeStdTz;
    }

    public function build()
    {
        $sub_heading = 'Team';
        $top_paragraphs = [
            'This is to notify you that the trial class scheduled for '.$this->student.' with '.$this->teacher.' on '.$this->classDatetimeStdTz.' has been cancelled by the customer.',
            "The teacher's booked slot has been opened and is available for another student to book."
        ];

        $bottom_paragraphs = [
            'Let’s ensure we monitor and manage our upcoming schedules effectively to accommodate such changes.'
        ];

        $contact_email = env('SCHEDULING_EMAIL');

        return $this->subject('Trial Class Cancellation Alert - '.$this->student)
            ->view('emails.email_template', compact('sub_heading', 'top_paragraphs', 'bottom_paragraphs', 'contact_email'));
    }
}
