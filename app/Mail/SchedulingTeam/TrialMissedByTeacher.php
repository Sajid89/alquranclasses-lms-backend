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
            'Please note that the trial class scheduled with '.$this->teacher.' for '.$this->student.' on '.$this->student.' was not conducted as the teacher was absent. This has resulted in a missed opportunity and a negative customer experience.',
            "We need to assess our scheduling protocol and put measures in place to prevent such occurrences. Also, please prepare to accommodate a rescheduled class for the affected customer as soon as possible."
        ];

        $bottom_paragraphs = [
            'Your cooperation in managing this situation effectively and preventing future lapses is crucial.'
        ];

        $contact_email = env('SCHEDULING_EMAIL');

        return $this->subject('Urgent: Review and Action Needed for Missed Trial Class by - '.$this->teacher)
            ->view('emails.email_template', compact('sub_heading', 'top_paragraphs', 'bottom_paragraphs', 'contact_email'));
    }
}
