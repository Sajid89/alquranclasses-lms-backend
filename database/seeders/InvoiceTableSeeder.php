<?php

namespace Database\Seeders;

use App\Models\Invoice;
use App\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class InvoiceTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $subscriptions = Subscription::orderBy('id')->get();
        $createdAt = Carbon::now('UTC')->format('Y-m-d H:i:s');

        foreach($subscriptions as $subscription) {
            Invoice::factory()->create([
                'subscription_id' => $subscription->id,
            'stripe_invoice_id' => 'in_' . $subscription->id,
            'invoice_date' => $createdAt,
            'amount' => 35.00,
            'line_items' => json_encode(array('test line items - '.$subscription->id))
            ]);
        }
    }
}

