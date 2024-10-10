<?php
namespace App\Services;

use App\Helpers\GeneralHelper;
use App\Models\Day;
use App\Models\Slot;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Repository\ChatRepository;
use App\Repository\Interfaces\CustomerRepositoryInterface;
use App\Repository\MakeupRequestRepository;
use App\Repository\TeacherCoordinatorRepository;
use App\Repository\TrialClassRepository;
use App\Repository\UserRepository;
use App\Repository\WeeklyClassRepository;
use App\Traits\DecryptionTrait;

class TeacherCoordinatorService
{
    private $teacherCoordinatorRepository;
    private $chatRepository;
    private $trialClassRepository;
    private $weeklyClassRepository;
    private $makeupRequestRepository;
    private $userRepository;
    private $customerRepository;
    use DecryptionTrait;

    
    public function __construct(
        TeacherCoordinatorRepository $teacherCoordinatorRepository,
        ChatRepository $chatRepository,
        TrialClassRepository $trialClassRepository,
        WeeklyClassRepository $weeklyClassRepository,
        MakeupRequestRepository $makeupRequestRepository,
        UserRepository $userRepository,
        CustomerRepositoryInterface $customerRepository
    )
    {
        $this->teacherCoordinatorRepository = $teacherCoordinatorRepository;
        $this->chatRepository = $chatRepository;
        $this->trialClassRepository = $trialClassRepository;
        $this->weeklyClassRepository = $weeklyClassRepository;
        $this->makeupRequestRepository = $makeupRequestRepository;
        $this->userRepository = $userRepository;
        $this->customerRepository = $customerRepository;
    }

    /**
     * Get all the teacher's
     * with unread messages count
     * 
     * @param $teacherCoordinatorId
     * @return mixed
     */
    public function getTeachersWithUnreadMessagesCount($teacherCoordinatorId)
    {
        $teachers = $this->teacherCoordinatorRepository->getTeachers();

        $teachersArray = [];
        
        foreach ($teachers as $teacher) {
            $unreadMessagesCount = $this->chatRepository
                ->getUnreadMessagesCount($teacher->id, $teacherCoordinatorId);
        
            $teachersArray[] = [
                'id' => $teacher->id,
                'name' => $this->decryptValue($teacher->name),
                'course' => $teacher->course,
                'profilePic' => $teacher->profilePic,
                'unreadMessagesCount' => $unreadMessagesCount,
                'active' => false,
            ];
        }
        
        return $teachersArray;
    }

    /**
     * Get teacher's notifications
     * 
     * @param $teacherCoordinatorId
     * @param $offset
     * @param $limit
     * @return mixed
     */
    public function getTeachersNotifications($teacherCoordinatorId, $offset, $limit) {
        $teacherIds = $this->userRepository->getCoordinatedTeachers($teacherCoordinatorId);
        return $this->customerRepository->getTeachersNotifications($teacherIds, $limit, $offset, null);
    }

    public function getTeachersTodaysClasses($teacherCoordinatorId, $todayDate, $offset, $limit) {
        /*
        i have to bring count of trial classes and count of weekly classes
        i have to bring data of both trial classes and weekly classes with pagination
        */
        $queryCount = "call spTeachersTodaysClassesCount($teacherCoordinatorId, $todayDate);";
        $resultSet = DB::select($queryCount);
        $count = $resultSet[0]->count;

        $queryClasses = "call spTeachersTodaysClasses($teacherCoordinatorId, '$todayDate', $offset, $limit);";
        $classes = DB::select($queryClasses);
        $data = array();

        foreach ($classes as $class) {
            $data[] = [
                'tw_class_id' => $class->tw_class_id,
                'class_time' => $class->class_time,
                'teacher_name' => $this->decryptValue($class->teacher_name),
                'teacher_timezone' => $class->teacher_timezone,
                'student_name' => $this->decryptValue($class->student_name),
                'student_timezone' => $class->student_timezone,
                'course_level' => $class->course_level,
                'student_course_id' => $class->student_course_id,
                'course_title' => $class->course_title,
            ];
        }
    }

    public function getListOfTeachers($teacherCoordinatorId, $offset, $limit) {
        return $this->userRepository->getListOfTeachers($teacherCoordinatorId, $offset, $limit);
    }

    public function getTeacherStudents($teacherId, $offset, $limit) {
        $count = $this->teacherCoordinatorRepository->getTotalStudentsOfATeacher($teacherId);
        $teacher = $this->teacherCoordinatorRepository->getTeacherById($teacherId);

        $studentCourses = $this->teacherCoordinatorRepository->getTeacherStudents($teacherId, $offset, $limit);
        $data = array();
        foreach($studentCourses as $studentCourse) {
            $data[] = [
                'student_course_id' => $studentCourse->id,
                'student_id' => $studentCourse->student_id,
                'student_name' => $this->decryptValue($studentCourse->student->name),
                'customer_name' => $this->decryptValue($studentCourse->student->user->name),
                'student_timezone' => $studentCourse->student->timezone,
                'course_title' => $studentCourse->course->title,
                'course_id' => $studentCourse->course->id,
                'course_level' => $studentCourse->course_level,
                'teacher_id' => $studentCourse->teacher_id,
                'teacher_name' => $this->decryptValue($teacher->name),
                'teacher_timezone' => $teacher->timezone,
                'student_registration_number' => 'ALQ-STD-' . $studentCourse->student->id,
            ];
        }

        return [
            'count' => $count,
            'students' => $data
        ];
    }

