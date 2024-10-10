<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StripeCard extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'customer_id',
        'stripe_card_id',
        'brand',
        'last4',
        'exp_month',
        'exp_year',
        'is_default',
    ];

    public function getExpDateAttribute()
    {
        return $this->exp_month . '/' . $this->exp_year;
    }
   
    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }
}
