<?php

namespace Database\Factories;

use App\Models\TrialRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

class TrialRequestFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = TrialRequest::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'student_id' => null,
            'request_date' => null,
            'status' => null,
            'label' => null,
            'reason' => null,
            'created_at' => null,
            'teacher_preference' => null,
            'availability_slot_id' => null,
        ];
        
    }

}
