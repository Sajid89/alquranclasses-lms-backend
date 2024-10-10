<?php

namespace Database\Factories;

use App\Models\Subscription;
use Illuminate\Database\Eloquent\Factories\Factory;

class SubscriptionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Subscription::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_id' => null,
            'student_id' => null,
            'payment_id' => null,
            'planID' => null,
            'payment_status' => null,
            'price' => null,
            'quantity' => null,
            'start_at' => null,
            'ends_at' => null,
            'created_at' => null,
            'updated_at' => null,
            'payment_name' => null,
            'payer_id' => null,
            'sub_id' => null,
            'status' => null,
        ];
        
    }

}
