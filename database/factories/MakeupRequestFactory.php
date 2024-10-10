<?php

namespace Database\Factories;

use App\Models\MakeupRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

class MakeupRequestFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = MakeupRequest::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'student_course_id' => null,
            'class_type' => null,
            'class_id' => null,
            'availability_slot_id' => null,
            'makeup_date_time' => null,
            'class_old_date_time' => null,
            'status' => null,
            'created_at' => null,
            'updated_at' => null,
            'is_teacher' => null,
        ];
        
    }

}
