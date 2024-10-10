<?php

namespace App\Mail\SchedulingTeam;

use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TrialClassCreated extends Mailable
{
    use SerializesModels;

    private $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function build()
    {
        $sub_heading = 'Team';
        $top_paragraphs = [
            'We are pleased to announce that we have successfully scheduled a trial class that will be conducted by '.$this->data['teacher_name'].' with our prospective student, '.$this->data['student_name'].'.',
            'Here are the details of the scheduled trial class:'
        ];

        $list = [
            'Teacher'         => $this->data['teacher_name'],
            'Student'         => $this->data['student_name'],
            'Date and Time'   => $this->data['classTimeStdTz'],
            'Timezone'        => $this->data['student_timezone']
        ];

        $bottom_paragraphs = [
            "This trial class is a critical step for {$this->data['student_name']} to experience the quality of education we provide and for us to demonstrate our teaching methodology and the value we bring to our students' learning journey.",
            "Let’s all ensure that {$this->data['teacher_name']} receives the necessary support and resources to deliver an engaging and effective trial class. This is an excellent opportunity for us to showcase our commitment to education and to welcoming new students into our community.",
            'Thank you for your cooperation and dedication to making our trial classes a resounding success.'
        ];

        $contact_email = env('SCHEDULING_EMAIL');

        return $this->subject('Trial Class Scheduled for - '.$this->data['student_name'].' with '.$this->data['teacher_name'])
            ->view('emails.email_template', compact('sub_heading', 'top_paragraphs', 'list', 'bottom_paragraphs', 'contact_email'));
    }
}
