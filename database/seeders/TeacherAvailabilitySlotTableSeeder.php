<?php

namespace Database\Seeders;

use App\Models\Availability;
use App\Models\AvailabilitySlot;
use App\Models\Day;
use App\Models\Slot;
use Illuminate\Database\Seeder;

class TeacherAvailabilitySlotTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $dayIds = Day::pluck('id')->toArray();
        $availabilityIds = Availability::where('available_type', 'App\Models\User')->pluck('id')->toArray();
        $slotIds = Slot::pluck('id')->toArray();
        
        foreach ($availabilityIds as $availabilityId) {
            foreach($dayIds as $dayId) {
                //for every day insert 5 availability slots
                for($i = 0; $i < 15; $i++) {
                    $slotId = $slotIds[array_rand($slotIds)];
                    AvailabilitySlot::factory()->create([
                        'availability_id' => $availabilityId,
                        'day_id' => $dayId,
                        'slot_id' => $slotId
                    ]);
                }
            }

        }
    }
}
