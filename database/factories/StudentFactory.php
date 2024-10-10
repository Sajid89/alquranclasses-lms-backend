<?php

namespace Database\Factories;

use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class StudentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Student::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $createdAt = Carbon::now('UTC')->format('Y-m-d H:i:s');
        return [
            'name' => $this->faker->name,
            'age' => $this->faker->numberBetween(1, 50),
            'status' => 'active',
            'reschedule_trial_limit_count' => 3,
            'timezone' => null,
            'shift_id' => 1,
            'regular_class_shift_id' => 1,
            'user_id' => null,
            'teacher_id' => null,
            'course_id' => 1,
            'user_subcription_id' => 1,
            'subscription_status' => 'active',
            'vacation_mode' => 0,
            'is_subscribed' => 1,
            'created_at' => $createdAt

        ];
        
    }

}
