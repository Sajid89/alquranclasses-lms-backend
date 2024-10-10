<?php

namespace Database\Factories;

use App\Models\Availability;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class AvailabilityFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Availability::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'available_type' => null,
            'available_id' => null,
            'created_at' => null,
            'updated_at' => null
        ];
        
    }

}
