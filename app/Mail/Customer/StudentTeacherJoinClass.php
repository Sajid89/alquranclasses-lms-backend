<?php

namespace App\Mail\Customer;

use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class StudentTeacherJoinClass extends Mailable
{
    use SerializesModels;

    public $classData;

    public function __construct($classData)
    {
        $this->classData = $classData;
    }

    public function build()
    {
        $sub_heading = 'Scheduling Team';
        $top_paragraphs = [
            'This automated message is to inform you that both the teacher and student 
            have successfully joined the scheduled class. This is a positive indication 
            of smooth operations and engagement on our platform.',
            'Please find the details of the class below:'
        ];

        $list = [
            'Student Name' => $this->classData['student_name'],
            'Teacher Name' => $this->classData['teacher_name'],
            'Class Time'   => $this->classData['class_time'],
            'Course Name'  => $this->classData['course'],
        ];

        $bottom_paragraphs = [
            'If you have any questions or need guidance on choosing the right course, our support team is here to help. You can reach us at <a href="mailto:product.scheduling@alquranclasses.com" style="color: #01563F; text-decoration: none; font-weight: 600;">product.scheduling@alquranclasses.com</a> or call us directly.',
            'We are excited to be a part of your Quranic journey and look forward to supporting you every step of the way.'
        ];

        $contact_email = env('SALES_EMAIL');

        return $this->subject('Trial Class Started for '. $this->classData['student_name'])
            ->view('emails.email_template', compact('sub_heading', 'top_paragraphs', 'list', 'bottom_paragraphs', 'contact_email'));
    }
}
