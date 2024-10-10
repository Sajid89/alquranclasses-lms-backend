<?php
namespace App\Http\Resources;

use App\Classes\Enums\CommonEnum;
use App\Helpers\GeneralHelper;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class MakeupRequestResource extends JsonResource
{
    protected $timezone;

    public function __construct($resource, $timezone)
    {
        parent::__construct($resource);
        $this->timezone = $timezone;
    }

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $previousDateTimeUserTimezone = GeneralHelper::convertTimeToUserTimezone($this->class_old_date_time, $this->timezone);
        $makeupDateTimeUserTimezone = GeneralHelper::convertTimeToUserTimezone($this->makeup_date_time, $this->timezone);

        return [
            'class_id' => $this->class_id,
            'student_id' => $this->studentCourse->student_id,
            'teacher_id' => $this->studentCourse->teacher_id,
            'teacher_name' => $this->studentCourse->teacher->name,
            'student' => $this->studentCourse->student->name,
            'course' => $this->studentCourse->course->title,
            'previousDate' => Carbon::parse($previousDateTimeUserTimezone)->format('F j, Y'),
            'previousTime' => Carbon::parse($previousDateTimeUserTimezone)->format('h:i A'),
            'newDate' => Carbon::parse($makeupDateTimeUserTimezone)->format('F j, Y'),
            'newTime' => Carbon::parse($makeupDateTimeUserTimezone)->format('h:i A'),
            'status' => ucfirst($this->status === CommonEnum::MAKEUP_REQUEST_REJECTED ? 'rejected' : $this->status),
            'type' => $this->class_type === 'App\Model\TrialClass' ? 
                'trial' : 'regular',
            'actionButton' => $this->status === 'pending' ? 'Withdraw Request' : '--'
        ];
    }
}