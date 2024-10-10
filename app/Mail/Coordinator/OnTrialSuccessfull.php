<?php

namespace App\Mail\Coordinator;

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
        $sub_heading = $this->classData['coordinator_name'];
        $top_paragraphs = [
            'We are pleased to inform you that '.$this->classData['teacher_name'].' has successfully conducted the trial class with '.$this->classData['student_name'].'. 
            This is an excellent reflection of our team’s commitment to quality education.',
            'Please check in with '.$this->classData['teacher_name'].' to ensure that they have everything they need for their upcoming sessions and to discuss any feedback that could improve future trials.'
        ];

        $bottom_paragraphs = [
            'Thank you for your support in coordinating our educational efforts.'
        ];

        $contact_email = env('SCHEDULING_EMAIL');

        return $this->subject(' Successful Trial Class Completion by '.$this->classData['teacher_name'])
            ->view('emails.email_template', compact('sub_heading', 'top_paragraphs', 'bottom_paragraphs', 'contact_email'));
    }
}
