<?php

namespace App\Mail\Customer;

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
        $sub_heading = $this->user->name;
        $top_paragraphs = [
            'Your student, ' . $this->studentName . ', has been successfully added to your account. You can now view and manage their course, availability, and class schedule from your dashboard.',
        ];

        // $list = [
        //     'Student Name' => $this->trialClass->student->name,
        //     'Teacher Name' => $this->trialClass->teacher->name,
        //     'Class Time'   => $this->trialClass->class_time,
        // ];

        $bottom_paragraphs = [
            'If you have any questions or need guidance on choosing the right course, our support team is here to help. You can reach us at <a href="mailto:product.scheduling@alquranclasses.com" style="color: #01563F; text-decoration: none; font-weight: 600;">product.scheduling@alquranclasses.com</a> or call us directly.',
            'We are excited to be a part of your Quranic journey and look forward to supporting you every step of the way.'
        ];

        $contact_email = env('SCHEDULING_EMAIL');

        return $this->subject('New Student Added to Your Account - ' . $this->studentName)
            ->view('emails.email_template', compact('sub_heading', 'top_paragraphs', 'bottom_paragraphs', 'contact_email'));
    }
}
