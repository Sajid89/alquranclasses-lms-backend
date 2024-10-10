<?php

namespace App\Mail\Teacher;

use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class FolderAssigned extends Mailable
{
    use SerializesModels;

    private $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function build()
    {
        $sub_heading = $this->data['teacher_name'];
        $top_paragraphs = [
            'We are pleased to inform you that a new folder, '.$this->data['folder_name'].', has been assigned to you. This folder contains important materials and resources that you will need for your upcoming classes.',
        ];

        $bottom_paragraphs = [
            'Please review the contents of the folder at your earliest convenience to ensure you are fully prepared for your sessions. If you encounter any issues accessing the folder or have any questions about the materials, feel free to reach out toyour teacher Cordinator.',
            'We appreciate your dedication and look forward to your continued success in teaching.'
        ];

        $contact_email = env('SALES_EMAIL');

        return $this->subject('New Folder Assigned: '.$this->data['folder_name'])
            ->view('emails.email_template', compact('sub_heading', 'top_paragraphs', 'bottom_paragraphs', 'contact_email'));
    }
}
