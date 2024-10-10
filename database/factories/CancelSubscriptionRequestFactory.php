<?php

namespace Database\Factories;

use App\Models\Attendance;
use App\Models\CancelSubscriptionRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

class CancelSubscriptionRequestFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = CancelSubscriptionRequest::class;

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
            'reasons' => null,
            'comments' => null,
            'status' => null,
            'created_at' => null,
            'updated_at' => null
        ];
        
    }

}
