<?php

namespace Database\Seeders;

use App\Models\Coupon;
use App\Models\CouponUser;
use App\Models\StudentCourse;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class CouponUserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $studentCourses = StudentCourse::orderBy('id')->limit(200)->get();
        $createdAt = Carbon::now('UTC')->format('Y-m-d H:i:s');
        $couponIds = Coupon::pluck('id')->toArray();

        $createdAt = Carbon::now('UTC')->format('Y-m-d H:i:s');
        foreach ($studentCourses as $studentCourse) {
            CouponUser::factory()->create([
                'student_id' => $studentCourse->student_id,
                'coupon_id' => $couponIds[array_rand($couponIds)],
                'user_id' => $studentCourse->student->user_id,
                'created_at' => $createdAt,
                'updated_at' => $createdAt
            ]);
        }
    }
}
