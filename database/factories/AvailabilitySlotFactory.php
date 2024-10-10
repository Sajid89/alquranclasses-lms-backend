<?php

namespace Database\Factories;

use App\Models\Availability;
use App\Models\AvailabilitySlot;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class AvailabilitySlotFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = AvailabilitySlot::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'availability_id' => null,
            'day_id' => null,
            'slot_id' => null
        ];
        
    }

}
