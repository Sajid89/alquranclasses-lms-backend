<?php

namespace Database\Seeders;

use App\Models\Student;
use App\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SubscriptionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $students = Student::orderBy('id')->get();
        $quantity = 1;
        $startsAt = Carbon::now('UTC')->format('Y-m-d H:i:s');
        $endsAt = Carbon::now('UTC')->addMonth(1)->format('Y-m-d H:i:s');
        foreach ($students as $student) {
            $planId = Str::random(10);
            $payerId = Str::random(10);
            $subId = Str::random(10);
            $paymentId = Str::random(10);
            Subscription::factory()->create([
                'user_id' => $student->user_id,
                'student_id' => $student->id,
                'payment_id' => $paymentId,
                'planID' => $planId,
                'payment_status' => 'succeeded',
                'price' => 3500,
                'quantity' => 1,
                'start_at' => $startsAt,
                'ends_at' => $endsAt,
                'created_at' => $startsAt,
                'updated_at' => $startsAt,
                'payment_name' => '',
                'payer_id' => $payerId,
                'sub_id' => $subId,
                'status' => 'active'
            ]);
        }
    }
}
