<?php

namespace Database\Seeders;

use App\Models\CancelSubscriptionRequest;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CancelSubscriptionRequestTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $createdAt = Carbon::now('UTC')->format('Y-m-d H:i:s');
        $students = Student::orderByDesc('id')->limit(100)->get();
        foreach ($students as $student) {
            $reason = Str::random(20);
            $comments = Str::random(20);
            CancelSubscriptionRequest::factory()->create([
                'user_id' => $student->user_id,
                'student_id' => $student->id,
                'reasons' => $reason,
                'comments' => $comments,
                'status' => 'pending',
                'created_at' => $createdAt,
                'updated_at' => $createdAt
            ]);
        }
    }
}
