<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Coupon extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'code',
        'type',
        'value',
        'usage_limit',
        'used',
        'expires_at',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['expires_at'];

    public function usersAndStudents()
    {
        return $this->belongsToMany(User::class, 'coupon_user')
            ->withPivot('student_id')->withTimestamps();
    }


    /**
     * Determine if the coupon is expired.
     * If there's no expiry date, consider it not expired.
     *
     * @return bool
     */
    public function isExpired()
    {
        if ($this->expires_at) {
            $expiresAt = Carbon::parse($this->expires_at);
            return $expiresAt->isPast() && !$expiresAt->isToday();
        }

        return false;
    }

    /**
     * Determine if the usage limit of the coupon has been reached.
     * If there's no usage limit, consider it not reached.
     *
     * @return bool
     */
    public function isUsageLimitReached()
    {
        if ($this->usage_limit) {
            return $this->used >= $this->usage_limit;
        }

        return false;
    }

    /**
     * @param $query
     * @param array $select
     * @param array $conditions
     * @return mixed
     */
    public function scopeSelectCoupon($query, array $select, array $conditions)
    {
        return $query->select($select)->where($conditions);
    }

    public function couponUsers()
    {
        return $this->hasMany(CouponUser::class);
    }
}
