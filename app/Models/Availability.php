<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

class Availability extends Model
{
    use HasFactory;
    protected $fillable = ['available_type' , 'available_id'];

    public function available()
    {
        return $this->morphTo();
    }

    public function availabilitySlots()
    {
        return $this->hasMany(AvailabilitySlot::class, 'availability_id');
    }


}
