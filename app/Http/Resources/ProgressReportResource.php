<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class ProgressReportResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'student_id' => $this->student_id,
            'course_id' => $this->course_id,
            'file' => basename($this->file),
            'file_size' => round(filesize(public_path($this->file)) / (1024 ** 2), 2).' MB',
            'file_url' => URL::asset($this->file),
            'student_name' => $this->student->name,
            'course_name' => $this->course->title,
            'created_at' => Carbon::parse($this->created_at)->format('d F Y'),
        ];
    }
}