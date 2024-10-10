<?php

namespace App\Mail\SchedulingTeam;

use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class StudentAdded extends Mailable
{
    use SerializesModels;

    public $user;
    public $studentName;

    public function __construct($user, $studentName)
    {
        $this->user = $user;
        $this->studentName = $studentName;
    }

    public function build()
    {
        $sub_heading = 'Scheduling Team';
        $top_paragraphs = [
            'This is an automated message to inform you that Customer ' . $this->user->name . ' has added a new student '. $this->studentName.' to their account.',
        ];

        // $list = [
        //     'Student Name' => $this->trialClass->student->name,
        //     'Teacher Name' => $this->trialClass->teacher->name,
        //     'Class Time'   => $this->trialClass->class_time,
        // ];

        $bottom_paragraphs = [
            'Lets make sure to provide ' . $this->studentName . ' with the best experience and support as they begin their journey with AlQuranClasses.'
        ];

        $contact_email = env('SCHEDULING_EMAIL');

        return $this->subject('New Student has been Added - ' . $this->studentName)
            ->view('emails.email_template', compact('sub_heading', 'top_paragraphs', 'bottom_paragraphs', 'contact_email'));
    }
}
