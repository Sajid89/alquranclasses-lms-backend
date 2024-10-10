<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NotoCancelSubscriptionReasonsSeeder extends Seeder
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
            ['created_by' => "1", 'reason' => "1 - Lorem ipsum dolor sit amet", 'created_at' => $createdAt],
            ['created_by' => "1", 'reason' => "2 - Lorem ipsum dolor sit amet", 'created_at' => $createdAt],
            ['created_by' => "1", 'reason' => "3 - Lorem ipsum dolor sit amet", 'created_at' => $createdAt],
        ];
        DB::table('not_to_cancel_subscription_reasons')->insert($reasons);
    }
}
