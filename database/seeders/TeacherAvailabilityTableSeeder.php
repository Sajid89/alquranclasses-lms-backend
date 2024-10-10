<?php

namespace Database\Seeders;

use App\Models\Availability;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class TeacherAvailabilityTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $teacherIds = User::where('user_type', 'teacher')->pluck('id')->toArray();
        $createdAt = Carbon::now('UTC')->format('Y-m-d H:i:s');
        foreach ($teacherIds as $teacherId) {
            Availability::factory()->create([
                'available_type' => 'App\\Models\\User',
                'available_id' => $teacherId,
                'created_at' => $createdAt,
                'updated_at' => $createdAt
            ]);
        }
    }
}
