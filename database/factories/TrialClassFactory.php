<?php

namespace Database\Factories;

use App\Models\Courseable;
use App\Models\TrialClass;
use Illuminate\Database\Eloquent\Factories\Factory;

class TrialClassFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = TrialClass::class;

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
            'class_time' => $this->faker->dateTimeBetween('+1 week', '+4 week'),
            'status' => 'trial_scheduled',
            'student_status' => 'scheduled',
            'teacher_status' => 'scheduled',
            'class_duration' => '00:30:00',
            'teacher_presence' => 0,
            'student_presence' => 0,
            'teacher_id' => null,
            'availability_slot_id' => null,
            'student_course_id' => null,
        ];
        
    }

}
