<?php
namespace App\Jobs;

use Illuminate\Support\Facades\Mail;
use App\Mail\Customer\FileAssigned;
use App\Mail\Teacher\FolderAssigned;

class SendFolderOrFileAssignedEmail extends Job
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function handle()
    {
        if (isset($this->data['customer_email'])) {
            Mail::to($this->data['customer_email'])->send(new FileAssigned($this->data));
        } else {
            Mail::to($this->data['teacher_email'])->send(new FolderAssigned($this->data));
        }
    }
}