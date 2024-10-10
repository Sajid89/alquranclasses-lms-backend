<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TrialClassResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'teacher_id' => $this->teacher_id,
            'student_id' => $this->student_id,
            'availability_slot_id' => $this->availability_slot_id,
            'class_time' => $this->class_time,
            'status' => $this->status,
            'created_at' => $this->created_at,
        ];
    }
}