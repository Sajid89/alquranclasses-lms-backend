<?php

namespace App\Traits;

use App\Classes\AlQuranConfig;
use App\Models\Availability;
use App\Models\AvailabilitySlot;
use App\Models\Slot;
use App\Models\User;
use App\Repository\AvailabilityRepositoryInterface;
use App\Repository\AvailabilitySlotRepositoryInterface;
use App\Repository\Eloquent\AvailabilityRepository;
use App\Repository\Eloquent\AvailabilitySlotRepository;
use Carbon\Carbon;
use Illuminate\Support\Collection;

trait AvailabilityTrait
{

    /**
     * Place that common code that would be
     * used in multiple methods
     * @return AvailabilitySlotRepository
     */
    protected function getAvailabilitySlotRepository() {
        return new AvailabilitySlotRepository(new AvailabilitySlot);
    }

    /**
     * Retrieve unique days for a given student's availabilities.
     *
     * @return Collection
     */
    public function getUniqueDaysForStudent()
    {
        if (!$this->relationLoaded('trialClass')) {
            $this->load('trialRequest.availability.availabilitySlots.day');
        }

        $uniqueDays = new Collection();

        if ($this->trialRequest && $this->trialRequest->availability) {
            foreach ($this->trialRequest->availability->availabilitySlots as $availabilitySlot) {
                $uniqueDays->push($availabilitySlot->day);
            }
        }

        return $uniqueDays->unique('id')->values();
    }

    /**
     * this method is responsible to store new teacher available time slots in db.
     * the selected days and selected timeslots are received in controller from gui
     * in a json format like below
     *
     * @param $availabilityId
     * @param $teacherAvailabilities
     *
     * @return array
     */
    public function makeTeacherAvailability2Darray($availabilityId, $teacherAvailabilities)
    {
        $teacherSlotsArray = [];
        foreach ($teacherAvailabilities as $daySlots) {
            $dayId = $daySlots['day_id'];
            $timeSlots = $daySlots['time_slots'];
            foreach ($timeSlots as $timeSlotId) {
                $teacherSlotsArray[] = array(
                    'availability_id' => $availabilityId,
                    'day_id' => $dayId,
                    'slot_id' => $timeSlotId
                );
            }
        }
        return $teacherSlotsArray;
    }

    /**
     * the below method will return user selected time slots with day ids
     * @return array
     */
    public function getUserSlots()
    {
        $slotsData = [];
        if ($this->availability) {
            $userSlots = $this->availability->availabilitySlots;
            $slotsData = $userSlots->map(function ($slot) {
                return ['day_id' => $slot->day_id, 'slot_id' => $slot->slot_id];
            });
            $slotsData = $slotsData->toArray();
        }

        return $slotsData;

    }

    /**
     * Filter time slots booked/free
     * @param $allSlots
     * @param $selectedSlots
     * @param array $teacherBookedSlots
     * @return array
     */
    public function filterSlots($allSlots, $selectedSlots, array $teacherBookedSlots = [])
    {
        $availabilitySlots = [];

        foreach (AlQuranConfig::Days as $key => $day) {
            foreach ($allSlots as $als) {
                $isSelected = $this->isDayTimeSlotSelected($selectedSlots, $key, $als['id']);
                $is_booked = 0;
                if (count($teacherBookedSlots) > 0)
                {
                    $is_booked = $this->isDayTimeSlotBooked($teacherBookedSlots, $key, $als['id']) ? 1 : 0;
                }

                $availabilitySlots[$key][] = array(
                    'day_id' => $key,
                    'day_name' => $day,
                    'slot_id' => $als['id'],
                    'slot_label' => $als['from'] . ' - ' . $als['to'],
                    'is_selected' => $isSelected ? 1 : 0,
                    'is_booked' => $is_booked
                );
            }
        }
        return $availabilitySlots;
    }

    /**
     * filter selected time slots
     * for selected/not_selected
     * @param $selectedSlots
     * @param $dayId
     * @param $currentSlotId
     * @return bool
     */
    private function isDayTimeSlotSelected($selectedSlots, $dayId, $currentSlotId)
    {
        foreach ($selectedSlots as $sl) {
            if ($sl['day_id'] == $dayId && $sl['slot_id'] == $currentSlotId) {
                return true;
                break;
            }
        }

        return false;
    }

    /**
     * filter teacher availability Slots
     * for Booked/Free slots
     * @param $teacherBookedSlots
     * @param $dayId
     * @param $currentSlotId
     * @return bool
     */
    public function isDayTimeSlotBooked($teacherBookedSlots, $dayId, $currentSlotId)
    {
        $key = $dayId . '-' . $currentSlotId;

        if (in_array($key, $teacherBookedSlots)) {
            return true;
        }

        return false;
    }

    /**
     * Get Teacher Slots(booked, free) and filter those slots
     * as per student selected shift for Trial Request
     * @return array
     */
    public function GetTeacherSlots()
    {
        $availabilitySlotRepository = $this->getAvailabilitySlotRepository();
        $this->teacherAvailability = $availabilitySlotRepository->getTeacherAvailabilitySlots($this->day_id, $this->teacher_id);

        $this->studentShiftSlots = $availabilitySlotRepository->getShiftSlots($this->student->shift_id);
        $this->studentSlots = $availabilitySlotRepository->getSlotsByShiftIds($this->studentShiftSlots->toArray());

        $studentSelectedSlots = [];
        foreach ($this->studentSlots as $time)
        {
            $timeString = $this->ConvertFromTeacherToStudentTimezone($this->teacherAvailability->timezone, $time->slot,
                $this->student->timezone, 'h:i A');

            // Convert to 24-hour format with seconds
            $convertedTime = date("H:i:s", strtotime($timeString));
            $studentSelectedSlots[] = [
                'slot_id' => $availabilitySlotRepository->getSlotByTime($convertedTime)
            ];
        }

        $teacherAvailabilitySlots = $this->teacherSlots($this->teacherAvailability, $availabilitySlotRepository, $this->student);

        return $availabilitySlotRepository->filterCollections($studentSelectedSlots, $teacherAvailabilitySlots);
    }

