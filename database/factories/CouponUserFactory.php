<?php

namespace Database\Factories;

use App\Models\CouponUser;
use Illuminate\Database\Eloquent\Factories\Factory;

class CouponUserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = CouponUser::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'student_id' => null,
            'coupon_id' => null,
            'user_id' => null,
            'created_at' => null,
            'updated_at' => null,
        ];
        
    }

}
