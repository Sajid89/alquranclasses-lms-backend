<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class VerificationEmail extends Mailable
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
            'Welcome to AlQuranClasses! We are excited to have you join our community, which is dedicated to learning and spiritual growth. To get started, we just need to verify your email address.',
            'Please verify your email by clicking on the link below:'
        ];

        $button = [
            'text' => 'Verify My Email',
            'url' => $this->data['verification_link'],
        ];

        $bottom_paragraphs = [
            'This link will expire in 24 hours. Verifying your email ensures that you receive important updates and access to our full range of classes.',
            'If you have any questions or need assistance, please don’t hesitate to <a href="mailto:support@alquranclasses.com" style="color: #01563F; text-decoration: none; font-weight: 600;">contact us</a>.',
            'Thank you for choosing AlQuranClasses. We look forward to supporting you on your journey of Quranic learning and understanding.'
        ];
        
        $contact_email = env('SCHEDULING_EMAIL');

        return $this->subject('Verify Your Email for AlQuranClasses')
            ->view('emails.email_template', compact('sub_heading', 'top_paragraphs', 'button', 'bottom_paragraphs', 'contact_email'));
    }
}
