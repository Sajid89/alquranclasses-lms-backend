<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ShiftSlotsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Get all the shifts
        $shifts = DB::table('shifts')->get();
        // Iterate over each shift
        foreach ($shifts as $shift) {
            $startsAt = Carbon::createFromFormat('H:i:s', $shift->from);
            $endsAt = Carbon::createFromFormat('H:i:s', $shift->to);

            if ($shift->title == 'Night') {
                // For the 'Night' shift, get slots that are greater than or equal to the start time OR less than the end time
                $slotIds = DB::table('slots')
                    ->where(function ($query) use ($startsAt, $endsAt) {
                        $query->whereTime('slot', '>=', $startsAt->toTimeString())
                              ->orWhereTime('slot', '<', $endsAt->toTimeString());
                    })
                    ->pluck('id');
            } else {
                // For other shifts, get slots that are within the shift's times
                $slotIds = DB::table('slots')
                    ->whereTime('slot', '>=', $startsAt->toTimeString())
                    ->whereTime('slot', '<', $endsAt->toTimeString())
                    ->pluck('id');
            }

            // Create records in the shift_slots table
            foreach ($slotIds as $slotId) {
                DB::table('shift_slots')->insert([
                    'shift_id' => $shift->id,
                    'slot_id' => $slotId,
                ]);
            }
        }
    }
}
