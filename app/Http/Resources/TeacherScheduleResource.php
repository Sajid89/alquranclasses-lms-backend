<?php

namespace App\Http\Resources;

use App\Classes\Enums\DaysEnum;
use App\Models\Slot;
use App\Models\StudentCourse;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class TeacherScheduleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $teacherAvailabilitySlots = [];
        $teacherAvailability = $this->availability->availabilitySlots;
        $studentTimezone = $this->additional['studentTimezone'] ?? null;
        $shiftSlotIds = $this->additional['slotIds']->toArray();
        $studentId = $this->additional['studentId'] ?? null;
        $teacherId = $this->additional['teacherId'] ?? null;
        $courseId = $this->additional['courseId'] ?? null;
        $dayId = $this->additional['dayId'] ?? null;

        if ($studentId !== null) {
            $studentCourse = StudentCourse::where('student_id', $studentId)->first();
        }

        // if the customer wants to update subscription plan
        // then we need to display the current teacher of the student
        $studentSelectedSlots = [];
        if ($teacherId !== null) {
            $studentCourse = StudentCourse::where(['student_id' => $studentId, 'course_id' => $courseId])->first();
            $studentSelectedSlots = $studentCourse->routineClasses->pluck('slot_id')->toArray();
        }

        $dayNames = [
            DaysEnum::MON => 'Mon',
            DaysEnum::TUE => 'Tue',
            DaysEnum::WED => 'Wed',
            DaysEnum::THU => 'Thu',
            DaysEnum::FRI => 'Fri',
            DaysEnum::SAT => 'Sat',
            DaysEnum::SUN => 'Sun',
        ];

        if($dayId !== null)
        {
            $teacherAvailability = $this->additional['availabilitySlots'];
        }

        foreach ($teacherAvailability as $teacherSlot) {
            if (in_array($teacherSlot->slot_id, $shiftSlotIds)) {
                $time = Slot::find($teacherSlot->slot_id)->slot;

                if ($dayId !== null)
                {
                    $convertedTimeToDisplay = Carbon::createFromFormat('H:i:s', $time)->format('h:i A');
                } else {
                    /**
                     * Convert the time to the student's timezone
                     */
                    $convertedTimeToDisplay = Carbon::createFromFormat('H:i:s', $time, 'Africa/Cairo')->setTimezone('UTC');
                    $convertedTimeToDisplay->setTimezone($studentTimezone)->format('h:i A');
                    $convertedTimeToDisplay = $convertedTimeToDisplay->format('h:i A');
                }

                // Reload the trialClass relationship because Laravel Eloquent caches the relationship
                // so we need to reload it to get the latest data
                $teacherSlot->load('trialClass');  
                $isFree = !(
                    $teacherSlot->routineClass ||
                    $teacherSlot->makeup_request && $teacherSlot->makeup_request->makeup_date_time > Carbon::now() ||
                    $teacherSlot->trialClass && $teacherSlot->trialClass->class_time > Carbon::now()
                );

                $isSelected = in_array($teacherSlot->id, $studentSelectedSlots);

                $teacherAvailabilitySlots[] = [
                    'availability_slot_id' => $teacherSlot->id,
                    'day_id' => $teacherSlot->day_id,
                    'day_name' => $dayNames[$teacherSlot->day_id],
                    'slot_id' => $teacherSlot->slot_id,
                    'slot_time' => $time,
                    'slot_label' => $convertedTimeToDisplay,
                    'is_free' => $isFree,
                    'is_selected' => $isSelected,
                ];
            }
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'gender' => $this->gender,
            'profile_photo_path' => $this->profile_photo_path,
            'schedule' => $teacherAvailabilitySlots,
            'current_teacher' => $studentId !== null && $studentCourse && $studentCourse->teacher_id === $this->id ? true : false,
        ];
    }
}