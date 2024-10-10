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
            'We wanted to inform you that '.$this->student.' missed the trial class scheduled for '.$this->classDateTimeTchrTz.' . We will reach out to the student to understand their situation and attempt to reschedule the session.',
        ];

        $bottom_paragraphs = [
            "Thank you for being prepared and ready to teach. We appreciate your flexibility and understanding. We will notify you once the class is rescheduled or if there are any updates."
        ];

        $contact_email = env('SCHEDULING_EMAIL');

        return $this->subject('Student Absence Notification for Trial Class '.$this->student)
            ->view('emails.email_template', compact('sub_heading', 'top_paragraphs', 'bottom_paragraphs', 'contact_email'));
    }
}
