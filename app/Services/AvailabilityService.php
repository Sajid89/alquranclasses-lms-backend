<?php

namespace App\Services;

use App\Http\Resources\TeacherScheduleResource;
use App\Models\Shift;
use App\Repository\Interfaces\AvailabilityRepositoryInterface;
use App\Repository\Interfaces\AvailabilitySlotRepositoryInterface;
use Carbon\Carbon;
use App\Repository\Interfaces\TeacherRepositoryInterface;
use App\Repository\ShiftRepository;

class AvailabilityService
{
    protected $teacherRepository;
    private $shiftRepository;
    private $availabilityRepository;

    /**
     * AvailabilityService constructor.
     * 
     * @param AvailabilitySlotRepositoryInterface $availabilitySlotRepository
     */
    public function __construct(
        TeacherRepositoryInterface $teacherRepository,
        ShiftRepository $shiftRepository,
        AvailabilityRepositoryInterface $availabilityRepository
    )
    {
        $this->teacherRepository = $teacherRepository;
        $this->shiftRepository = $shiftRepository;
        $this->availabilityRepository = $availabilityRepository;
    }

    /**
     * Get teacher list with weekly schedule
     * 
     * @param $courseId
     * @param $teacherPreference
     * @param $shiftId
     * @param $studentTimezone
     * @return mixed
     */
    public function getTeacherList(
        $courseId, $teacherPreference, $shiftId, 
        $studentTimezone, $studentId, $teacherId, $isTeacherChanged
    )
    {
        // Get the student's selected shift
        $studentShift = Shift::find($shiftId);

        // Convert the student's shift times to UTC and then to Teacher timezone
        $studentShift->from = $this->convertFromStudentToTeacherTimezone($studentShift->from, $studentTimezone);
        $studentShift->to = $this->convertFromStudentToTeacherTimezone($studentShift->to, $studentTimezone);
        
        // Get the slot ids for the selected shift
        $shiftSlotIds = $this->shiftRepository->getSlotsForShift($studentShift->from, $studentShift->to);

        if ($teacherId && !$isTeacherChanged) {
            $teachers = $this->teacherRepository->getCurrentTeacher($teacherId);
        } else {
            $teachers = $this->teacherRepository->getTeachers(
                $teacherPreference, $courseId, $shiftSlotIds, 
                $studentId, $isTeacherChanged, $teacherId
            );
        }

        return TeacherScheduleResource::collection($teachers)->map(function ($teacher) 
            use ($studentTimezone, $shiftSlotIds, $studentId, $teacherId, $courseId) 
            {
                return (new TeacherScheduleResource($teacher))
                    ->additional([
                        'studentTimezone' => $studentTimezone,
                        'slotIds' => $shiftSlotIds,
                        'studentId' => $studentId,
                        'teacherId' => $teacherId,
                        'courseId' => $courseId,
                    ]);
            }
        );
    }

    /**
     * Get teacher schedule
     * 
     * @param $teacherId
     * @param $dayId
     * @param $shiftId
     * @return mixed
     */
    public function getTeacherSchedule(
        $teacherId, $studentId, $courseId, 
        $dayId, $shiftId
    )
    {
        // Get the student's selected shift
        $teacherShift = Shift::find($shiftId);
        
        // Get the slot ids for the selected shift
        $shiftSlotIds = $this->shiftRepository->getShiftSlots($teacherShift->from, $teacherShift->to);

        $teacher = $this->teacherRepository->getTeacherById($teacherId);

        // Get the teacher's availability slots
        $availability = $this->availabilityRepository->getTeacherAvailability($teacherId, $dayId);
    
        return (new TeacherScheduleResource($teacher))
            ->additional([
                'availabilitySlots' => $availability->availabilitySlots,
                'studentId' => $studentId,
                'courseId' => $courseId,
                'dayId' => $dayId,
                'slotIds' => $shiftSlotIds,
            ]);
    }
  
    /**
     * Convert student's shift times to teacher's timezone
     * 
     * @param $studentShift
     * @param $studentTimezone
     * @return string
     */
    private function convertFromStudentToTeacherTimezone($studentShift, $studentTimezone) 
    {
        $studentShift = Carbon::createFromFormat('H:i:s', $studentShift, $studentTimezone)->setTimezone('UTC');
        $studentShift->setTimezone('Africa/Cairo');
        $studentShift = $studentShift->format('H:i:s');
        
        return $studentShift;
    }
}