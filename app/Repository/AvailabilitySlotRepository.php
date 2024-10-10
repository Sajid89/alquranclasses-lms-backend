<?php

namespace App\Repository;

use App\Models\AvailabilitySlot;
use App\Models\ShiftSlot;
use App\Models\Slot;
use App\Models\Student;
use App\Repository\Interfaces\AvailabilitySlotRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use App\Models\User;

class AvailabilitySlotRepository implements AvailabilitySlotRepositoryInterface
{
    protected $model;

    /**
     * Availability Repository constructor.
     * @param AvailabilitySlot $model
     */
    public function __construct(AvailabilitySlot $model)
    {
        $this->model = $model;
    }

    /**
     * add student availability slots
     * @param $availabilityId
     * @param array $days
     * @param $shiftId
     * @return mixed
     */
    public function addStudentAvailabilitySlots($availabilityId, array $days, $shiftId) {

        $slots = $this->getShiftSlots($shiftId);
        $toInsert = [];

        foreach ($days as $day) {
            foreach ($slots as $slot) {
                $toInsert[] = ['availability_id' => $availabilityId, 'day_id' => $day, 'slot_id' => $slot];
            }
        }

        return AvailabilitySlot::insert($toInsert);
    }

    /**
     * get slots from shifts table as per shift
     * @param $shiftId
     * @return
     */
    public function getShiftSlots($shiftId)
    {
        return ShiftSlot::getShiftSlots($shiftId)->pluck('slot_id');
    }

    /**
     * Get slots by shift ids
     * @param array $slotIds
     * @return mixed
     */
    public function getSlotsByShiftIds(array $slotIds)
    {
        return Slot::getSlotsByShift($slotIds)->get();
    }

    /**
     * Get Slots by time
     * @param $time
     * @return mixed
     */
    public function getSlotByTime($time)
    {
        return Slot::getSlotByTime($time)->first()->id;
    }

    /**
     * all time slots from db
     * 10:00 AM - 10:30 AM
     * @return array
     */
    public function getAllSlots() {

        $slotsObjects = Slot::orderBy('id')->get();
        $slots = array();

        foreach($slotsObjects as $slot) {
            $from = Carbon::parse($slot->slot)->format('h:i A');
            $to = Carbon::parse($slot->slot)->addMinutes(30)->format('h:i A');
            $slots[] = array(
                'id' => $slot->id,
                'from' => $from,
                'to' => $to
            );

        }

        return $slots;
    }

    /**
     * to add/update teacher availability slots
     * check if slots already exist in table
     * then update them otherwise create new records
     * @param $teacherAvailabilityId
     * @param array $teacherAvailabilitySlots
     * @return mixed
     */
    public function addTeacherAvailabilitySlots($teacherAvailabilityId, array $teacherAvailabilitySlots) {

        $availabilityId = $teacherAvailabilityId;
        $existingAvailabilitySlots = AvailabilitySlot::where('availability_id', $availabilityId)->get()->toArray();

        $slotsToDelete = [];
        foreach($existingAvailabilitySlots as $exAvs) {
            $isFound = 0;
            foreach($teacherAvailabilitySlots as $tav) {
                if($exAvs['day_id'] == $tav['day_id'] && $exAvs['slot_id'] == $tav['slot_id']) {
                    $isFound = 1;
                }
            }
            if($isFound == 0) {
                $slotsToDelete[] = $exAvs['id'];
            }
        }

        foreach ($teacherAvailabilitySlots as $slot)
        {
            $dayId = $slot['day_id'];
            $slotId = $slot['slot_id'];

            $existingSlot = AvailabilitySlot::where('availability_id', $availabilityId)
                ->where('day_id', $dayId)
                ->where('slot_id', $slotId)
                ->first();

            if ($existingSlot) {
                $existingSlot->update($slot);
            } else {
                AvailabilitySlot::create($slot);
            }
        }

        // Delete slots that are no longer needed
        AvailabilitySlot::where('availability_id', $availabilityId)
            ->whereIn('id', $slotsToDelete)
            ->delete();
    }

