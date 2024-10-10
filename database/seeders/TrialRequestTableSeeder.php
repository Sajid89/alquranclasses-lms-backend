<?php

namespace Database\Seeders;

use App\Models\TrialClass;
use App\Models\TrialRequest;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class TrialRequestTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $trialClasses = TrialClass::orderBy('id')->get();

        foreach ($trialClasses as $trial) {
            TrialRequest::factory()->create([
                'student_id' => $trial->student_id,
                'request_date' => Carbon::parse($trial->created_at)->format('Y-m-d'),
                'status' => 'scheduled',
                'label' => 'Trial Request',
                'reason' => 'Trial Request',
                'created_at' => $trial->created_at,
                'teacher_preference' => 'male',
                'availability_slot_id' => $trial->availability_slot_id
            ]);
        }

    }
}
