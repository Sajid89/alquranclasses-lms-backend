<?php
namespace App\Http\Resources;

use App\Helpers\GeneralHelper;
use App\Traits\DecryptionTrait;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentCourseAcitivityResource extends JsonResource
{
    use DecryptionTrait;

    protected $timezone;

    public function __construct($resource)
    {
        parent::__construct($resource);
        $this->timezone = $resource->additional['timezone'] ?? config('app.timezone');
    }

    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'profile_pic' => $this->profile_photo_path ? env('APP_URL').'/'.$this->profile_photo_path : null,
            'teacher_name' => $this->decryptValue($this->studentCourse->teacher->name),
            'course_name' => $this->studentCourse->course->name,
            'activity_type' => $this->activity_type,
            'description' => $this->description,
            'file_size' => $this->file_size,
            'file_name' => $this->file_name,
            'created_at' => Carbon::parse(GeneralHelper::convertTimeToUserTimezone($this->created_at, $this->timezone))->format('M d, Y H:i:s'),
        ];
    }

    public function with($request)
    {
        return [
            'timezone' => $this->timezone,
        ];
    }
}