    /**
     * Get teacher's courses
     * 
     * @param $teacherId
     * @return mixed
     */
    public function getTeacherCourses($teacherId) {
        
        $teacherCourses = $this->teacherCoordinatorRepository->getTeacherCourses($teacherId);
        $data = array();
        foreach($teacherCourses as $teacherCourse) {
            $data[] = [
                'assigned_course_id' => $teacherCourse->id,
                'course_id' => $teacherCourse->course_id,
                'course_title' => $teacherCourse->course->title,
                'course_description' => $teacherCourse->course->description,
                'teacher_id' => $teacherId
            ];
        }

        return $data;

    }

    /**
     * Get teacher's profile
     * 
     * @param $teacherId
     * @return mixed
     */
    public function getTeacherProfile($teacherId) {
        $teacher = $this->teacherCoordinatorRepository->getTeacherById($teacherId);
        $studentsCount = $this->teacherCoordinatorRepository->getTotalStudentsOfATeacher($teacherId);
        $trialClassRate = $this->teacherCoordinatorRepository->getLatestTrialClassRate();
        $regularClassRate = $this->teacherCoordinatorRepository->getLatestRegularClassRate($teacherId);

        return [
                'teacher_id' => $teacher->id,
                'teacher_registration' => 'ALQ-TCH-'.$teacher->id,
                'teacher_name' => $this->decryptValue($teacher->name),
                'email' => $this->decryptValue($teacher->email),
                'secondary_email' => $this->decryptValue($teacher->secondary_email),
                'phone' => $this->decryptValue($teacher->phone),
                'secondary_phone' => $this->decryptValue($teacher->secondary_phone),
                'timezone' => $teacher->timezone,
                'total_students' => $studentsCount,
                'teacher_profile_pic' => $teacher->profile_photo_path,
                'trial_rate' => $trialClassRate,
                'regular_rate' => $regularClassRate
        ];
        
    }

    public function assignCourseToTeacher($teacherId, $courseId) {
        return $this->teacherCoordinatorRepository->assignCourseToTeacher($teacherId, $courseId);
    }

    public function removeCourseFromTeacher($teacherId, $courseId) {
        //to check if the teacher has some students in this course
        $studentsCount = $this->teacherCoordinatorRepository->getTotalStudentsOfATeacherInCourse($teacherId, $courseId);
        if ($studentsCount > 0) {
            return 'You cannot remove this course from teacher as there are students enrolled in this course';
        }
        $this->teacherCoordinatorRepository->removeCourseFromTeacher($teacherId, $courseId);
        return 'Course removed from teacher successfully';
    }


    /**
     * the below function will return all time slots
     * if available timeslots of a teacher are already 
     * stored in the database, then a flag will be set to true
     * and all timeslots will be returned. flag with reaming timeslots 
     * will be set as false
     */
    public function getTeacherAvailability($teacherId) {
        $days = Day::orderBy('id')->get();
        $timeSlots = Slot::orderBy('id')->get();
        $teacherTimeZone = User::find($teacherId)->timezone;
        $teacherAvailabilities = $this->teacherCoordinatorRepository->getTeacherAvailability($teacherId);
        $availabilitySlots = [
            'teacher_id' => $teacherId,
            'teacher_timezone' => $teacherTimeZone,
            'days' => []
        ];

        //populate days and slots
        foreach ($days as $day) {
            $dayId = $day->id;
            $dayName = $day->day_name;
            $availabilitySlots['days'][] = [
                'day_id' => $dayId,
                'day_name' => $dayName,
                'is_day_selected' => false,
                'slots' => null
            ];
        }
        
        //populate slots with isAssigned flag
        $index = 0;
        $isDaySelected = false;
        $temp = $availabilitySlots;
        foreach($temp['days'] as $day) {
            $dayId = $day['day_id'];
            $slots = [];
            foreach($timeSlots as $slot) {
                $slotId = $slot->id;
                $slotTime = $slot->slot;
                $isAssigned = false;
                $availabilityId = null;
                $availabilitySlotId = null;
                foreach($teacherAvailabilities as $availability) {
                    if ($availability->day_id == $dayId && $availability->slot_id == $slotId) {
                        $isAssigned = true;
                        $availabilityId = $availability->id;
                        $availabilitySlotId = $availability->availability_slot_id;
                        $isDaySelected = true;
                        break;
                    }
                }
                $slots[] = [
                    'day_id' => $dayId,
                    'slot_id' => $slotId,
                    // 'slot_time' => $slotTime,
                    'slot_time' => GeneralHelper::getAvailabilityTime($slotTime),
                    'is_assigned' => $isAssigned,
                    'availability_id' => $availabilityId,
                    'availability_slot_id' => $availabilitySlotId
                ];
            }
            $availabilitySlots['days'][$index]['is_day_selected'] = $isDaySelected;
            $availabilitySlots['days'][$index]['slots'] = $slots;
            $isDaySelected = false;
            $index++;
        }

        return $availabilitySlots;
    }

    /**
     * delete teacher availability
     * The teacher availability can only be deleted if it is not in use
     * i.e. if there is no class scheduled in that slot
     * i.e. if there is no trial class scheduled in that slot
     */
    public function deleteTeacherAvailability($teacherId, $availabilitySlotId) {
        $count = $this->teacherCoordinatorRepository->getTotalClassesScheduledInAvailabilitySlot($teacherId, $availabilitySlotId);
        if($count > 0) {
            return 'You cannot delete this availability slot as there are classes scheduled in this slot';
        }

        $this->teacherCoordinatorRepository->deleteTeacherAvailability($availabilitySlotId);
        return 'Availability slot deleted successfully';
    }
}