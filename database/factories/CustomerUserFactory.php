<?php

namespace Database\Factories;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Faker\Generator as Faker;
use Illuminate\Support\Facades\Hash;

class CustomerUserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $random = Str::random(10);
        return [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'email_verified_at' => Carbon::now('UTC')->format('Y-m-d H:i:s'),
            'password' => Hash::make($random), // you can change the password if needed
            'remember_token' => $random,
            'user_type' => 'customer',
            'gender' => 'male',
            'timezone' => 'America/New_York',
        ];
        
    }

}
