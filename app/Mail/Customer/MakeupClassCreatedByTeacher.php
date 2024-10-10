<?php

namespace App\Mail\Customer;

use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MakeupClassCreatedByTeacher extends Mailable
{
    use SerializesModels;

    public $details;

    public function __construct($details)
    {
        $this->details = $details;
    }

    public function build()
    {
        $customerName = $this->details['customerName'];
        $studentName = $this->details['student'];
        $courseName = $this->details['course'];
        $teacherName = $this->details['teacherName'];
        $classType = $this->details['classType'];
        $makeupDateTime = $this->details['makeupDateTimeStudentTZ'];
        $oldClassDateTime = $this->details['oldClassDateTimeStudentTZ'];

        $sub_heading = $customerName;
        $top_paragraphs = [
            'It is to inform you that your teacher '.$teacherName.' has created a makeup class request.',
            'Here are the details of the request:',
        ];

        $list = [
            'Student Name: '  => $studentName,
            'Course Name: ' => $courseName,
            'Teacher Name: ' => $teacherName,
            'Old Class Time: ' => $oldClassDateTime,
            'New Class Time: ' => $makeupDateTime,
        ];

        $bottom_paragraphs = [
            'If you have any questions or need guidance on choosing the right course, our support team is here to help. You can reach us at <a href="mailto:product.scheduling@alquranclasses.com" style="color: #01563F; text-decoration: none; font-weight: 600;">product.scheduling@alquranclasses.com</a> or call us directly.',
            'We are excited to be a part of your Quranic journey and look forward to supporting you every step of the way.'
        ];

        $contact_email = env('SCHEDULING_EMAIL');

        return $this->subject('Makeup request created For '.$studentName. ' - '. $courseName)
            ->view('emails.email_template', compact('sub_heading', 'top_paragraphs', 'list', 'bottom_paragraphs', 'contact_email'));
    }
}
