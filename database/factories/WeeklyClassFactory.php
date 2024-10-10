<?php

namespace Database\Factories;

use App\Models\WeeklyClass;
use Illuminate\Database\Eloquent\Factories\Factory;

class WeeklyClassFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = WeeklyClass::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'customer_id' => null,
            'student_id' => null,
            'teacher_id' => null,
            'routine_class_id' => null,
            'status' => null,
            'student_status' => null,
            'teacher_status' => null,
            'class_time' => null,
            'class_link' => null,
            'created_at' => null,
            'updated_at' => null,
            'class_duration' => null,
            'teacher_presence' => null,
            'student_presence' => null,
        ];
        
    }

}