    /**
     * Get Teacher All slots For Student
     * it will be used to buy a new subscription
     * @param $teacher_id
     * @param $student
     * @param bool $isTeacher
     * @return array
     */
    public function getTeacherAllSlots($teacher_id, $student, $isTeacher = false)
    {
        $availabilitySlotRepository = $this->getAvailabilitySlotRepository();
        $teacherAvailability = $availabilitySlotRepository->getTeacherAllAvailabilitySlots($teacher_id);

        return $this->teacherSlots($teacherAvailability, $availabilitySlotRepository, $student, $isTeacher);
    }

    /**
     * Get Teacher All slots and
     * student slots selected during subscription buy
     * it will be used for update subscription
     * @param $teacher_id
     * @param $student
     * @return array
     */
    public function getTeacherAlongStudentSubscribedSlots($teacher_id, $student)
    {
        $availabilitySlotRepository = $this->getAvailabilitySlotRepository();
        $teacherAvailability = $availabilitySlotRepository->getTeacherAllAvailabilitySlots($teacher_id);

        $studentSlotsSet = $availabilitySlotRepository->mapStudentAvailabilitySlots($student->id);

        return $this->teacherSlots(
            $teacherAvailability, $availabilitySlotRepository,
            $student, false, true,
            $studentSlotsSet
        );
    }

    /**
     * Get teacher slots
     * @param $teacherAvailability
     * @param $availabilitySlotRepository
     * @param $student
     * @param bool $isTeacher
     * @param bool $forUpdateSubscription
     * @param array $studentSlotsSet
     * @return array
     */
    public function teacherSlots(
        $teacherAvailability, $availabilitySlotRepository,
        $student, $isTeacher = false, $forUpdateSubscription = false,
        $studentSlotsSet = []
    )
    {
        $teacherAvailabilitySlots = [];

        if ($teacherAvailability->availability)
        {
            foreach ($teacherAvailability->availability->availabilitySlots as $key => $teacherSlot)
            {
                $availabilitySlotRepository->EagerLoadRelationships($teacherSlot);
                $from = Carbon::parse($teacherSlot->slot->slot)->format('h:i A');
                $convertedTime = date("H:i:s", strtotime($teacherSlot->slot->slot));

                if (!$isTeacher)
                {
                    $from = $this->ConvertFromTeacherToStudentTimezone($student->timezone, $teacherSlot->slot->slot,
                        $teacherAvailability->timezone, 'h:i A');

                    $convertedTime = $this->ConvertFromTeacherToStudentTimezone($student->timezone, $teacherSlot->slot->slot,
                        $teacherAvailability->timezone, 'H:i:s');
                }

                $to = Carbon::parse($from)->addMinutes(30)->format('h:i A');


                $teacherAvailabilitySlots[] = [
                    'availability_slot_id' => $teacherSlot->id,
                    'day_id' => $teacherSlot->day_id,
                    'slot_id' => $teacherSlot->slot_id,
                    'slot_label' => $from . ' - ' . $to,
                    'slot_time' => $convertedTime,
                    'slot_starts_from' => $from,
                    'is_free' => !(
                        $teacherSlot->routineClass !== null ||
                        $teacherSlot->trialClass !== null ||
                        $teacherSlot->reschedule_request !== null
                    )
                ];

                if ($forUpdateSubscription)
                {
                    $checkKey = $teacherSlot->day_id . '_' . $teacherSlot->slot_id;
                    $teacherAvailabilitySlots[$key]['student_booked'] = isset($studentSlotsSet[$checkKey]);
                }
            }
        }

        return $teacherAvailabilitySlots;
    }

    /**
     * Get teacher/student timezone and slot time
     * to convert it into UTC and then in student timezone
     * @param $timeZoneTo
     * @param $time
     * @param string $timeZoneFrom
     * @param $format
     * @return string
     */
    public function ConvertFromTeacherToStudentTimezone(
        $timeZoneTo, $time,
        $timeZoneFrom = 'Africa/Cairo',
        $format
    )
    {
        return Carbon::parse($time, $timeZoneFrom)
            ->timezone('UTC')
            ->timezone($timeZoneTo)
            ->format($format);
    }

    /**
     * Convert given date time to given timezone
     * @param $timeZoneTo
     * @param $datetime
     * @return string
     */
    public function ConvertDateToTimezone($timeZoneTo, $datetime)
    {
        return Carbon::parse($datetime, 'UTC')
            ->timezone($timeZoneTo)
            ->format('m/d/Y');
    }

    /**
     * Convert datetime to given timezone
     * @param $dateTime
     * @param $fromTimeZone
     * @return Carbon
     */
    public function ConvertDateTimeToUTC($dateTime, $fromTimeZone)
    {
        return Carbon::parse($dateTime, $fromTimeZone)->timezone('UTC');
    }

    /**
     * Convert datetime to given timezone
     *
     * @param $datetime
     * @param $timezone
     * @return Carbon
     */
    public function ConvertTimeToGivenTimeZone($datetime, $timezone)
    {
        // Parse the time assuming it is in UTC
        $timeInUTC = Carbon::parse($datetime, 'UTC');

        // Convert the time to the desired timezone
        return $timeInUTC->timezone($timezone);
    }
}
