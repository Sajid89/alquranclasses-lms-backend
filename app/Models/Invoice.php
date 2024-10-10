<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;
    // protected $timestamps = false;
    
    protected $fillable = [
        'subscription_id',
        'stripe_invoice_id',
        'invoice_date',
        'amount',
        'line_items'
    ];

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }
    

}