    /**
     * Get teachers availability slots
     * for a specific date/day
     * @param $day_id
     * @param $teacher_id
     * @return Builder
     */
    public function getTeacherAvailabilitySlots($day_id, $teacher_id)
    {
        return User::with([
            'availability.availabilitySlots' => function ($query) use ($day_id) {
                $query->where('day_id', $day_id);
            }
        ])->findOrFail($teacher_id);
    }

    /**
     * Get teacher all availability slots
     * @param $teacher_id
     * @return Builder
     */
    public function getTeacherAllAvailabilitySlots($teacher_id)
    {

        return User::with([
            'availability.availabilitySlots'
        ])->findOrFail($teacher_id);
    }

    /**
     * Get student all availability slots
     * on which student has routine classes
     * @param $student_id
     * @return Builder
     */
    public function getStudentAllAvailabilitySlots($student_id)
    {
        return Student::with([
            'routine_classes.availabilitySlot'
        ])->findOrFail($student_id);
    }

    /**
     * Intersection of two arrays(StudentSelectedSlots and TeacherAvailableSlots)
     * @param $array1
     * @param $array2
     * @return array
     */
    public function filterCollections($array1, $array2)
    {
        $collection1 = collect($array1);
        $collection2 = collect($array2);
        $teacherAvailabilitySlots = [];

        $collection2->each(function ($item) use ($collection1, &$teacherAvailabilitySlots) {
            if ($collection1->contains('slot_id', $item['slot_id'])) {
                $teacherAvailabilitySlots[] = [
                    'availability_slot_id' => $item['availability_slot_id'],
                    'day_id' => $item['day_id'],
                    'slot_id' => $item['slot_id'],
                    'slot_label' => $item['slot_label'],
                    'slot_time' => $item['slot_time'],
                    'slot_starts_from' => $item['slot_starts_from'],
                    'is_free' => $item['is_free']
                ];
            }
        });

        return $teacherAvailabilitySlots;
    }

    /**
     * Eagerload relationships for AvailabilitySlot Model
     * @param $obj
     * @return mixed
     */
    public function EagerLoadRelationships($obj)
    {
        return $obj->load(['slot' => function ($query) {
            $query->select('id', 'slot');
        }, 'routineClass' => function ($query) {
            $query->select('id', 'slot_id');
        }, 'trialClass' => function ($query) {
            $query->select('id', 'availability_slot_id');
        }, 'reschedule_request' => function ($query) {
            $query->select('id', 'reschedule_slot_id');
        }]);
    }

    /**
     * Get teacher availability slots and filter
     * them with only those who are booked
     * with(routineClasses, trialClasses, reschedule_request)
     * @param $teacher_id
     * @return mixed
     */
    public function mapTeacherAvailabilitySlots($teacher_id)
    {
        $teacher = $this->getTeacherAllAvailabilitySlots($teacher_id);

        if(!$teacher->availability) {
            return array();
        }

        return $teacher->availability->availabilitySlots
            ->filter(function ($availabilitySlot) {
                $this->EagerLoadRelationships($availabilitySlot);
                return $availabilitySlot->routineClass !== null || $availabilitySlot->trialClass !== null || $availabilitySlot->reschedule_request !== null;
            })
            ->map(function ($availabilitySlot) {
                return $availabilitySlot->day_id . '-' . $availabilitySlot->slot_id;
            })
            ->all();
    }

    /**
     * Get student subscribed slots
     * and map them so we use them later
     * for update subscription
     * @param $student_id
     * @return array
     */
    public function mapStudentAvailabilitySlots($student_id)
    {
        $student = $this->getStudentAllAvailabilitySlots($student_id);
        $studentSlotsSet = [];

        foreach ($student->routine_classes as $studentClass) {
            $key = $studentClass->availabilitySlot->day_id . '_' . $studentClass->availabilitySlot->slot_id;
            $studentSlotsSet[$key] = true;
        }

        return $studentSlotsSet;
    }
}
