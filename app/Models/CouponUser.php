<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CouponUser extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'coupon_user';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'student_id',
        'coupon_id',
        'user_id',
        'created_at',
        'updated_at'
    ];

    public function coupon() {
        return $this->belongsTo(Coupon::class);
    }

}
