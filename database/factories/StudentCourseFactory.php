<?php

namespace Database\Factories;

use App\Models\StudentCourse;
use Illuminate\Database\Eloquent\Factories\Factory;

class StudentCourseFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = StudentCourse::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'student_id' => null,
            'course_id' => null,
            'course_level' => null,
            'teacher_id' => null,
            'teacher_preference' => null,
            'shift_id' => null,
            'created_at' => null,
            'updated_at' => null,
            'subscription_id' => null,
        ];
        
    }

}
