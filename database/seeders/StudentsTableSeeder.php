<?php

namespace Database\Seeders;

use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;

class StudentsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $customerIds = User::where('user_type', 'customer')->pluck('id')->toArray();
        $teacherIds = User::where('user_type', 'teacher')->pluck('id')->toArray();
        $timezones = array('Australia/Perth', 'America/Chicago', 'America/New_York');
        // Loop through all customer IDs

        foreach ($customerIds as $customerId) {
            //each customer should have 4 students
            for ($i = 0; $i < 4; $i++) {
                // Get a random teacher ID
                $randomTeacherId = $teacherIds[array_rand($teacherIds)];
                $timezone = $timezones[array_rand($timezones)];
                // Create a student record with a random teacher ID
                Student::factory()->create([
                    'user_id' => $customerId,
                    'teacher_id' => $randomTeacherId,
                    'timezone' => $timezone
                ]);
            }
        }
    }
}
