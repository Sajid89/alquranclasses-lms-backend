<?php

namespace Database\Factories;

use App\Models\Chat;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class ChatFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Chat::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $createdAt = Carbon::now('UTC')->format('Y-m-d H:i:s');
        return [
            'from' => null,
            'to' => null,
            'message' => null,
            'seen' => null,
            'type' => null,
            'created_at' => null,
            'updated_at' => null,
        ];
        
    }

}
