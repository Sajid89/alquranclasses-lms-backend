<?php

namespace Database\Factories;

use App\Models\RoutineClass;
use Illuminate\Database\Eloquent\Factories\Factory;

class RoutineClassFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = RoutineClass::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'student_id' => null,
            'teacher_id' => null,
            'slot_id' => null,
            'student_course_id' => null,
            'class_link' => null,
            'status' => null,
            'created_at' => null,
            'updated_at' => null,
        ];
        
    }

}
