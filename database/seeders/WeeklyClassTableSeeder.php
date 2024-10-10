<?php

namespace Database\Seeders;

use App\Models\RoutineClass;
use App\Models\WeeklyClass;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class WeeklyClassTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        $routineClasses = RoutineClass::orderBy('id')->get();
        $createdAt = Carbon::now('UTC')->format('Y-m-d H:i:s');
        foreach ($routineClasses as $routine) {
            $slotTime = $routine->availabilitySlot->slot->slot;
            $dayId = $routine->availabilitySlot->day_id;
            $dayName = $days[$dayId - 1];

            $teacherTimezone = $routine->teacher->timezone;
           
            $currentDate = Carbon::now($teacherTimezone); // Current date
            $nextDay = $currentDate->copy()->next($dayName);
            $nextDateTime = $nextDay->format('Y-m-d') . ' ' . $slotTime;

            // Convert the combined string into a Carbon instance
            //the current date time is already in teacher timezone, we just have to 
            //convert it to UTC
            for($i = 0; $i < 4; $i++) {

                $nextDateTimeUTC = Carbon::parse($nextDateTime)
                ->addWeeks($i) // Adds 2 weeks
                ->setTimezone('UTC') // Converts to UTC
                ->format('Y-m-d H:i:s'); // Formats the output
                
                WeeklyClass::factory()->create([
                    'customer_id' => $routine->student->user_id,
                    'student_id' => $routine->student_id,
                    'teacher_id' => $routine->teacher_id,
                    'routine_class_id' => $routine->id,
                    'status' => 'scheduled',
                    'student_status' => 'scheduled',
                    'teacher_status' => 'scheduled',
                    'class_time' => $nextDateTimeUTC,
                    'class_link' => '',
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                    'class_duration' => '00:00:00',
                    'teacher_presence' => 0,
                    'student_presence' => 0,
                ]);
            }
        }

    }
}
