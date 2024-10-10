<?php

namespace App\Mail\SchedulingTeam;

use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OnTrialSuccessfull extends Mailable
{
    use SerializesModels;

    public $classData;

    public function __construct($classData)
    {
        $this->classData = $classData;
    }

    public function build()
    {
        $sub_heading = 'Team';
        $top_paragraphs = [
            'The trial class with '.$this->classData['student_name'].', conducted by '.$this->classData['teacher_name'].', has been completed. The Trial class status has been updated successfully. 
            You should be able to access the class details on your portal.',
        ];

        $bottom_paragraphs = [
            'Let’s continue to monitor the progress and feedback from these sessions to optimise our scheduling and offer the best possible experience to both students and teachers.'
        ];

        $contact_email = env('SCHEDULING_EMAIL');

        return $this->subject('Successful Trial Class Completion by '.$this->classData['teacher_name'])
            ->view('emails.email_template', compact('sub_heading', 'top_paragraphs', 'bottom_paragraphs', 'contact_email'));
    }
}
