<?php

namespace App\Mail\Customer;

use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class Welcome extends Mailable
{
    use SerializesModels;

    public $data;

    public function __construct($data)
    {
        $this->user = $data;
    }

    public function build()
    {
        $sub_heading = $this->data['customer_name'];
        $top_paragraphs = [
            'Welcome to AlQuranClasses. We are thrilled to have you in our community, where learning and spiritual growth go hand in hand.',
            'Your journey to discovering the Quran begins here! AlQuranClasses offers a range of courses tailored to meet your needs, whether you’re beginning to explore the Quran or looking to deepen your understanding. With our experienced tutors and flexible scheduling, you’re in control of your learning path.'
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

        return $this->subject('Welcome to AlQuranClasses – Let’s Get Started!')
            ->view('emails.email_template', compact('sub_heading', 'top_paragraphs', 'bottom_paragraphs', 'contact_email'));
    }
}
