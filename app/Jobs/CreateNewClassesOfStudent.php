<?php

namespace App\Jobs;

use App\Entity\QueueThrottleExceptionsLimitor;

use App\Models\WeeklyClass;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Models\Student;
use App\Traits\QueTrait;
use App\Traits\VSDKTrait;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

class CreateNewClassesOfStudent extends QueueThrottleExceptionsLimitor implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 5;

    use VSDKTrait;
    use QueTrait;

    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            DB::transaction(function ()
            {
                $today = Carbon::now();
                $students = Student::select('id', 'name', 'teacher_id', 'timezone')
                    ->with([
                        'subscription' => function ($query) {
                            $query->select('id', 'student_id', 'start_at', 'ends_at');
                        },
                        'weekly_classes' => function ($query) {
                            $query->select('id', 'student_id', 'class_time');
                        }
                    ])
                    ->whereHas('subscription', function ($query) use ($today) {
                        $query->whereNull('ends_at');
                    })
                    ->whereHas('teacher')
                    ->where('is_subscribed', 1)
                    ->get();

                $studentsNeedingClasses = $students->filter(function ($student) use ($today)
                {
                    $subscription = $student->subscription;
                    $classes = $student->weekly_classes;

                    $subscriptionStartDate = Carbon::parse($subscription->start_at);
                    $startOfSubscriptionCycle = $subscriptionStartDate->copy()->addMonth();
                    if ($today->lt($startOfSubscriptionCycle)) {
                        $startOfSubscriptionCycle->subMonth();
                    }

                    $endOfSubscriptionCycle = $startOfSubscriptionCycle->copy()->addMonth();
                    return $classes->isEmpty() ||
                        $classes->every(function ($class) use ($startOfSubscriptionCycle, $endOfSubscriptionCycle)
                        {
                            $classTime = Carbon::parse($class->class_time);
                            return $classTime->lt($startOfSubscriptionCycle) || $classTime->gt($endOfSubscriptionCycle);
                        });
                });

                $now = now("UTC")->endOfDay()->format('Y-m-d H:i:s');
                $classData = array();

                foreach ($studentsNeedingClasses as $student)
                {
                    $teacher = $student->teacher;

                    foreach ($student->routine_classes as $routine_class)
                    {
                        $classSlot = $routine_class->availabilitySlot->slot->slot;
                        $dayName = $routine_class->availabilitySlot->day->day_name;
                        $teacherTimezone = $teacher->timezone;
                        $currentTime = new \DateTime();
                        $givenTime = $classSlot;

                        list($hours, $minutes, $seconds) = explode(':', $givenTime);

                        $nextDay = clone $currentTime;
                        $nextDay->modify('next ' . $dayName);
                        $nextDay->setTime((int)$hours, (int)$minutes, (int)$seconds);

                        for ($i = 0; $i < 4; $i++)
                        {
                            $upcomingDay = clone $nextDay;
                            $nextDay->modify('+1 week');
                            $class_time = convertTimeToUTCzone(
                                $upcomingDay->format('Y-m-d H:i:s'),
                                $teacherTimezone
                            );

                            if (Carbon::parse($class_time)->gt($now))
                            {
                                $Custom_RoomID = CreateUUId();
                                $Response = $this->CreateMeeting($Custom_RoomID);
                                if (!is_null($Response))
                                {
                                    $classData[] = [
                                        'student_id' => $student->id,
                                        'teacher_id' => $teacher->id,
                                        'routine_class_id' => $routine_class->id,
                                        'class_time' => $class_time->toDateTimeString(),
                                        'created_at' => Carbon::now(),
                                        'session_key' => $Custom_RoomID,
                                    ];
                                }
                            }
                        }
                    }
                }

                WeeklyClass::insert($classData);
            });
        } catch (Exception $e) {
            Log::error($e->getMessage());
        }
    }
}
