<?php

namespace App\Mail\Customer;

use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TrialMissedByTeacher extends Mailable
{
    use SerializesModels;

    private $customer;
    private $student;
    private $teacher;
    private $classDatetimeStdTz;

    public function __construct($customer, $teacher, $student, $classDatetimeStdTz)
    {
        $this->customer = $customer;
        $this->student = $student;
        $this->teacher = $teacher;
        $this->classDatetimeStdTz = $classDatetimeStdTz;
    }

    public function build()
    {
        $sub_heading = $this->customer;
        $top_paragraphs = [
            'We regret to inform you that your scheduled trial class with '.$this->teacher.' on '.$this->classDatetimeStdTz.' could not take place as planned. We sincerely apologize for this unexpected inconvenience.',
            'We value your time and are committed to providing you with the best learning experience. To make up for this, we would like to reschedule your trial class at your earliest convenience.'
        ];

        $bottom_paragraphs = [
            'Please accept our apologies for any inconvenience this may have caused. We are taking steps to ensure that such incidents are avoided in the future.',
            'Thank you for your understanding and patience. If you have any questions or concerns, please feel free to contact us at <a href="mailto:product.scheduling@alquranclasses.com" style="color: #01563F; text-decoration: none; font-weight: 600;">product.scheduling@alquranclasses.com</a>.',
        ];

        $contact_email = env('SCHEDULING_EMAIL');

        return $this->subject('Important Update Regarding Trial Class For '.$this->student)
            ->view('emails.email_template', compact('sub_heading', 'top_paragraphs', 'bottom_paragraphs', 'contact_email'));
    }
}
