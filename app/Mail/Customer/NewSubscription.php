<?php

namespace App\Mail\Customer;

use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewSubscription extends Mailable
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
            'Congratulations! Your subscription to '.$this->data['stripe_plan'].' at AlQuranClasses for '.$this->data['student_name'].', course '.$this->data['course'].' is now active. 
            We are thrilled to welcome you to our community where learning and growth go hand in hand.',
            'What’s Next?'
        ];

        $list = [
            'Explore:' => 'Dive into our extensive course catalog and start planning your learning journey.',
            'Support:' => 'Our team is here to assist you every step of the way.',
        ];

        $bottom_paragraphs = [
            'You can access your account dashboard <a href="https://test.v2.alquranclasses.com" style="color: #01563F; text-decoration: none; font-weight: 600;">here</a> to manage your subscription and track your progress.',
            'Should you have any questions or need assistance, feel free to reach out to us at <a href="mailto:support@alquranclasses.com" style="color: #01563F; text-decoration: none; font-weight: 600;">contact us</a>.',
            'We’re here to ensure you have the best experience possible. Thank you for choosing AlQuranClasses. We look forward to being a part of your learning journey.'
        ];

        $contact_email = env('SALES_EMAIL');

        return $this->subject($this->data['student_name'].'’s Subscription is Activated!')
            ->view('emails.email_template', compact('sub_heading', 'top_paragraphs', 'list', 'bottom_paragraphs', 'contact_email'));
    }
}
