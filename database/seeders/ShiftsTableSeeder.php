<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ShiftsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $shifts = [
            ['title' => 'Morning', 'from' => '08:00:00', 'to' => '12:00:00'],
            ['title' => 'Afternoon', 'from' => '12:00:00', 'to' => '16:00:00'],
            ['title' => 'Evening', 'from' => '16:00:00', 'to' => '20:00:00'],
            ['title' => 'Night', 'from' => '20:00:00', 'to' => '00:00:00'],
            ['title' => 'Midnight', 'from' => '00:00:00', 'to' => '04:00:00'],
            ['title' => 'Early Morning', 'from' => '04:00:00', 'to' => '08:00:00']
        ];

        DB::table('shifts')->insert($shifts);
    }
}
