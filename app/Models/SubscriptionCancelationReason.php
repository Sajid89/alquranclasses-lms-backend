<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubscriptionCancelationReason extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = "subscription_cancelation_reasons";
    protected $fillable = ['created_by', 'reason'];

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function subscription()
    {
        return $this->hasMany(Subscription::class, 'cancelation_reason_id');
    }
}
