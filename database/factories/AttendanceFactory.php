<?php

namespace Database\Factories;

use App\Models\Attendance;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttendanceFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Attendance::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'person_id' => null,
            'person_type' => null,
            'class_id' => null,
            'class_type' => null,
            'joined_at' => null,
            'left_at' => null,
            'created_at' => null,
        ];
        
    }

}
