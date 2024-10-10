<?php

namespace App\Mail\Customer;

use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PaymentFailed extends Mailable
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
            'We encountered an issue processing the latest payment for your subscription. As a result, your subscription access may be temporarily affected for '.$this->data['student_name'].' 
            with course '.$this->data['course_name'].' until the issue is resolved.',
            'To update your payment information and resolve this issue:'
        ];

        $list = [
            'Log in to your account at [Your Company’s Website].',
            'Navigate to the ‘Billing’ section under ‘My Account’.',
            'Update your payment details with a valid credit card or payment method.',
            'Save the changes to reinitiate your subscription payment.'
        ];

        $bottom_paragraphs = [
            'If you have any questions or need assistance with updating your payment information, please do not hesitate to contact our customer support team at <a href="mailto:support@alquranclasses.com" style="color: #01563F; text-decoration: none; font-weight: 600;">support@alquranclasses.com</a>.',
            'Thank you for your immediate attention to this matter.'
        ];

        $contact_email = env('SCHEDULING_EMAIL');

        return $this->subject('Urgent: Action Required for Your Subscription Payment - '.$this->data['student_name'])
            ->view('emails.email_template', compact('sub_heading', 'top_paragraphs', 'list', 'bottom_paragraphs', 'contact_email'));
    }
}
