<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CourseResource extends JsonResource
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
            'course_title' => $this->title,
            'description' => $this->description,
            'is_custom' => $this->is_custom==1 ? 'Yes' : 'No',
            'status' => $this->is_locked==1 ? 'Publish': 'Unpublish',
        ];
    }
}