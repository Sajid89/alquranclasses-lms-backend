<?php

namespace Database\Seeders;

use App\Models\Courseable;
use App\Models\User;
use Illuminate\Database\Seeder;

class CourseableTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $teacherIds = User::where('user_type', 'teacher')
                        ->where('id', '>', 100)
                        ->pluck('id')->toArray();

        foreach ($teacherIds as $teacherId) {
            for($i= 1; $i <= 3; $i++) {
                Courseable::factory()->create([
                    'course_id' => $i,
                    'courseable_id' => $teacherId,
                    'courseable_type' => 'App\Models\User',
                ]);
            }
      
        }


    }
}
