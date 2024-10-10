<?php

namespace App\Jobs;

use App\Helpers\GeneralHelper;
use App\Models\Student;
use App\Models\WeeklyClass;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Traits\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

class CreateOneTimeWeeklyClasses implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $details;

    /**
     * Create a new job instance.
     *
     * @param array $details
     */
    public function __construct(array $details)
    {
        $this->details = $details;
    }

    /**
     * Create one time weekly classes
     *
     * @return void
     */
    public function handle()
    {
        $userId    = $this->details['user_id'];
        $studentId = $this->details['student_id'];
        $slotIds   = $this->details['slot_ids'];
        $courseId  = $this->details['course_id'];

        DB::transaction(function () use ($userId, $studentId, $slotIds, $courseId)
        {
            $student = Student::select('id', 'name', 'teacher_id', 'timezone')
                ->with([
                    'routineClasses' => function ($query) use ($slotIds) {
                        $query->select('id', 'student_id', 'slot_id')
                            ->whereIn('slot_id', $slotIds);
                    }
                ])
                ->whereHas('courses', function ($query) use ($courseId) {
                    $query->where('id', $courseId)
                        ->whereHas('subscription', function ($query) {
                            $query->where('status', 'active');
                        })
                        ->whereHas('teacher');
                })
                ->where(['id' => $studentId])
                ->first();

            if ($student)
            {
                $studentCourse = $student->courses()->where('id', $courseId)->first();
                $now = Carbon::now("UTC")->endOfDay()->format('Y-m-d H:i:s');
                $classData = array();

                foreach ($student->routineClasses as $routine_class)
                {
                    $teacher = $studentCourse->teacher;
                    $classSlot = $routine_class->availabilitySlot->slot->slot;
                    $dayName = $routine_class->availabilitySlot->day->day_name;
                    $teacherTimezone = $teacher->timezone;
                    $currentTime = new \DateTime();
                    $givenTime = $classSlot;

                    list($hours, $minutes, $seconds) = explode(':', $givenTime);

                    $nextDay = clone $currentTime;
                    $nextDay->modify('next ' . $dayName);
                    $nextDay->setTime((int)$hours, (int)$minutes, (int)$seconds);

                    for ($i = 0; $i < 4; $i++) {
                        $upcomingDay = clone $nextDay;
                        $nextDay->modify('+1 week');
                        $class_time = GeneralHelper::convertTimeToUTCzone(
                            $upcomingDay->format('Y-m-d H:i:s'),
                            $teacherTimezone
                        );

                        if (Carbon::parse($class_time)->gt($now)) {
                            $classData[] = [
                                'customer_id' => $userId,
                                'student_id' => $student->id,
                                'teacher_id' => $teacher->id,
                                'routine_class_id' => $routine_class->id,
                                'class_time' => $class_time,
                                'created_at' => Carbon::now(),
                            ];
                        }
                    }
                }

                WeeklyClass::insert($classData);
            }
        });
    }
}
