<?php

namespace App\Mail\Customer;

use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TrialMissedByStudent extends Mailable
{
    use SerializesModels;

    private $customer;
    private $student;
    private $teacher;
    private $classDatetimeStdTz;

    public function __construct($customer, $teacher, $student, $classDatetimeStdTz)
    {
        $this->customer = $customer;
        $this->student = $student;
        $this->teacher = $teacher;
        $this->classDatetimeStdTz = $classDatetimeStdTz;
    }

    public function build()
    {
        $sub_heading = $this->customer;
        $top_paragraphs = [
            'We noticed that you were unable to attend your scheduled trial class with '.$this->teacher.' on '.$this->classDatetimeStdTz.'. We missed having you with us!',
            'We understand that life gets busy, and schedules can change. If you’re still interested in experiencing our classes, we’d be more than happy to reschedule your trial at a more convenient time.'
        ];

        $bottom_paragraphs = [
            'If there was a specific issue that prevented your attendance, please let us know how we can assist. Your feedback is important to us, and we want to ensure a seamless experience for your learning journey.',
            'Looking forward to welcoming you soon!',
        ];

        $contact_email = env('SCHEDULING_EMAIL');

        return $this->subject(' We Missed You in Your Trial Class')
            ->view('emails.email_template', compact('sub_heading', 'top_paragraphs', 'bottom_paragraphs', 'contact_email'));
    }
}
