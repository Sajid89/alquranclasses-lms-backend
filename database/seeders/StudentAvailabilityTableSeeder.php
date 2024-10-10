<?php

namespace Database\Seeders;

use App\Models\Availability;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class StudentAvailabilityTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $studentIds = Student::pluck('id')->toArray();
        $createdAt = Carbon::now('UTC')->format('Y-m-d H:i:s');
        foreach ($studentIds as $studentId) {
            Availability::factory()->create([
                'available_type' => 'App\Models\Student',
                'available_id' => $studentId,
                'created_at' => $createdAt,
                'updated_at' => $createdAt
            ]);
        }
    }
}
