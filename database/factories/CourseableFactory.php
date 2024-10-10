<?php

namespace Database\Factories;

use App\Models\Courseable;
use App\Models\StudentCourse;
use Illuminate\Database\Eloquent\Factories\Factory;

class CourseableFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Courseable::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'course_id' => null,
            'courseable_id' => null,
            'courseable_type' => null,
        ];
        
    }

}
