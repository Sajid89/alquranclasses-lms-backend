<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PasswordResetMail extends Mailable
{
    use Queueable, SerializesModels;

    private $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function build()
    {
        $sub_heading = $this->data['customer_name'];
        $top_paragraphs = [
            'We received a request to reset the password for your account associated with this email address. You can reset your password by clicking the link below:',
            'Steps to reset your password:',
        ];

        $list = [
            'Click the button below to reset your password.',
            'Enter your new password in the form provided.',
            'Confirm your new password and submit the form.',
        ];

        $button = [
            'text' => 'Reset My Password',
            'url' => $this->data['reset_link'],
        ];

        $bottom_paragraphs = [
            'This link will remain active for 24 hours. If you did not request to reset your password, please ignore this email or contact our support team immediately to ensure your account remains secure.',
            'If you encounter any issues during the reset process or if you did not initiate this request, please contact us immediately at <a href="mailto:support@alquranclasses.com" style="color: #01563F; text-decoration: none; font-weight: 600;">support@alquranclasses.com</a>.',
            'Thank you for choosing AlQuranClasses. We look forward to supporting you on your journey of Quranic learning and understanding.'
        ];

        $contact_email = env('SCHEDULING_EMAIL');

        return $this->subject('Password Reset Instructions for Your Account')
            ->view('emails.email_template', compact('sub_heading', 'top_paragraphs', 'list', 'button', 'bottom_paragraphs', 'contact_email'));
    }
}
