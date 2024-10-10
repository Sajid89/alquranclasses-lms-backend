<?php

namespace Database\Seeders;

use App\Models\CancelSubscriptionHistory;
use App\Models\CancelSubscriptionRequest;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class CancelSubscriptionHistoryTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $createdAt = Carbon::now('UTC')->format('Y-m-d H:i:s');
        $students = CancelSubscriptionRequest::orderByDesc('id')->get();
        foreach ($students as $student) {
            CancelSubscriptionHistory::factory()->create([
                'user_id' => $student->user_id,
                'student_id' => $student->student_id,
                'student_name' => Student::find($student->student_id)->name,
                'sub_id' => $student->user_subcription_id,
                'price' => 3500,
                'reasons' => $student->reasons,
                'comments' => $student->comments,
                'status' => 'approved',
                'created_at' => $createdAt,
                'updated_at' => $createdAt
            ]);
        }
    }
}
