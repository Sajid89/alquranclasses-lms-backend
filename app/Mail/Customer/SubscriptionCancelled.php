<?php

namespace App\Mail\Customer;

use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SubscriptionCancelled extends Mailable
{
    use SerializesModels;

    protected $details;

    public function __construct($details)
    {
        $this->details = $details;
    }

    public function build()
    {
        $sub_heading = $this->details['user']->name;
        $top_paragraphs = [
            'We are sorry to see you go. Your subscription for ' . $this->details['course'] . ' has been cancelled.'
        ];

        $bottom_paragraphs = [
            'If you have any questions or need guidance on choosing the right course, our support team is here to help. You can reach us at <a href="mailto:product.scheduling@alquranclasses.com" style="color: #01563F; text-decoration: none; font-weight: 600;">product.scheduling@alquranclasses.com</a> or call us directly.',
            "We are excited to be a part of your Quranic journey and look forward to supporting you every step of the way."
        ];

        $contact_email = env('SCHEDULING_EMAIL');

        return $this->subject('Subscription cancelled to Your Account - ' . $this->details['student']->name)
            ->view('emails.email_template', compact('sub_heading', 'top_paragraphs', 'bottom_paragraphs', 'contact_email'));
    }
}
