<?php

namespace Database\Factories;

use App\Models\CancelSubscriptionHistory;
use Illuminate\Database\Eloquent\Factories\Factory;

class CancelSubscriptionHistoryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = CancelSubscriptionHistory::class;

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
            'student_name' => null,
            'sub_id' => null,
            'price' => null,
            'reasons' => null,
            'comments' => null,
            'status' => null,
            'created_at' => null,
            'updated_at' => null
        ];
        
    }

}
