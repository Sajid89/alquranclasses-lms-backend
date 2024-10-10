<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubscriptionPlan extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = "subscription_plans";
    protected $fillable = [
        'title',
        'description',
        'us_price',
        'uk_price',
        'type',
        'status',
        'is_locked',
        'stripe_plan_id'
    ];


    public function subscription()
    {
        return $this->hasMany(Subscription::class, 'planID', 'stripe_plan_id');
    }
}
