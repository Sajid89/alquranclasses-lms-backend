<?php

namespace App\Mail\Coordinator;

use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TrialMissedByTeacher extends Mailable
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
            'We want to bring to your attention that '.$this->teacher.' missed the scheduled trial class with '.$this->student.' on '.$this->classDateTimeTchrTz.'. This incident has led to the cancellation of the session, impacting our commitment to providing timely and reliable services to our customers.',
            "Please investigate the circumstances leading to this absence and discuss with '.$this->teacher.' the importance of adhering to the schedule. We need to ensure reliability and prevent similar incidents in the future."
        ];

        $bottom_paragraphs = [
            'Thank you for your prompt attention to this matter and for taking the necessary steps to address this issue.'
        ];

        $contact_email = env('SCHEDULING_EMAIL');

        return $this->subject('Action Required: Missed Trial Class by '.$this->teacher)
            ->view('emails.email_template', compact('sub_heading', 'top_paragraphs', 'bottom_paragraphs', 'contact_email'));
    }
}
