<?php

namespace App\Mail\Customer;

use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class FileAssigned extends Mailable
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
            'Your teacher has just assigned new files for your course,  '.$this->data['course_name'].'. These files contain important materials that will aid you in your studies and help you better prepare for your upcoming classes.',
        ];

        $bottom_paragraphs = [
            'Please make sure to review the files at your earliest convenience. If you have any questions about the content or need further assistance, feel free to reach out to your teacher.',
            'We wish you the best in your studies and are here to support you every step of the way.'
        ];

        $contact_email = env('SALES_EMAIL');

        return $this->subject('New Files Assigned to You for '.$this->data['course_name'])
            ->view('emails.email_template', compact('sub_heading', 'top_paragraphs', 'bottom_paragraphs', 'contact_email'));
    }
}
