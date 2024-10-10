<?php

namespace App\Mail\Customer;

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
        $sub_heading = $this->classData['customer_name'];
        $top_paragraphs = [
            'We are thrilled that you completed your trial class with Teacher '.$this->classData['teacher_name'].' and Student '.$this->classData['student_name'].'! We hope it was an enriching and enjoyable learning experience for you.',
            'Take the Next Step in Your Learning Journey To continue exploring and expanding your knowledge, we encourage you to subscribe to one of our plans. By subscribing, you gain unlimited access to our courses, expert tutors, and flexible scheduling.',
            '*Discover Subscription Plans*',
            'Your journey towards mastery is just beginning, and we are excited to be a part of it.'
        ];

        $bottom_paragraphs = [
            'If you have any questions or need guidance on choosing the right course, our support team is here to help. You can reach us at <a href="mailto:product.scheduling@alquranclasses.com" style="color: #01563F; text-decoration: none; font-weight: 600;">product.scheduling@alquranclasses.com</a> or call us directly.',
            'We are excited to be a part of your Quranic journey and look forward to supporting you every step of the way.'
        ];

        $contact_email = env('SCHEDULING_EMAIL');

        return $this->subject('Congratulations '.$this->classData['student_name'].' on Completing Your Trial Class!'. $this->classData['student_name'])
            ->view('emails.email_template', compact('sub_heading', 'top_paragraphs', 'bottom_paragraphs', 'contact_email'));
    }
}
