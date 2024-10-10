<?php

namespace Database\Factories;

use App\Models\CreditHistory;
use Illuminate\Database\Eloquent\Factories\Factory;

class CreditHistoryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = CreditHistory::class;

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
            'created_at' => null,
            'expired_at' => null,
        ];
        
    }

}
