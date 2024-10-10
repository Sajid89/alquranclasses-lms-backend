<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class NotToCancelSubscriptionReason extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = "not_to_cancel_subscription_reasons";
    protected $fillable = ['created_by', 'reason'];

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
