<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class subscription_cancelation_reasons_seeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $createdAt = date('Y-m-d H:i:s');
        $reasons = [
            ['created_by' => "1", 'reason' => "It\'s too expensive", 'created_at' => $createdAt],
            ['created_by' => '1', 'reason' => "Talafuz or tajweed issue", 'created_at' => $createdAt],
            ['created_by' => '1', 'reason' => "I am not satisfied with your services", 'created_at' => $createdAt],
            ['created_by' => '1', 'reason' => "I encountered an error", 'created_at' => $createdAt],
            ['created_by' => '1', 'reason' => "Other", 'created_at' => $createdAt]
        ];
        DB::table('subscription_cancelation_reasons')->insert($reasons);
    }
}
