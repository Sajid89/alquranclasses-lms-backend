<?php

namespace App\Mail\SchedulingTeam;

use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TrialMissedByStudent extends Mailable
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
            'We have a missed trial class situation with '.$this->student.' for the session that was scheduled on '.$this->classDatetimeStdTz.' with '.$this->teacher.'. Please mark this in our records as a missed session and hold the slot for potential rescheduling.',
            "We've already informed the student about the missed class. Please ensure you're in touch with the student to reschedule the class at the earliest."
        ];

        $bottom_paragraphs = [
            'Thank you for your attention to this matter.'
        ];

        $contact_email = env('SCHEDULING_EMAIL');

        return $this->subject('Trial Class Missed by '.$this->student)
            ->view('emails.email_template', compact('sub_heading', 'top_paragraphs', 'bottom_paragraphs', 'contact_email'));
    }
}
