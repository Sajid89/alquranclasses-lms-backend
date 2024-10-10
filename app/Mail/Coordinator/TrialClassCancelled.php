<?php

namespace App\Mail\Coordinator;

use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TrialClassCancelled extends Mailable
{
    use SerializesModels;

    private $coordinator;
    private $teacher;
    private $classDateTimeTchrTz;

    public function __construct($coordinator, $teacher, $classDateTimeTchrTz)
    {
        $this->coordinator = $coordinator;
        $this->teacher = $teacher;
        $this->classDateTimeTchrTz = $classDateTimeTchrTz;
    }

    public function build()
    {
        $sub_heading = $this->coordinator;
        $top_paragraphs = [
            'We wanted to inform you that the trial class scheduled for '.$this->teacher.' on '.$this->classDateTimeTchrTz.' has been cancelled by the customer.',
            "No action is required from the teacher's side at this moment."
        ];

        $bottom_paragraphs = [
            'Thank you for your attention to this matter.'
        ];

        $contact_email = env('SCHEDULING_EMAIL');

        return $this->subject('Cancelled Trial Class Notification for '.$this->teacher)
            ->view('emails.email_template', compact('sub_heading', 'top_paragraphs', 'bottom_paragraphs', 'contact_email'));
    }
}
