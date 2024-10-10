<?php

namespace App\Http\Resources;

use App\Traits\DecryptionTrait;
use Illuminate\Http\Resources\Json\JsonResource;

class TeacherCoordinatorResource extends JsonResource
{
    use DecryptionTrait;
    private $teachers;
    public function __construct($teachers)
    {
        parent::__construct($teachers);
        $this->teachers = $teachers;
    }

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
            'name' => $this->decryptValue($this->name),
            'email' => $this->decryptValue($this->email),
            'phone' => $this->decryptValue($this->phone),
            'profile_photo_path' => $this->profile_photo_path,
            'online_status' => 'offline',
            'created_at' => $this->created_at,
            'timezone' => $this->timezone,
        ];
    }
}