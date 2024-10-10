<?php

namespace App\Mail\Customer;

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
        $sub_heading = $this->data['customer_name'];
        $top_paragraphs = [
            'A trial class has been scheduled again '.$this->data['student_name'].'.',
            'Please find the details below:'
        ];

        $list = [
            'Student Name' => $this->data['student_name'],
            'Teacher Name' => $this->data['teacher_name'],
            'Class Time'   => $this->data['classTimeStdTz'],
        ];

        $bottom_paragraphs = [
            'If you have any questions or need assistance, please don’t hesitate to <a href="mailto:support@alquranclasses.com" style="color: #01563F; text-decoration: none; font-weight: 600;">contact us</a>.',
            'Thank you for choosing AlQuranClasses. We look forward to supporting you on your journey of Quranic learning and understanding.'
        ];

        $contact_email = env('SCHEDULING_EMAIL');

        return $this->subject('Successfully Scheduled a Trial - '.$this->data['student_name'])
            ->view('emails.email_template', compact('sub_heading', 'top_paragraphs', 'list', 'bottom_paragraphs', 'contact_email'));
    }
}
