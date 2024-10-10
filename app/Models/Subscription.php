<?php

namespace App\Models;

use App\Models\Invoice as ModelsInvoice;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;

class Subscription extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table="subscriptions";
    protected $fillable = ['user_id', 'student_id', 'payment_id','payer_id','sub_id', 'payment_status','planID', 'price', 'quantity', 'start_at', 'ends_at','payment_name', 'status'];
    protected $dates = ['start_at', 'ends_at'];

    public function user()
    {
        return $this->belongsTo(User::class,"user_id");
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function invoices() {
        return $this->hasMany(ModelsInvoice::class, 'subscription_id');
    }

    public function course() {
        return $this->hasOne(StudentCourse::class, 'subscription_id');
    }

    public function cancelationReason()
    {
        return $this->belongsTo(SubscriptionCancelationReason::class, 'cancelation_reason_id');
    }

    public function subscriptionPlan()
    {
        return $this->belongsTo(SubscriptionPlan::class, 'planID', 'stripe_plan_id');
    }

    /**
     * Get student subscription
     * @param $query
     * @param $column
     * @param $ID
     * @param string[] $select
     * @param array $relationship
     * @return mixed
     */
    public function ScopeById($query, $column, $ID, $select = ['*'], array $relationship = []) {

        return $query->select($select)->where($column, $ID)->with($relationship);
    }

    public function scopeFailedPaymentSum($query)
    {
        return $query->where('payment_status', 'Failed')->sum('price');
    }

}
