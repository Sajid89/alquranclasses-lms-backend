<?php

namespace Database\Factories;

use App\Models\StudentCourseActivity;
use Illuminate\Database\Eloquent\Factories\Factory;

class StudentCourseActivityFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = StudentCourseActivity::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'student_course_id' => null,
            'activity_type' => null,
            'description' => null,
            'file_size' => null,
            'file_name' => null,
        ];
        
    }

}
