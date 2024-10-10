<?php

namespace App\Mail\SchedulingTeam;

use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class Welcome extends Mailable
{
    use SerializesModels;

    public $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function build()
    {
        $sub_heading = 'Scheduling Team';
        $top_paragraphs = [
            'This is an automated message to inform you that we have a new customer registration in the system.',
            'Here are the details of the newly registered customer:'
        ];

        $list = [
            'Name' => $this->data['customer_name'],
            'Email' => $this->data['customer_email'],
            'Phone' => $this->data['customer_phone'],
            'Registered At' => $this->data['customer_register_at'],
            'Country' => $this->data['customer_country'],
            'Timezone' => $this->data['customer_timezone'],
        ];

        $bottom_paragraphs = [
            'Lets make sure to provide '.$this->data['customer_name']. ' with the best experience and support as they begin their journey with AlQuranClasses.'
        ];

        $contact_email = env('SCHEDULING_EMAIL');

        return $this->subject('New Customer Registration Alert: '.$this->data['customer_name'].' has signed up!')
            ->view('emails.email_template', compact('sub_heading', 'top_paragraphs', 'list', 'bottom_paragraphs', 'contact_email'));
    }
}
