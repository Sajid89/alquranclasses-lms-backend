<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PasswordChangedEmail extends Mailable
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
            'We want to confirm that your password has been successfully changed. Your account security is important to us, and this change helps keep your information safe.',
            "If you did not make this change, please contact us immediately to secure your account. It's important to ensure that only you have access to your personal information.",
            'What to do next:'
        ];

        $list = [
            'If you made this change, no further action is required.',
            'If you did not initiate this change, please contact our customer support team immediately at support@alquranclasses.com',
        ];

        $bottom_paragraphs = [
            'We are committed to ensuring the security of your account and personal information. If you have any questions or concerns, please don’t hesitate to reach out to us.',
            'Thank you for being a valued member of our community.'
        ];

        $contact_email = env('SALES_EMAIL');

        return $this->subject('Your Password Has Been Successfully Changed')
            ->view('emails.email_template', compact('sub_heading', 'top_paragraphs', 'list', 'bottom_paragraphs', 'contact_email'));
    }
}
