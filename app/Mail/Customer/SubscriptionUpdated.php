<?php

namespace App\Mail\Customer;

use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SubscriptionUpdated extends Mailable
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
        $subscriptionPlan = $this->details['subscriptionPlan'];
        $courseName = $this->details['course'];

        $sub_heading = $this->details['user']->name;
        $top_paragraphs = [
            'We are excited to inform you that your subscription has been updated successfully.',
            'Here are the details of your updated subscription:',
        ];

        $list = [
            'Customer Name: ' => $customerName,
            'Student Name: '  => $studentName,
            'Subscription Plan: ' => $subscriptionPlan,
            'Course Name: ' => $courseName,
        ];

        $bottom_paragraphs = [
            'If you have any questions or need guidance on choosing the right course, our support team is here to help. You can reach us at <a href="mailto:product.scheduling@alquranclasses.com" style="color: #01563F; text-decoration: none; font-weight: 600;">product.scheduling@alquranclasses.com</a> or call us directly.',
            'We are excited to be a part of your Quranic journey and look forward to supporting you every step of the way.'
        ];

        $contact_email = env('SCHEDULING_EMAIL');

        return $this->subject('Subscription Updated Successfully For '.$studentName. ' - '. $courseName)
            ->view('emails.email_template', compact('sub_heading', 'top_paragraphs', 'bottom_paragraphs', 'contact_email'));
    }
}
